# vault — HashiCorp Vault

Хранит API-KEY E-IMZO для доменов (кроме локальных `null`, `localhost`, `127.0.0.1`, которые прописаны в `client/e-imzo-init.js`).

## Файлы

| Файл | Назначение |
|---|---|
| `Dockerfile` | Образ на базе `hashicorp/vault:1.18` |
| `entrypoint.sh` | Запускает Vault (dev mode), затем `init.sh` |
| `init.sh` | Загружает ключи, создаёт политику и токен для proxy |
| `api-keys.json` | Ключи по умолчанию `{домен: ключ}` |
| `nginx-policy.hcl` | Политика proxy: только чтение `secret/data/eimzo/api-keys` |

Ключи по умолчанию: `test.e-imzo.uz`, `test.e-imzo.local`.

## Как работает

1. `entrypoint.sh` запускает `vault server -dev`.
2. `init.sh` записывает `api-keys.json` в `secret/eimzo/api-keys`, создаёт политику `eimzo-nginx` и токен `NGINX_VAULT_TOKEN`.
3. Read-only токен записывается в volume `vault-token` для proxy.
4. Healthcheck становится `healthy`, после этого стартует proxy.

## Токены — `vault.env`

Токены лежат в `vault.env` в корне проекта (не в git). Шаблон — `vault.env.example`:

```sh
cp vault.env.example vault.env
# задайте значения, например: openssl rand -hex 24
```

| Переменная | Назначение |
|---|---|
| `VAULT_DEV_ROOT_TOKEN_ID` | Root-токен, используется только внутри контейнера vault |
| `NGINX_VAULT_TOKEN` | Read-only токен для proxy (политика `eimzo-nginx`) |

`vault.env` получает только контейнер vault. Read-only токен vault записывает в общий volume `vault-token` (`/run/vault-token/nginx-token`), proxy читает его оттуда — root-токен в proxy не попадает.

## Доступ

Порт Vault **не публикуется**. Vault находится в сети `secrets` (`internal`): к нему подключён только proxy, нет интернета и доступа с хоста. Администрирование — через `docker compose exec`.

## Добавить / изменить ключ

```sh
docker compose exec vault sh -c \
  'VAULT_TOKEN=$VAULT_DEV_ROOT_TOKEN_ID vault kv patch secret/eimzo/api-keys example.uz=КЛЮЧ...'
docker compose restart proxy   # или подождать до 5 минут
```

Чтобы ключ сохранился после пересоздания контейнера, добавьте его в `api-keys.json` и пересоберите:

```sh
docker compose up -d --build vault
```

## Важно

Dev mode хранит данные в памяти: после перезапуска контейнера Vault заново загружает `api-keys.json`, изменения через `kv patch` теряются. Для production нужен Vault с постоянным хранилищем (file/raft), unseal и отдельные токены/AppRole.
