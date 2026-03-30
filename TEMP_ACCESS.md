# Временные доступы (локально)

> Временный файл. Удалить перед деплоем.

## Frontend

- URL: http://localhost:5173
- API base (.env): http://localhost:8000/api

## API / PHP

- URL: http://localhost:8000
- Endpoints:
  - http://localhost:8000/api/auth.php
  - http://localhost:8000/api/products.php

## База данных (из api/config.php)

- Host: 127.0.0.1
- DB: jbi_db
- User: jbi_user
- Password: jbi_pass

## Docker MySQL

- Container: jbi-mysql
- Root user: root
- Root password: root
- Port: 3306

## phpMyAdmin

- URL: http://localhost:8081
- User: root
- Password: root

## Админка приложения

- URL: http://localhost:5173/admin
- Email: admin@local.test
- Password: Admin123!

## Полезные команды

- Запуск PHP API:
  - php -S localhost:8000 -t .
- Хеш пароля:
  - php -r "echo password_hash('Admin123!', PASSWORD_DEFAULT);"
- Пересоздать админа в MySQL:
  - docker exec jbi-mysql mysql -u root -proot -D jbi_db -e "DELETE FROM admins; INSERT INTO admins (email, password_hash) VALUES ('admin@local.test', '<HASH>');"
