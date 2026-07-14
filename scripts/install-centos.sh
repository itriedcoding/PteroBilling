#!/bin/bash
# PteroBilling CentOS/RHEL Installer (7/8/9)
# Usage: sudo bash install-centos.sh

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}PteroBilling - CentOS/RHEL Installer${NC}"

if [[ $EUID -ne 0 ]]; then
    echo -e "${RED}Run as root: sudo bash install-centos.sh${NC}"
    exit 1
fi

read -p "Panel URL (e.g., https://billing.example.com): " PANEL_URL
read -p "Pterodactyl Panel URL: " PTERO_URL

DOMAIN=$(echo "$PANEL_URL" | sed 's|https\?://||')
DB_PASS=$(openssl rand -hex 16)
APP_KEY=$(openssl rand -hex 32)
INSTALL_DIR="/var/www/pterobilling"

echo -e "${YELLOW}[1/8] Installing EPEL & Remi...${NC}"
yum install -y epel-release
yum install -y https://rpms.remirepo.net/enterprise/remi-release-7.rpm 2>/dev/null || \
    yum install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm 2>/dev/null || \
    yum install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm 2>/dev/null || true

yum install -y yum-utils
yum-config-manager --enable remi-php81 2>/dev/null || true

echo -e "${YELLOW}[2/8] Installing packages...${NC}"
yum install -y nginx mariadb-server php php-fpm php-cli php-mysqlnd php-curl php-gd \
    php-mbstring php-xml php-zip php-bcmath php-intl php-dom composer git unzip curl

echo -e "${YELLOW}[3/8] Configuring database...${NC}"
systemctl enable mariadb
systemctl start mariadb

mysql -u root <<EOSQL
CREATE DATABASE IF NOT EXISTS pterobilling;
CREATE USER IF NOT EXISTS 'pterobilling'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON pterobilling.* TO 'pterobilling'@'localhost';
FLUSH PRIVILEGES;
EOSQL

echo -e "${YELLOW}[4/8] Configuring PHP...${NC}"
PHP_INI=$(find /etc/php.ini -maxdepth 0 2>/dev/null | head -1)
if [ -n "$PHP_INI" ]; then
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' "$PHP_INI"
    sed -i 's/post_max_size = .*/post_max_size = 100M/' "$PHP_INI"
    sed -i 's/memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
    sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' "$PHP_INI"
fi
systemctl restart php-fpm

echo -e "${YELLOW}[5/8] Installing PteroBilling...${NC}"
mkdir -p "$INSTALL_DIR"

if [ ! -f "$INSTALL_DIR/composer.json" ]; then
    echo -e "${RED}Please clone PteroBilling to $INSTALL_DIR first:${NC}"
    echo "  git clone https://github.com/YOUR_USER/PteroBilling.git $INSTALL_DIR"
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
MAIL_FROM_ADDRESS=noreply@${DOMAIN}
MAIL_FROM_NAME=PteroBilling
JWT_SECRET=$(openssl rand -hex 32)
EOF

php database/migrate.php
chown -R nginx:nginx "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/storage"

echo -e "${YELLOW}[6/8] Configuring Nginx...${NC}"
cat > /etc/nginx/conf.d/pterobilling.conf <<NGINX
server {
    listen 80;
    server_name ${DOMAIN};
    root ${INSTALL_DIR}/public;
    index index.php;
    client_max_body_size 100M;

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
}
NGINX

rm -f /etc/nginx/conf.d/default.conf 2>/dev/null || true
nginx -t && systemctl restart nginx

echo -e "${YELLOW}[7/8] Setting up SSL...${NC}"
yum install -y certbot python3-certbot-nginx 2>/dev/null || true
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@${DOMAIN}" 2>/dev/null || echo "SSL setup skipped."

echo -e "${YELLOW}[8/8] Setting up cron...${NC}"
(crontab -l 2>/dev/null; echo "* * * * * cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  Installation Complete!${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo -e "  Panel URL: ${PANEL_URL}"
echo -e "  Database:  pterobilling"
echo -e "  DB User:   pterobilling"
echo -e "  DB Pass:   ${DB_PASS}"
echo ""
echo -e "  1. Set Pterodactyl API key in ${INSTALL_DIR}/.env"
echo -e "  2. Set Stripe/PayPal keys in ${INSTALL_DIR}/.env"
echo -e "  3. Visit ${PANEL_URL} to register admin"
echo ""
