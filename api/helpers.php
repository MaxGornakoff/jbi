<?php
require_once __DIR__ . '/config.php';

function setCorsHeaders(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, ALLOWED_ORIGINS, true)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function jsonResponse(mixed $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function errorResponse(string $message, int $status = 400): never {
    jsonResponse(['error' => $message], $status);
}

function encodeMimeHeader(string $value): string {
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

function buildSmtpSocketTarget(): string {
    if (SMTP_HOST === '') {
        throw new RuntimeException('SMTP не настроен: отсутствует SMTP_HOST');
    }

    if (SMTP_ENCRYPTION === 'ssl') {
        return 'ssl://' . SMTP_HOST;
    }

    return SMTP_HOST;
}

function smtpReadResponse($socket, array $expectedCodes): string {
    $response = '';

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;

        if (strlen($line) >= 4 && $line[3] === ' ') {
            break;
        }
    }

    if ($response === '') {
        throw new RuntimeException('SMTP сервер не ответил');
    }

    $code = (int) substr($response, 0, 3);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException(trim($response));
    }

    return $response;
}

function smtpSendCommand($socket, string $command, array $expectedCodes): string {
    if (fwrite($socket, $command . "\r\n") === false) {
        throw new RuntimeException('Не удалось отправить SMTP-команду');
    }

    return smtpReadResponse($socket, $expectedCodes);
}

function smtpSendMail(string $to, string $subject, string $body): void {
    if (SMTP_USERNAME === '' || SMTP_PASSWORD === '' || SMTP_FROM_EMAIL === '') {
        throw new RuntimeException('SMTP не настроен: проверьте SMTP_USERNAME, SMTP_PASSWORD и SMTP_FROM_EMAIL');
    }

    $socket = @fsockopen(buildSmtpSocketTarget(), SMTP_PORT, $errno, $errstr, SMTP_TIMEOUT);
    if (!is_resource($socket)) {
        throw new RuntimeException("Не удалось подключиться к SMTP: {$errstr} ({$errno})");
    }

    stream_set_timeout($socket, SMTP_TIMEOUT);

    try {
        smtpReadResponse($socket, [220]);
        smtpSendCommand($socket, 'EHLO localhost', [250]);

        if (SMTP_ENCRYPTION === 'tls') {
            smtpSendCommand($socket, 'STARTTLS', [220]);

            $cryptoEnabled = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($cryptoEnabled !== true) {
                throw new RuntimeException('Не удалось включить TLS для SMTP');
            }

            smtpSendCommand($socket, 'EHLO localhost', [250]);
        }

        smtpSendCommand($socket, 'AUTH LOGIN', [334]);
        smtpSendCommand($socket, base64_encode(SMTP_USERNAME), [334]);
        smtpSendCommand($socket, base64_encode(SMTP_PASSWORD), [235]);
        smtpSendCommand($socket, 'MAIL FROM:<' . SMTP_FROM_EMAIL . '>', [250]);
        smtpSendCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
        smtpSendCommand($socket, 'DATA', [354]);

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . encodeMimeHeader(SMTP_FROM_NAME) . ' <' . SMTP_FROM_EMAIL . '>',
            'To: <' . $to . '>',
            'Subject: ' . encodeMimeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n.", "\n..", $body) . "\r\n.";
        if (fwrite($socket, $message . "\r\n") === false) {
            throw new RuntimeException('Не удалось передать тело письма');
        }

        smtpReadResponse($socket, [250]);
        smtpSendCommand($socket, 'QUIT', [221]);
    } finally {
        fclose($socket);
    }
}
