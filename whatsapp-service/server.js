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
let connectionStatus = 'initializing'; // initializing, waiting_for_scan, connected, disconnected
let connectedPhone = null;
let connectedName = null;
let isReconnecting = false;

const logger = pino({ level: 'silent' });

async function initWhatsApp(forceNew = false) {
    if (isReconnecting) return;
    isReconnecting = true;

    try {
        if (forceNew && fs.existsSync(AUTH_DIR)) {
            try {
                fs.rmSync(AUTH_DIR, { recursive: true, force: true });
                fs.mkdirSync(AUTH_DIR, { recursive: true });
            } catch (e) {
                console.error('Error clearing auth dir:', e);
            }
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
            browser: ['WiseLook Admin', 'Chrome', '1.0.0'],
            connectTimeoutMs: 60000,
            defaultQueryTimeoutMs: 60000,
            keepAliveIntervalMs: 25000,
            emitOwnEvents: false,
            markOnlineOnConnect: true,
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                try {
                    currentQr = await qrcode.toDataURL(qr);
                    connectionStatus = 'waiting_for_scan';
                    console.log('[WhatsApp] New QR code generated');
                } catch (err) {
                    console.error('[WhatsApp] QR generate error:', err);
                }
            }

            if (connection === 'open') {
                connectionStatus = 'connected';
                currentQr = null;
                currentPairingCode = null;

                const rawId = sock.user?.id || '';
                connectedPhone = rawId.split(':')[0] || rawId.split('@')[0] || 'Unknown';
                connectedName = sock.user?.name || 'WiseLook Admin';

                console.log(`[WhatsApp] Connected successfully as ${connectedName} (${connectedPhone})`);
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

                console.log(`[WhatsApp] Connection closed. Status: ${statusCode}. Reconnecting: ${shouldReconnect}`);

                connectionStatus = 'disconnected';
                connectedPhone = null;
                connectedName = null;

                if (statusCode === DisconnectReason.loggedOut) {
                    currentQr = null;
                    currentPairingCode = null;
                    console.log('[WhatsApp] Logged out. Clearing credentials...');
                    try {
                        fs.rmSync(AUTH_DIR, { recursive: true, force: true });
                        fs.mkdirSync(AUTH_DIR, { recursive: true });
                    } catch (e) {}
                    isReconnecting = false;
                    setTimeout(() => initWhatsApp(false), 2000);
                } else if (shouldReconnect) {
                    isReconnecting = false;
                    setTimeout(() => initWhatsApp(false), 3000);
                } else {
                    isReconnecting = false;
                }
            }
        });
    } catch (err) {
        console.error('[WhatsApp] Initialization error:', err);
        connectionStatus = 'disconnected';
    } finally {
        isReconnecting = false;
    }
}

// 1. Get Live Status & QR Code
app.get('/status', (req, res) => {
    res.json({
        success: true,
        status: connectionStatus,
        is_connected: connectionStatus === 'connected',
        phone: connectedPhone,
        name: connectedName,
        qr: currentQr,
        pairing_code: currentPairingCode,
        timestamp: new Date().toISOString()
    });
});

// 2. Request a Pairing Code for phone number
app.post('/pairing-code', async (req, res) => {
    try {
        let phone = req.body.phone;
        if (!phone) {
            return res.status(400).json({ success: false, message: 'Phone number is required' });
        }

        phone = phone.toString().replace(/[^0-9]/g, '');

        if (!sock) {
            await initWhatsApp();
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

// 3. Send WhatsApp Text Message
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
                message: 'WhatsApp is not connected. Please scan the QR code first.'
            });
        }

        // Clean phone number (remove +, spaces, leading zeros if international)
        let cleanPhone = phone.toString().replace(/[^0-9]/g, '');
        if (cleanPhone.startsWith('00')) {
            cleanPhone = cleanPhone.substring(2);
        }

        const jid = `${cleanPhone}@s.whatsapp.net`;

        // Send message via Baileys
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

// 4. Disconnect / Logout
app.post('/logout', async (req, res) => {
    try {
        if (sock) {
            try {
                await sock.logout();
            } catch (e) {}
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

        setTimeout(() => initWhatsApp(true), 1500);

        res.json({
            success: true,
            message: 'WhatsApp session logged out and reset successfully'
        });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// 5. Restart / Refresh QR
app.post('/restart', async (req, res) => {
    try {
        await initWhatsApp(true);
        res.json({
            success: true,
            message: 'WhatsApp service restarted'
        });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Start Express server and initialize WhatsApp
app.listen(PORT, '127.0.0.1', () => {
    console.log(`[WhatsApp Service] Server running on http://127.0.0.1:${PORT}`);
    initWhatsApp();
});
