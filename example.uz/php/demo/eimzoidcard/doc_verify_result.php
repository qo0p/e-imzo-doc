<?

include("config.php");

$user_ip = empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'];
$host = $_SERVER['HTTP_HOST'];

$headers = array('Content-Type: application/x-www-form-urlencoded', 'Host: '.$host, 'X-Real-IP: '.$user_ip);

$document=$_POST['Document'];
if (strlen($document) > 128) {
	die("Размер не должен превышать 128, т.к. это тест");
}

$ch = curl_init();

$obj = "";

$url = $verify_url;
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_POST, 1);                //0 for a get request
curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch,CURLOPT_POSTFIELDS,'documentId='.urlencode($_POST['documentId']).'&document='.urlencode(base64_encode($document)));
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
curl_setopt($ch,CURLOPT_TIMEOUT, 20);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if($httpcode == 200) {
  $obj = json_decode($response);
} else {
   die($response);
}
curl_close ($ch);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/bootstrap.min.js"></script>
</head>

    <body>
<?
if($obj->{"status"} != 1) {
?><div class="alert alert-danger" role="alert">
  <?=$obj->{"message"}?>
</div><?
} else {
$si = $obj->{"subjectCertificateInfo"};
$sn = $si->{"subjectName"};
$vi = $obj->{"verificationInfo"};
?>
<div class="container">
  <div class="row">
    <div class="col-sm">Certificate SN</div>
    <div class="col-sm"><?=$si->{"serialNumber"}?></div>
  </div>
  <div class="row">
    <div class="col-sm">Certificate Validity</div>
    <div class="col-sm"><?=$si->{"validFrom"}?> - <?=$si->{"validTo"}?></div>
  </div>
<?
foreach($sn as $key => $value){
?>
  <div class="row">
    <div class="col-sm"><?=$key?></div>
    <div class="col-sm"><?=$value?></div>
  </div>
<?
}
foreach($vi as $key => $value){
?>
  <div class="row">
    <div class="col-sm"><?=$key?></div>
	<?
	if($key=="policyIdentifiers"){
	?><div class="col-sm"><?=implode(",",$value)?></div><?
} else {
?>
    <div class="col-sm"><?=$value?></div>
<? } ?>
  </div>
<?
}?>
</div>
<?





}
?>
<form>
<div class="form-group">
    <label for="exampleFormControlTextarea1">JSON</label>
    <textarea class="form-control" id="exampleFormControlTextarea1" rows="10">
<?=$response?>
</textarea>
  </div>
</form>

    </body>
    
</html>
