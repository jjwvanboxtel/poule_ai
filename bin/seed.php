#!/usr/bin/env php
<?php declare(strict_types=1);

use App\Infrastructure\Persistence\Pdo\ConnectionFactory;
use Database\Seeders\DevSeeder;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

// Load .env
(static function (): void {
    $envFile = BASE_PATH . '/.env';
    if (!file_exists($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
})();

$dbConfig = require BASE_PATH . '/config/database.php';

$args = $argv ?? [];
$help = in_array('--help', $args, true) || in_array('-h', $args, true);

if ($help) {
    echo <<<HELP
    Usage: php bin/seed.php [options]
    
    Options:
      --help, -h  Show this help message
    
    Seeds the development database with default admin + participant users
    and a sample draft competition.
    
    HELP;
    exit(0);
}

echo "Voetbalpoule Dev Seeder\n";
echo str_repeat('─', 40) . "\n";

try {
    $pdo = ConnectionFactory::fromConfig($dbConfig);
} catch (\RuntimeException $e) {
    echo "ERROR: Could not connect to database: {$e->getMessage()}\n";
    exit(1);
}

$seeder = new DevSeeder($pdo);
$seeder->run();

echo str_repeat('─', 40) . "\n";
echo "Seeding complete.\n";
exit(0);
