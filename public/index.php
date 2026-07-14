<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Application;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = new Application();
$app->run();