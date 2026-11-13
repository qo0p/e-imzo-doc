# client — демо-сайт (PHP + Apache)

Демо интеграции E-IMZO: вход по ЭЦП, подписание документа, ID-карта (`eimzoidcard/`).

## Файлы

| Файл | Назначение |
|---|---|
| `index.php` | Страница входа по ЭЦП |
| `auth.php` | Backend: проверка PKCS#7 → `/backend/auth` |
| `cabinet.php` | Кабинет: подписание документа |
| `verify.php` | Backend: проверка подписи → `/backend/pkcs7/verify` |
| `test.php` | Тестовая страница |
| `config.php` | URL методов e-imzo-server |
| `e-imzo.js`, `e-imzo-client.js` | Библиотека E-IMZO (CAPIWS) |
| `e-imzo-init.js` | Инициализация и API-KEY |
| `eimzoidcard/` | Вход/подпись через E-IMZO ID-карту (мобильное приложение) |

## Исходный код

Код клиента не изменён — это исходный демо-код (`config.php` с адресом по умолчанию `http://127.0.0.1:8080`, API-KEY в `e-imzo-init.js`).

## Docker

- Образ: `php:8.3.36-apache` (curl уже включён).
- `network_mode: service:proxy` — контейнер использует сеть proxy:
  - Apache слушает `localhost:80`, proxy отдаёт его наружу на `8082`;
  - `127.0.0.1:8080` из `config.php` — это внутренний listener proxy, который ведёт на e-imzo-server.
- Собственных портов и сетей нет. При пересоздании proxy пересоздайте и client: `docker compose up -d --force-recreate client`.
- Папка `client/` подключена как volume в `/var/www/html` — изменения видны сразу.

Без Docker: скопируйте папку на любой PHP-сервер (PHP 8+, curl). e-imzo-server должен быть доступен на `127.0.0.1:8080`, пути `/frontend/*` должны проксироваться на него.
