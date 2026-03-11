@echo off
setlocal EnableDelayedExpansion

:: ─────────────────────────────────────────
::  Dev Script — CI4 + Tailwind CSS (Windows)
::  Usage: dev.bat [port]
::  Default port: 8080
:: ─────────────────────────────────────────

set PORT=%~1
if "%PORT%"=="" set PORT=8080

set CSS_INPUT=.\app\Views\css\input.css
set CSS_OUTPUT=.\public\assets\css\app.css
set CSS_DIR=.\public\assets\css

echo.
echo +======================================+
echo ^|     CI4 + Tailwind Dev Server        ^|
echo +======================================+
echo.

:: ─── Cek dependency ───
echo [1/4] Mengecek dependency...

where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [X] PHP tidak ditemukan. Pastikan PHP sudah terinstall dan ada di PATH.
    pause
    exit /b 1
)
for /f "tokens=*" %%i in ('php -r "echo PHP_VERSION;"') do echo [OK] PHP %%i

where npx >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [X] npx tidak ditemukan. Pastikan Node.js sudah terinstall.
    pause
    exit /b 1
)
for /f "tokens=*" %%i in ('node -v') do echo [OK] Node %%i

if not exist "%CSS_INPUT%" (
    echo [X] File input CSS tidak ditemukan: %CSS_INPUT%
    pause
    exit /b 1
)
echo [OK] File CSS input ditemukan

:: ─── Kill port yang sudah dipakai ───
echo.
echo [2/4] Membersihkan port %PORT%...
for /f "tokens=5" %%a in ('netstat -aon ^| findstr ":%PORT% " ^| findstr "LISTENING"') do (
    echo [!] Port %PORT% dipakai oleh PID %%a. Membersihkan...
    taskkill /PID %%a /F >nul 2>&1
    echo [OK] Port %PORT% berhasil dibebaskan
)

:: ─── Kill proses lama ───
echo.
echo [3/4] Membersihkan proses lama...
taskkill /F /IM "php.exe" /FI "WINDOWTITLE eq spark*" >nul 2>&1
echo [OK] Proses lama dibersihkan

:: ─── Buat direktori output CSS ───
if not exist "%CSS_DIR%" (
    mkdir "%CSS_DIR%"
    echo [OK] Direktori %CSS_DIR% dibuat
)

:: ─── Build CSS awal ───
echo.
echo [4/4] Build CSS awal...
call npx @tailwindcss/cli -i %CSS_INPUT% -o %CSS_OUTPUT%
if %ERRORLEVEL% neq 0 (
    echo [X] Build CSS awal gagal.
    pause
    exit /b 1
)
echo [OK] CSS berhasil di-build

:: ─── Jalankan semua proses ───
echo.
echo [OK] Menjalankan server di http://localhost:%PORT%
echo [!] Tutup kedua window yang terbuka untuk menghentikan server
echo.

:: Tailwind watch di window terpisah
start "Tailwind Watch" cmd /k "npx @tailwindcss/cli -i %CSS_INPUT% -o %CSS_OUTPUT% --watch"

:: PHP Spark di window terpisah
start "PHP Spark" cmd /k "php spark serve --port=%PORT%"

echo [OK] Tailwind watch dan PHP Spark berjalan di window terpisah.
echo [!] Tutup window Tailwind Watch dan PHP Spark untuk menghentikan.
echo.
pause