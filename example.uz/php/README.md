# Windows operatsion tizimida ishlab chiquvchilar uchun demo saytni o‘rnatish va test qilish  

Kompyuteringizda **TCP-portlari 80, 8080, 9123 band emasligiga** ishonch hosil qiling.  

---

## 1. NGINX o‘rnatish  

1. **NGINX yuklab oling**:  
   Rasmiy saytdan yuklab oling: [http://nginx.org/ru/download.html](http://nginx.org/ru/download.html)  

2. Ushbu qo‘llanmada **_1.26.1_ versiyasi** ishlatiladi:  
   Yuklab olish: [http://nginx.org/download/nginx-1.26.1.zip](http://nginx.org/download/nginx-1.26.1.zip)  

3. **Arxivni oching**:  
   `nginx-1.26.1.zip` faylini `C:\` diskka chiqarib oling.  

4. **Papka nomini o‘zgartiring**:  
   `C:\nginx-1.26.1` papkasini `C:\nginx` deb o‘zgartiring.  

5. **Konfiguratsiya faylini tahrirlash**:  
   `C:\nginx\conf\nginx.conf` faylini oching va `server` blokini **`# !!! НАЧАЛО ИЗМЕНЕНИЙ !!! #` va `# !!! КОНЕЦ ИЗМЕНЕНИЙ !!! #`** orasida mos ravishda o‘zgartiring.
   
```
server {
        listen       80;
        server_name  localhost;

        #charset koi8-r;

        #access_log  logs/host.access.log  main;


        # !!! НАЧАЛО ИЗМЕНЕНИЙ !!! #

        index index.php;
        root c:/nginx/html;

        location /frontend {
            proxy_set_header   Host             $host;
            proxy_set_header   X-Real-IP        $remote_addr;
            proxy_set_header   X-Forwarded-For  $proxy_add_x_forwarded_for;

            proxy_pass http://127.0.0.1:8080;
        }	

        location / {
            try_files $uri /index.php$is_args$args;
        }

        location ~ \.php {
            try_files $uri =404;
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param SCRIPT_NAME $fastcgi_script_name;
            fastcgi_index index.php;
            fastcgi_pass 127.0.0.1:9123;
        }

        # !!! КОНЕЦ ИЗМЕНЕНИЙ !!! #

        #error_page  404              /404.html;

        ...
        ...
        ...
```

Скопируйте папку `demo` (из этого репозитория) в папку `c:\nginx\html`

Запустите `cmd.exe` от имени _Администратора_, переидите в папку `c:\nginx` и запустите nginx командой `nginx`

## 2. PHP

Скачайте php с сайта https://windows.php.net/download/

В инструкции применяется версия _PHP 8.3 (8.3.10) VS16 x64 Thread Safe (2024-Jul-30 18:09:04)_ (https://windows.php.net/downloads/releases/php-8.3.10-Win32-vs16-x64.zip)

Распакуйте содержимое `php-8.3.10-Win32-vs16-x64.zip` в `c:\nginx\php` (предварительно создав папку `php`)

Скопируйте файл `c:\nginx\php\php.ini-production` в `c:\nginx\php\php.ini`

Откройте файл `c:\nginx\php\php.ini` и измените следующие параметры соответственно

```
short_open_tag = On

extension_dir = "c:/nginx/php/ext"

extension=curl

```

Запустите `cmd.exe` от имени _Администратора_, переидите в папку `c:\nginx\php` и запустите php командой `php-cgi.exe -b 127.0.0.1:9123`

## 3. JAVA

Скачайте и установите JRE 1.8

В инструкции применяется версия _1.8.0_361_ и установлено в папке `c:\Program Files (x86)\Java\jre1.8.0_361`

## 4. E-IMZO-SERVER

Распакуйте `e-imzo-server-v1.9.1.zip` в `c:\` диск

Откройте `test-example.uz-2024-12-31.zip` в котором есть папка `keys` (VPN-ключи) и файл `test-config.properties` (файл конфигурации) и скопируйте их в папку `c:\e-imzo-server`

Запустите `cmd.exe` от имени _Администратора_, переидите в папку `c:\e-imzo-server` и запустите e-imzo-server командой `"c:\Program Files (x86)\Java\jre1.8.0_361\bin\java" -jar e-imzo-server.jar test-config.properties`

Чтобы проверить VPN-соединение, откройте в браузере http://localhost:8080/ping , должен вернуть JSON как указано в инструкции

## 5. E-IMZO

Скачайте с сайта https://e-imzo.soliq.uz/ и установите E-IMZO от имени _Администратора_

Инструкция по установке: https://esi.uz/index/help

Скопируйте ваши PFX-файлы в папку `c:\DSKEYS`

## 6. DEMO сайт

Откройте в браузере http://localhost/demo/index.php

Выберите ключ ЭЦП и выполните вход

Введите текст для подписи и подпишите, посмотрите результат проверки
