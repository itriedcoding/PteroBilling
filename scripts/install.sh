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
BOLD='\033[1m'
NC='\033[0m'

PANEL_URL=""
PTERO_URL=""
PTERO_KEY=""
ENABLE_STRIPE="n"
STRIPE_SECRET=""
STRIPE_PUBLIC=""
STRIPE_WEBHOOK=""
ENABLE_PAYPAL="n"
PAYPAL_ID=""
PAYPAL_SECRET=""
PAYPAL_MODE="live"
ENABLE_CREDITS="y"
MIN_DEPOSIT="1"
MAX_DEPOSIT="1000"
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
    echo -e "  ${BLUE}https://github.com/itriedcoding/PteroBilling${NC}"
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

    echo -e "${BLUE}[INFO] Detected OS: ${OS} ${OS_VERSION}${NC}"

    case $OS in
        ubuntu|debian) PKG_MANAGER="apt" ;;
        centos|almalinux|rocky|rhel|fedora) PKG_MANAGER="yum" ;;
        *)
            echo -e "${RED}[ERROR] Unsupported OS: ${OS}${NC}"
            echo -e "Supported: Ubuntu 22.04/24.04, Debian 11/12, CentOS 7/8/9, AlmaLinux, Rocky Linux"
            exit 1
            ;;
    esac
}

collect_information() {
    echo ""
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${BOLD}${CYAN}  PteroBilling Installation Setup${NC}"
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo ""

    echo -e "${BOLD}${YELLOW}[Step 1/4] Panel Configuration${NC}"
    echo -e "${BLUE}─────────────────────────────────────────────────────────${NC}"
    read -p "  Panel URL (e.g., https://billing.example.com): " PANEL_URL
    read -p "  Pterodactyl Panel URL (e.g., https://panel.example.com): " PTERO_URL

    if [ -z "$PANEL_URL" ] || [ -z "$PTERO_URL" ]; then
        echo -e "${RED}[ERROR] Panel URL and Pterodactyl URL are required.${NC}"
        exit 1
    fi

    echo ""
    echo -e "${BOLD}${YELLOW}[Step 2/4] Pterodactyl API Key${NC}"
    echo -e "${BLUE}─────────────────────────────────────────────────────────${NC}"
    echo -e "  ${CYAN}Where to find it:${NC}"
    echo -e "  1. Login to your Pterodactyl Panel as admin"
    echo -e "  2. Go to ${BOLD}Admin > Application > API Credentials${NC}"
    echo -e "  3. Click ${BOLD}Create New${NC}, give it a name, select all permissions"
    echo -e "  4. Copy the key (starts with ${BOLD}ptla_${NC})"
    echo ""
    read -p "  Pterodactyl API Key: " PTERO_KEY

    if [ -z "$PTERO_KEY" ]; then
        echo -e "${RED}[ERROR] Pterodactyl API Key is required.${NC}"
        echo -e "You can skip this for now by entering a placeholder and update it later."
        exit 1
    fi

    echo ""
    echo -e "${BOLD}${YELLOW}[Step 3/4] Payment Methods${NC}"
    echo -e "${BLUE}─────────────────────────────────────────────────────────${NC}"
    echo -e "  Choose which payment methods to enable:"
    echo ""

    echo -e "  ${GREEN}[1]${NC} Stripe (Credit/Debit Cards)"
    read -p "      Enable Stripe? (y/n): " ENABLE_STRIPE

    if [[ "$ENABLE_STRIPE" =~ ^[Yy]$ ]]; then
        echo ""
        echo -e "      ${CYAN}Get your keys from: https://dashboard.stripe.com/apikeys${NC}"
        read -p "      Stripe Secret Key (sk_live_...): " STRIPE_SECRET
        read -p "      Stripe Publishable Key (pk_live_...): " STRIPE_PUBLIC
        read -p "      Stripe Webhook Secret (whsec_...): " STRIPE_WEBHOOK
        echo -e "      ${CYAN}Webhook URL: ${PANEL_URL}/api/v1/payment/stripe${NC}"
        echo -e "      ${CYAN}Events: checkout.session.completed, invoice.payment_succeeded, payment_intent.succeeded${NC}"
    fi

    echo ""
    echo -e "  ${GREEN}[2]${NC} PayPal"
    read -p "      Enable PayPal? (y/n): " ENABLE_PAYPAL

    if [[ "$ENABLE_PAYPAL" =~ ^[Yy]$ ]]; then
        echo ""
        echo -e "      ${CYAN}Get your credentials from: https://developer.paypal.com/dashboard/applications${NC}"
        read -p "      PayPal Client ID: " PAYPAL_ID
        read -p "      PayPal Client Secret: " PAYPAL_SECRET
        read -p "      Mode (sandbox/live) [live]: " PAYPAL_MODE
        PAYPAL_MODE=${PAYPAL_MODE:-live}
        echo -e "      ${CYAN}Webhook URL: ${PANEL_URL}/api/v1/payment/paypal${NC}"
        echo -e "      ${CYAN}Event: PAYMENT.CAPTURE.COMPLETED${NC}"
    fi

    echo ""
    echo -e "  ${GREEN}[3]${NC} Credit System (Built-in)"
    read -p "      Enable Credit System? (Y/n): " ENABLE_CREDITS
    ENABLE_CREDITS=${ENABLE_CREDITS:-y}

    if [[ "$ENABLE_CREDITS" =~ ^[Yy]$ ]]; then
        ENABLE_CREDITS="y"
        read -p "      Minimum deposit amount ($) [1]: " MIN_DEPOSIT
        MIN_DEPOSIT=${MIN_DEPOSIT:-1}
        read -p "      Maximum deposit amount ($) [1000]: " MAX_DEPOSIT
        MAX_DEPOSIT=${MAX_DEPOSIT:-1000}
    else
        ENABLE_CREDITS="n"
    fi

    echo ""
    echo -e "${BOLD}${YELLOW}[Step 4/4] SSL Configuration${NC}"
    echo -e "${BLUE}─────────────────────────────────────────────────────────${NC}"
    read -p "  Set up SSL with Let's Encrypt? (y/n): " SETUP_SSL

    print_summary_before_install
}

print_summary_before_install() {
    DOMAIN=$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/$||')

    echo ""
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo -e "${BOLD}${CYAN}  Installation Summary${NC}"
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "  ${BOLD}Panel URL:${NC}          ${PANEL_URL}"
    echo -e "  ${BOLD}Domain:${NC}             ${DOMAIN}"
    echo -e "  ${BOLD}Pterodactyl URL:${NC}    ${PTERO_URL}"
    echo -e "  ${BOLD}Pterodactyl Key:${NC}    ${PTERO_KEY:0:10}...${PTERO_KEY: -4}"
    echo ""
    echo -e "  ${BOLD}Payment Methods:${NC}"
    echo -e "    Stripe:     $(if [[ "$ENABLE_STRIPE" =~ ^[Yy]$ ]]; then echo -e "${GREEN}ENABLED${NC}"; else echo -e "${RED}DISABLED${NC}"; fi)"
    echo -e "    PayPal:     $(if [[ "$ENABLE_PAYPAL" =~ ^[Yy]$ ]]; then echo -e "${GREEN}ENABLED${NC}"; else echo -e "${RED}DISABLED${NC}"; fi)"
    echo -e "    Credits:    $(if [[ "$ENABLE_CREDITS" =~ ^[Yy]$ ]]; then echo -e "${GREEN}ENABLED${NC} (min: \$${MIN_DEPOSIT}, max: \$${MAX_DEPOSIT})${NC}"; else echo -e "${RED}DISABLED${NC}"; fi)"
    echo ""
    echo -e "  ${BOLD}SSL:${NC}                $(if [[ "$SETUP_SSL" =~ ^[Yy]$ ]]; then echo -e "${GREEN}Yes (Let's Encrypt)${NC}"; else echo -e "${YELLOW}No (configure later)${NC}"; fi)"
    echo ""
    echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
    echo ""
    read -p "  Proceed with installation? (Y/n): " CONFIRM
    CONFIRM=${CONFIRM:-y}

    if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Installation cancelled.${NC}"
        exit 0
    fi
}

install_dependencies() {
    echo ""
    echo -e "${YELLOW}[1/7] Installing dependencies...${NC}"

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
    echo -e "${YELLOW}[2/7] Setting up database...${NC}"

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
    echo -e "${YELLOW}[3/7] Configuring PHP...${NC}"

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
    echo -e "${YELLOW}[4/7] Installing PteroBilling...${NC}"

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
    DOMAIN=$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/$||')

    cat > .env <<EOF
APP_NAME=PteroBilling
APP_ENV=production
APP_DEBUG=false
APP_URL=${PANEL_URL}
APP_DOMAIN=${DOMAIN}
APP_SECURE=true
APP_KEY=${APP_KEY}

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

PTERODACTYL_URL=${PTERO_URL}
PTERODACTYL_API_KEY=${PTERO_KEY}

STRIPE_KEY=${STRIPE_SECRET}
STRIPE_WEBHOOK_SECRET=${STRIPE_WEBHOOK}
STRIPE_PUBLIC_KEY=${STRIPE_PUBLIC}

PAYPAL_CLIENT_ID=${PAYPAL_ID}
PAYPAL_CLIENT_SECRET=${PAYPAL_SECRET}
PAYPAL_MODE=${PAYPAL_MODE}

MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@${DOMAIN}
MAIL_FROM_NAME=PteroBilling

JWT_SECRET=${JWT_SECRET}
EOF

    cat > config/settings.php <<SETTINGS
<?php
return [
    'site_name' => 'PteroBilling',
    'site_url' => '${PANEL_URL}',
    'custom_domain' => '${DOMAIN}',
    'site_description' => 'Game Server Billing Panel',
    'currency' => 'USD',
    'currency_symbol' => '\$',
    'min_deposit' => ${MIN_DEPOSIT}.00,
    'max_deposit' => ${MAX_DEPOSIT}.00,
    'default_server_term' => 30,
    'allow_registration' => true,
    'require_email_verification' => false,
    'maintenance_mode' => false,
    'theme' => 'default',
    'sidebar_color' => '#1e3a8a',
    'accent_color' => '#3b82f6',
    'stripe_enabled' => $(if [[ "$ENABLE_STRIPE" =~ ^[Yy]$ ]]; then echo "true"; else echo "false"; fi),
    'stripe_secret' => '${STRIPE_SECRET}',
    'stripe_public' => '${STRIPE_PUBLIC}',
    'stripe_webhook_secret' => '${STRIPE_WEBHOOK}',
    'paypal_enabled' => $(if [[ "$ENABLE_PAYPAL" =~ ^[Yy]$ ]]; then echo "true"; else echo "false"; fi),
    'paypal_client_id' => '${PAYPAL_ID}',
    'paypal_client_secret' => '${PAYPAL_SECRET}',
    'paypal_mode' => '${PAYPAL_MODE}',
    'credits_enabled' => $(if [[ "$ENABLE_CREDITS" =~ ^[Yy]$ ]]; then echo "true"; else echo "false"; fi),
    'ptero_url' => '${PTERO_URL}',
    'ptero_api_key' => '${PTERO_KEY}',
    'mail_host' => '',
    'mail_port' => 587,
    'mail_username' => '',
    'mail_password' => '',
    'mail_encryption' => 'tls',
    'mail_from' => 'noreply@${DOMAIN}',
    'mail_from_name' => 'PteroBilling',
];
SETTINGS

    php database/migrate.php 2>/dev/null || true

    chown -R www-data:www-data "$INSTALL_DIR" 2>/dev/null || chown -R nginx:nginx "$INSTALL_DIR" 2>/dev/null || true
    chmod -R 755 "$INSTALL_DIR"
    chmod -R 775 "$INSTALL_DIR/storage" 2>/dev/null || mkdir -p "$INSTALL_DIR/storage" && chmod -R 775 "$INSTALL_DIR/storage"

    echo -e "${GREEN}[OK] Panel installed to ${INSTALL_DIR}${NC}"
}

setup_nginx() {
    echo -e "${YELLOW}[5/7] Configuring Nginx...${NC}"

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
    echo -e "${YELLOW}[6/7] SSL Setup...${NC}"

    if [[ "$SETUP_SSL" =~ ^[Yy]$ ]]; then
        DOMAIN=$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/$||g')
        certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@${DOMAIN}" 2>/dev/null || {
            echo -e "${YELLOW}[WARN] SSL setup failed. Set up later with:${NC}"
            echo -e "  ${YELLOW}sudo certbot --nginx -d ${DOMAIN}${NC}"
        }
    else
        echo -e "${CYAN}[SKIP] SSL not configured. Set up later with:${NC}"
        echo -e "  ${YELLOW}sudo certbot --nginx -d $(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/$||')${NC}"
    fi
}

setup_cron() {
    echo -e "${YELLOW}[7/7] Setting up cron jobs...${NC}"

    (crontab -l 2>/dev/null; echo "* * * * * cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab - 2>/dev/null || true

    echo -e "${GREEN}[OK] Cron jobs configured.${NC}"
}

print_final_summary() {
    DOMAIN=$(echo ${PANEL_URL} | sed 's|https://||' | sed 's|http://||' | sed 's|/$||')

    echo ""
    echo -e "${BOLD}${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${BOLD}${GREEN}║           PteroBilling Installation Complete!                ║${NC}"
    echo -e "${BOLD}${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "  ${BOLD}${CYAN}Panel URL:${NC}        ${PANEL_URL}"
    echo -e "  ${BOLD}${CYAN}Install Directory:${NC} ${INSTALL_DIR}"
    echo ""
    echo -e "  ${BOLD}${YELLOW}Database Credentials:${NC}"
    echo -e "    Host:     localhost"
    echo -e "    Database: ${DB_NAME}"
    echo -e "    Username: ${DB_USER}"
    echo -e "    Password: ${BOLD}${DB_PASS}${NC}"
    echo ""
    echo -e "  ${BOLD}${YELLOW}Enabled Payment Methods:${NC}"
    [[ "$ENABLE_STRIPE" =~ ^[Yy]$ ]] && echo -e "    ${GREEN}✓${NC} Stripe (Credit/Debit Cards)"
    [[ "$ENABLE_PAYPAL" =~ ^[Yy]$ ]] && echo -e "    ${GREEN}✓${NC} PayPal"
    [[ "$ENABLE_CREDITS" =~ ^[Yy]$ ]] && echo -e "    ${GREEN}✓${NC} Credit System (min: \$${MIN_DEPOSIT}, max: \$${MAX_DEPOSIT})"
    echo ""
    echo -e "  ${BOLD}${YELLOW}What's Next:${NC}"
    echo -e "  ${GREEN}1.${NC} Visit ${BOLD}${PANEL_URL}${NC} in your browser"
    echo -e "  ${GREEN}2.${NC} Register your admin account (first user is auto-admin)"
    echo -e "  ${GREEN}3.${NC} Go to ${BOLD}Admin > Settings${NC} to manage everything"
    echo -e "  ${GREEN}4.${NC} Create server plans in ${BOLD}Admin > Plans${NC}"
    echo -e "  ${GREEN}5.${NC} Start selling!"
    echo ""

    if [[ "$ENABLE_STRIPE" =~ ^[Yy]$ ]] || [[ "$ENABLE_PAYPAL" =~ ^[Yy]$ ]]; then
        echo -e "  ${BOLD}${YELLOW}Webhook URLs (configure in your payment provider):${NC}"
        [[ "$ENABLE_STRIPE" =~ ^[Yy]$ ]] && echo -e "    Stripe: ${PANEL_URL}/api/v1/payment/stripe"
        [[ "$ENABLE_PAYPAL" =~ ^[Yy]$ ]] && echo -e "    PayPal: ${PANEL_URL}/api/v1/payment/paypal"
        echo ""
    fi

    echo -e "  ${BOLD}${YELLOW}Admin Settings URL:${NC}"
    echo -e "    ${PANEL_URL}/admin/settings"
    echo ""
    echo -e "${BOLD}${GREEN}══════════════════════════════════════════════════════════════${NC}"
}

main() {
    print_banner
    check_root
    detect_os
    collect_information

    echo ""
    echo -e "${CYAN}Starting installation...${NC}"

    install_dependencies
    setup_database
    setup_php
    install_panel
    setup_nginx
    setup_ssl
    setup_cron
    print_final_summary
}

main "$@"
