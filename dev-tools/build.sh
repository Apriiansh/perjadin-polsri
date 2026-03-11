#!/bin/bash

# ─────────────────────────────────────────
#  Build Script — CI4 + Tailwind CSS
#  Usage: bash build.sh
# ─────────────────────────────────────────

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
echo "║       CI4 + Tailwind Build           ║"
echo "╚══════════════════════════════════════╝"
echo -e "${RESET}"

# ─── Cek dependency ───
echo -e "${CYAN}[1/3] Mengecek dependency...${RESET}"

if ! command -v php &> /dev/null; then
    echo -e "${RED}✗ PHP tidak ditemukan.${RESET}"
    exit 1
fi
echo -e "${GREEN}✓ PHP $(php -r 'echo PHP_VERSION;')${RESET}"

if ! command -v composer &> /dev/null; then
    echo -e "${RED}✗ Composer tidak ditemukan.${RESET}"
    exit 1
fi
echo -e "${GREEN}✓ Composer $(composer --version --no-ansi | head -1)${RESET}"

if ! command -v npx &> /dev/null; then
    echo -e "${RED}✗ npx tidak ditemukan.${RESET}"
    exit 1
fi
echo -e "${GREEN}✓ Node $(node -v)${RESET}"

# ─── Composer install ───
echo -e "\n${CYAN}[2/3] Menjalankan composer install...${RESET}"
composer install --optimize-autoloader --no-interaction

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Composer install gagal.${RESET}"
    exit 1
fi
echo -e "${GREEN}✓ Composer install selesai${RESET}"

# ─── Build Tailwind CSS (minified) ───
echo -e "\n${CYAN}[3/3] Build Tailwind CSS (minified)...${RESET}"
mkdir -p $CSS_DIR
npx @tailwindcss/cli -i $CSS_INPUT -o $CSS_OUTPUT --minify

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Build CSS gagal.${RESET}"
    exit 1
fi

CSS_SIZE=$(du -sh $CSS_OUTPUT 2>/dev/null | cut -f1)
echo -e "${GREEN}✓ CSS berhasil di-build → $CSS_OUTPUT ($CSS_SIZE)${RESET}"

# ─── Migrate ───
echo -e "\n${CYAN}[4/3] Menjalankan database migration...${RESET}"
php spark migrate --all

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠ Migration gagal atau tidak ada migration baru. Melanjutkan...${RESET}"
else
    echo -e "${GREEN}✓ Migration selesai${RESET}"
fi

echo -e "\n${GREEN}🎉 Build selesai!${RESET}\n"