var EIMZO_MAJOR = 3;
var EIMZO_MINOR = 37;


var errorCAPIWS = 'Ошибка соединения с E-IMZO. Возможно у вас не установлен модуль E-IMZO.';
var errorBrowserWS = 'Браузер не поддерживает технологию WebSocket. Установите последнюю версию браузера.';
var errorUpdateApp = 'ВНИМАНИЕ !!! Установите новую версию приложения E-IMZO.<br /><a href="https://e-imzo.uz/main/downloads/" role="button">Скачать ПО E-IMZO</a>';
var errorWrongPassword = 'Пароль неверный.';


var AppLoad = function () {
    EIMZOClient.API_KEYS = [
        'null', 'E0A205EC4E7B78BBB56AFF83A733A1BB9FD39D562E67978CC5E7D73B0951DB1954595A20672A63332535E13CC6EC1E1FC8857BB09E0855D7E76E411B6FA16E9D',
        'localhost', '96D0C1491615C82B9A54D9989779DF825B690748224C2B04F500F370D51827CE2644D8D4A82C18184D73AB8530BB8ED537269603F61DB0D03D2104ABF789970B',
        '127.0.0.1', 'A7BCFA5D490B351BE0754130DF03A068F855DB4333D43921125B9CF2670EF6A40370C646B90401955E1F7BC9CDBF59CE0B2C5467D820BE189C845D0B79CFC96F',
        'test.e-imzo.uz', 'DE783306B4717AFE4AE1B185E1D967C265AA397A35D8955C7D7E38A36F02798AA62FBABE2ABA15C888FE2F057474F35A5FC783D23005E4347A3E34D6C1DDBAE5',
        'test.e-imzo.local', 'D56ABC7F43A23466D9ADB1A2335E7430FCE0F46B0EC99B578D554333245FC071357AE9E7E2F75F96B73AEEE7E0D61AE84E414F5CD795D8B6484E5645CAF958FC'
    ];
    uiLoading();
    EIMZOClient.checkVersion(function (major, minor) {
        var newVersion = EIMZO_MAJOR * 100 + EIMZO_MINOR;
        var installedVersion = parseInt(major) * 100 + parseInt(minor);
        if (installedVersion < newVersion) {
            uiUpdateApp();
        } else {
            EIMZOClient.installApiKeys(function () {
                uiAppLoad();
            }, function (e, r) {
                if (r) {
                    uiShowMessage(r);
                } else {
                    wsError(e);
                }
            });
        }
    }, function (e, r) {
        if (r) {
            uiShowMessage(r);
        } else {
            uiNotLoaded(e);
        }
    });
}


var uiShowProgress = function () {
    // show loaging indicator
};

var uiHideProgress = function () {
    // hide loaging indicator           
};

var uiLoading = function () {
    // show loaging indicator
};

var uiLoaded = function () {
    // hide loaging indicator       
};

var uiShowMessage = function (message) {
    alert(message);
};

var uiUpdateApp = function () {
    // show message "Update E-IMZO"
};

var uiNotLoaded = function (e) {
    // show message "E-IMZO not installed"
    uiShowMessage(errorCAPIWS + " : " + wsErroCodeDesc(e));
};

var wsErroCodeDesc = function (code) {
    var reason;
    if (code == 1000)
        reason = code + " - " + "Normal closure, meaning that the purpose for which the connection was established has been fulfilled.";
    else if (code == 1001)
        reason = code + " - " + "An endpoint is \"going away\", such as a server going down or a browser having navigated away from a page.";
    else if (code == 1002)
        reason = code + " - " + "An endpoint is terminating the connection due to a protocol error";
    else if (code == 1003)
        reason = code + " - " + "An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).";
    else if (code == 1004)
        reason = code + " - " + "Reserved. The specific meaning might be defined in the future.";
    else if (code == 1005)
        reason = code + " - " + "No status code was actually present.";
    else if (code == 1006)
        reason = code + " - " + "The connection was closed abnormally, e.g., without sending or receiving a Close control frame";
    else if (code == 1007)
        reason = code + " - " + "An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message.";
    else if (code == 1008)
        reason = code + " - " + "An endpoint is terminating the connection because it has received a message that \"violates its policy\". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.";
    else if (code == 1009)
        reason = code + " - " + "An endpoint is terminating the connection because it has received a message that is too big for it to process.";
    else if (code == 1010) // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
        reason = code + " - " + "An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn't return them in the response message of the WebSocket handshake.";
    else if (code == 1011)
        reason = code + " - " + "A server is terminating the connection because it encountered an unexpected condition that prevented it from fulfilling the request.";
    else if (code == 1015)
        reason = code + " - " + "The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can't be verified).";
    else
        reason = code;
    return reason;
};

var wsError = function (e) {
    if (e) {
        uiShowMessage(errorCAPIWS + " : " + wsErroCodeDesc(e));
    } else {
        uiShowMessage(errorBrowserWS);
    }
};

var uiAppLoad = function () {
    // Load your App
};

var uiHandleError = function (e, r) {
    uiHideProgress();
    if (r) {
        if (r.indexOf("BadPaddingException") != -1) {
            uiShowMessage(errorWrongPassword);
        } else {
            uiShowMessage(r);
        }
    } else {
        uiShowMessage(errorBrowserWS);
    }
    if (e) wsError(e);

};