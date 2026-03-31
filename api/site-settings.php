<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

setCorsHeaders();

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

$method = $_SERVER['REQUEST_METHOD'];

function requireAuth(): void {
    if (empty($_SESSION['admin_id'])) {
        errorResponse('Требуется авторизация', 401);
    }
}

function ensureSiteSettingsTable(PDO $pdo): void {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS site_settings (
            id TINYINT PRIMARY KEY,
            phone VARCHAR(255) NOT NULL DEFAULT '',
            email VARCHAR(255) NOT NULL DEFAULT '',
            whatsapp VARCHAR(500) NOT NULL DEFAULT '',
            telegram VARCHAR(500) NOT NULL DEFAULT '',
            max VARCHAR(500) NOT NULL DEFAULT '',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    // Add missing columns when upgrading from an older schema
    foreach (['whatsapp', 'telegram', 'max'] as $col) {
        try {
            $pdo->exec("ALTER TABLE site_settings ADD COLUMN `$col` VARCHAR(500) NOT NULL DEFAULT ''");
        } catch (\PDOException) {
            // Column already exists — ignore
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (id, phone, email) VALUES (1, ?, ?) ON DUPLICATE KEY UPDATE id = id'
    );
    $stmt->execute(['+7(918)654-32-10', 'hello@example.com']);
}

function loadSiteSettings(PDO $pdo): array {
    ensureSiteSettingsTable($pdo);

    $stmt = $pdo->query('SELECT phone, email, whatsapp, telegram, max FROM site_settings WHERE id = 1 LIMIT 1');
    $row = $stmt->fetch();

    if ($row) {
        return [
            'phone'     => (string)($row['phone']     ?? ''),
            'email'     => (string)($row['email']     ?? ''),
            'whatsapp'  => (string)($row['whatsapp']  ?? ''),
            'telegram'  => (string)($row['telegram']  ?? ''),
            'max'       => (string)($row['max']       ?? ''),
        ];
    }

    $pdo->exec("INSERT INTO site_settings (id, phone, email) VALUES (1, '', '')");

    return [
        'phone'    => '',
        'email'    => '',
        'whatsapp' => '',
        'telegram' => '',
        'max'      => '',
    ];
}

if ($method === 'GET') {
    $pdo = getDb();
    jsonResponse(loadSiteSettings($pdo));
}

if ($method === 'POST') {
    requireAuth();

    $body = json_decode(file_get_contents('php://input'), true);
    $phone     = trim((string)($body['phone']     ?? ''));
    $email     = trim((string)($body['email']     ?? ''));
    $whatsapp  = trim((string)($body['whatsapp']  ?? ''));
    $telegram  = trim((string)($body['telegram']  ?? ''));
    $max       = trim((string)($body['max']       ?? ''));

    if ($phone === '') {
        errorResponse('Телефон обязателен');
    }

    if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        errorResponse('Укажите корректный email');
    }

    $pdo = getDb();
    ensureSiteSettingsTable($pdo);
    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (id, phone, email, whatsapp, telegram, max) VALUES (1, ?, ?, ?, ?, ?)'
        . ' ON DUPLICATE KEY UPDATE phone = VALUES(phone), email = VALUES(email),'
        . ' whatsapp = VALUES(whatsapp), telegram = VALUES(telegram), max = VALUES(max)'
    );
    $stmt->execute([$phone, $email, $whatsapp, $telegram, $max]);

    jsonResponse(loadSiteSettings($pdo));
}

errorResponse('Метод не поддерживается', 405);
