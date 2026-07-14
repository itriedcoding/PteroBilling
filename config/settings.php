<?php
return [
    'site_name' => 'PteroBilling',
    'site_url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'site_description' => 'Game Server Billing Panel',
    'currency' => 'USD',
    'currency_symbol' => '$',
    'min_deposit' => 1.00,
    'max_deposit' => 1000.00,
    'default_server_term' => 30,
    'allow_registration' => true,
    'require_email_verification' => false,
    'maintenance_mode' => false,
    'theme' => 'default',
    'sidebar_color' => '#1e3a8a',
    'accent_color' => '#3b82f6',
];
