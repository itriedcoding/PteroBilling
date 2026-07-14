<?php
declare(strict_types=1);

namespace App\Core;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Database
{
    private static ?Database $instance = null;
    private Connection $connection;
    private Logger $logger;

    private function __construct()
    {
        $this->logger = new Logger('pterobilling');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../storage/logs/app.log'));

        $config = [
            'dbname' => $_ENV['DB_DATABASE'] ?? 'pterobilling',
            'user' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
            'defaultTableOptions' => [
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ],
        ];

        $this->connection = DriverManager::getConnection($config);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }

    public function __clone()
    {
        throw new \Exception("Cannot clone singleton");
    }
}