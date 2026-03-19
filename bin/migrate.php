#!/usr/bin/env php
<?php declare(strict_types=1);

use App\Infrastructure\Persistence\Pdo\ConnectionFactory;

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
$dryRun = in_array('--dry-run', $args, true);
$help = in_array('--help', $args, true) || in_array('-h', $args, true);

if ($help) {
    echo <<<HELP
    Usage: php bin/migrate.php [options]
    
    Options:
      --dry-run   Show pending migrations without running them
      --help, -h  Show this help message
    
    HELP;
    exit(0);
}

echo "Voetbalpoule Migration Runner\n";
echo str_repeat('─', 40) . "\n";

try {
    $pdo = ConnectionFactory::fromConfig($dbConfig);
} catch (\RuntimeException $e) {
    echo "ERROR: Could not connect to database: {$e->getMessage()}\n";
    exit(1);
}

// Ensure migrations tracking table exists
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration  VARCHAR(255) NOT NULL UNIQUE,
        ran_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
);

// Find all migration files
$migrationsDir = BASE_PATH . '/database/migrations';
$files = glob($migrationsDir . '/*.php') ?: [];
sort($files);

// Get already-run migrations
$ranStatement = $pdo->query('SELECT migration FROM migrations ORDER BY id');
$ran = $ranStatement !== false
    ? $ranStatement->fetchAll(\PDO::FETCH_COLUMN)
    : [];
$ranSet = array_flip($ran);

$pending = [];
foreach ($files as $file) {
    $name = basename($file, '.php');
    if (!isset($ranSet[$name])) {
        $pending[] = ['name' => $name, 'file' => $file];
    }
}

if ($pending === []) {
    echo "Nothing to migrate. All migrations are up to date.\n";
    exit(0);
}

if ($dryRun) {
    echo "Pending migrations (dry-run, not applied):\n";
    foreach ($pending as $migration) {
        echo "  - {$migration['name']}\n";
    }
    exit(0);
}

$success = 0;
foreach ($pending as $migrationData) {
    echo "  Migrating: {$migrationData['name']} ... ";
    $migration = require $migrationData['file'];

    try {
        $migration->up($pdo);

        if ($pdo->inTransaction()) {
            $pdo->commit();
        }

        $stmt = $pdo->prepare('INSERT INTO migrations (migration) VALUES (?)');
        $stmt->execute([$migrationData['name']]);
        echo "DONE\n";
        ++$success;
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "FAILED\n";
        echo "  Error: {$e->getMessage()}\n";
        exit(1);
    }
}

echo str_repeat('─', 40) . "\n";
echo "Migrated {$success} file(s) successfully.\n";
exit(0);
