<?

session_start();

include("config.php");

$user_ip = empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'];
$host = $_SERVER['HTTP_HOST'];

$headers = array('Host: '.$host, 'X-Real-IP: '.$user_ip);

$pkcs7 = $_POST['pkcs7'];
$keyId = $_POST['keyId'];

$ch = curl_init();
$postvars = $pkcs7;
$url = $auth_url;
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
    $jr = json_decode($response);
    if($jr->{"status"} != 1){
        ?>{"status":<?=$jr->{"status"}?>,"message":"<?=addslashes($jr->{"message"})?>"}<?
    } else {
        $_SESSION["USER_INFO"] = json_encode($jr->{"subjectCertificateInfo"});
        $_SESSION["KEY_ID"] = $keyId;
        ?>{"status":1,"redirect":"cabinet.php"}<?
    }
} else {
    ?>{"status":0,"message":"<?=addslashes($response)?>"}<?
}
curl_close ($ch);



?>
