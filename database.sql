-- Выполните этот скрипт в phpMyAdmin на Beget один раз

CREATE TABLE IF NOT EXISTS `admins` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `email`         VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `products` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(255)  NOT NULL,
  `image_url`   VARCHAR(500)  NOT NULL DEFAULT '',
  `description` TEXT          NOT NULL,
  `zones`       TEXT          NOT NULL,
  `note`        TEXT          NOT NULL,
  `spec_url`    VARCHAR(500)  NOT NULL DEFAULT '',
  `spec_name`   VARCHAR(255)  NOT NULL DEFAULT '',
  `sort_order`  INT           NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
-- ВАЖНО: замените email и пароль перед выполнением скрипта
-- Пароль здесь соответствует строке "changeme123"
-- Сгенерируйте свой хеш: php -r "echo password_hash('ВАШ_ПАРОЛЬ', PASSWORD_DEFAULT);"
-- !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
INSERT INTO `admins` (`email`, `password_hash`)
VALUES (
  'admin@yourdomain.ru',
  '$2y$12$placeholder_replace_with_real_hash_generated_by_php'
);
