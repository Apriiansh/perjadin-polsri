#!/bin/bash

# ─────────────────────────────────────────
#  Dev Script — CI4 + Tailwind CSS
#  Usage: bash dev.sh [port]
#  Default port: 8080
# ─────────────────────────────────────────

PORT=${1:-8081}
CSS_INPUT="./app/Views/css/input.css"
CSS_OUTPUT="./public/assets/css/app.css"
CSS_DIR="./public/assets/css"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
RESET='\033[0m'

echo -e "${CYAN}"
echo "╔══════════════════════════════════════╗"
echo "║       CI4 + Tailwind Dev Server      ║"
echo "╚══════════════════════════════════════╝"
echo -e "${RESET}"

# ─── Kill proses di port yang sudah ada ───
kill_port() {
    local port=$1
    local pids=$(lsof -ti tcp:$port 2>/dev/null)
    if [ -n "$pids" ]; then
        echo -e "${YELLOW}⚠ Port $port sudah dipakai. Membersihkan...${RESET}"
        echo "$pids" | xargs kill -9 2>/dev/null
        sleep 1
        echo -e "${GREEN}✓ Port $port berhasil dibebaskan${RESET}"
    else
        echo -e "${GREEN}✓ Port $port kosong${RESET}"
    fi
}

# ─── Kill proses lama yang mungkin masih jalan ───
echo -e "\n${CYAN}[1/4] Membersihkan proses lama...${RESET}"
kill_port $PORT

# Kill tailwind watch yang mungkin masih jalan
pkill -f "tailwindcss.*--watch" 2>/dev/null && echo -e "${YELLOW}⚠ Tailwind watch lama dihentikan${RESET}"

# Kill php spark yang mungkin masih jalan
pkill -f "php spark serve" 2>/dev/null && echo -e "${YELLOW}⚠ PHP Spark lama dihentikan${RESET}"

sleep 1

# ─── Buat direktori output CSS jika belum ada ───
echo -e "\n${CYAN}[2/4] Menyiapkan direktori...${RESET}"
mkdir -p $CSS_DIR
echo -e "${GREEN}✓ $CSS_DIR siap${RESET}"

# ─── Cek dependency ───
echo -e "\n${CYAN}[3/4] Mengecek dependency...${RESET}"

if ! command -v php &> /dev/null; then
    echo -e "${RED}✗ PHP tidak ditemukan. Pastikan PHP sudah terinstall.${RESET}"
    exit 1
fi
echo -e "${GREEN}✓ PHP $(php -r 'echo PHP_VERSION;')${RESET}"

if ! command -v npx &> /dev/null; then
    echo -e "${RED}✗ npx tidak ditemukan. Pastikan Node.js sudah terinstall.${RESET}"
    exit 1
fi
echo -e "${GREEN}✓ Node $(node -v)${RESET}"

if [ ! -f "$CSS_INPUT" ]; then
    echo -e "${RED}✗ File input CSS tidak ditemukan: $CSS_INPUT${RESET}"
    exit 1
fi
echo -e "${GREEN}✓ File CSS input ditemukan${RESET}"

# ─── Build CSS awal sebelum watch ───
echo -e "\n${CYAN}[4/4] Build CSS awal...${RESET}"
npx @tailwindcss/cli -i $CSS_INPUT -o $CSS_OUTPUT 2>/dev/null
echo -e "${GREEN}✓ CSS berhasil di-build${RESET}"

# ─── Jalankan semua proses ───
echo -e "\n${GREEN}🚀 Menjalankan server di http://localhost:${PORT}${RESET}"
echo -e "${YELLOW}   Tekan Ctrl+C untuk menghentikan semua proses\n${RESET}"

# Trap Ctrl+C → kill semua child process (flag agar tidak double trigger)
CLEANING=0
cleanup() {
    if [ $CLEANING -eq 1 ]; then return; fi
    CLEANING=1
    echo -e "\n${YELLOW}⏹  Menghentikan semua proses...${RESET}"
    kill -- -$$ 2>/dev/null
    wait
    echo -e "${GREEN}✓ Semua proses dihentikan. Sampai jumpa!${RESET}"
    exit 0
}
trap cleanup INT TERM

# Jalankan tailwind watch di background
npx @tailwindcss/cli -i $CSS_INPUT -o $CSS_OUTPUT --watch 2>&1 | \
    sed 's/^/[tailwind] /' &
TAILWIND_PID=$!

# Jalankan php spark serve di background
php spark serve --port=$PORT 2>&1 | \
    sed 's/^/[spark]    /' &
SPARK_PID=$!

# Tunggu semua proses
wait $TAILWIND_PID $SPARK_PID