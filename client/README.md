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

## Адрес e-imzo-server

`config.php` и `eimzoidcard/config.php` берут адрес из переменной окружения:

| Переменная | По умолчанию | В docker compose |
|---|---|---|
| `EIMZO_SERVER_URL` | `http://127.0.0.1:8080` | `http://proxy:8080` |

PHP передаёт заголовки `Host` (домен сайта) и `X-Real-IP` (IP пользователя), как требует e-imzo-server.

## API-KEY

В `e-imzo-init.js` прописаны только локальные ключи: `null`, `localhost`, `127.0.0.1`.
Ключи остальных доменов хранятся в [Vault](../vault/README.md); при загрузке страницы JS получает их с `/api-keys.json` (отдаёт [proxy](../proxy/README.md)) и добавляет к локальным. Если запрос не удался — используются только локальные ключи.

## Docker

- Образ: `php:8.3.36-apache` (curl уже включён).
- Порт наружу не публикуется — сайт открывается через proxy: http://localhost:8082
- Папка `client/` подключена как volume в `/var/www/html` — изменения видны сразу (для разработки). В production volume можно убрать, код будет из образа.

Без Docker: скопируйте папку на любой PHP-сервер (PHP 8+, расширение curl) и задайте `EIMZO_SERVER_URL` или оставьте значение по умолчанию. Пути `/frontend/*` должны проксироваться на e-imzo-server.
