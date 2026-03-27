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

// Эмуляция PUT через POST с _method=PUT
if ($method === 'POST' && ($_POST['_method'] ?? '') === 'PUT') {
    $method = 'PUT';
}

function requireAuth(): void {
    if (empty($_SESSION['admin_id'])) {
        errorResponse('Требуется авторизация', 401);
    }
}

function getUploadErrorMessage(int $code): string {
    return match ($code) {
        UPLOAD_ERR_INI_SIZE => 'Файл превышает лимит upload_max_filesize на сервере',
        UPLOAD_ERR_FORM_SIZE => 'Файл превышает лимит формы',
        UPLOAD_ERR_PARTIAL => 'Файл загружен частично, попробуйте еще раз',
        UPLOAD_ERR_NO_TMP_DIR => 'На сервере отсутствует временная папка для загрузок',
        UPLOAD_ERR_CANT_WRITE => 'Сервер не смог записать файл на диск',
        UPLOAD_ERR_EXTENSION => 'Загрузка остановлена расширением PHP',
        default => "Ошибка загрузки файла: {$code}",
    };
}

function isValidImageUpload(string $tmpPath, array $allowedMimes): bool {
    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        return false;
    }

    $mimeType = $imageInfo['mime'] ?? '';
    return in_array($mimeType, $allowedMimes, true);
}

function sanitizeUploadedFilename(string $filename): string {
    $filename = trim(basename($filename));
    $filename = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);
    $filename = implode('', array_map(
        static fn(string $char): string => match (ord($char)) {
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15,
            16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29,
            30, 31, 127 => '_',
            default => $char,
        },
        str_split($filename)
    ));
    $filename = trim($filename, ". \t\n\r\0\x0B");
    return $filename === '' ? 'file' : $filename;
}

function uploadFile(string $field, string $subdir, array $allowedMimes, array $allowedExts): ?array {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        errorResponse(getUploadErrorMessage((int)$file['error']));
    }
    if ($file['size'] > 10 * 1024 * 1024) { // 10 MB
        errorResponse('Файл слишком большой (максимум 10 MB)');
    }

    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) {
        errorResponse("Недопустимое расширение файла '{$ext}' для поля '{$field}'. Разрешены: " . implode(', ', $allowedExts));
    }

    $isValid = $subdir === 'specs' || isValidImageUpload($file['tmp_name'], $allowedMimes);

    if (!$isValid) {
        errorResponse("Недопустимый тип файла для поля '{$field}' ({$file['name']})");
    }

    $uploadDir = __DIR__ . "/../uploads/$subdir/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest     = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        errorResponse('Не удалось сохранить файл');
    }

    return [
        'url' => "/uploads/$subdir/$filename",
        'original_name' => sanitizeUploadedFilename($file['name']),
    ];
}

function deleteFile(string $url): void {
    if ($url === '') return;
    $path = __DIR__ . '/../' . ltrim($url, '/');
    if (file_exists($path)) {
        unlink($path);
    }
}

function rowToProduct(array $row): array {
    $row['zones'] = json_decode($row['zones'] ?? '[]', true) ?: [];
    $row['spec_name'] = $row['spec_name'] ?? '';
    return $row;
}

// ──────────────── GET ─── список всех продуктов (публичный)
if ($method === 'GET') {
    $pdo  = getDb();
    $stmt = $pdo->query('SELECT * FROM products ORDER BY sort_order ASC, id ASC');
    $rows = $stmt->fetchAll();
    jsonResponse(array_map('rowToProduct', $rows));
}

// ──────────────── POST ─── создание
if ($method === 'POST') {
    requireAuth();

    $imageUpload = uploadFile('image', 'images',
        ['image/jpeg', 'image/png', 'image/webp'],
        ['jpg', 'jpeg', 'png', 'webp']
    );
    $imageUrl = $imageUpload['url'] ?? '';

    $specUpload = uploadFile('spec', 'specs',
        [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        ['pdf', 'doc', 'docx', 'xls', 'xlsx']
    );
    $specUrl = $specUpload['url'] ?? '';
    $specName = $specUpload['original_name'] ?? '';

    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $note        = trim($_POST['note']        ?? '');
    $zones       = json_encode(
        array_values(array_filter(array_map('trim', $_POST['zones'] ?? []))),
        JSON_UNESCAPED_UNICODE
    );

    if ($name === '') {
        errorResponse('Название обязательно');
    }

    $pdo  = getDb();
    $stmt = $pdo->prepare(
        'INSERT INTO products (name, image_url, description, zones, note, spec_url, spec_name) VALUES (?,?,?,?,?,?,?)'
    );
    $stmt->execute([$name, $imageUrl, $description, $zones, $note, $specUrl, $specName]);
    $id = (int)$pdo->lastInsertId();

    $row = $pdo->query("SELECT * FROM products WHERE id = $id")->fetch();
    jsonResponse(rowToProduct($row), 201);
}

// ──────────────── PUT ─── обновление
if ($method === 'PUT') {
    requireAuth();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        errorResponse('Не указан id');
    }

    $pdo  = getDb();
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        errorResponse('Продукт не найден', 404);
    }

    // Загружаем новый файл только если пришёл новый
    $imageUpload = uploadFile('image', 'images',
        ['image/jpeg', 'image/png', 'image/webp'],
        ['jpg', 'jpeg', 'png', 'webp']
    );
    if ($imageUpload !== null) {
        $imageUrl = $imageUpload['url'];
        deleteFile($existing['image_url']);
    } else {
        $imageUrl = $existing['image_url'];
    }

    $specUpload = uploadFile('spec', 'specs',
        [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        ['pdf', 'doc', 'docx', 'xls', 'xlsx']
    );
    if ($specUpload !== null) {
        $specUrl = $specUpload['url'];
        $specName = $specUpload['original_name'];
        deleteFile($existing['spec_url']);
    } else {
        $specUrl = $existing['spec_url'];
        $specName = $existing['spec_name'] ?? '';
    }

    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $note        = trim($_POST['note']        ?? '');
    $zones       = json_encode(
        array_values(array_filter(array_map('trim', $_POST['zones'] ?? []))),
        JSON_UNESCAPED_UNICODE
    );

    if ($name === '') {
        errorResponse('Название обязательно');
    }

    $stmt = $pdo->prepare(
        'UPDATE products SET name=?, image_url=?, description=?, zones=?, note=?, spec_url=?, spec_name=? WHERE id=?'
    );
    $stmt->execute([$name, $imageUrl, $description, $zones, $note, $specUrl, $specName, $id]);

    $row = $pdo->query("SELECT * FROM products WHERE id = $id")->fetch();
    jsonResponse(rowToProduct($row));
}

// ──────────────── DELETE
if ($method === 'DELETE') {
    requireAuth();

    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        errorResponse('Не указан id');
    }

    $pdo  = getDb();
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        errorResponse('Продукт не найден', 404);
    }

    deleteFile($existing['image_url']);
    deleteFile($existing['spec_url']);

    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    jsonResponse(['deleted' => true]);
}

errorResponse('Метод не поддерживается', 405);
