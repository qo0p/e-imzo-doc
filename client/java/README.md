# Библиотека клиента для вызова методов E-IMZO-SERVER

## Требования

Для применения библиотеки в Java проекте подключить библиотеку с помощью Maven

```
<dependency>
    <groupId>io.github.qo0p</groupId>
    <artifactId>e-imzo-server-client</artifactId>
    <version>1.7.6</version>
</dependency>
```

Импортируйте Java-классы

```

import io.github.qo0p.eimzo.server.client.Client;
import io.github.qo0p.eimzo.server.client.http.HttpClient;
import io.github.qo0p.eimzo.server.client.json.AuthJsonResponse;
import io.github.qo0p.eimzo.server.client.json.Pkcs7VerifyJsonResponse;

```

## Пример вызова методов

```
    @Test
    public void testAuth() throws Exception {

        URL baseUrl = new URL("http://127.0.0.1:8080");

        Client client = new HttpClient(baseUrl);

        String userRealIP = "1.2.3.4";
        String host = "example.uz";

        String pkcs7b64 = "MIAGCS...AAAAAAAA=";

        AuthJsonResponse response = client.auth(userRealIP, host, pkcs7b64);
        
        System.out.println(response.getStatus());
    }

```

```
    @Test
    public void testVerifyPkcs7Attached() throws Exception {

        URL baseUrl = new URL("http://127.0.0.1:8080");

        Client client = new HttpClient(baseUrl);

        String userRealIP = "1.2.3.4";
        String host = "example.uz";

        String pkcs7b64 = "MIAGCSqG...6bAAAAAAAA";

        Pkcs7VerifyJsonResponse response = client.verifyPkcs7Attached(userRealIP, host, pkcs7b64);
        
        System.out.println(response.getStatus());
    }

```

```
    @Test
    public void testVerifyPkcs7Detached() throws Exception {

        URL baseUrl = new URL("http://127.0.0.1:8080");

        Client client = new HttpClient(baseUrl);

        String userRealIP = "1.2.3.4";
        String host = "example.uz";

        String data64 = "c29tZS...udA==";
        String pkcs7b64 = "MIAGCSqG...AAAAAAA==";

        Pkcs7VerifyJsonResponse response = client.verifyPkcs7Detached(userRealIP, host, data64, pkcs7b64);
        
        System.out.println(response.getStatus());
    }

```
