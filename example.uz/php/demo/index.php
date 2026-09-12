<?

session_start();

unset($_SESSION["USER_INFO"]);

?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>E-IMZO Demo — Вход</title>
        <link rel="stylesheet" href="demo.css">
        <script src="e-imzo.js" type="text/javascript"></script>
        <script src="e-imzo-client.js?v=1.2" type="text/javascript"></script>
        <script src="micro-ajax.js" type="text/javascript"></script>
        <script src="e-imzo-init.js?v=1.0" type="text/javascript"></script>
    </head>
    <body>
        <main class="page">
            <header class="brand">
                <div class="brand__mark">E-<span>IMZO</span></div>
                <p class="brand__tag">Демонстрация входа по электронной цифровой подписи</p>
            </header>

            <section class="panel">
                <h1 class="panel__title">Вход по PFX</h1>
                <p class="panel__hint">Работает только с тестовыми ключами</p>
                <form name="testform" class="stack" onsubmit="return false;">
                    <label class="field">
                        <span>Сертификат</span>
                        <select name="key" onchange="cbChanged(this)"></select>
                    </label>
                    <div class="row">
                        <button onclick="signinPFX()" type="button" id="signinPFXButton" class="btn">Вход</button>
                    </div>
                </form>
            </section>

            <section class="panel">
                <h2 class="panel__title">Вход по USB-токену</h2>
                <p class="panel__hint">CryptKeyContainer — совместимые токены и ID-карта</p>
                <button onclick="signinToken()" type="button" id="signinTokenButton" class="btn">Вход</button>
            </section>

            <div id="progress" class="status status--progress"></div>
            <div id="message" class="status status--message"></div>

            <p class="footer-note">Требуется установленный E-IMZO и тестовый ключ</p>
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

            var uiShowProgress = function(){
                var l = document.getElementById('progress');
                l.innerHTML = 'Идет подписание, ждите.';
                l.style.color = 'green';
            };

            var uiHideProgress = function(){
                var l = document.getElementById('progress');
                l.innerHTML = '';                
            };

            signinPFX = function () {
                uiShowProgress();
                
                getChallenge(function(challenge){                
                    
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
                    
                }); 
            }

            signinToken = function () {
                uiShowProgress();
                
                getChallenge(function(challenge){               
                    
                    var keyId = "ckc";

                    auth(keyId, challenge, function(redirect){
                        window.location.href = redirect;
                        uiShowProgress();
                    });                    
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
