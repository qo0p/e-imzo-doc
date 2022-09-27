<?

session_start();

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

        <?

        if(!isset($_SESSION["USER_INFO"])){
            ?><h3>You are not authorized</h3><a href="index.php">SignIn</a><?    
            exit();
        }

        ?>

        UserInfo: <div id="userInfo"><?=$_SESSION["USER_INFO"]?></div>

        <form name="testform">
            <label id="message" style="color: red;"></label>
            <br />
            Текст для подписи <br />
            <textarea name="data"></textarea><br />
            <p>Выберите тип подписанного документа:</p>
            <input type="radio" id="attached" name="pkcs7Type" value="attached" onchange="pkcs7Type_changed()" checked="checked"><label for="attached">PKCS#7/Attached</label><br />
            <input type="radio" id="detached" name="pkcs7Type" value="detached" onchange="pkcs7Type_changed()"><label for="detached">PKCS#7/Detached</label><br>
            <br />
            <button onclick="sign()" type="button" id="signButton">Подписать</button><br />
            <label id="progress"></label>
            <br />
            ID ключа: <label id="keyId"><?=$_SESSION["KEY_ID"]?></label><br />
            <br />
            <label id="pkcs7Type_label">Подписанный документ PKCS#7</label><br />
            <textarea name="pkcs7"></textarea><br />
            <br />
            <label>Результат проверки</label><br />
            <textarea name="verifyResult"></textarea><br />
       </form>

        <script language="javascript">

            var pkcs7Type_changed = function(){
                var pkcs7Type = document.testform.pkcs7Type.value;
                document.getElementById('pkcs7Type_label').innerHTML = pkcs7Type==="attached" ? "Подписанный документ PKCS#7/Attached (содержит исходный документ)" : "Подписанный документ PKCS#7/Detached (НЕ содержит исходный документ)";
            };

            pkcs7Type_changed();

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
                // enable ui
                uiLoaded();
            }

            var uiLoaded = function(){  
                var l = document.getElementById('message');
                l.innerHTML = '';
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

            sign = function () {
                uiShowProgress();
                var pkcs7Type = document.testform.pkcs7Type.value;
                var data = document.testform.data.value;
                var keyId = document.getElementById('keyId').innerHTML;   

                EIMZOClient.createPkcs7(keyId, data, null, function(pkcs7){
                    attachTimestamp(pkcs7, function(pkcs7wtst){
                        document.testform.pkcs7.value = pkcs7wtst;
                        uiShowProgress();
                        verify(pkcs7wtst, pkcs7Type==="detached", data, function(result){
                            document.testform.verifyResult.value = JSON.stringify(result,'',' ');
                        });
                    });
                }, uiHandleError, pkcs7Type==="detached");
            };  

            attachTimestamp = function (pkcs7, callback){
                microAjax('/frontend/timestamp/pkcs7', function (data, s) {
                    uiHideProgress();
                    if(s.status != 200){
                        uiShowMessage(s.status + " - " + s.statusText);
                        return;
                    }
                    var pkcs7wtst;
                    try {
                        var data = JSON.parse(data);
                        if (data.status != 1) {
                            uiShowMessage(data.status + " - " + s.message);
                            return;
                        }
                        pkcs7wtst = data.pkcs7b64;
                    } catch (e) {
                        uiShowMessage(s.status + " - " + s.statusText + "<br />" + e);
                    }
                    callback(pkcs7wtst);
                },pkcs7);
            }

            verify = function (pkcs7wtst, detached, data, callback){    
                var data64;
                if(detached){
                    data64 = Base64.encode(data);
                }            
                microAjax('verify.php', function (data, s) {
                    uiHideProgress();
                    if(s.status != 200){
                        uiShowMessage(s.status + " - " + s.statusText);
                        return;
                    }
                    var result;
                    try {
                        var data = JSON.parse(data);
                        if (data.status != 1) {
                            uiShowMessage(data.status + " - " + s.message);
                            return;
                        }
                        result = data.pkcs7Info;
                    } catch (e) {
                        uiShowMessage(s.status + " - " + s.statusText + "<br />" + e);
                    }
                    callback(result);
                }, 'pkcs7wtst=' + encodeURIComponent(pkcs7wtst) + (detached ? '&data64=' + encodeURIComponent(data64) : ""));  
            }
            
            window.onload = AppLoad;
        </script>

    </body>
</html>