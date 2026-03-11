@echo off
setlocal EnableDelayedExpansion

:: ─────────────────────────────────────────
::  Build Script — CI4 + Tailwind CSS (Windows)
::  Usage: build.bat
:: ─────────────────────────────────────────

set CSS_INPUT=.\app\Views\css\input.css
set CSS_OUTPUT=.\public\assets\css\app.css
set CSS_DIR=.\public\assets\css

echo.
echo +======================================+
echo ^|       CI4 + Tailwind Build           ^|
echo +======================================+
echo.

:: ─── Cek dependency ───
echo [1/4] Mengecek dependency...

where php >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [X] PHP tidak ditemukan.
    pause
    exit /b 1
)
for /f "tokens=*" %%i in ('php -r "echo PHP_VERSION;"') do echo [OK] PHP %%i

where composer >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [X] Composer tidak ditemukan.
    pause
    exit /b 1
)
echo [OK] Composer ditemukan

where npx >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [X] npx tidak ditemukan.
    pause
    exit /b 1
)
for /f "tokens=*" %%i in ('node -v') do echo [OK] Node %%i

:: ─── Composer install ───
echo.
echo [2/4] Menjalankan composer install...
call composer install --optimize-autoloader --no-interaction

if %ERRORLEVEL% neq 0 (
    echo [X] Composer install gagal.
    pause
    exit /b 1
)
echo [OK] Composer install selesai

:: ─── Build Tailwind CSS ───
echo.
echo [3/4] Build Tailwind CSS (minified)...
if not exist "%CSS_DIR%" mkdir "%CSS_DIR%"

call npx @tailwindcss/cli -i %CSS_INPUT% -o %CSS_OUTPUT% --minify

if %ERRORLEVEL% neq 0 (
    echo [X] Build CSS gagal.
    pause
    exit /b 1
)
echo [OK] CSS berhasil di-build → %CSS_OUTPUT%

:: ─── Migrate ───
echo.
echo [4/4] Menjalankan database migration...
call php spark migrate --all

if %ERRORLEVEL% neq 0 (
    echo [!] Migration gagal atau tidak ada migration baru. Melanjutkan...
) else (
    echo [OK] Migration selesai
)

echo.
echo [OK] Build selesai!
echo.
pause