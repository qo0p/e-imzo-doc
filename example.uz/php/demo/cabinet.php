<?

session_start();

?>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>E-IMZO Demo — Кабинет</title>
        <link rel="stylesheet" href="demo.css">
        <script src="e-imzo.js" type="text/javascript"></script>
        <script src="e-imzo-client.js" type="text/javascript"></script>
        <script src="micro-ajax.js" type="text/javascript"></script>
        <script src="e-imzo-init.js" type="text/javascript"></script>
    </head>
    <body>

        <?

        if(!isset($_SESSION["USER_INFO"])){
            ?>
            <main class="page">
                <header class="brand">
                    <div class="brand__mark">E-<span>IMZO</span></div>
                    <p class="brand__tag">Демонстрация подписания документов</p>
                </header>
                <section class="panel auth-gate">
                    <h3>Вы не авторизованы</h3>
                    <p>Войдите с помощью электронной цифровой подписи</p>
                    <a class="btn" href="index.php">Войти</a>
                </section>
            </main>
            <?
            exit();
        }

        ?>

        <main class="page page--wide">
            <header class="brand">
                <div class="brand__mark">E-<span>IMZO</span></div>
                <p class="brand__tag">Кабинет — подписание и проверка PKCS#7</p>
            </header>

            <section class="panel">
                <h1 class="panel__title">Сессия</h1>
                <p class="panel__hint">Данные сертификата после успешного входа</p>
                <div class="meta-box" style="margin-bottom: 0.85rem;">
                    <div>
                        <strong>ID ключа</strong>
                        <label id="keyId"><?=$_SESSION["KEY_ID"]?></label>
                    </div>
                </div>
                <pre class="user-info" id="userInfo"><?
                    $userInfoPretty = $_SESSION["USER_INFO"];
                    $userInfoDecoded = json_decode($userInfoPretty);
                    if ($userInfoDecoded !== null) {
                        $userInfoPretty = json_encode($userInfoDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                    echo htmlspecialchars($userInfoPretty, ENT_QUOTES, 'UTF-8');
                ?></pre>
            </section>

            <section class="panel">
                <h2 class="panel__title">Подписание</h2>
                <p class="panel__hint">Выберите формат PKCS#7 и подпишите текст или файл</p>

                <form name="testform" class="stack" onsubmit="return false;">
                    <div id="message" class="status status--message"></div>

                    <div class="field">
                        <span class="field-label">Тип подписанного документа</span>
                        <div class="choice-list">
                            <label class="choice" for="attached">
                                <input type="radio" id="attached" name="pkcs7Type" value="attached" onchange="pkcs7Type_changed()" checked="checked">
                                <span class="choice__title">PKCS#7 / Attached</span>
                                <span class="choice__meta">с вложением</span>
                            </label>
                            <label class="choice" for="detached">
                                <input type="radio" id="detached" name="pkcs7Type" value="detached" onchange="pkcs7Type_changed()">
                                <span class="choice__title">PKCS#7 / Detached</span>
                                <span class="choice__meta">без вложения</span>
                            </label>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <label class="field">
                        <span>Текст для подписи</span>
                        <textarea name="data" placeholder="Введите текст документа…"></textarea>
                    </label>
                    <div class="row">
                        <button onclick="sign()" type="button" id="signButton" class="btn">Подписать текст</button>
                    </div>

                    <div class="divider"></div>

                    <label class="field">
                        <span>Файл для подписи</span>
                        <input type="file" id="fileInput" accept="*/*">
                    </label>
                    <label class="field">
                        <span>Содержимое файла (Base64)</span>
                        <textarea name="fileData64" class="tall" placeholder="Заполняется автоматически после выбора файла…"></textarea>
                    </label>
                    <div class="row">
                        <button onclick="signFile()" type="button" id="signFileButton" class="btn">Подписать файл</button>
                    </div>

                    <div id="progress" class="status status--progress"></div>

                    <div class="divider"></div>

                    <label class="field">
                        <span id="pkcs7Type_label">Подписанный документ PKCS#7</span>
                        <textarea name="pkcs7" class="tall"></textarea>
                    </label>

                    <label class="field">
                        <span>Результат проверки</span>
                        <textarea name="verifyResult" class="tall"></textarea>
                    </label>
                </form>
            </section>

            <p class="footer-note"><a href="index.php">Выйти</a></p>
        </main>

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
                            uiShowMessage(data.status + " - " + data.message);
                            return;
                        }
                        pkcs7wtst = data.pkcs7b64;
                    } catch (e) {
                        uiShowMessage(s.status + " - " + s.statusText + "<br />" + e);
                        return;
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
                            uiShowMessage(data.status + " - " + data.message);
                            return;
                        }
                        result = data.pkcs7Info;
                    } catch (e) {
                        uiShowMessage(s.status + " - " + s.statusText + "<br />" + e);
                        return;
                    }
                    callback(result);
                }, 'pkcs7wtst=' + encodeURIComponent(pkcs7wtst) + (detached ? '&data64=' + encodeURIComponent(data64) : ""));  
            }
            
            window.onload = AppLoad;
        </script>

    </body>
</html>
