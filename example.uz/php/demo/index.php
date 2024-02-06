<?

session_start();

unset($_SESSION["USER_INFO"]);

?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <title></title>
        <script src="e-imzo.js" type="text/javascript"></script> 
        <script src="e-imzo-client.js" type="text/javascript"></script> 
        <script src="micro-ajax.js" type="text/javascript"></script> 
        <script src="e-imzo-init.js" type="text/javascript"></script> 
    </head>
    <body>
        <form name="testform">
            <label id="message" style="color: red;"></label>
            <p>Выберите тип ключа:</p>
            <input type="radio" id="pfx" name="keyType" value="pfx" onchange="keyType_changed()" checked="checked">
            <label for="pfx">PFX</label> - <select name="key" onchange="cbChanged(this)"></select><br>
            <input type="radio" id="idcard" name="keyType" value="idcard" onchange="keyType_changed()">
            <label for="idcard">ID-card</label> - <label id="plugged">не подключена</label><br>
            <br>

            <button onclick="signin()" type="button" id="signButton">Вход</button><br>
            <label id="progress" style="color: green;"></label>
       </form>

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
                },function(e, r){
                    if(e){
                        uiShowMessage(errorCAPIWS + " : " + e);
                    } else {
                        console.log(r);
                    }
                });
                EIMZOClient.idCardIsPLuggedIn(function(yes){
                    document.getElementById('plugged').innerHTML = yes ? 'подключена': 'не подключена';
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
                document.getElementById('keyId').innerHTML = '';
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
                document.getElementById('signButton').innerHTML = keyType==="pfx" ? "Вход ключем PFX" : "Вход ключем ID-card";
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
