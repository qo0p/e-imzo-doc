

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

Метод нужен для генерации случайного значение `Challenge` которое пользоваетль должен будет подписать и создать PKCS#7 документ.

Пример вызова CURL командой:
```
curl -v http://127.0.0.1:8080/frontend/challenge
```
Ответ
```
{
  "challenge": "9b573e40-cefd-42cc-a534-f6e78b27c2ae",
  "ttl": 120,
  "status": 1,
  "message": ""
}
```
HTTP 200 - означает успешное выполнение HTTP запроса

`status` - код состояния (1 - Успешно, иначе ошибка)

`message` - если `status` не равно 1, то сообщения об ошибки.

`challenge` - случайного значение которое пользоваетль должен будет подписать и создать PKCS#7 документ с помощю E-IMZO.

`ttl` - время жизни `challenge` в секундах.

Смотрите пример http://test.e-imzo.uz/demo/

### 3.1.2. /backend/auth

Метод нужен для аутентификации пользователя по PKCS#7 документу которое содежит `Challenge`

Пример вызова CURL командой:

```
curl -v -H 'X-Real-IP: 1.2.3.4' -H 'Host: example.uz' -X POST -d 'MIAGCSqGSIb...ak5wAAAAAAAA=' http://127.0.0.1:8080/backend/auth
```
В HTTP -заголовке  `X-Real-IP` - должен передаваться  IP-Адрес пользователя а в `Host` - должен передаваться доменное имя сайта куда пользователь выполяет Вход.

Тело запроса должно содержать Base64-закодированный PKCS#7 документ.

Ответ:
```
{
  "subjectCertificateInfo": {
    "serialNumber": "218711a92",
    "subjectName": {
      "1.2.860.3.16.1.2": "11111111111111",
      "CN": "sfasdfa s asdfasd"
    },
    "validFrom": "2022-09-24 17:29:21",
    "validTo": "2022-10-24 17:29:21"
  },
  "status": 1,
  "message": ""
}
```
HTTP 200 - означает успешное выполнение HTTP запроса

`status` - код состояния (1 - Успешно, иначе ошибка)

`message` - если `status` не равно 1, то сообщения об ошибки.

`subjectCertificateInfo` - информация о серитификате пользователя.

Смотрите пример http://test.e-imzo.uz/demo/

### 3.1.3. /frontend/timestamp/pkcs7

Метод нужен для прикрепления токена штампа времени к PKCS#7 документу.

Пример вызова CURL командой:
```
curl -v -H 'X-Real-IP: 1.2.3.4' -H 'Host: example.uz' -X POST -d 'MIAGCSq...GekNAAAAAAAA' http://127.0.0.1:8080/frontend/timestamp/pkcs7
```
В HTTP -заголовке  `X-Real-IP` - должен передаваться  IP-Адрес пользователя а в `Host` - должен передаваться доменное имя сайта где пользователь подписал документ и создал PKCS#7 документ.

Тело запроса должно содержать Base64-закодированный PKCS#7 документ.

Ответ:
```
{
  "pkcs7b64": "MIAGCSqG...bAAAAAAAA",
  "timestampedSignerList": [
    {
      "serialNumber": "218711a92",
      "subjectName": {
        "1.2.860.3.16.1.2": "11111111111111",
        "CN": "sfasdfa s asdfasd"
      },
      "validFrom": "2022-09-24 17:29:21",
      "validTo": "2022-10-24 17:29:21"
    }
  ],
  "status": 1,
  "message": ""
}
```
HTTP 200 - означает успешное выполнение HTTP запроса

`status` - код состояния (1 - Успешно, иначе ошибка)

`message` - если `status` не равно 1, то сообщения об ошибки.

`timestampedSignerList` - информация о серитификате пользователя к подписи которому был прикреплен токен штампа времени. Если массив пуст, то скорее всего отправленный PKCS#7 документ уже содержит токен штампа времени.

`pkcs7b64` - PKCS#7 документ с прикрепленным токеном штампа времени.

Смотрите пример http://test.e-imzo.uz/demo/

### 3.1.4. /backend/pkcs7/verify/attached

Метод для проверки подписи PKCS#7 документа с прикрепленным токеном штампа времени.

Пример вызова CURL командой:
```
curl -v -H 'X-Real-IP: 1.2.3.4' -H 'Host: example.uz' -X POST -d 'MIAGCSq...GekNAAAAAAAA' http://127.0.0.1:8080/backend/pkcs7/verify/attached
```
В HTTP -заголовке  `X-Real-IP` - должен передаваться  IP-Адрес пользователя а в `Host` - должен передаваться доменное имя сайта где пользователь подписал документ и создал PKCS#7 документ.

Тело запроса должно содержать Base64-закодированный PKCS#7 документ.

Ответ:
```
{
  "pkcs7Info": {
    "signers": [
      {
        "signerId": {
          "issuer": "CN=TestCA,O=test.e-imzo.uz",
          "subjectSerialNumber": "218711a92"
        },
        "signingTime": "2022-09-27 11:17:53",
        "signature": "a88ab92b3eed2221925a8532a88ff52d94fc7fa2d0b3579f614822f0723395ee4727de2ed694a21715879637b3181febb94da9016f5a1737d7e9f9920719e90d",
        "digest": "3369cd520c8e556502b9bc0ac34ca69cafee96f2f4a8371a63d4dd7d3a458d05",
        "timeStampInfo": {
          "certificate": [
            {
              "subjectInfo": {
                "CN": "TSA",
                "O": "test.e-imzo.uz"
              },
              "issuerInfo": {
                "CN": "TestCA",
                "O": "test.e-imzo.uz"
              },
              "serialNumber": "218711a50",
              "subjectName": "CN=TSA,O=test.e-imzo.uz",
              "validFrom": "2022-09-15 12:19:13",
              "validTo": "2027-09-15 12:19:13",
              "issuerName": "CN=TestCA,O=test.e-imzo.uz",
              "publicKey": {
                "keyAlgName": "OZDST-1092-2009-2",
                "publicKey": "MGAwGQYJKoZcAw8BAQIBMAwGCiqGXAMPAQECAQEDQwAEQGH1kMo1AelwZDhM/vQLX1CIsnLlyBIWBd/UNZhcfWeGZhSa9BdIDak6Ro2e4lWm77lssBbqQfeVO+ieuYp6qv4="
              },
              "signature": {
                "signAlgName": "OZDST-1106-2009-2-AwithOZDST-1092-2009-2",
                "signature": "aa67e8f444bde68ad365892d94a29fd1cb129acefb318b4099a103e85b1b71337a5535e012663bc0f6fc172096bdde558cedc32e7a8383c35746c1a807bd337b"
              }
            },
            {
              "subjectInfo": {
                "CN": "TestCA",
                "O": "test.e-imzo.uz"
              },
              "issuerInfo": {
                "CN": "TestRoot"
              },
              "serialNumber": "648484238a3380a7",
              "subjectName": "CN=TestCA,O=test.e-imzo.uz",
              "validFrom": "2022-09-15 12:19:13",
              "validTo": "2027-09-15 12:19:13",
              "issuerName": "CN=TestRoot",
              "publicKey": {
                "keyAlgName": "OZDST-1092-2009-2",
                "publicKey": "MGAwGQYJKoZcAw8BAQIBMAwGCiqGXAMPAQECAQEDQwAEQPukdUklFYxOLtzSKjnJqFamWaVX+zbyekEayLz69NIis8fxRZUMIVmGljwvQrPmtHXXDL281MNMM3vAcY0XR/A="
              },
              "signature": {
                "signAlgName": "OZDST-1106-2009-2-AwithOZDST-1092-2009-2",
                "signature": "dabe206257ed646465f56d2c4cd1f993172975d1c1dd2970c8227f95139c44a77fda504a13344683b0a97fe8833dcfc48f3cc2ac281d111bda60b9e94bddb656"
              }
            }
          ],
          "OCSPResponse": "MIIGdTCBr6...YLnpS922Vg==",
          "statusUpdatedAt": "2022-09-27 11:26:08",
          "statusNextUpdateAt": "2022-09-27 11:27:08",
          "digestVerified": true,
          "certificateVerified": true,
          "trustedCertificate": {
            "subjectInfo": {
              "CN": "TestRoot"
            },
            "issuerInfo": {
              "CN": "TestRoot"
            },
            "serialNumber": "cfd4becd127e5063",
            "subjectName": "CN=TestRoot",
            "validFrom": "2022-08-22 12:19:13",
            "validTo": "2042-08-22 12:19:13",
            "issuerName": "CN=TestRoot",
            "publicKey": {
              "keyAlgName": "OZDST-1092-2009-2",
              "publicKey": "MGAwGQYJKoZcAw8BAQIBMAwGCiqGXAMPAQECAQEDQwAEQK9gMs6YLoWRm3C2sN8jTwxs5rC/XhUERW11h0XeNGglOTMO8rtFKNKJjQNwcG5oyn8OLOfnlR0g2ymGNi7ud3c="
            },
            "signature": {
              "signAlgName": "OZDST-1106-2009-2-AwithOZDST-1092-2009-2",
              "signature": "be8a5e9c656ae4b81fdba479c59857f9063e08604d441f3c635ebdf95d976263292200c33b309e7cb5a4d190997f945cdfeef7e6fb3f6f57a5ce477a0caaa129"
            }
          },
          "certificateValidAtSigningTime": true,
          "signerId": {
            "issuer": "CN=TestCA,O=test.e-imzo.uz",
            "subjectSerialNumber": "218711a50"
          },
          "tsaPolicy": "1.2.860.3.2.11.1",
          "time": "2022-09-27 11:18:53",
          "hashAlgorithm": "1.2.860.3.15.1.3.2.1.1",
          "serialNumber": "13219ec520231519",
          "tsa": "6: http://test.e-imzo.uz/cams/tst",
          "messageImprintAlgOID": "1.2.860.3.15.1.3.2.1.1",
          "messageImprintDigest": "e96377344d7bab212bcb6c6b82c25fb61600bb4d3f9645aa40c3ec51e4827605",
          "verified": true
        },
        "certificate": [
          {
            "subjectInfo": {
              "1.2.860.3.16.1.2": "11111111111111",
              "CN": "sfasdfa s asdfasd"
            },
            "issuerInfo": {
              "CN": "TestCA",
              "O": "test.e-imzo.uz"
            },
            "serialNumber": "218711a92",
            "subjectName": "CN=sfasdfa s asdfasd,1.2.860.3.16.1.2=11111111111111",
            "validFrom": "2022-09-24 17:29:21",
            "validTo": "2022-10-24 17:29:21",
            "issuerName": "CN=TestCA,O=test.e-imzo.uz",
            "publicKey": {
              "keyAlgName": "OZDST-1092-2009-2",
              "publicKey": "MGAwGQYJKoZcAw8BAQIBMAwGCiqGXAMPAQECAQEDQwAEQH7cS8X50WPYfroxrnD6DKpUChb845rKi6Dac+B95rZ8QDoQn1o6QcHbuMihP4g8ZyEIxEjChHRrl1b1kOSJUrc="
            },
            "signature": {
              "signAlgName": "OZDST-1106-2009-2-AwithOZDST-1092-2009-2",
              "signature": "a30226a14ad8c6f3ccb0ac57c81262c39543f6bd6aa69f761a9920d68992bf656405edb9d0b91fce36d999ffccdeb1d7bfd343e5c6ae0c3c6c4776ca43b32c38"
            }
          },
          {
            "subjectInfo": {
              "CN": "TestCA",
              "O": "test.e-imzo.uz"
            },
            "issuerInfo": {
              "CN": "TestRoot"
            },
            "serialNumber": "648484238a3380a7",
            "subjectName": "CN=TestCA,O=test.e-imzo.uz",
            "validFrom": "2022-09-15 12:19:13",
            "validTo": "2027-09-15 12:19:13",
            "issuerName": "CN=TestRoot",
            "publicKey": {
              "keyAlgName": "OZDST-1092-2009-2",
              "publicKey": "MGAwGQYJKoZcAw8BAQIBMAwGCiqGXAMPAQECAQEDQwAEQPukdUklFYxOLtzSKjnJqFamWaVX+zbyekEayLz69NIis8fxRZUMIVmGljwvQrPmtHXXDL281MNMM3vAcY0XR/A="
            },
            "signature": {
              "signAlgName": "OZDST-1106-2009-2-AwithOZDST-1092-2009-2",
              "signature": "dabe206257ed646465f56d2c4cd1f993172975d1c1dd2970c8227f95139c44a77fda504a13344683b0a97fe8833dcfc48f3cc2ac281d111bda60b9e94bddb656"
            }
          }
        ],
        "OCSPResponse": "MIIGdTCBr...pS922Vg==",
        "statusUpdatedAt": "2022-09-27 11:26:07",
        "statusNextUpdateAt": "2022-09-27 11:27:07",
        "verified": true,
        "certificateVerified": true,
        "trustedCertificate": {
          "subjectInfo": {
            "CN": "TestRoot"
          },
          "issuerInfo": {
            "CN": "TestRoot"
          },
          "serialNumber": "cfd4becd127e5063",
          "subjectName": "CN=TestRoot",
          "validFrom": "2022-08-22 12:19:13",
          "validTo": "2042-08-22 12:19:13",
          "issuerName": "CN=TestRoot",
          "publicKey": {
            "keyAlgName": "OZDST-1092-2009-2",
            "publicKey": "MGAwGQYJKoZcAw8BAQIBMAwGCiqGXAMPAQECAQEDQwAEQK9gMs6YLoWRm3C2sN8jTwxs5rC/XhUERW11h0XeNGglOTMO8rtFKNKJjQNwcG5oyn8OLOfnlR0g2ymGNi7ud3c="
          },
          "signature": {
            "signAlgName": "OZDST-1106-2009-2-AwithOZDST-1092-2009-2",
            "signature": "be8a5e9c656ae4b81fdba479c59857f9063e08604d441f3c635ebdf95d976263292200c33b309e7cb5a4d190997f945cdfeef7e6fb3f6f57a5ce477a0caaa129"
          }
        },
        "policyIdentifiers": [
          "1.3.6.1.4.1.46709.1.2.2",
          "1.3.6.1.4.1.46709.1.2.4",
          "1.3.6.1.4.1.46709.1.2.1",
          "1.3.6.1.4.1.46709.1.2.3"
        ],
        "certificateValidAtSigningTime": true
      }
    ],
    "documentBase64": "c29tZSBkb2N1bWVudA=="
  },
  "status": 1,
  "message": ""
}
```
HTTP 200 - означает успешное выполнение HTTP запроса

`status` - код состояния (1 - Успешно, иначе ошибка)

`message` - если `status` не равно 1, то сообщения об ошибки.

`pkcs7Info` - информация о проверки подписи PKCS#7 документа.

| Поле | Описание |
|--|--|
| pkcs7Info.documentBase64 | подписанный документ в кодировке (Base64) |
| pkcs7Info.signers[N] | информация о том кто подписал документ |
| pkcs7Info.signers[N].certificate[0] | информация о сертификате пользователя |
| pkcs7Info.signers[N].certificate[1] | информация о сертификате ЦРК |
| pkcs7Info.signers[N].certificate[2] | информация о корневом сертификате (если имеется) |
| pkcs7Info.signers[N].OCSPResponse | OCSP ответ от сервера ЦРК |
| pkcs7Info.signers[N].signingTime | дата на компьютере пользователя при подписании (при получении сервером подписанного документа следует сверить это поле с реальным временем если PKCS#7 документ не содержит токен штампа времени). токен штампа времени - содержит ЭЦП документа и точную дату и время подписи, выдается сервером Доверительной третьей стороны в виде подписанного электронной цифровой подписью документа, которая подтверждает что ЭЦП документа была создана в определенный момент времени. |
| pkcs7Info.signers[N].verified | ЭЦП действительна (если true - да, если false нет) |
| pkcs7Info.signers[N].certificateVerified | цепочка сертификатов действительна (если true - да, если false нет) |
| pkcs7Info.signers[N].revokedStatusInfo | Если сертификат пользователя был приостановлен или отозван, то поле содержит дату и причину. |
| pkcs7Info.signers[N].certificateValidAtSigningTime | сертификат действителен на дату подписи (если true - да, если false нет). За дату подписи берется поле pkcs7Info.signers[N].signingTime или дата и время токена штампа времени (если присутствует) |
| pkcs7Info.signers[N].exception | ошибка при проверке подписи (причина ошибки при проверке подписи или статуса сертификата) |
| UID | Физ.ИНН. |
| 1.2.860.3.16.1.2 | ПИНФЛ |
| 1.2.860.3.16.1.1 | Юр.ИНН (поле отсутствует если субъект является физ. лицом) |



Смотрите пример http://test.e-imzo.uz/demo/

### 3.1.5. /backend/pkcs7/verify/detached
### 3.1.6. /frontend/pkcs7/make-attached
### 3.1.7. /frontend/pkcs7/join
## 4. Use Cases
### 3.1. Authentication
### 3.2. Sign/Verify

