# Команды запуска перед новой сессией разработки

## 1) Открыть проект

```powershell
cd C:\Users\maxxx\Documents\githubProjects\jbi
```

## 2) Поднять Docker-контейнеры (MySQL + phpMyAdmin)

```powershell
docker start jbi-mysql
docker start jbi-pma
```

Проверка:

```powershell
docker ps --filter name=jbi-mysql --filter name=jbi-pma
```

## 3) Запустить локальный PHP API (в отдельном терминале)

```powershell
php -S localhost:8000 -t .
```

Проверка API:

```powershell
Invoke-WebRequest http://localhost:8000/api/auth.php -UseBasicParsing | Select-Object -ExpandProperty Content
Invoke-WebRequest http://localhost:8000/api/products.php -UseBasicParsing | Select-Object -ExpandProperty Content
```

## 4) Запустить фронтенд Vite (в отдельном терминале)

```powershell
npm run dev
```

## 5) Быстрые URL для работы

- Сайт: http://localhost:5173
- Админка: http://localhost:5173/admin
- API auth: http://localhost:8000/api/auth.php
- API products: http://localhost:8000/api/products.php
- phpMyAdmin: http://localhost:8081

## 6) Если не пускает в админку (сброс тестового админа)

```powershell
$hash = php -r "echo password_hash('Admin123!', PASSWORD_DEFAULT);"
docker exec jbi-mysql mysql -u root -proot -D jbi_db -e "DELETE FROM admins; INSERT INTO admins (email, password_hash) VALUES ('admin@local.test', '$hash');"
```

## 7) Полезные проверки при проблемах

Порт API:

```powershell
Get-NetTCPConnection -LocalPort 8000 -State Listen | Select-Object LocalAddress,LocalPort,State,OwningProcess
```

Доступность phpMyAdmin:

```powershell
Invoke-WebRequest http://localhost:8081/ -UseBasicParsing | Select-Object -ExpandProperty StatusCode
```
