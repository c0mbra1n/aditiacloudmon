#!/usr/bin/env bash

# ==============================================================================
# AditiaCloudMon — Automated VPS Production Deployment Script
# ==============================================================================
# Skrip ini mengotomatiskan git pull, composer install, database migration,
# frontend asset build, clearing & caching Laravel, serta perbaikan permission.
# ==============================================================================

set -e

# Format Warna Terminal
GREEN='\033[0;32m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${CYAN}=================================================="${NC}
echo -e "${CYAN} 🚀 Memulai Otomatisasi Deployment AditiaCloudMon"${NC}
echo -e "${CYAN}=================================================="${NC}

# 1. Tarik pembaruan kode terbaru dari GitHub
echo -e "${YELLOW}[1/6] Menarik pembaruan kode dari Git (git pull)..."${NC}
git pull origin main

# 2. Install / Update PHP Dependencies via Composer
echo -e "${YELLOW}[2/6] Memperbarui dependency PHP (composer install)..."${NC}
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Jalankan Database Migrations
echo -e "${YELLOW}[3/6] Menjalankan Database Migrations (php artisan migrate)..."${NC}
php artisan migrate --force

# 4. Build Frontend Production Assets (Vite / Tailwind)
echo -e "${YELLOW}[4/6] Mengkompilasi aset frontend (npm run build)..."${NC}
if [ -f "package-lock.json" ]; then
    npm ci --no-audit
else
    npm install --no-audit
fi
npm run build

# 5. Membersihkan & Mengoptimalkan Cache Laravel
echo -e "${YELLOW}[5/6] Mengoptimalkan cache Laravel..."${NC}
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Perbaiki Hak Akses Folder Storage & Cache
echo -e "${YELLOW}[6/6] Memperbarui hak akses folder storage & bootstrap/cache..."${NC}
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo -e "${GREEN}=================================================="${NC}
echo -e "${GREEN} ✓ DEPLOYMENT BERHASIL SELESAI! "${NC}
echo -e "${GREEN}=================================================="${NC}
