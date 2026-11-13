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
| `config.php` | URL методов e-imzo-server (`http://server:8080`) |
| `e-imzo.js`, `e-imzo-client.js` | Библиотека E-IMZO (CAPIWS) |
| `e-imzo-init.js` | Инициализация; локальные API-KEY + ключи доменов из Vault (`/api-keys.json`) |
| `eimzoidcard/` | Вход/подпись через E-IMZO ID-карту (мобильное приложение) |

## Исходный код

Отличия от исходного демо-кода:

- `config.php`, `eimzoidcard/config.php` — адрес `http://server:8080` (сервис `server` в docker compose).
- `e-imzo-init.js` — в коде только локальные ключи (`null`, `localhost`, `127.0.0.1`); ключи `test.e-imzo.uz`, `test.e-imzo.local` и др. загружаются из Vault через `/api-keys.json`.

## Docker

- Образ: `php:8.3.36-apache` (curl уже включён).
- `network_mode: service:proxy` — контейнер использует сеть proxy:
  - Apache слушает `localhost:80`, proxy отдаёт его наружу на `8082`;
  - `server:8080` из `config.php` — e-imzo-server в сети `eimzo_lan`.
- Собственных портов и сетей нет. При пересоздании proxy пересоздайте и client: `docker compose up -d --force-recreate client`.
- Папка `client/` подключена как volume в `/var/www/html` — изменения видны сразу.

Без Docker: скопируйте папку на любой PHP-сервер (PHP 8+, curl). укажите адрес e-imzo-server в `config.php`, пути `/frontend/*` должны проксироваться на него.
