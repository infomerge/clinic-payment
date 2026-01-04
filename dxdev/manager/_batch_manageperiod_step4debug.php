<?php
######################
#
#　請求書発行対象者に請求書発行
#
######################
ini_set( 'display_errors', 1 );
ini_set("memory_limit", "5120M");
set_time_limit(0);

include_once "../class/config.php";
include_once "../class/generatepdf.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$path = "../PHPMailer/vendor/phpmailer/phpmailer/";

require $path .'src/PHPMailer.php';
require $path .'src/SMTP.php';
require $path .'src/POP3.php';
require $path .'src/Exception.php';
require $path .'src/OAuth.php';
require $path .'language/phpmailer.lang-ja.php';

//SMTPの設定
$mailer = new PHPMailer();
$mailer->IsSMTP();
#$mailer->SMTPDebug = 1;
$mailer->CharSet = 'utf-8';

$mailer->SMTPAuth = TRUE;
$mailer->Host = 'infomerge.sakura.ne.jp';
$mailer->Username = 'takegaki@infomerge.jp';
$mailer->Password = 'Zenj8CHK';
$mailer->Port = 587;
#$mailer->Port = 465;
$mailer->SMTPSecure = 'tls';
$mailer->From     = 'takegaki@infomerge.jp';
$mailer->SMTPOptions = array(
  'ssl' => array(
    'verify_peer'       => false,	//SSLサーバー証明書の検証を要求するか（デフォルト：true）
    'verify_peer_name'  => false,	//ピア名の検証を要求するか（デフォルト：true）
    'allow_self_signed' => true		//自己証明の証明書を許可するか（デフォルト：false、trueにする場合は「verify_peer」をfalseに）
  )
);

$to = "takegaki@infomerge.jp";
$title = "CLシステム";


//メール本体
$mailer->FromName = mb_convert_encoding("CLシステム","UTF-8","AUTO");
$mailer->Subject  = mb_convert_encoding($title,"UTF-8","AUTO");

$mailer->AddAddress($to);

exit;

#DB接続
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#締めのターゲット抽出
$sql = "SELECT * FROM manageperiod where status = 2";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);
#print_r($data);exit;

$errMsg = "";
$errFlg = false;
$targetym = "";
if(count($data)>1){
  $errMsg .= "対象が2以上ある\n";
  $errFlg = true;
}elseif(count($data) == 0){
  $sql = "SELECT * FROM manageperiod order by id desc limit 1";
  $stmt = $dbh->query($sql);
  $data2 = $stmt->fetchALL(PDO::FETCH_ASSOC);
  $targetym = $data2[0]['targetym'];
}else{
  $targetym = $data[0]['targetym'];
}

if($errFlg){
  $message = "--------------------
  エラー内容
  --------------------
  ";
  $message .= $errMsg;
  $mailer->Body     = mb_convert_encoding($message,"UTF-8","AUTO");
  //送信する
  if($mailer->Send()){}
  else{
  }
  $mailer->ClearAllRecipients();
}else{
  $genPDF = new GENERATEPDF();
  #$genPDF->type = 1;
  $genPDF->type = 9;  #デバッグ用
  $genPDF->format = "seikyu";
  $genPDF->targetym = $targetym;
  $genPDF->mode = "debug";
  $genPDF->generate();
#exit;
  $sql = "UPDATE manageperiod SET status = 3 where status = 2 and targetym = '{$targetym}';";
  $dbh->query($sql);
  $sql = "UPDATE re_shinryo SET manageperiod_status = 3 where manageperiod_status = 2 and manageperiod_targetym = '{$targetym}';";
  $dbh->query($sql);
  $sql = "UPDATE rek_service SET manageperiod_status = 3 where manageperiod_status = 2 and manageperiod_targetym = '{$targetym}';";
  $dbh->query($sql);
  $sql = "UPDATE appendix SET manageperiod_status = 3 where manageperiod_status = 2 and manageperiod_targetym = '{$targetym}';";
  $dbh->query($sql);
}
?>
