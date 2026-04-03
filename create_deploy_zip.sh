#!/bin/bash

#--------------------------------------------------------------------
# Script untuk membuat file ZIP deployment ke cPanel
# Project: Sistem Perjalanan Dinas (Perjadin) Polsri
# Target: Subdomain perjadin.polsri.ac.id
#--------------------------------------------------------------------

echo "========================================"
echo "Deployment ZIP Generator"
echo "Project: Perjadin Polsri"
echo "========================================"
echo ""

# Set variables
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Output files
ZIP_APP="$PROJECT_DIR/deploy_perjadin-app.zip"
ZIP_PUBLIC="$PROJECT_DIR/deploy_perjadin_public.zip"

# Remove old zip files if exist
rm -f "$ZIP_APP" "$ZIP_PUBLIC"

echo "📁 Project Directory: $PROJECT_DIR"
echo "📦 Creating deployment packages..."
echo ""

#--------------------------------------------------------------------
# 1. Create perjadin-app.zip (untuk upload ke home directory)
#--------------------------------------------------------------------
echo "🔷 Creating perjadin-app.zip..."

cd "$PROJECT_DIR"

# Create temp directory for app files
TEMP_APP="$PROJECT_DIR/temp_perjadin-app"
rm -rf "$TEMP_APP"
mkdir -p "$TEMP_APP/perjadin-app"

# Copy application files
echo "   Copying framework folders (app, vendor, writable)..."
cp -r app "$TEMP_APP/perjadin-app/"
cp -r vendor "$TEMP_APP/perjadin-app/"
cp -r writable "$TEMP_APP/perjadin-app/"

echo "   Copying other necessary files..."
cp composer.json "$TEMP_APP/perjadin-app/" 2>/dev/null || true
cp preload.php "$TEMP_APP/perjadin-app/" 2>/dev/null || true
cp spark "$TEMP_APP/perjadin-app/" 2>/dev/null || true

# Ambil configurasi khusus production (cPanel) dari folder deploy
cp "$PROJECT_DIR/deploy/perjadin-app/app/Config/Paths.php" "$TEMP_APP/perjadin-app/app/Config/Paths.php"
cp "$PROJECT_DIR/deploy/perjadin-app/.env" "$TEMP_APP/perjadin-app/.env"

# Ensure writable directories exist and are empty of sensitive data
rm -rf "$TEMP_APP/perjadin-app/writable/cache/"* 2>/dev/null || true
rm -rf "$TEMP_APP/perjadin-app/writable/debugbar/"* 2>/dev/null || true
rm -rf "$TEMP_APP/perjadin-app/writable/logs/"* 2>/dev/null || true
rm -rf "$TEMP_APP/perjadin-app/writable/session/"* 2>/dev/null || true

# Keep directory structure with .gitkeep
touch "$TEMP_APP/perjadin-app/writable/cache/.gitkeep"
touch "$TEMP_APP/perjadin-app/writable/debugbar/.gitkeep"
touch "$TEMP_APP/perjadin-app/writable/logs/.gitkeep"
touch "$TEMP_APP/perjadin-app/writable/session/.gitkeep"
touch "$TEMP_APP/perjadin-app/writable/uploads/.gitkeep"

# Create zip
cd "$TEMP_APP"
zip -rq "$ZIP_APP" perjadin-app

# Cleanup
rm -rf "$TEMP_APP"

echo "   ✅ Created: deploy_perjadin-app.zip"
echo ""


#--------------------------------------------------------------------
# 2. Create perjadin_public.zip (untuk upload ke document root subdomain)
#--------------------------------------------------------------------
echo "🔷 Creating perjadin_public.zip..."

cd "$PROJECT_DIR"

# Create temp directory for public files
TEMP_PUBLIC="$PROJECT_DIR/temp_perjadin_public"
rm -rf "$TEMP_PUBLIC"
mkdir -p "$TEMP_PUBLIC"

# Copy public folder contents
echo "   Copying public folder contents..."
cp -r public/* "$TEMP_PUBLIC/" 2>/dev/null || true
cp public/.htaccess "$TEMP_PUBLIC/" 2>/dev/null || true

# Inject RewriteBase for subdirectory deployment (PENTING AGAR ROUTING CI4 JALAN)
sed -i 's|# RewriteBase /|RewriteBase /perjadin/|g' "$TEMP_PUBLIC/.htaccess"

# Ambil index.php khusus production (cPanel) dari folder deploy
cp "$PROJECT_DIR/deploy/perjadin_public/index.php" "$TEMP_PUBLIC/index.php"

# Remove unnecessary lock files
rm -f "$TEMP_PUBLIC/.~lock."* 2>/dev/null || true

# Create zip
cd "$TEMP_PUBLIC"
zip -rq "$ZIP_PUBLIC" .

# Cleanup
rm -rf "$TEMP_PUBLIC"

echo "   ✅ Created: deploy_perjadin_public.zip"
echo ""

#--------------------------------------------------------------------
# Summary
#--------------------------------------------------------------------
echo "========================================"
echo "✅ Deployment packages created!"
echo "========================================"
echo ""
echo "📦 Panduan ZIP yang Dihasilkan:"
echo ""
echo "   1. deploy_perjadin-app.zip"
echo "      → Posisinya harus tidak dapat diakses publik demi keamanan."
echo "      → Upload dan extract zip ini ke home/root directory cPanel Anda,"
echo "        ( HARUS TEPAT DI: /home/polsripayop/perjadin-app/ )"
echo "      ✅ File .env & Paths.php di dalamnya sudah otomatis dikonfigurasi!"
echo ""
echo "   2. deploy_perjadin_public.zip"  
echo "      → Upload dan extract zip ini ke DADALAM folder public_html/perjadin/"
echo "        ( HARUS TEPAT DI: /home/polsripayop/public_html/perjadin/ )"
echo "      ✅ File index.php otomatis menavigasi dua folder ke atas untuk mencari perjadin-app!"
echo ""
echo "📝 Ukuran File:"
ls -lh "$ZIP_APP" "$ZIP_PUBLIC" 2>/dev/null | awk '{print "   " $9 ": " $5}'
echo "========================================"
