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

// GET — проверить статус сессии
if ($method === 'GET') {
    jsonResponse(['authenticated' => !empty($_SESSION['admin_id'])]);
}

// POST — логин
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    $email    = trim($body['email']    ?? '');
    $password = trim($body['password'] ?? '');

    if ($email === '' || $password === '') {
        errorResponse('Email и пароль обязательны');
    }

    $pdo  = getDb();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        errorResponse('Неверный email или пароль', 401);
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    jsonResponse(['authenticated' => true]);
}

// DELETE — выход
if ($method === 'DELETE') {
    session_destroy();
    jsonResponse(['authenticated' => false]);
}

errorResponse('Метод не поддерживается', 405);
