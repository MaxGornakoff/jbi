<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method Not Allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$phone = trim($data['phone'] ?? '');

if ($phone === '') {
    errorResponse('Укажите номер телефона');
}

if (mb_strlen($phone) > 50) {
    errorResponse('Слишком длинный номер телефона');
}

$pdo = getDb();
$stmt = $pdo->query('SELECT email FROM site_settings WHERE id = 1');
$row  = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
$to   = $row['email'] ?? '';

if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    errorResponse('На сайте не настроен email для получения заявок', 500);
}

$subject = 'Заявка на обратный звонок';
$message = "Поступила заявка на обратный звонок.\n\nНомер телефона: {$phone}\n\nОтветьте клиенту как можно скорее.";

try {
    smtpSendMail($to, $subject, $message);
} catch (RuntimeException $e) {
    errorResponse('Не удалось отправить письмо: ' . $e->getMessage(), 500);
}

jsonResponse(['ok' => true]);
