<?php
// Настройки подключения к БД — подставьте данные из панели Beget
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'jbi_db');
define('DB_USER', 'jbi_user');
define('DB_PASS', 'jbi_pass');

// URL вашего сайта (используется в CORS)
define('SITE_URL', 'http://localhost:5173');

// Допустимые CORS-источники
define('ALLOWED_ORIGINS', [
    'http://localhost:5173',
    'http://localhost:5174',
    SITE_URL,
]);
