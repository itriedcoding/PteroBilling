<?php
declare(strict_types=1);

use Doctrine\DBAL\Connection;

return function (Connection $conn) {
    $schema = $conn->createSchemaManager();

    if (!$schema->tablesExist(['users'])) {
        $schema->createTable('users', function ($table) {
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('username', 'string', ['length' => 255]);
            $table->addColumn('email', 'string', ['length' => 255]);
            $table->addColumn('password', 'string', ['length' => 255]);
            $table->addColumn('credits', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => '0.00']);
            $table->addColumn('role', 'string', ['length' => 50, 'default' => 'user']);
            $table->addColumn('ptero_user_id', 'integer', ['notnull' => false]);
            $table->addColumn('created_at', 'datetime');
            $table->addColumn('updated_at', 'datetime');
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['email']);
            $table->addIndex(['role']);
        });
    }

    if (!$schema->tablesExist(['plans'])) {
        $schema->createTable('plans', function ($table) {
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('name', 'string', ['length' => 255]);
            $table->addColumn('description', 'text', ['notnull' => false]);
            $table->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2]);
            $table->addColumn('billing_cycle', 'string', ['length' => 50, 'default' => 'monthly']);
            $table->addColumn('cpu', 'integer', ['default' => 100]);
            $table->addColumn('memory', 'integer', ['default' => 1024]);
            $table->addColumn('disk', 'integer', ['default' => 10240]);
            $table->addColumn('io', 'integer', ['default' => 500]);
            $table->addColumn('cpu_limit', 'integer', ['default' => 100]);
            $table->addColumn('databases', 'integer', ['default' => 0]);
            $table->addColumn('allocations', 'integer', ['default' => 0]);
            $table->addColumn('backups', 'integer', ['default' => 0]);
            $table->addColumn('nest_id', 'integer', ['default' => 0]);
            $table->addColumn('egg_id', 'integer', ['default' => 0]);
            $table->addColumn('is_active', 'boolean', ['default' => true]);
            $table->addColumn('sort_order', 'integer', ['default' => 0]);
            $table->addColumn('created_at', 'datetime');
            $table->addColumn('updated_at', 'datetime');
            $table->setPrimaryKey(['id']);
        });
    }

    if (!$schema->tablesExist(['servers'])) {
        $schema->createTable('servers', function ($table) {
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('user_id', 'integer');
            $table->addColumn('plan_id', 'integer');
            $table->addColumn('ptero_server_id', 'integer', ['notnull' => false]);
            $table->addColumn('name', 'string', ['length' => 255]);
            $table->addColumn('status', 'string', ['length' => 50, 'default' => 'active']);
            $table->addColumn('expires_at', 'datetime');
            $table->addColumn('created_at', 'datetime');
            $table->addColumn('updated_at', 'datetime');
            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id']);
            $table->addIndex(['plan_id']);
            $table->addIndex(['status']);
            $table->addIndex(['expires_at']);
        });
    }

    if (!$schema->tablesExist(['invoices'])) {
        $schema->createTable('invoices', function ($table) {
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('user_id', 'integer');
            $table->addColumn('invoice_number', 'string', ['length' => 50]);
            $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2]);
            $table->addColumn('status', 'string', ['length' => 50, 'default' => 'pending']);
            $table->addColumn('payment_method', 'string', ['length' => 50, 'notnull' => false]);
            $table->addColumn('transaction_id', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('description', 'text', ['notnull' => false]);
            $table->addColumn('metadata', 'json', ['notnull' => false]);
            $table->addColumn('created_at', 'datetime');
            $table->addColumn('updated_at', 'datetime');
            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id']);
            $table->addIndex(['status']);
            $table->addIndex(['invoice_number']);
        });
    }

    if (!$schema->tablesExist(['transactions'])) {
        $schema->createTable('transactions', function ($table) {
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('user_id', 'integer');
            $table->addColumn('type', 'string', ['length' => 50]);
            $table->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2]);
            $table->addColumn('currency', 'string', ['length' => 10, 'default' => 'USD']);
            $table->addColumn('status', 'string', ['length' => 50, 'default' => 'pending']);
            $table->addColumn('provider', 'string', ['length' => 50]);
            $table->addColumn('provider_transaction_id', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('description', 'text', ['notnull' => false]);
            $table->addColumn('metadata', 'json', ['notnull' => false]);
            $table->addColumn('created_at', 'datetime');
            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id']);
            $table->addIndex(['status']);
            $table->addIndex(['provider_transaction_id']);
        });
    }

    if (!$schema->tablesExist(['payment_methods'])) {
        $schema->createTable('payment_methods', function ($table) {
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('user_id', 'integer');
            $table->addColumn('type', 'string', ['length' => 50]);
            $table->addColumn('provider', 'string', ['length' => 50]);
            $table->addColumn('provider_id', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('last_four', 'string', ['length' => 4, 'notnull' => false]);
            $table->addColumn('brand', 'string', ['length' => 50, 'notnull' => false]);
            $table->addColumn('exp_month', 'integer', ['notnull' => false]);
            $table->addColumn('exp_year', 'integer', ['notnull' => false]);
            $table->addColumn('is_default', 'boolean', ['default' => false]);
            $table->addColumn('metadata', 'json', ['notnull' => false]);
            $table->addColumn('created_at', 'datetime');
            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id']);
        });
    }

    if (!$schema->tablesExist(['api_keys'])) {
        $schema->createTable('api_keys', function ($table) {
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('user_id', 'integer');
            $table->addColumn('name', 'string', ['length' => 255]);
            $table->addColumn('key', 'string', ['length' => 255]);
            $table->addColumn('permissions', 'json', ['notnull' => false]);
            $table->addColumn('last_used_at', 'datetime', ['notnull' => false]);
            $table->addColumn('created_at', 'datetime');
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['key']);
            $table->addIndex(['user_id']);
        });
    }

    if (!$schema->tablesExist(['pterodactyl_nodes'])) {
        $schema->createTable('pterodactyl_nodes', function ($table) {
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('name', 'string', ['length' => 255]);
            $table->addColumn('fqdn', 'string', ['length' => 255]);
            $table->addColumn('memory', 'integer', ['default' => 0]);
            $table->addColumn('disk', 'integer', ['default' => 0]);
            $table->addColumn('created_at', 'datetime');
            $table->setPrimaryKey(['id']);
        });
    }

    if (!$schema->tablesExist(['pterodactyl_nests'])) {
        $schema->createTable('pterodactyl_nests', function ($table) {
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('name', 'string', ['length' => 255]);
            $table->addColumn('description', 'text', ['notnull' => false]);
            $table->addColumn('created_at', 'datetime');
            $table->setPrimaryKey(['id']);
        });
    }
};