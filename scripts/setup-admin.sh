#!/bin/bash
# Create admin user for PteroBilling
# Usage: sudo bash setup-admin.sh

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

INSTALL_DIR="/var/www/pterobilling"

if [[ $EUID -ne 0 ]]; then
    echo -e "${RED}Run as root: sudo bash setup-admin.sh${NC}"
    exit 1
fi

if [ ! -f "$INSTALL_DIR/.env" ]; then
    echo -e "${RED}PteroBilling not found at $INSTALL_DIR${NC}"
    exit 1
fi

# Load DB credentials from .env
source <(grep -E '^DB_' "$INSTALL_DIR/.env" | sed 's/^/export /')

echo -e "${YELLOW}Create Admin User${NC}"
echo ""

read -p "Username: " USERNAME
read -p "Email: " EMAIL
read -s -p "Password: " PASSWORD
echo ""

if [ -z "$USERNAME" ] || [ -z "$EMAIL" ] || [ -z "$PASSWORD" ]; then
    echo -e "${RED}All fields are required.${NC}"
    exit 1
fi

HASH=$(php -r "echo password_hash('$PASSWORD', PASSWORD_ARGON2ID);")

mysql -u root <<EOSQL
INSERT INTO users (username, email, password, credits, role, created_at, updated_at)
VALUES ('$USERNAME', '$EMAIL', '$HASH', 0, 'admin', NOW(), NOW())
ON DUPLICATE KEY UPDATE role='admin', updated_at=NOW();
EOSQL

echo ""
echo -e "${GREEN}Admin user created successfully!${NC}"
echo -e "  Username: $USERNAME"
echo -e "  Email:    $EMAIL"
echo ""
