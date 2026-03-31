<?php

function loadEnvFile(string $path): void {
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function envValue(string $key, string $default = ''): string {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return is_string($value) && $value !== '' ? $value : $default;
}

loadEnvFile(dirname(__DIR__) . '/.env');

// Настройки подключения к БД — подставьте данные из панели Beget
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'jbi_db');
define('DB_USER', 'jbi_user');
define('DB_PASS', 'jbi_pass');

// URL вашего сайта (используется в CORS)
define('SITE_URL', 'http://localhost:5173');

// SMTP для заявок на звонок
define('SMTP_HOST', envValue('SMTP_HOST'));
define('SMTP_PORT', (int) envValue('SMTP_PORT', '587'));
define('SMTP_ENCRYPTION', strtolower(envValue('SMTP_ENCRYPTION', 'tls')));
define('SMTP_USERNAME', envValue('SMTP_USERNAME'));
define('SMTP_PASSWORD', envValue('SMTP_PASSWORD'));
define('SMTP_FROM_EMAIL', envValue('SMTP_FROM_EMAIL', SMTP_USERNAME));
define('SMTP_FROM_NAME', envValue('SMTP_FROM_NAME', 'ЖБИ'));
define('SMTP_TIMEOUT', (int) envValue('SMTP_TIMEOUT', '15'));

// Допустимые CORS-источники
define('ALLOWED_ORIGINS', [
    'http://localhost:5173',
    'http://localhost:5174',
    SITE_URL,
]);
