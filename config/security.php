<?php declare(strict_types=1);

return [
    'session_name' => $_ENV['SESSION_NAME'] ?? 'voetbalpoule_session',
    'session_lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 7200),
    'csrf_token_name' => $_ENV['CSRF_TOKEN_NAME'] ?? '_token',
    'csrf_token_length' => (int) ($_ENV['CSRF_TOKEN_LENGTH'] ?? 40),
    'password_algo' => PASSWORD_BCRYPT,
    'password_cost' => 12,
    'cookie_secure' => filter_var($_ENV['APP_ENV'] ?? 'production', FILTER_VALIDATE_BOOLEAN) === false
        && ($_ENV['APP_ENV'] ?? 'production') === 'production',
    'cookie_http_only' => true,
    'cookie_same_site' => 'Lax',
    'upload_path' => $_ENV['UPLOAD_PATH'] ?? 'storage/uploads/logos',
    'upload_max_size' => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 2 * 1024 * 1024),
    'upload_allowed_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
];
