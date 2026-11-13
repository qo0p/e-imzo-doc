<?php

$eimzo_server = rtrim(getenv('EIMZO_SERVER_URL') ?: 'http://127.0.0.1:8080', '/');

$auth_url = $eimzo_server . "/backend/mobile/authenticate/";

$verify_url = $eimzo_server . "/backend/mobile/verify";

?>
