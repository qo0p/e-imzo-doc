# proxy — nginx

Единственная точка входа. Вся конфигурация в одном файле: [`nginx.conf`](nginx.conf).

## Маршруты

Публичный сервер — `:80` (на хосте **`8082`**):

| Путь | Куда |
|---|---|
| `/` | `client:80` (PHP-сайт) |
| `/frontend/*` | `server:8080` (e-imzo-server) |
| `/backend/*` | `404` — закрыто снаружи |
| `/api-keys.json` | API-KEY из Vault |

Внутренний сервер — `:8080` (не публикуется): `client` → `proxy:8080` → `server:8080`.
`Host` и `X-Real-IP`, переданные PHP, пробрасываются в e-imzo-server.

## API-KEY из Vault

Скрипт [`docker-entrypoint.d/40-vault-api-keys.sh`](docker-entrypoint.d/40-vault-api-keys.sh) выполняется при старте nginx:

1. Читает секрет из Vault (`{домен: ключ}`).
2. Записывает `/etc/nginx/eimzo/api-keys.json` в формате `["домен","ключ",...]`.
3. Обновляет файл в фоне каждые `VAULT_REFRESH_INTERVAL` секунд.

Если Vault недоступен — отдаётся `[]`, сайт работает с локальными ключами.

| Переменная | По умолчанию |
|---|---|
| `VAULT_ADDR` | `http://vault:8200` |
| `VAULT_TOKEN` | `nginx-read-token` (только чтение) |
| `VAULT_SECRET_PATH` | `secret/data/eimzo/api-keys` |
| `VAULT_REFRESH_INTERVAL` | `300` |

## Команды

```sh
docker compose up -d --build proxy                 # после изменения nginx.conf
docker compose restart proxy                       # сразу перечитать ключи из Vault
docker compose exec proxy nginx -t                 # проверить конфиг
curl http://localhost:8082/api-keys.json
```
