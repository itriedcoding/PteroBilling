#!/bin/bash
#
# PteroBilling - One-Line Installer
# Supports: Ubuntu 22.04/24.04, Debian 11/12, CentOS 7/8/9
#
# Usage: curl -sL https://raw.githubusercontent.com/YOUR_USER/PteroBilling/main/scripts/install.sh | bash
#

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Config
PANEL_URL=""
PTERO_URL=""
DB_NAME="pterobilling"
DB_USER="pterobilling"
DB_PASS=""
APP_KEY=""
INSTALL_DIR="/var/www/pterobilling"
WEB_SERVER="nginx"

print_banner() {
    echo -e "${BLUE}"
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
        echo -e "${RED}Error: This script must be run as root.${NC}"
        echo "Usage: sudo bash install.sh"
        exit 1
    fi
}

detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
        OS_VERSION=$VERSION_ID
    else
        echo -e "${RED}Error: Unable to detect OS.${NC}"
        exit 1
    fi

    echo -e "${BLUE}Detected OS: ${OS} ${OS_VERSION}${NC}"

    case $OS in
        ubuntu|debian)
            PKG_MANAGER="apt"
            ;;
        centos|almalinux|rocky|rhel|fedora)
            PKG_MANAGER="yum"
            ;;
        *)
            echo -e "${RED}Error: Unsupported OS: ${OS}${NC}"
            echo "Supported: Ubuntu 22.04/24.04, Debian 11/12, CentOS 7/8/9"
            exit 1
            ;;
    esac
}

install_dependencies() {
    echo -e "${YELLOW}Installing dependencies...${NC}"

    if [ "$PKG_MANAGER" = "apt" ]; then
        export DEBIAN_FRONTEND=noninteractive
        apt-get update -y
        apt-get install -y software-properties-common curl git unzip nginx mariadb-server php8.1 php8.1-fpm php8.1-cli php8.1-mysql php8.1-curl php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip php8.1-bcmath php8.1-intl php8.1-dom composer
    elif [ "$PKG_MANAGER" = "yum" ]; then
        yum install -y epel-release
        yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm 2>/dev/null || true
        yum install -y yum-utils
        yum-config-manager --enable remi-php81 2>/dev/null || true
        yum install -y nginx mariadb-server php php-fpm php-cli php-mysqlnd php-curl php-gd php-mbstring php-xml php-zip php-bcmath php-intl php-dom composer
    fi

    echo -e "${GREEN}Dependencies installed.${NC}"
}

setup_database() {
    echo -e "${YELLOW}Setting up database...${NC}"

    DB_PASS=$(openssl rand -hex 16)

    systemctl enable mariadb
    systemctl start mariadb

    mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME};
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF

    echo -e "${GREEN}Database configured.${NC}"
}

setup_php() {
    echo -e "${YELLOW}Configuring PHP...${NC}"

    PHP_INI="/etc/php/8.1/fpm/php.ini"
    if [ -f "$PHP_INI" ]; then
        sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' "$PHP_INI"
        sed -i 's/post_max_size = .*/post_max_size = 100M/' "$PHP_INI"
        sed -i 's/memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
    fi

    systemctl restart php8.1-fpm 2>/dev/null || systemctl restart php-fpm 2>/dev/null || true
    echo -e "${GREEN}PHP configured.${NC}"
}

install_panel() {
    echo -e "${YELLOW}Installing PteroBilling...${NC}"

    mkdir -p "$INSTALL_DIR"
    cd "$INSTALL_DIR"

    if [ ! -f "composer.json" ]; then
        echo -e "${RED}Error: composer.json not found. Please clone the repository first.${NC}"
        echo "Run: git clone https://github.com/YOUR_USER/PteroBilling.git $INSTALL_DIR"
        exit 1
    fi

    composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || true

    APP_KEY=$(openssl rand -hex 32)

    cat > .env <<EOF
APP_NAME=PteroBilling
APP_ENV=production
APP_DEBUG=false
APP_URL=${PANEL_URL}
APP_DOMAIN=$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||')
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
MAIL_FROM_ADDRESS=noreply@$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||')
MAIL_FROM_NAME=PteroBilling

JWT_SECRET=$(openssl rand -hex 32)
EOF

    php database/migrate.php

    chown -R www-data:www-data "$INSTALL_DIR" 2>/dev/null || chown -R nginx:nginx "$INSTALL_DIR" 2>/dev/null || true
    chmod -R 755 "$INSTALL_DIR"
    chmod -R 775 "$INSTALL_DIR/storage"

    echo -e "${GREEN}Panel installed to ${INSTALL_DIR}${NC}"
}

setup_nginx() {
    echo -e "${YELLOW}Configuring Nginx...${NC}"

    DOMAIN=$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/||g')

    cat > /etc/nginx/sites-available/pterobilling <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};

    root ${INSTALL_DIR}/public;
    index index.php;

    client_max_body_size 100M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_intercept_errors on;
    }

    location ~ /\.ht {
        deny all;
    }

    location ~ /\.git {
        deny all;
    }

    location ~ /storage {
        deny all;
    }

    location ~ /config {
        deny all;
    }
}
EOF

    ln -sf /etc/nginx/sites-available/pterobilling /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

    nginx -t
    systemctl restart nginx
    systemctl enable nginx

    echo -e "${GREEN}Nginx configured.${NC}"
}

setup_ssl() {
    echo -e "${YELLOW}Setting up SSL with Let's Encrypt...${NC}"

    DOMAIN=$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/||g')

    if [ "$PKG_MANAGER" = "apt" ]; then
        apt-get install -y certbot python3-certbot-nginx
    else
        yum install -y certbot python3-certbot-nginx
    fi

    certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@${DOMAIN}" || {
        echo -e "${YELLOW}SSL setup skipped (domain may not be pointing to this server).${NC}"
    }
}

setup_cron() {
    echo -e "${YELLOW}Setting up cron jobs...${NC}"

    (crontab -l 2>/dev/null; echo "* * * * * cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab - || true
    (crontab -l 2>/dev/null; echo "0 0 * * * cd ${INSTALL_DIR} && php database/migrate.php >> /dev/null 2>&1") | crontab - || true

    echo -e "${GREEN}Cron jobs configured.${NC}"
}

print_summary() {
    echo ""
    echo -e "${GREEN}============================================${NC}"
    echo -e "${GREEN}  PteroBilling Installation Complete!${NC}"
    echo -e "${GREEN}============================================${NC}"
    echo ""
    echo -e "  ${BLUE}Panel URL:${NC}    ${PANEL_URL}"
    echo -e "  ${BLUE}Admin Login:${NC}  Register a new account and set role to admin in DB"
    echo ""
    echo -e "  ${YELLOW}Database Credentials:${NC}"
    echo -e "    Host:     localhost"
    echo -e "    Database: ${DB_NAME}"
    echo -e "    Username: ${DB_USER}"
    echo -e "    Password: ${DB_PASS}"
    echo ""
    echo -e "  ${YELLOW}Next Steps:${NC}"
    echo -e "    1. Configure your Pterodactyl API key in .env"
    echo -e "    2. Configure Stripe/PayPal keys in .env"
    echo -e "    3. Set up webhook URLs in payment providers"
    echo -e "    4. Visit ${PANEL_URL} to create your admin account"
    echo ""
    echo -e "  ${YELLOW}Webhook URLs:${NC}"
    echo -e "    Stripe:   ${PANEL_URL}/api/v1/payment/stripe"
    echo -e "    PayPal:   ${PANEL_URL}/api/v1/payment/paypal"
    echo ""
    echo -e "${GREEN}============================================${NC}"
}

main() {
    print_banner
    check_root
    detect_os

    echo ""
    echo -e "${YELLOW}Please provide the following information:${NC}"
    echo ""

    read -p "Panel URL (e.g., https://billing.example.com): " PANEL_URL
    read -p "Pterodactyl Panel URL (e.g., https://panel.example.com): " PTERO_URL

    if [ -z "$PANEL_URL" ] || [ -z "$PTERO_URL" ]; then
        echo -e "${RED}Error: All fields are required.${NC}"
        exit 1
    fi

    echo ""
    echo -e "${YELLOW}Starting installation...${NC}"
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
