# server — E-IMZO-SERVER

Docker-образ для `e-imzo-server` (JRE 8, `amazoncorretto:8-alpine3.22-jre`).

Документация: https://github.com/qo0p/e-imzo-doc (раздел «2. E-IMZO-SERVER» в корневом [README](../README.md)).

> e-imzo-server официально распространяется только через канал разработчика (см. раздел «Официальное предупреждение» в корневом README). Используйте `e-imzo-server.jar` и `lib/` только из официального источника.

## Содержимое

| Файл / папка | Назначение |
|---|---|
| `e-imzo-server.jar`, `lib/` | Сервер и его зависимости |
| `config.properties` | Конфигурация (порт, VPN, ключи и т.д.) |
| `logging.properties` | Логирование (вывод в консоль) |
| `keys/` | VPN-ключ, `vpn.jks`, `truststore.jks` (не хранятся в git) |
| `Dockerfile` | Образ, порты `8080`, `8081` |

## В docker compose

- Имя сервиса: `server` (контейнер `e-imzo-server`).
- Порты наружу **не публикуются**. Сеть `eimzo_lan`:
  - браузер → [proxy](../proxy/README.md) `:8082/frontend/*` → `server:8080`
  - PHP-клиент → `server:8080` напрямую (методы `/backend/*`, `client/config.php`)
- `config.properties`, `logging.properties` и `keys/` подключены как volume — после изменения достаточно:

```sh
docker compose restart server
```

## Логи

- Формат задаётся в `logging.properties` (`java.util.logging.SimpleFormatter.format`).
- `entrypoint.sh` пишет вывод сервера в `docker logs` и в файл за день: `server/logs/ddMMyyyy.log` (например `13112026.log`). Новый файл — после полуночи по `TZ` (`Asia/Tashkent` в `docker-compose.yml`).
- Папка `server/logs/` подключена как volume; `*.log` не попадают в git.

```sh
tail -f server/logs/$(date +%d%m%Y).log
```

## Ключи

Положите VPN-ключи в `server/keys/` и пропишите пути в `config.properties`:

```properties
vpn.key.file.path=keys/example.uz-2022-10-24.key
vpn.key.password=...
vpn.truststore.file.path=keys/vpn.jks
tsp.jks.file.path=keys/truststore.jks
```

Переменная окружения `JAVA_OPTS` передаётся в команду запуска `java`.
