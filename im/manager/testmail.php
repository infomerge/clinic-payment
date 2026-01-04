<?php
ini_set("display_errors",1);
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


$errMsg = "";
$errFlg = false;
$targetym = "";

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

echo "完了";
