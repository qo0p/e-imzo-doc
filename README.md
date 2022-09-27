# E-IMZO - ИНСТРУКЦИЯ ПО ИНТЕГРАЦИИ

## 1. E-IMZO
## 1.1. Создание документа PKCS#7

Для создание документа [PKCS#7](https://www.rfc-editor.org/rfc/rfc2315) применяется функция [`create_pkcs7`](http://127.0.0.1:64646/apidoc.html#pkcs7.create_pkcs7)

    CAPIWS.callFunction({
        plugin    :"pkcs7",
        name      :"create_pkcs7",
        arguments :[
          //Данные в кодировке BASE64 (будут предваритьльно декодированы, подписаны и вложены в документ)
          data_64,
          //Идентификатор ключа подписывающего лица (полученный из фукнции других плагинов)
          id,
          //Возможные значения: 'yes' - будет создан PKCS#7/CMS документ без вложения исходных данных, 'no' или '' - будет создан PKCS#7/CMS документ с вложением исходных данных
          detached
        ]
      },
      function(event, data){
        console.log(data);
      },
      function(error){
        window.alert(error);
      }
    );

Параметр `id`:
 - Если нужно подписать ключем PFX, то `id` нужно получить из функции `load_key`
 - Если нужно подписать ключем ID-карты, то `id` = `"idcard"`

Смотрите пример https://dls.yt.uz/pfx-idcard-pkcs7.html

## 2. E-IMZO-SERVER

E-IMZO-SERVER - ПО предназначека для Аутентификации пользователя по ЭЦП и Проверки подписи PKCS#7 документа. 

## 2.1. Запуск и настройка

Для запуска требуется:
 - JRE v1.8.
 - Интернет соединение до сервера `vpn.e-imzo.uz:3443` (`testvpn.e-imzo.uz:2443` для тестирования).
 - Файлы конфигурации и VPN-ключи.

Запуск выполняется командой:

    java -Dfile.encoding=UTF-8 -jar e-imzo-server.jar config.properties

После запуска в консоле напечатается лог примерно следующего содержания:
```
Sep 27, 2022 9:55:03 AM uz.yt.eimzo.server.Application main
INFO: e-imzo-server version: 1.1.1
********************************************************************************

УСЛОВИЯ ИСПОЛЬЗОВАНИЯ ПРОГРАММНОГО ОБЕСПЕЧЕНИЯ E-IMZO-SERVER 
НАУЧНО-ИНФОРМАЦИОННОГО ЦЕНТРА НОВЫХ ТЕХНОЛОГИЙ (НИЦ)

Эти условия являются соглашением между НИЦ и Вами. Пожалуйста, прочтите их. 
Они применяются к вышеуказанному программному обеспечению. 

Используя это программное обеспечение, Вы тем самым подтверждаете свое согласие 
соблюдать данные условия. Если Вы не согласны, не используйте это программное 
обеспечение. 

Программное обеспечение можно использовать только в пределах прав, 
предоставляемых заключенным договором (для проверки электронной цифровой 
подписи формата PKCS#7).

Данное программное обеспечение содержит другие модули. Использование модулей, 
без заключенного письменного договора является нарушением авторского права НИЦ.

НИЦ не несет ответственности за последствия использования  модулей 
E-IMZO-SERVER без заключенного письменного договора.

********************************************************************************

Sep 27, 2022 9:55:03 AM uz.yt.eimzo.server.Application loadConfig
INFO: Config file:config.properties
Sep 27, 2022 9:55:03 AM uz.yt.eimzo.server.Application loadConfig
INFO: Loading config from file:/e-imzo-server/config.properties
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.service.cache.local.LocalCache <init>
INFO: Using LocalCache
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.Application main
INFO: Registered: /frontend/challenge
SLF4J: Failed to load class "org.slf4j.impl.StaticLoggerBinder".
SLF4J: Defaulting to no-operation (NOP) logger implementation
SLF4J: See http://www.slf4j.org/codes.html#StaticLoggerBinder for further details.
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.service.notifier.vpn.VpnNotifier <init>
INFO: Using VpnNotifier
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.Application main
INFO: Registered: /backend/auth
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.Application main
INFO: Registered: /frontend/timestamp/pkcs7
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.Application main
INFO: Registered: /frontend/timestamp/data
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.Application main
INFO: Registered: /backend/pkcs7/verify/attached
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.Application main
INFO: Registered: /backend/pkcs7/verify/detached
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.Application main
INFO: Registered: /frontend/pkcs7/make-attached
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.Application main
INFO: Registered: /frontend/pkcs7/join
Sep 27, 2022 9:55:04 AM uz.yt.eimzo.server.Application main
INFO: Started http server on: /0.0.0.0:8080

```
Содержание файла конфигурации `config.properties`:
```
# слушать со всех IP-адресов сетевых карт и слушать порт 
listen.ip=0.0.0.0
listen.port=8080

# Адрес VPN-сервера
vpn.tls.enabled=yes
vpn.connect.host=vpn.e-imzo.uz
vpn.connect.port=3443

# Файлы VPN-ключей
vpn.key.file.path=keys/example.uz-2022-10-24.key
vpn.key.password=19E581A1AF9382F0
vpn.truststore.file.path=keys/vpn.jks
tsp.jks.file.path=keys/truststore.jks
```
*Тестовая конфигурация может отличаться от приведенной выше конфигурации*

## 3.1. Описание методов

E-IMZO-SERVER предоставляет REST-API методы к которым может обращаться Backend приложение или HTML/JavaScript приложение на прямую.

Методы начинающиеся с `/backend` **должны быть доступны только Backend приложению**, а методы начинающиеся с `/frontend` могут быть доступны как Backend приложению так и HTML/JavaScript приложению.

Изоляцию методов можно осуществить с помощю `Nginx`.
Пример конфигурации `Nginx`:
```
server {
	listen 80;
	
	root /usr/share/nginx/html;
	index index.html index.htm;
	
	server_name example.uz;

	location /frontend {
		proxy_set_header   Host             $host;
		proxy_set_header   X-Real-IP        $remote_addr;
		proxy_set_header   X-Forwarded-For  $proxy_add_x_forwarded_for;

		proxy_pass http://E-IMZO-SERVER:8080;
	}	
  
	location / {
		proxy_set_header   Host             $host;
		proxy_set_header   X-Real-IP        $remote_addr;
		proxy_set_header   X-Forwarded-For  $proxy_add_x_forwarded_for;

		proxy_pass http://YOUR-BACKEND-APP:8080;
	}

}
```
`YOUR-BACKEND-APP:8080` - IP-Адрес и порт сервера где работает ваше Backend приложение.

`E-IMZO-SERVER:8080` - IP-Адрес и порт сервера где работает E-IMZO-SERVER.

### 3.1.1. /frontend/challenge
### 3.1.2. /backend/auth
### 3.1.3. /frontend/timestamp/pkcs7
### 3.1.4. /backend/pkcs7/verify/attached
### 3.1.4. /backend/pkcs7/verify/detached
### 3.1.5. /frontend/pkcs7/make-attached
### 3.1.6. /frontend/pkcs7/join
## 4. UseCase
### 3.1. Authentication
### 3.2. Sign/Verify

