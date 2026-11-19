#!/usr/bin/env bash
# ==============================================================================
# Launcher Terisolasi Cabang Vulnerable (TKT-22 / PRD Guard)
# Batasan Keras:
# 1. HANYA mengikat pada loopback 127.0.0.1:8001.
# 2. Menolak keras bind 0.0.0.0 atau IP eksternal.
# 3. Menggunakan database terpisah database/vulnerable.sqlite.
# ==============================================================================

set -euo pipefail

HOST="127.0.0.1"
PORT="8001"

if [ "${1:-}" != "" ] && [ "$1" != "127.0.0.1" ]; then
    echo "[GUARD ERROR] Cabang vulnerable DILARANG mengikat ke host non-loopback: $1" >&2
    echo "Hanya boleh dijalankan di 127.0.0.1 demi keamanan laboratorium." >&2
    exit 1
fi

if [ "${2:-}" != "" ] && [ "$2" != "8001" ]; then
    echo "[GUARD ERROR] Port wajib 8001 untuk isolasi demo. Diberikan: $2" >&2
    exit 1
fi

echo "=========================================================="
echo "   MINI LIBRARY — MODE DEMO KERENTANAN (TKT-22)"
echo "=========================================================="
echo "Host        : $HOST (Strict Loopback)"
echo "Port        : $PORT"
echo "Database    : database/vulnerable.sqlite"
echo "Session     : mini_library_vuln_session"
echo "Target Demo : SQLi (TKT-23), Stored XSS (TKT-24), IDOR (TKT-25)"
echo "=========================================================="

if [ ! -f "database/vulnerable.sqlite" ]; then
    echo "Membuat database/vulnerable.sqlite..."
    touch database/vulnerable.sqlite
    php artisan migrate --force
    php artisan db:seed --force
fi

echo "Menjalankan server demo rentan..."
exec php artisan serve --host="$HOST" --port="$PORT"
