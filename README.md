# PteroBilling

A fully custom, feature-rich billing panel for [Pterodactyl Panel](https://pterodactyl.io/). Accept payments via **Stripe**, **PayPal**, or a built-in **credit system**. Beautiful UI, enterprise-grade security, and seamless Pterodactyl integration.

![PteroBilling](https://img.shields.io/badge/PHP-8.1+-blue) ![License](https://img.shields.io/badge/License-MIT-green) ![Status](https://img.shields.io/badge/Status-Stable-brightgreen)

## Features

- **Multiple Payment Methods**: Stripe (cards), PayPal, and credit-based system
- **Setup Wizard**: Guided configuration for domain, payments, and Pterodactyl
- **Custom Domain**: Full support for custom domains (billing.example.com)
- **Beautiful UI**: Modern, responsive design with Tailwind CSS
- **Admin Dashboard**: Full control over users, plans, invoices, and settings
- **Pterodactyl Integration**: Automatic server provisioning via Pterodactyl API
- **Credit System**: Users can add funds and use credits to purchase servers
- **Invoice Management**: Automatic invoice generation and tracking
- **API Access**: RESTful API with API key authentication
- **Security First**: CSRF protection, rate limiting, security headers, XSS prevention
- **One-Line Install**: Easy installation on Ubuntu, Debian, CentOS

## Requirements

- PHP 8.1 or higher
- MySQL/MariaDB 10.3+
- Nginx
- Composer
- Pterodactyl Panel (running)

## Supported Operating Systems

| OS | Versions |
|----|----------|
| Ubuntu | 22.04, 24.04 |
| Debian | 11, 12 |
| CentOS | 7, 8, 9 |
| AlmaLinux | 8, 9 |
| Rocky Linux | 8, 9 |

## Quick Install

### One-Line Install (Recommended)

```bash
# Clone the repository first
sudo git clone https://github.com/itriedcoding/PteroBilling.git /var/www/pterobilling

# Then run the installer
curl -sL https://raw.githubusercontent.com/itriedcoding/PteroBilling/main/scripts/install.sh | sudo bash
```

### Ubuntu/Debian

```bash
sudo git clone https://github.com/itriedcoding/PteroBilling.git /var/www/pterobilling
sudo bash /var/www/pterobilling/scripts/install-ubuntu.sh
```

### CentOS/RHEL

```bash
sudo git clone https://github.com/itriedcoding/PteroBilling.git /var/www/pterobilling
sudo bash /var/www/pterobilling/scripts/install-centos.sh
```

### Manual Install

1. **Clone the repository**
   ```bash
   sudo mkdir -p /var/www/pterobilling
   sudo git clone https://github.com/itriedcoding/PteroBilling.git /var/www/pterobilling
   cd /var/www/pterobilling
   ```

2. **Install dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   nano .env
   ```

4. **Run database migration**
   ```bash
   php database/migrate.php
   ```

5. **Set permissions**
   ```bash
   sudo chown -R www-data:www-data /var/www/pterobilling
   sudo chmod -R 755 /var/www/pterobilling
   sudo chmod -R 775 /var/www/pterobilling/storage
   ```

6. **Configure Nginx**
   ```nginx
   server {
       listen 80;
       server_name billing.example.com;
       root /var/www/pterobilling/public;
       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include fastcgi_params;
           fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       }
   }
   ```

7. **Set up SSL**
   ```bash
   sudo certbot --nginx -d billing.example.com
   ```

8. **Visit your panel** and the Setup Wizard will guide you through the rest!

## Setup Wizard

After installation, visit your panel URL. The Setup Wizard will automatically appear and guide you through:

### Step 1: Domain & General
- Site name
- Panel URL (your custom domain)
- Custom domain configuration
- DNS instructions

### Step 2: Pterodactyl Connection
- Panel URL
- Application API Key
- Connection test

### Step 3: Stripe Configuration
- Secret Key
- Publishable Key
- Webhook Signing Secret
- Webhook URL and events

### Step 4: PayPal Configuration
- Client ID
- Client Secret
- Mode (Sandbox/Live)
- Webhook URL and events

### Step 5: Complete
- Summary and next steps

## Custom Domain Setup

To use a custom domain like `billing.example.com`:

1. **Add DNS Record**: Create an A record for `billing.example.com` pointing to your server's IP
2. **Wait for propagation**: DNS changes can take up to 48 hours
3. **Run the installer** or configure nginx manually
4. **Set up SSL**:
   ```bash
   sudo certbot --nginx -d billing.example.com
   ```
5. **Update .env**:
   ```
   APP_URL=https://billing.example.com
   APP_DOMAIN=billing.example.com
   ```
6. **Or use the Admin Settings** to update domain settings through the UI

## Configuration

### Environment Variables (.env)

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Panel name | PteroBilling |
| `APP_URL` | Full panel URL | http://localhost |
| `APP_DOMAIN` | Custom domain | localhost |
| `APP_KEY` | Encryption key | (auto-generated) |
| `DB_HOST` | Database host | localhost |
| `DB_DATABASE` | Database name | pterobilling |
| `DB_USERNAME` | Database user | pterobilling |
| `DB_PASSWORD` | Database password | - |
| `PTERODACTYL_URL` | Pterodactyl panel URL | - |
| `PTERODACTYL_API_KEY` | Pterodactyl API key | - |
| `STRIPE_KEY` | Stripe secret key | - |
| `STRIPE_PUBLIC_KEY` | Stripe publishable key | - |
| `STRIPE_WEBHOOK_SECRET` | Stripe webhook secret | - |
| `PAYPAL_CLIENT_ID` | PayPal client ID | - |
| `PAYPAL_CLIENT_SECRET` | PayPal client secret | - |
| `PAYPAL_MODE` | PayPal mode (live/sandbox) | live |

### Admin Panel Settings

Navigate to **Admin > Settings** to configure:

- **General & Domain**: Site name, URL, custom domain, currency, deposit limits
- **Payments**: Enable/disable Stripe, PayPal, and credits; API keys
- **Pterodactyl**: Panel URL and API key with connection test
- **Mail**: SMTP configuration for notifications

### Pterodactyl API Key

1. Login to your Pterodactyl Panel admin
2. Go to **Admin** > **Application** > **API Credentials**
3. Create a new key with full permissions
4. Copy the key (starts with `ptla_`)

### Stripe Setup

1. Create a [Stripe account](https://stripe.com)
2. Get API keys from [Stripe Dashboard](https://dashboard.stripe.com/apikeys)
3. Create webhook endpoint:
   - URL: `https://your-domain.com/api/v1/payment/stripe`
   - Events: `checkout.session.completed`, `invoice.payment_succeeded`, `payment_intent.succeeded`
4. Copy the webhook signing secret

### PayPal Setup

1. Create a [PayPal Developer account](https://developer.paypal.com)
2. Create an app in [PayPal Dashboard](https://developer.paypal.com/dashboard/applications)
3. Set Client ID and Secret
4. Create webhook:
   - URL: `https://your-domain.com/api/v1/payment/paypal`
   - Event: `PAYMENT.CAPTURE.COMPLETED`

## Creating Plans

1. Login as admin
2. Go to **Admin** > **Plans** > **Create Plan**
3. Fill in:
   - **Name**: Display name
   - **Price**: Monthly price in USD
   - **CPU**: CPU limit (%)
   - **Memory**: RAM (MB)
   - **Disk**: Disk space (MB)
   - **Nest/Egg ID**: Pterodactyl resource IDs

## API Documentation

### Authentication

```
X-API-Key: your_api_key_here
```

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/plans` | List all plans |
| GET | `/api/v1/servers` | List user's servers |
| POST | `/api/v1/servers` | Create a server |
| GET | `/api/v1/invoices` | List user's invoices |
| GET | `/api/v1/balance` | Get credit balance |

## Security Features

- **CSRF Protection**: All forms protected with CSRF tokens
- **Rate Limiting**: API rate limiting to prevent abuse
- **Security Headers**: X-Frame-Options, CSP, HSTS, etc.
- **XSS Prevention**: All output is HTML-escaped
- **SQL Injection Prevention**: Parameterized queries via Doctrine DBAL
- **Password Hashing**: Argon2ID password hashing
- **Session Security**: Secure, HttpOnly, SameSite cookies
- **Webhook Verification**: Stripe signature verification

## Directory Structure

```
pterobilling/
├── app/
│   ├── Controllers/       # Request handlers
│   │   ├── Admin/         # Admin controllers
│   │   └── Api/           # API controllers
│   ├── Core/              # Core framework classes
│   ├── Helpers/           # Helper functions
│   ├── Middleware/         # HTTP middleware
│   ├── Models/            # Database models
│   └── Services/          # Business logic
│       ├── Payment/       # Payment providers
│       └── Pterodactyl/   # Pterodactyl integration
├── config/                # Configuration files
├── database/
│   └── migrations/        # Database migrations
├── public/                # Web root
├── resources/
│   └── views/             # PHP templates
│       ├── setup/         # Setup wizard
│       ├── admin/         # Admin views
│       ├── auth/          # Authentication views
│       ├── client/        # Client views
│       └── layouts/       # Layout templates
├── scripts/               # Installation scripts
├── storage/               # Logs, cache, sessions
├── .env.example           # Environment template
├── composer.json          # PHP dependencies
└── README.md              # This file
```

## Updating

```bash
cd /var/www/pterobilling
git pull origin main
composer install --no-dev --optimize-autoloader
php database/migrate.php
sudo chown -R www-data:www-data .
```

## Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data /var/www/pterobilling
sudo chmod -R 755 /var/www/pterobilling
sudo chmod -R 775 /var/www/pterobilling/storage
```

### Database Connection Issues
1. Check `.env` database credentials
2. Verify MySQL/MariaDB is running: `sudo systemctl status mariadb`
3. Check permissions: `mysql -u root -e "SHOW GRANTS FOR 'pterobilling'@'localhost';"`

### Webhook Not Working
1. Verify webhook URL in payment provider dashboard
2. Check webhook secret matches `.env`
3. Check logs: `cat storage/logs/app.log`
4. Test with Stripe CLI: `stripe listen --forward-to https://your-domain.com/api/v1/payment/stripe`

### Setup Wizard Not Appearing
- The wizard only appears if `PTERODACTYL_API_KEY` is not configured
- If stuck, manually set the API key in `.env` and the wizard will skip

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- **Issues**: [GitHub Issues](https://github.com/itriedcoding/PteroBilling/issues)
- **Discussions**: [GitHub Discussions](https://github.com/itriedcoding/PteroBilling/discussions)

---

Built with care for the Pterodactyl community.
