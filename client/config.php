<?php

$eimzo_server = rtrim(getenv('EIMZO_SERVER_URL') ?: 'http://127.0.0.1:8080', '/');

$auth_url = $eimzo_server . "/backend/auth";

$verify_url = $eimzo_server . "/backend/pkcs7/verify";

?>
