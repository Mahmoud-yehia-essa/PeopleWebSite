const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const fs = require('fs');
const path = require('path');
const pino = require('pino');
const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    delay
} = require('@whiskeysockets/baileys');

// Prevent any unhandled error from crashing the Node.js process
process.on('uncaughtException', (err) => {
    console.error('[WhatsApp Uncaught Exception]', err.message || err);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('[WhatsApp Unhandled Rejection]', reason);
});

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 3001;
const AUTH_DIR = path.join(__dirname, 'auth_info');

if (!fs.existsSync(AUTH_DIR)) {
    fs.mkdirSync(AUTH_DIR, { recursive: true });
}

let sock = null;
let currentQr = null;
let currentPairingCode = null;
let connectionStatus = 'initializing'; // initializing, waiting_for_scan, reconnecting, connected, disconnected
let connectedPhone = null;
let connectedName = null;
let isInitializing = false;
let reconnectAttempts = 0;
const MAX_RECONNECT_ATTEMPTS = 50;

const logger = pino({ level: 'silent' });

// Check existing credentials on disk
function checkExistingCreds() {
    try {
        const credsPath = path.join(AUTH_DIR, 'creds.json');
        if (fs.existsSync(credsPath)) {
            const raw = fs.readFileSync(credsPath, 'utf8');
            const parsed = JSON.parse(raw);
            if (parsed && parsed.me && parsed.me.id) {
                const rawId = parsed.me.id;
                connectedPhone = rawId.split(':')[0] || rawId.split('@')[0];
                connectedName = parsed.me.name || 'WiseLook WhatsApp';
                console.log(`[WhatsApp] Found existing session for: ${connectedName} (${connectedPhone})`);
                return true;
            }
        }
    } catch (e) {
        console.error('[WhatsApp] Error reading creds.json:', e.message);
    }
    return false;
}

// Check initial session
checkExistingCreds();

async function initWhatsApp(forceNew = false) {
    if (isInitializing) {
        console.log('[WhatsApp] Already initializing, skipping duplicate call');
        return;
    }
    isInitializing = true;

    try {
        // Clean up previous socket if exists
        if (sock) {
            try {
                sock.ev.removeAllListeners();
                sock.ws?.close();
            } catch (e) {}
            sock = null;
        }

        if (forceNew) {
            console.log('[WhatsApp] Force new session requested. Clearing auth folder...');
            try {
                if (fs.existsSync(AUTH_DIR)) {
                    fs.rmSync(AUTH_DIR, { recursive: true, force: true });
                    fs.mkdirSync(AUTH_DIR, { recursive: true });
                }
            } catch (e) {
                console.error('Error clearing auth dir:', e);
            }
            connectedPhone = null;
            connectedName = null;
            currentQr = null;
            currentPairingCode = null;
        }

        const hasSavedCreds = checkExistingCreds();
        if (hasSavedCreds) {
            connectionStatus = 'reconnecting';
        } else {
            connectionStatus = 'initializing';
        }

        const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
        const { version } = await fetchLatestBaileysVersion();

        sock = makeWASocket({
            version,
            logger,
            printQRInTerminal: false,
            auth: {
                creds: state.creds,
                keys: makeCacheableSignalKeyStore(state.keys, logger),
            },
            browser: ['WiseLook Gateway', 'Chrome', '120.0.0'],
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            keepAliveIntervalMs: 25000,
            emitOwnEvents: false,
            markOnlineOnConnect: true,
            retryRequestDelayMs: 3000,
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                try {
                    currentQr = await qrcode.toDataURL(qr);
                    connectionStatus = 'waiting_for_scan';
                    console.log('[WhatsApp] New QR code generated and ready for scan');
                } catch (err) {
                    console.error('[WhatsApp] QR generate error:', err);
                }
            }

            if (connection === 'open') {
                reconnectAttempts = 0;
                connectionStatus = 'connected';
                currentQr = null;
                currentPairingCode = null;

                const rawId = sock.user?.id || '';
                connectedPhone = rawId.split(':')[0] || rawId.split('@')[0] || connectedPhone || 'Unknown';
                connectedName = sock.user?.name || connectedName || 'WiseLook Admin';

                console.log(`[WhatsApp] Connected successfully as ${connectedName} (+${connectedPhone})`);
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const errorMsg = lastDisconnect?.error?.message || 'Connection lost';
                const isLoggedOut = statusCode === DisconnectReason.loggedOut;

                console.log(`[WhatsApp] Connection closed (Code: ${statusCode}, Reason: ${errorMsg})`);

                if (isLoggedOut) {
                    console.log('[WhatsApp] Logged out from WhatsApp on phone. Resetting session...');
                    connectionStatus = 'disconnected';
                    connectedPhone = null;
                    connectedName = null;
                    currentQr = null;
                    currentPairingCode = null;

                    try {
                        fs.rmSync(AUTH_DIR, { recursive: true, force: true });
                        fs.mkdirSync(AUTH_DIR, { recursive: true });
                    } catch (e) {}

                    isInitializing = false;
                    setTimeout(() => initWhatsApp(false), 2000);
                } else {
                    // Safe auto-reconnection without wiping credentials
                    reconnectAttempts++;
                    connectionStatus = 'reconnecting';
                    console.log(`[WhatsApp] Reconnecting attempt #${reconnectAttempts}...`);

                    isInitializing = false;
                    const delayMs = statusCode === DisconnectReason.restartRequired ? 1000 : 3500;
                    setTimeout(() => initWhatsApp(false), delayMs);
                }
            }
        });
    } catch (err) {
        console.error('[WhatsApp] Initialization error:', err.message || err);
        connectionStatus = 'disconnected';
    } finally {
        isInitializing = false;
    }
}

// 1. Live Status Endpoint
app.get('/status', (req, res) => {
    // If not connected and we have saved creds, status is reconnecting
    const hasCreds = Boolean(connectedPhone) || fs.existsSync(path.join(AUTH_DIR, 'creds.json'));

    res.json({
        success: true,
        online: true,
        status: connectionStatus,
        is_connected: connectionStatus === 'connected',
        has_session: hasCreds,
        phone: connectedPhone,
        name: connectedName,
        qr: currentQr,
        pairing_code: currentPairingCode,
        reconnect_attempts: reconnectAttempts,
        timestamp: new Date().toISOString()
    });
});

// 2. Request Pairing Code
app.post('/pairing-code', async (req, res) => {
    try {
        let phone = req.body.phone;
        if (!phone) {
            return res.status(400).json({ success: false, message: 'Phone number is required' });
        }

        phone = phone.toString().replace(/[^0-9]/g, '');

        if (!sock) {
            await initWhatsApp(false);
            await delay(2000);
        }

        if (connectionStatus === 'connected') {
            return res.status(400).json({ success: false, message: 'WhatsApp is already connected' });
        }

        const code = await sock.requestPairingCode(phone);
        currentPairingCode = code;

        res.json({
            success: true,
            pairing_code: code,
            message: 'Pairing code generated successfully'
        });
    } catch (err) {
        console.error('Pairing code error:', err);
        res.status(500).json({ success: false, message: err.message || 'Failed to generate pairing code' });
    }
});

// 3. Send Message
app.post('/send', async (req, res) => {
    try {
        let { phone, message } = req.body;

        if (!phone || !message) {
            return res.status(400).json({
                success: false,
                message: 'Both phone and message fields are required'
            });
        }

        if (connectionStatus !== 'connected' || !sock) {
            return res.status(503).json({
                success: false,
                status: connectionStatus,
                message: 'WhatsApp is not connected yet. Current status: ' + connectionStatus
            });
        }

        let cleanPhone = phone.toString().replace(/[^0-9]/g, '');
        if (cleanPhone.startsWith('00')) {
            cleanPhone = cleanPhone.substring(2);
        }

        const jid = `${cleanPhone}@s.whatsapp.net`;

        const result = await sock.sendMessage(jid, { text: message.trim() });

        res.json({
            success: true,
            message: 'Message sent successfully',
            message_id: result?.key?.id || null,
            recipient: cleanPhone
        });
    } catch (err) {
        console.error('Send message error:', err);
        res.status(500).json({
            success: false,
            message: err.message || 'Failed to send WhatsApp message'
        });
    }
});

// 4. Logout / Disconnect explicitly
app.post('/logout', async (req, res) => {
    try {
        if (sock) {
            try {
                await sock.logout();
            } catch (e) {}
            try {
                sock.ev.removeAllListeners();
                sock.ws?.close();
            } catch (e) {}
            sock = null;
        }

        try {
            fs.rmSync(AUTH_DIR, { recursive: true, force: true });
            fs.mkdirSync(AUTH_DIR, { recursive: true });
        } catch (e) {}

        connectionStatus = 'disconnected';
        connectedPhone = null;
        connectedName = null;
        currentQr = null;
        currentPairingCode = null;
        reconnectAttempts = 0;

        setTimeout(() => initWhatsApp(true), 1500);

        res.json({
            success: true,
            message: 'WhatsApp session logged out and reset successfully'
        });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// 5. Restart Engine
app.post('/restart', async (req, res) => {
    try {
        await initWhatsApp(false);
        res.json({
            success: true,
            message: 'WhatsApp connection restarted'
        });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Start Express HTTP Server
app.listen(PORT, '127.0.0.1', () => {
    console.log(`[WhatsApp Service] Server running on http://127.0.0.1:${PORT}`);
    initWhatsApp(false);
});
