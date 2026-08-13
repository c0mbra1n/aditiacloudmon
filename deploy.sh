#!/usr/bin/env bash

# ==============================================================================
# AditiaCloudMon — Interactive Deployment Menu Tools
# ==============================================================================

# Format Warna Terminal
GREEN='\033[0;32m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BOLD='\033[1m'
NC='\033[0m' # No Color

show_menu() {
    clear
    echo -e "${CYAN}=================================================="${NC}
    echo -e "${CYAN}${BOLD} 🛠️  ADITIACLOUDMON DEPLOYMENT MENU TOOLS"${NC}
    echo -e "${CYAN}=================================================="${NC}
    echo -e " ${GREEN}1)${NC} Full Deployment (Pull + Migrate + Build + Cache)"
    echo -e " ${GREEN}2)${NC} Git Pull Only (git pull origin main)"
    echo -e " ${GREEN}3)${NC} Run Database Migration (php artisan migrate --force)"
    echo -e " ${GREEN}4)${NC} Build Frontend Assets (npm run build)"
    echo -e " ${GREEN}5)${NC} Clear & Optimize Laravel Cache"
    echo -e " ${GREEN}6)${NC} Fix Folder Permissions (chmod storage & cache)"
    echo -e " ${RED}0)${NC} Keluar (Exit)"
    echo -e "${CYAN}=================================================="${NC}
}

full_deployment() {
    echo -e "\n${YELLOW}[1/6] Menarik pembaruan dari Git...${NC}"
    git pull origin main

    echo -e "\n${YELLOW}[2/6] Memperbarui Composer dependencies...${NC}"
    composer install --no-dev --optimize-autoloader --no-interaction

    echo -e "\n${YELLOW}[3/6] Menjalankan Database Migrations...${NC}"
    php artisan migrate --force

    echo -e "\n${YELLOW}[4/6] Mengkompilasi aset frontend...${NC}"
    npm run build

    echo -e "\n${YELLOW}[5/6] Mengoptimalkan cache Laravel...${NC}"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo -e "\n${YELLOW}[6/6] Memperbarui hak akses folder...${NC}"
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true

    echo -e "\n${GREEN}✓ Full Deployment Selesai!${NC}"
}

git_pull_only() {
    echo -e "\n${YELLOW}Menjalankan git pull origin main...${NC}"
    git pull origin main
    echo -e "${GREEN}✓ Git Pull Selesai!${NC}"
}

migrate_only() {
    echo -e "\n${YELLOW}Menjalankan php artisan migrate --force...${NC}"
    php artisan migrate --force
    echo -e "${GREEN}✓ Migration Selesai!${NC}"
}

build_only() {
    echo -e "\n${YELLOW}Menjalankan npm run build...${NC}"
    npm run build
    echo -e "${GREEN}✓ Frontend Build Selesai!${NC}"
}

clear_cache_only() {
    echo -e "\n${YELLOW}Membersihkan & mengoptimalkan cache Laravel...${NC}"
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear

    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo -e "${GREEN}✓ Cache Optimization Selesai!${NC}"
}

fix_permissions_only() {
    echo -e "\n${YELLOW}Memperbarui hak akses folder storage & bootstrap/cache...${NC}"
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    echo -e "${GREEN}✓ Permissions Fixed!${NC}"
}

# Main Loop Menu Interaktif
while true; do
    show_menu
    read -p "Pilih menu [0-6]: " choice
    case $choice in
        1)
            full_deployment
            read -p "Tekan [Enter] untuk kembali ke menu..."
            ;;
        2)
            git_pull_only
            read -p "Tekan [Enter] untuk kembali ke menu..."
            ;;
        3)
            migrate_only
            read -p "Tekan [Enter] untuk kembali ke menu..."
            ;;
        4)
            build_only
            read -p "Tekan [Enter] untuk kembali ke menu..."
            ;;
        5)
            clear_cache_only
            read -p "Tekan [Enter] untuk kembali ke menu..."
            ;;
        6)
            fix_permissions_only
            read -p "Tekan [Enter] untuk kembali ke menu..."
            ;;
        0)
            echo -e "\n${CYAN}Terima kasih! Keluar dari Deployment Menu Tools.${NC}"
            exit 0
            ;;
        *)
            echo -e "\n${RED}Pilihan tidak valid. Harap pilih angka 0 - 6.${NC}"
            sleep 2
            ;;
    esac
done
