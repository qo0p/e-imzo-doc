# proxy — nginx

Единственная точка входа. Вся конфигурация в одном файле: [`nginx.conf`](nginx.conf).

## Маршруты

Публичный сервер — `:8082` (на хосте **`8082`**):

| Путь | Куда |
|---|---|
| `/` | `127.0.0.1:80` — Apache клиента (общая сеть) |
| `/frontend/*` | `server:8080` (e-imzo-server) |
| `/backend/*` | `404` — закрыто снаружи |
| `/api-keys.json` | API-KEY из Vault |

client работает в сети proxy (`network_mode: service:proxy`) и вызывает `/backend/*` напрямую на `http://server:8080` (`client/config.php`). `Host` и `X-Real-IP` задаёт сам PHP.

## Защита

Только port `8082` опубликован. `server`, `client`, `vault` наружу не открыты.

| Что | Как |
|---|---|
| Боты, curl, wget, Postman, Insomnia, Fiddler, python, сканеры… | По `User-Agent` → `444` (соединение закрывается без ответа). Исключение: `/frontend/mobile/upload` (мобильное приложение E-IMZO) |
| `/api-keys.json` | Только `GET` от браузера: `Sec-Fetch-Site: same-origin`, `Sec-Fetch-Mode: cors`, `Sec-Fetch-Dest: empty`, `X-Requested-With: XMLHttpRequest`, иначе `444` |
| Лимиты | `/api-keys.json` — 10 запросов/мин с IP, остальное — 20 запросов/сек, 30 соединений с IP |
| Методы | Только `GET`, `POST`, `HEAD` |
| `/backend/*`, `config.php`, `.*` | `404` снаружи |

Сеть одна — `eimzo_lan` (proxy, server, vault; client — в сети proxy через `service:proxy`).

> **Ограничение.** Ключи всё равно попадают в браузер — иначе E-IMZO не сможет их установить. Пользователь сайта может увидеть их в DevTools браузера, а настойчивый человек может подделать заголовки, повторив шаги браузера. Защита отсекает ботов и простые запросы, но не делает ключи секретными. API-KEY E-IMZO привязан к домену, поэтому на чужом домене он не работает.
>
> `ping`/`telnet` на сам хост закрываются файрволом хоста, не nginx. Контейнеры не отвечают снаружи, кроме порта `8082`.

## API-KEY из Vault

Скрипт [`docker-entrypoint.d/40-vault-api-keys.sh`](docker-entrypoint.d/40-vault-api-keys.sh) выполняется при старте nginx:

1. Читает секрет из Vault (`{домен: ключ}`).
2. Записывает `/etc/nginx/eimzo/api-keys.json` в формате `["домен","ключ",...]`.
3. Обновляет файл в фоне каждые `VAULT_REFRESH_INTERVAL` секунд.

Если Vault недоступен — отдаётся `[]`, сайт работает с локальными ключами.

| Переменная | По умолчанию |
|---|---|
| `VAULT_ADDR` | `http://vault:8200` |
| `VAULT_TOKEN_FILE` | `/run/vault-token/nginx-token` — read-only токен, его пишет vault в общий volume `vault-token` |
| `VAULT_SECRET_PATH` | `secret/data/eimzo/api-keys` |
| `VAULT_REFRESH_INTERVAL` | `300` |

## Команды

```sh
docker compose up -d --build proxy                 # после изменения nginx.conf
docker compose restart proxy                       # сразу перечитать ключи из Vault
docker compose exec proxy nginx -t                 # проверить конфиг
curl http://localhost:8082/api-keys.json
```
