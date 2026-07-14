<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$db = Database::getInstance();
$conn = $db->getConnection();

$migrationFile = __DIR__ . '/migrations/001_initial.php';
if (file_exists($migrationFile)) {
    $migration = require $migrationFile;
    $migration($conn);
    echo "Migration completed successfully!\n";
} else {
    echo "Migration file not found.\n";
}