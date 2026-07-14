#!/bin/bash
#
# PteroBilling - CentOS/RHEL Installer (7/8/9)
#
# Usage: sudo bash install-centos.sh
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
INSTALL_DIR="/var/www/pterobilling"

echo -e "${CYAN}"
echo "  ____   ____  _____                       _ "
echo " |  _ \ |  _ \|  ___| ___  _ __   ___  __| |"
echo " | |_) || |_) | |_   / _ \| '_ \ / _ \/ _| |"
echo " |  __/ |  __/|  _| | (_) | | | |  __/ (_| |"
echo " |_|    |_|   |_|    \___/|_| |_|\___|\__,_|"
echo -e "${NC}"
echo -e "${GREEN}CentOS/RHEL 7/8/9 Installer${NC}"
echo ""

if [[ $EUID -ne 0 ]]; then
    echo -e "${RED}Run as root: sudo bash install-centos.sh${NC}"
    exit 1
fi

echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BOLD}${CYAN}  PteroBilling Installation Setup${NC}"
echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
echo ""

echo -e "${BOLD}${YELLOW}[Step 1/4] Panel Configuration${NC}"
echo -e "${BLUE}─────────────────────────────────────────────────────────${NC}"
read -p "  Panel URL (e.g., https://billing.example.com): " PANEL_URL
read -p "  Pterodactyl Panel URL (e.g., https://panel.example.com): " PTERO_URL

if [ -z "$PANEL_URL" ] || [ -z "$PTERO_URL" ]; then
    echo -e "${RED}[ERROR] All URLs are required.${NC}"
    exit 1
fi

DOMAIN=$(echo "$PANEL_URL" | sed 's|https\?://||' | sed 's|/$||')

echo ""
echo -e "${BOLD}${YELLOW}[Step 2/4] Pterodactyl API Key${NC}"
echo -e "${BLUE}─────────────────────────────────────────────────────────${NC}"
echo -e "  ${CYAN}Get this from: Admin > Application > API Credentials${NC}"
read -p "  Pterodactyl API Key (ptla_...): " PTERO_KEY

if [ -z "$PTERO_KEY" ]; then
    echo -e "${RED}[ERROR] Pterodactyl API Key is required.${NC}"
    exit 1
fi

echo ""
echo -e "${BOLD}${YELLOW}[Step 3/4] Payment Methods${NC}"
echo -e "${BLUE}─────────────────────────────────────────────────────────${NC}"

echo -e "  ${GREEN}[1]${NC} Stripe (Credit/Debit Cards)"
read -p "      Enable Stripe? (y/n): " ENABLE_STRIPE

if [[ "$ENABLE_STRIPE" =~ ^[Yy]$ ]]; then
    read -p "      Stripe Secret Key (sk_live_...): " STRIPE_SECRET
    read -p "      Stripe Publishable Key (pk_live_...): " STRIPE_PUBLIC
    read -p "      Stripe Webhook Secret (whsec_...): " STRIPE_WEBHOOK
fi

echo ""
echo -e "  ${GREEN}[2]${NC} PayPal"
read -p "      Enable PayPal? (y/n): " ENABLE_PAYPAL

if [[ "$ENABLE_PAYPAL" =~ ^[Yy]$ ]]; then
    read -p "      PayPal Client ID: " PAYPAL_ID
    read -p "      PayPal Client Secret: " PAYPAL_SECRET
    read -p "      Mode (sandbox/live) [live]: " PAYPAL_MODE
    PAYPAL_MODE=${PAYPAL_MODE:-live}
fi

echo ""
echo -e "  ${GREEN}[3]${NC} Credit System (Built-in)"
read -p "      Enable Credit System? (Y/n): " ENABLE_CREDITS
ENABLE_CREDITS=${ENABLE_CREDITS:-y}

if [[ "$ENABLE_CREDITS" =~ ^[Yy]$ ]]; then
    ENABLE_CREDITS="y"
    read -p "      Minimum deposit ($) [1]: " MIN_DEPOSIT
    MIN_DEPOSIT=${MIN_DEPOSIT:-1}
    read -p "      Maximum deposit ($) [1000]: " MAX_DEPOSIT
    MAX_DEPOSIT=${MAX_DEPOSIT:-1000}
else
    ENABLE_CREDITS="n"
fi

echo ""
echo -e "${BOLD}${YELLOW}[Step 4/4] SSL${NC}"
echo -e "${BLUE}─────────────────────────────────────────────────────────${NC}"
read -p "  Set up SSL with Let's Encrypt? (y/n): " SETUP_SSL

echo ""
echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BOLD}${CYAN}  Summary${NC}"
echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
echo -e "  Panel:        ${PANEL_URL}"
echo -e "  Domain:       ${DOMAIN}"
echo -e "  Pterodactyl:  ${PTERO_URL}"
echo -e "  Stripe:       $(if [[ "$ENABLE_STRIPE" =~ ^[Yy]$ ]]; then echo -e "${GREEN}ON${NC}"; else echo -e "${RED}OFF${NC}"; fi)"
echo -e "  PayPal:       $(if [[ "$ENABLE_PAYPAL" =~ ^[Yy]$ ]]; then echo -e "${GREEN}ON${NC}"; else echo -e "${RED}OFF${NC}"; fi)"
echo -e "  Credits:      $(if [[ "$ENABLE_CREDITS" =~ ^[Yy]$ ]]; then echo -e "${GREEN}ON${NC}"; else echo -e "${RED}OFF${NC}"; fi)"
echo -e "  SSL:          $(if [[ "$SETUP_SSL" =~ ^[Yy]$ ]]; then echo -e "${GREEN}Yes${NC}"; else echo -e "${YELLOW}No${NC}"; fi)"
echo -e "${BOLD}${CYAN}═══════════════════════════════════════════════════════════${NC}"
echo ""
read -p "  Proceed? (Y/n): " CONFIRM
CONFIRM=${CONFIRM:-y}
if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Cancelled.${NC}"
    exit 0
fi

echo ""
echo -e "${YELLOW}[1/7] Installing EPEL & Remi...${NC}"
yum install -y epel-release > /dev/null 2>&1
yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm 2>/dev/null || \
    yum install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm 2>/dev/null || \
    yum install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm 2>/dev/null || true
yum install -y yum-utils > /dev/null 2>&1
yum-config-manager --enable remi-php81 2>/dev/null || true

echo -e "${YELLOW}[2/7] Installing packages...${NC}"
yum install -y nginx mariadb-server php php-fpm php-cli php-mysqlnd php-curl php-gd \
    php-mbstring php-xml php-zip php-bcmath php-intl php-dom composer git unzip curl \
    certbot python3-certbot-nginx > /dev/null 2>&1

echo -e "${YELLOW}[3/7] Configuring database...${NC}"
DB_PASS=$(openssl rand -hex 16)
APP_KEY=$(openssl rand -hex 32)
JWT_SECRET=$(openssl rand -hex 32)

systemctl enable mariadb > /dev/null 2>&1
systemctl start mariadb > /dev/null 2>&1

mysql -u root <<EOSQL
CREATE DATABASE IF NOT EXISTS pterobilling;
CREATE USER IF NOT EXISTS 'pterobilling'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON pterobilling.* TO 'pterobilling'@'localhost';
FLUSH PRIVILEGES;
EOSQL

echo -e "${YELLOW}[4/7] Configuring PHP...${NC}"
PHP_INI=$(find /etc/php.ini -maxdepth 0 2>/dev/null | head -1)
if [ -n "$PHP_INI" ]; then
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' "$PHP_INI"
    sed -i 's/post_max_size = .*/post_max_size = 100M/' "$PHP_INI"
    sed -i 's/memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
    sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' "$PHP_INI"
fi
systemctl restart php-fpm > /dev/null 2>&1

echo -e "${YELLOW}[5/7] Installing PteroBilling...${NC}"
mkdir -p "$INSTALL_DIR"

if [ ! -f "$INSTALL_DIR/composer.json" ]; then
    echo -e "${RED}PteroBilling not found at $INSTALL_DIR${NC}"
    echo "Run: git clone https://github.com/itriedcoding/PteroBilling.git $INSTALL_DIR"
    exit 1
fi

cd "$INSTALL_DIR"
composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || true

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
DB_DATABASE=pterobilling
DB_USERNAME=pterobilling
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
chown -R nginx:nginx "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/storage" 2>/dev/null || mkdir -p "$INSTALL_DIR/storage" && chmod -R 775 "$INSTALL_DIR/storage"

echo -e "${YELLOW}[6/7] Configuring Nginx...${NC}"
cat > /etc/nginx/conf.d/pterobilling.conf <<NGINX
server {
    listen 80;
    server_name ${DOMAIN};
    root ${INSTALL_DIR}/public;
    index index.php;
    client_max_body_size 100M;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php-fpm/php-fpm.sock;
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

rm -f /etc/nginx/conf.d/default.conf 2>/dev/null || true
nginx -t && systemctl restart nginx

echo -e "${YELLOW}[7/7] SSL${NC}"
if [[ "$SETUP_SSL" =~ ^[Yy]$ ]]; then
    certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@${DOMAIN}" 2>/dev/null || echo "SSL failed - run later: sudo certbot --nginx -d $DOMAIN"
fi

echo ""
echo -e "${BOLD}${GREEN}Installation Complete!${NC}"
echo ""
echo -e "  Panel:     ${PANEL_URL}"
echo -e "  Database:  pterobilling / ${DB_PASS}"
echo ""
echo -e "  ${GREEN}1.${NC} Visit ${PANEL_URL}"
echo -e "  ${GREEN}2.${NC} Register admin account"
echo -e "  ${GREEN}3.${NC} Manage in Admin > Settings"
echo ""
