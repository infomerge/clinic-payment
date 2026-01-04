<?php
######################
#
#　請求書発行対象者に請求書発行
#
######################
ini_set( 'display_errors', 0 );
ini_set("memory_limit", "5120M");
set_time_limit(0);

include_once "../class/config.php";
include_once "../class/generatepdf.php";
include_once "../class/clsystem.php";

/*
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$path = "../PHPMailer/vendor/phpmailer/phpmailer/";

require $path .'src/PHPMailer.php';
require $path .'src/SMTP.php';
require $path .'src/POP3.php';
require $path .'src/Exception.php';
require $path .'src/OAuth.php';
require $path .'language/phpmailer.lang-ja.php';

$mailer = new PHPMailer();
$mailer->IsSMTP();
$mailer->CharSet = 'utf-8';

$mailer->SMTPAuth = TRUE;
$mailer->Host = 'infomerge.sakura.ne.jp';
$mailer->Username = 'takegaki@infomerge.jp';
$mailer->Password = 'Zenj8CHK';
$mailer->Port = 587;
$mailer->SMTPSecure = 'tls';
$mailer->From     = 'takegaki@infomerge.jp';
$mailer->SMTPOptions = array(
  'ssl' => array(
    'verify_peer'       => false,
    'verify_peer_name'  => false,
    'allow_self_signed' => true
  )
);

$to = "takegaki@infomerge.jp";
$title = "CLシステム";

//メール本体
$mailer->FromName = mb_convert_encoding("CLシステム","UTF-8","AUTO");
$mailer->Subject  = mb_convert_encoding($title,"UTF-8","AUTO");

$mailer->AddAddress($to);
*/

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
}else{
  $targetym = $data[0]['targetym'];
}

if($errFlg){
  /*
  $message = "--------------------
  エラー内容
  --------------------
  ";
  $message .= $errMsg;
  $mailer->Body     = mb_convert_encoding($message,"UTF-8","AUTO");

  if($mailer->Send()){}
  else{
  }
  $mailer->ClearAllRecipients();
  */

}else{


  #2203新開発
/*
  $cl = new CLSYSTEM();
  $targetym = $cl->getTargetymFromManageperiod();

  $cl->targetym = $targetym;
  $cl->manageperiod_flag = 1;
  $cl->manageperiod_debug_flag = true;

  $format = "seikyu";
  $cl->format = $format;
  $cl->pdf_path = dirname(dirname(__FILE__)) . "/downloadpdf/".$targetym."_".$format.".pdf";
  $cl->generatePDF();

  $format = "ryosyu";
  $cl->format = $format;
  $cl->pdf_path = dirname(dirname(__FILE__)) . "/downloadpdf/".$targetym."_".$format.".pdf";
  $cl->generatePDF();
*/

  ##### 新開発ここまで



  /*
  $genPDF = new GENERATEPDF();
  $genPDF->type = 1;
  $genPDF->format = "seikyu";
  $genPDF->targetym = $targetym;
  $genPDF->generate();
  */
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
