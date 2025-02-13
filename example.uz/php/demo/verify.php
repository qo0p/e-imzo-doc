<?

session_start();

include("config.php");

$user_ip = empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'];
$host = $_SERVER['HTTP_HOST'];

$headers = array('Host: '.$host, 'X-Real-IP: '.$user_ip);

$pkcs7wtst = $_POST['pkcs7wtst'];
$data64 = isset($_POST['data64']) ? $_POST['data64'] : "";

if($data64 == ""){
    $verify_url = $verify_url . "/attached";
} else {
    $verify_url = $verify_url . "/detached";
}

$ch = curl_init();

$postvars = "";

if($data64 == ""){
    $postvars = $pkcs7wtst;
} else {
    $postvars = $data64 . "|" . $pkcs7wtst;
}


$url = $verify_url;
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_POST, 1);                //0 for a get request
curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch,CURLOPT_POSTFIELDS,$postvars);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
curl_setopt($ch,CURLOPT_TIMEOUT, 20);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if($httpcode == 200) {
    print($response);
} else {
    ?>{"status":0,"message":"<?=addslashes($response)?>"}<?
}
curl_close ($ch);



?>