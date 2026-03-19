<?php declare(strict_types=1);

return [
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'name' => $_ENV['APP_NAME'] ?? 'Voetbalpoule',
    'timezone' => 'Europe/Amsterdam',
    'charset' => 'UTF-8',
];
