#!/bin/bash
#
# PteroBilling - One-Line Installer
# Supports: Ubuntu 22.04/24.04, Debian 11/12, CentOS 7/8/9
#
# Usage: curl -sL https://raw.githubusercontent.com/itriedcoding/PteroBilling/main/scripts/install.sh | sudo bash
#

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

PANEL_URL=""
PTERO_URL=""
DB_NAME="pterobilling"
DB_USER="pterobilling"
DB_PASS=""
APP_KEY=""
INSTALL_DIR="/var/www/pterobilling"

print_banner() {
    echo -e "${CYAN}"
    echo "  ____   ____  _____                       _ "
    echo " |  _ \ |  _ \|  ___| ___  _ __   ___  __| |"
    echo " | |_) || |_) | |_   / _ \| '_ \ / _ \/ _| |"
    echo " |  __/ |  __/|  _| | (_) | | | |  __/ (_| |"
    echo " |_|    |_|   |_|    \___/|_| |_|\___|\__,_|"
    echo -e "${NC}"
    echo -e "  ${GREEN}Game Server Billing Panel for Pterodactyl${NC}"
    echo ""
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        echo -e "${RED}[ERROR] This script must be run as root.${NC}"
        echo -e "Usage: ${YELLOW}sudo bash install.sh${NC}"
        exit 1
    fi
}

detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
        OS_VERSION=$VERSION_ID
    else
        echo -e "${RED}[ERROR] Unable to detect OS. Only Linux is supported.${NC}"
        exit 1
    fi

    echo -e "${BLUE}[INFO] Detected: ${OS} ${OS_VERSION}${NC}"

    case $OS in
        ubuntu|debian)
            PKG_MANAGER="apt"
            ;;
        centos|almalinux|rocky|rhel|fedora)
            PKG_MANAGER="yum"
            ;;
        *)
            echo -e "${RED}[ERROR] Unsupported OS: ${OS}${NC}"
            echo -e "Supported: Ubuntu 22.04/24.04, Debian 11/12, CentOS 7/8/9, AlmaLinux, Rocky Linux"
            exit 1
            ;;
    esac
}

install_dependencies() {
    echo -e "${YELLOW}[1/8] Installing dependencies...${NC}"

    if [ "$PKG_MANAGER" = "apt" ]; then
        export DEBIAN_FRONTEND=noninteractive
        apt-get update -y > /dev/null 2>&1
        apt-get install -y software-properties-common curl git unzip nginx mariadb-server \
            php8.1 php8.1-fpm php8.1-cli php8.1-mysql php8.1-curl php8.1-gd \
            php8.1-mbstring php8.1-xml php8.1-zip php8.1-bcmath php8.1-intl php8.1-dom \
            certbot python3-certbot-nginx composer > /dev/null 2>&1
    elif [ "$PKG_MANAGER" = "yum" ]; then
        yum install -y epel-release > /dev/null 2>&1
        yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm 2>/dev/null || \
            yum install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm 2>/dev/null || \
            yum install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm 2>/dev/null || true
        yum install -y yum-utils > /dev/null 2>&1
        yum-config-manager --enable remi-php81 2>/dev/null || true
        yum install -y nginx mariadb-server php php-fpm php-cli php-mysqlnd php-curl php-gd \
            php-mbstring php-xml php-zip php-bcmath php-intl php-dom composer git unzip curl \
            certbot python3-certbot-nginx > /dev/null 2>&1
    fi

    echo -e "${GREEN}[OK] Dependencies installed.${NC}"
}

setup_database() {
    echo -e "${YELLOW}[2/8] Setting up database...${NC}"

    DB_PASS=$(openssl rand -hex 16)

    systemctl enable mariadb > /dev/null 2>&1 || true
    systemctl start mariadb > /dev/null 2>&1 || true

    mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME};
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF

    echo -e "${GREEN}[OK] Database configured.${NC}"
}

setup_php() {
    echo -e "${YELLOW}[3/8] Configuring PHP...${NC}"

    PHP_INI=$(find /etc/php -name "php.ini" -path "*/fpm/*" 2>/dev/null | head -1)
    if [ -z "$PHP_INI" ]; then
        PHP_INI="/etc/php.ini"
    fi

    if [ -f "$PHP_INI" ]; then
        sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' "$PHP_INI"
        sed -i 's/post_max_size = .*/post_max_size = 100M/' "$PHP_INI"
        sed -i 's/memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
        sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' "$PHP_INI"
    fi

    systemctl restart php8.1-fpm 2>/dev/null || systemctl restart php-fpm 2>/dev/null || true
    echo -e "${GREEN}[OK] PHP configured.${NC}"
}

install_panel() {
    echo -e "${YELLOW}[4/8] Installing PteroBilling...${NC}"

    mkdir -p "$INSTALL_DIR"

    if [ ! -f "$INSTALL_DIR/composer.json" ]; then
        echo -e "${RED}[ERROR] PteroBilling not found at ${INSTALL_DIR}${NC}"
        echo -e "Please clone the repository first:"
        echo -e "  ${YELLOW}git clone https://github.com/itriedcoding/PteroBilling.git ${INSTALL_DIR}${NC}"
        exit 1
    fi

    cd "$INSTALL_DIR"
    composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || true

    APP_KEY=$(openssl rand -hex 32)
    JWT_SECRET=$(openssl rand -hex 32)

    cat > .env <<EOF
APP_NAME=PteroBilling
APP_ENV=production
APP_DEBUG=false
APP_URL=${PANEL_URL}
APP_DOMAIN=$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/$||')
APP_SECURE=true
APP_KEY=${APP_KEY}

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

PTERODACTYL_URL=${PTERO_URL}
PTERODACTYL_API_KEY=

STRIPE_KEY=
STRIPE_WEBHOOK_SECRET=
STRIPE_PUBLIC_KEY=

PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=
PAYPAL_MODE=live

MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/$||')
MAIL_FROM_NAME=PteroBilling

JWT_SECRET=${JWT_SECRET}
EOF

    php database/migrate.php 2>/dev/null || true

    chown -R www-data:www-data "$INSTALL_DIR" 2>/dev/null || chown -R nginx:nginx "$INSTALL_DIR" 2>/dev/null || true
    chmod -R 755 "$INSTALL_DIR"
    chmod -R 775 "$INSTALL_DIR/storage" 2>/dev/null || mkdir -p "$INSTALL_DIR/storage" && chmod -R 775 "$INSTALL_DIR/storage"

    echo -e "${GREEN}[OK] Panel installed to ${INSTALL_DIR}${NC}"
}

setup_nginx() {
    echo -e "${YELLOW}[5/8] Configuring Nginx...${NC}"

    DOMAIN=$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/$||g')

    cat > /etc/nginx/sites-available/pterobilling <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};

    root ${INSTALL_DIR}/public;
    index index.php;

    client_max_body_size 100M;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_intercept_errors on;
    }

    location ~ /\.ht { deny all; }
    location ~ /\.git { deny all; }
    location ~ /storage { deny all; }
    location ~ /config { deny all; }
    location ~ /vendor { deny all; }
}
NGINX

    ln -sf /etc/nginx/sites-available/pterobilling /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

    nginx -t 2>/dev/null
    systemctl restart nginx
    systemctl enable nginx > /dev/null 2>&1 || true

    echo -e "${GREEN}[OK] Nginx configured.${NC}"
}

setup_ssl() {
    echo -e "${YELLOW}[6/8] Setting up SSL...${NC}"

    DOMAIN=$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/$||g')

    echo -e "${CYAN}Would you like to set up SSL with Let's Encrypt?${NC}"
    read -p "(y/n): " SETUP_SSL

    if [[ "$SETUP_SSL" =~ ^[Yy]$ ]]; then
        certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@${DOMAIN}" 2>/dev/null || {
            echo -e "${YELLOW}[WARN] SSL setup failed. You can set it up later with:${NC}"
            echo -e "  ${YELLOW}sudo certbot --nginx -d ${DOMAIN}${NC}"
        }
    else
        echo -e "${CYAN}[SKIP] SSL not configured. Set up later with:${NC}"
        echo -e "  ${YELLOW}sudo certbot --nginx -d ${DOMAIN}${NC}"
    fi
}

setup_cron() {
    echo -e "${YELLOW}[7/8] Setting up cron jobs...${NC}"

    (crontab -l 2>/dev/null; echo "* * * * * cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab - 2>/dev/null || true

    echo -e "${GREEN}[OK] Cron jobs configured.${NC}"
}

print_summary() {
    echo ""
    echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║         PteroBilling Installation Complete!              ║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "  ${CYAN}Panel URL:${NC}    ${PANEL_URL}"
    echo -e "  ${CYAN}Install Dir:${NC}  ${INSTALL_DIR}"
    echo ""
    echo -e "  ${YELLOW}Database:${NC}"
    echo -e "    Host:     localhost"
    echo -e "    Database: ${DB_NAME}"
    echo -e "    Username: ${DB_USER}"
    echo -e "    Password: ${DB_PASS}"
    echo ""
    echo -e "  ${YELLOW}Next Steps:${NC}"
    echo -e "  ${GREEN}1.${NC} Visit ${CYAN}${PANEL_URL}${NC} in your browser"
    echo -e "  ${GREEN}2.${NC} The Setup Wizard will guide you through:"
    echo -e "      - Custom domain configuration"
    echo -e "      - Pterodactyl API connection"
    echo -e "      - Stripe payment setup"
    echo -e "      - PayPal payment setup"
    echo -e "  ${GREEN}3.${NC} Create your admin account"
    echo -e "  ${GREEN}4.${NC} Create server plans and start selling!"
    echo ""
    echo -e "  ${YELLOW}Webhook URLs (for payment providers):${NC}"
    echo -e "    Stripe: ${PANEL_URL}/api/v1/payment/stripe"
    echo -e "    PayPal: ${PANEL_URL}/api/v1/payment/paypal"
    echo ""
    echo -e "${GREEN}══════════════════════════════════════════════════════════${NC}"
}

main() {
    print_banner
    check_root
    detect_os

    echo ""
    echo -e "${CYAN}Please provide the following information:${NC}"
    echo -e "${YELLOW}(You can configure payment providers later via the Setup Wizard)${NC}"
    echo ""

    read -p "Panel URL (e.g., https://billing.example.com): " PANEL_URL
    read -p "Pterodactyl Panel URL (e.g., https://panel.example.com): " PTERO_URL

    if [ -z "$PANEL_URL" ] || [ -z "$PTERO_URL" ]; then
        echo -e "${RED}[ERROR] All fields are required.${NC}"
        exit 1
    fi

    echo ""
    echo -e "${CYAN}Starting installation...${NC}"
    echo ""

    install_dependencies
    setup_database
    setup_php
    install_panel
    setup_nginx
    setup_ssl
    setup_cron
    print_summary
}

main "$@"
