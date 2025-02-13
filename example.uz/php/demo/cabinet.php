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
            <p>Выберите тип подписанного документа:</p>
            <input type="radio" id="attached" name="pkcs7Type" value="attached" onchange="pkcs7Type_changed()" checked="checked"><label for="attached">PKCS#7/Attached</label><br />
            <input type="radio" id="detached" name="pkcs7Type" value="detached" onchange="pkcs7Type_changed()"><label for="detached">PKCS#7/Detached</label><br>
            <br />
            Текст для подписи <br />
            <textarea name="data"></textarea><br />
            <button onclick="sign()" type="button" id="signButton">Подписать Текст</button><br />
            <br />
            Файл для подписи <br />
            <input type="file" id="fileInput" accept="*/*"><br />
            <textarea name="fileData64"></textarea><br />
            <button onclick="signFile()" type="button" id="signFileButton">Подписать Файл</button><br />
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

            // Function to handle file input change event
            document.getElementById('fileInput').addEventListener('change', function(event) {
                const file = event.target.files[0]; // Get the uploaded file

                if (file) {
                    const reader = new FileReader(); // Create a new FileReader

                    // When the file is loaded, convert to Base64 and log it
                    reader.onload = function(e) {
                        const base64Content = e.target.result.split(',')[1]; // Extract Base64 part
                        document.testform.fileData64.value = base64Content;
                    };

                    // Read the file as a data URL (Base64 encoding)
                    reader.readAsDataURL(file);
                }
            });


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

            signFile = function () {
                uiShowProgress();
                var pkcs7Type = document.testform.pkcs7Type.value;
                var data64 = document.testform.fileData64.value;
                var keyId = document.getElementById('keyId').innerHTML;   

                EIMZOClient.createPkcs7(keyId, data64, null, function(pkcs7){
                    attachTimestamp(pkcs7, function(pkcs7wtst){
                        document.testform.pkcs7.value = pkcs7wtst;
                        uiShowProgress();
                        verify(pkcs7wtst, pkcs7Type==="detached", data64, function(result){
                            document.testform.verifyResult.value = JSON.stringify(result,'',' ');
                        }, true);  // !! set isDataBase64Encoded = TRUE
                    });
                }, uiHandleError, pkcs7Type==="detached", true); // !! set isDataBase64Encoded = TRUE
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

            verify = function (pkcs7wtst, detached, data, callback, isDataBase64Encoded){    
                var data64;
                if(detached){
                    if(isDataBase64Encoded === true){
                        data64 = data;
                    } else {
                        data64 = Base64.encode(data);
                    }
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