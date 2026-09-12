<?

session_start();

unset($_SESSION["USER_INFO"]);

?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>E-IMZO Demo — Выбор ключа</title>
        <link rel="stylesheet" href="demo.css">
        <script src="e-imzo.js" type="text/javascript"></script>
        <script src="e-imzo-client.js?v=1.2" type="text/javascript"></script>
        <script src="micro-ajax.js" type="text/javascript"></script>
        <script src="e-imzo-init.js" type="text/javascript"></script>
    </head>
    <body>
        <main class="page">
            <header class="brand">
                <div class="brand__mark">E-<span>IMZO</span></div>
                <p class="brand__tag">Вход с выбором типа ключа (только тестовые ключи)</p>
            </header>

            <section class="panel">
                <h1 class="panel__title">Тип ключа</h1>
                <p class="panel__hint">Выберите носитель и нажмите «Вход»</p>

                <form name="testform" onsubmit="return false;">
                    <div id="message" class="status status--message"></div>

                    <div class="choice-list">
                        <label class="choice" for="pfx">
                            <input type="radio" id="pfx" name="keyType" value="pfx" onchange="keyType_changed()" checked="checked">
                            <span class="choice__title">PFX</span>
                            <span class="choice__meta">файл сертификата</span>
                            <select name="key" class="choice-select" onchange="cbChanged(this)"></select>
                        </label>

                        <label class="choice" for="idcard">
                            <input type="radio" id="idcard" name="keyType" value="idcard" onchange="keyType_changed()">
                            <span class="choice__title">EIMZO-Token / ID-card</span>
                            <span class="choice__meta" id="plugged_idcard">не подключена</span>
                        </label>

                        <label class="choice" for="baikey">
                            <input type="radio" id="baikey" name="keyType" value="baikey" onchange="keyType_changed()">
                            <span class="choice__title">BAIK-Token</span>
                            <span class="choice__meta" id="plugged_baikey">не подключена</span>
                        </label>

                        <label class="choice" for="ckc">
                            <input type="radio" id="ckc" name="keyType" value="ckc" onchange="keyType_changed()">
                            <span class="choice__title">CryptKeyContainer</span>
                            <span class="choice__meta" id="plugged_ckc">не подключена</span>
                        </label>
                    </div>

                    <div class="row" style="margin-top: 1.1rem;">
                        <button onclick="signin()" type="button" id="signButton" class="btn">Вход</button>
                    </div>

                    <div id="progress" class="status status--progress"></div>
                </form>
            </section>

            <p class="footer-note"><a href="index.php">Обычный вход</a></p>
        </main>

        <script language="javascript">
            
            var uiShowMessage = function(message){
                alert(message);
            }
            
            var uiLoading = function(){
                var l = document.getElementById('message');
                l.innerHTML = 'Загрузка ...';
                l.style.color = 'red';
            }

            var uiNotLoaded = function(e){    
                var l = document.getElementById('message');
                l.innerHTML = '';
                if (e) {
                    wsError(e);
                } else {
                    uiShowMessage(errorBrowserWS);
                }
            }
            
            var uiUpdateApp = function(){    
                var l = document.getElementById('message');
                l.innerHTML = errorUpdateApp;
            }     
            
            var uiAppLoad = function(){
                uiClearCombo();
                EIMZOClient.listAllUserKeys(function(o, i){
                    var itemId = "itm-" + o.serialNumber + "-" + i;
                    return itemId;
                },function(itemId, v){
                    return uiCreateItem(itemId, v);
                },function(items, firstId){        
                    uiFillCombo(items);
                    uiLoaded();
                    uiComboSelect(firstId);
                },uiHandleError);
                if(!EIMZOClient.NEW_API3){
                    alert("E-IMZO version should be 4.86 or newer");
                }
                EIMZOClient.idCardIsPLuggedIn(function(yes){
                    var el = document.getElementById('plugged_idcard');
                    el.innerHTML = yes ? 'подключена': 'не подключена';
                    el.className = 'choice__meta' + (yes ? ' is-on' : '');
                },function(e, r){
                    if(e){
                        uiShowMessage(errorCAPIWS + " : " + e);
                    } else {
                        console.log(r);
                    }
                })
                EIMZOClient.isBAIKTokenPLuggedIn(function(yes){
                    var el = document.getElementById('plugged_baikey');
                    el.innerHTML = yes ? 'подключена': 'не подключена';
                    el.className = 'choice__meta' + (yes ? ' is-on' : '');
                },function(e, r){
                    if(e){
                        uiShowMessage(errorCAPIWS + " : " + e);
                    } else {
                        console.log(r);
                    }
                })
                EIMZOClient.isCKCPLuggedIn(function(yes){
                    var el = document.getElementById('plugged_ckc');
                    el.innerHTML = yes ? 'подключена': 'не подключена';
                    el.className = 'choice__meta' + (yes ? ' is-on' : '');
                },function(e, r){
                    if(e){
                        uiShowMessage(errorCAPIWS + " : " + e);
                    } else {
                        console.log(r);
                    }
                })
            }
            
            var uiComboSelect = function(itm){
                if(itm){
                    var id = document.getElementById(itm);   
                    id.setAttribute('selected','true');
                }
            }
            
            var cbChanged = function(c){                
                if(document.getElementById('keyId')) {
                    document.getElementById('keyId').innerHTML = '';
                }
            }
            
            var uiClearCombo = function(){    
                var combo = document.testform.key;
                combo.length = 0;
            }

            var uiFillCombo = function(items){    
                var combo = document.testform.key;
                for (var itm in items) {
                    combo.append(items[itm]);
                }
            }

            var uiLoaded = function(){  
                var l = document.getElementById('message');
                l.innerHTML = '';
            }
            
            var uiCreateItem = function (itmkey, vo) {
                var now = new Date();
                vo.expired = dates.compare(now, vo.validTo) > 0;
                var itm = document.createElement("option");
                itm.value = itmkey;
                itm.text = vo.CN;
                if (!vo.expired) {
                    
                } else {
                    itm.style.color = 'gray';
                    itm.text = itm.text + ' (срок истек)';
                }                
                itm.setAttribute('vo',JSON.stringify(vo));
                itm.setAttribute('id',itmkey);
                return itm;
            }

            var keyType_changed = function(){
                var keyType = document.testform.keyType.value;
                if(keyType==="pfx"){
                    document.getElementById('signButton').innerHTML = "Вход ключем PFX";
                }
                if(keyType==="idcard"){
                    document.getElementById('signButton').innerHTML = "Вход ключем EIMZO-Token или ID-card";
                }
                if(keyType==="baikey"){
                    document.getElementById('signButton').innerHTML = "Вход ключем BAIK-Token";
                }
                if(keyType==="ckc"){
                    document.getElementById('signButton').innerHTML = "Вход любым совместимым токеном";
                }
            };

            keyType_changed();

            var uiShowProgress = function(){
                var l = document.getElementById('progress');
                l.innerHTML = 'Идет подписание, ждите.';
                l.style.color = 'green';
            };

            var uiHideProgress = function(){
                var l = document.getElementById('progress');
                l.innerHTML = '';                
            };

            signin = function () {
                uiShowProgress();
                
                getChallenge(function(challenge){                
                    var keyType = document.testform.keyType.value;
                    if(keyType==="idcard"){
                        var keyId = "idcard";

                        auth(keyId, challenge, function(redirect){
                            window.location.href = redirect;
                            uiShowProgress();
                        });

                    } else if(keyType==="baikey"){
                        var keyId = "baikey";

                        auth(keyId, challenge, function(redirect){
                            window.location.href = redirect;
                            uiShowProgress();
                        });

                    } else if(keyType==="ckc"){
                        var keyId = "ckc";

                        auth(keyId, challenge, function(redirect){
                            window.location.href = redirect;
                            uiShowProgress();
                        });

                    } else {
                        var itm = document.testform.key.value;
                        if (itm) {                 
                            var id = document.getElementById(itm);   
                            var vo = JSON.parse(id.getAttribute('vo'));                        
                            
                            EIMZOClient.loadKey(vo, function(id){                            
                                var keyId = id;
                               
                                auth(keyId, challenge, function(redirect){
                                    window.location.href = redirect;
                                    uiShowProgress();
                                });

                            }, uiHandleError);                                 
                        } else {                        
                            uiHideProgress();
                        }
                    }
                });                
            };

            getChallenge = function (callback){
                microAjax('/frontend/challenge?_uc='+(Date.now() + "_" + Math.random()), function (data, s) {
                    if(s.status != 200){
                        uiShowMessage(s.status + " - " + s.statusText);
                        return;
                    }
                    try {
                        var data = JSON.parse(data);
                        if (data.status != 1) {
                            uiShowMessage(data.status + " - " + data.message);
                            return;
                        }
                        callback(data.challenge);
                    } catch (e) {
                        uiShowMessage(s.status + " - " + s.statusText + ": " + e);
                    }
                });
            }

            auth = function (keyId, challenge, callback){
                EIMZOClient.createPkcs7(keyId, challenge, null, function(pkcs7){
                    microAjax('auth.php', function (data, s) {
                        uiHideProgress();
                        if(s.status != 200){
                            uiShowMessage(s.status + " - " + s.statusText);
                            return;
                        }
                        try {
                            var data = JSON.parse(data);
                            if (data.status != 1) {
                                uiShowMessage(data.status + " - " + data.message);
                                return;
                            }
                            callback(data.redirect);
                        } catch (e) {
                            uiShowMessage(s.status + " - " + s.statusText + ": " + e);
                        }
                        
                    }, 'keyId=' + encodeURIComponent(keyId) + '&pkcs7=' + encodeURIComponent(pkcs7));  
                }, uiHandleError, false);  
            }
            
            window.onload = AppLoad;
        </script>
    

</body></html>
