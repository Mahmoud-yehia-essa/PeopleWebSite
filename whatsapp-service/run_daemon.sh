#!/bin/bash
# ========================================================
# Self-Healing WhatsApp Daemon for WiseLook
# Automatically keeps WhatsApp running and restarts if killed
# ========================================================

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
LOG="$DIR/../storage/logs/whatsapp.log"

cd "$DIR"

echo "[$(date)] Starting WhatsApp Baileys Gateway Daemon..." >> "$LOG"

while true; do
    echo "[$(date)] Launching node server.js..." >> "$LOG"
    node server.js >> "$LOG" 2>&1
    EXIT_CODE=$?
    echo "[$(date)] node server.js exited with code $EXIT_CODE. Restarting in 3 seconds..." >> "$LOG"
    sleep 3
done
