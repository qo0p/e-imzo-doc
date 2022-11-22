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
            <input type="radio" id="pfx" name="keyType" value="pfx" onchange="keyType_changed()" checked="checked"><label for="pfx">PFX</label> - <select name="key" onchange="cbChanged(this)"><option value="itm-218711A92-0" vo="{&quot;disk&quot;:&quot;/media/flash/&quot;,&quot;path&quot;:&quot;&quot;,&quot;name&quot;:&quot;DS11111111111111-test&quot;,&quot;alias&quot;:&quot;cn=sfasdfa s asdfasd,1.2.860.3.16.1.2=11111111111111,serialnumber=218711a92,validfrom=2022.09.24 17:29:21,validto=2022.10.24 17:29:21&quot;,&quot;serialNumber&quot;:&quot;218711A92&quot;,&quot;validFrom&quot;:&quot;2022-09-24T12:29:21.000Z&quot;,&quot;validTo&quot;:&quot;2022-10-24T12:29:21.000Z&quot;,&quot;CN&quot;:&quot;SFASDFA S ASDFASD&quot;,&quot;TIN&quot;:&quot;&quot;,&quot;UID&quot;:&quot;&quot;,&quot;PINFL&quot;:&quot;11111111111111&quot;,&quot;O&quot;:&quot;&quot;,&quot;T&quot;:&quot;&quot;,&quot;type&quot;:&quot;pfx&quot;,&quot;expired&quot;:false}" id="itm-218711A92-0" selected="selected">SFASDFA S ASDFASD</option><option value="itm-77C6F0BF-1" vo="{&quot;disk&quot;:&quot;/media/flash/&quot;,&quot;path&quot;:&quot;DSKEYS&quot;,&quot;name&quot;:&quot;DS4711541400018&quot;,&quot;alias&quot;:&quot;cn=musaxanov azamat axmedjanovich,name=azamat,surname=musaxanov,l=яккасарой тумани,st=тошкент ш.,c=uz,o=не указано,uid=471154140,1.2.860.3.16.1.2=30705850240037,serialnumber=77c6f0bf,validfrom=2022.08.16 12:21:38,validto=2024.08.16 23:59:59&quot;,&quot;serialNumber&quot;:&quot;77C6F0BF&quot;,&quot;validFrom&quot;:&quot;2022-08-16T07:21:38.000Z&quot;,&quot;validTo&quot;:&quot;2024-08-16T18:59:59.000Z&quot;,&quot;CN&quot;:&quot;MUSAXANOV AZAMAT AXMEDJANOVICH&quot;,&quot;TIN&quot;:&quot;471154140&quot;,&quot;UID&quot;:&quot;471154140&quot;,&quot;PINFL&quot;:&quot;30705850240037&quot;,&quot;O&quot;:&quot;НЕ УКАЗАНО&quot;,&quot;T&quot;:&quot;&quot;,&quot;type&quot;:&quot;pfx&quot;,&quot;expired&quot;:false}" id="itm-77C6F0BF-1">MUSAXANOV AZAMAT AXMEDJANOVICH</option></select><br>
            <input type="radio" id="idcard" name="keyType" value="idcard" onchange="keyType_changed()"><label for="idcard">ID-card</label> - <label id="plugged">не подключена</label><br>
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
                microAjax('/frontend/challenge', function (data, s) {
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
