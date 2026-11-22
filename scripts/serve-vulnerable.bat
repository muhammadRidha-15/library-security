@echo off
rem ==============================================================================
rem Launcher Terisolasi Cabang Vulnerable (TKT-22 / PRD Guard)
rem Batasan Keras:
rem 1. HANYA mengikat pada loopback 127.0.0.1:8001.
rem 2. Menolak keras bind 0.0.0.0 atau IP eksternal.
rem 3. Menggunakan database terpisah database/vulnerable.sqlite.
rem ==============================================================================

set HOST=127.0.0.1
set PORT=8001

if not "%~1"=="" (
    if not "%~1"=="127.0.0.1" (
        echo [GUARD ERROR] Cabang vulnerable DILARANG mengikat ke host non-loopback: %~1
        echo Hanya boleh dijalankan di 127.0.0.1 demi keamanan laboratorium.
        exit /b 1
    )
)

if not "%~2"=="" (
    if not "%~2"=="8001" (
        echo [GUARD ERROR] Port wajib 8001 untuk isolasi demo. Diberikan: %~2
        exit /b 1
    )
)

echo ==========================================================
echo    MINI LIBRARY - MODE DEMO KERENTANAN (TKT-22)
echo ==========================================================
echo Host        : %HOST% (Strict Loopback)
echo Port        : %PORT%
echo Database    : database/vulnerable.sqlite
echo Session     : mini_library_vuln_session
echo Target Demo : SQLi (TKT-23), Stored XSS (TKT-24), IDOR (TKT-25)
echo ==========================================================

if not exist "database\vulnerable.sqlite" (
    echo Membuat database\vulnerable.sqlite...
    type nul > database\vulnerable.sqlite
    php artisan migrate --force
    php artisan db:seed --force
)

echo Menjalankan server demo rentan...
php artisan serve --host=%HOST% --port=%PORT%
