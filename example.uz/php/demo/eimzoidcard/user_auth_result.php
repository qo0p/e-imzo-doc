<?

include("config.php");

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


$user_ip = empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP'];
$host = $_SERVER['HTTP_HOST'];

$headers = array('Host: '.$host, 'X-Real-IP: '.$user_ip);

$ch = curl_init();
$url = $auth_url.$_GET['documentId'];
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch,CURLOPT_POST, 0);                
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT ,3);
curl_setopt($ch,CURLOPT_TIMEOUT, 20);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if($httpcode == 200) {
    $obj = json_decode($response);
    if($obj->{"status"} != 1){
      ?><div class="alert alert-danger" role="alert">
      <?=$obj->{"message"}?>
      </div><?
    } else {
      
      $si = $obj->{"subjectCertificateInfo"};
      $sn = $si->{"subjectName"};
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
      }?>
      </div>
      <?

    }
} else {
    ?><div class="alert alert-danger" role="alert">
      <?=$obj->{"response"}?>
      </div><?
}
curl_close ($ch);


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
