<?php
ini_set("display_errors",1);
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

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
$title = "CLシステム：口座振替PUSH通知";
$message = "";

//メール本体
$mailer->FromName = mb_convert_encoding("CLシステム","UTF-8","AUTO");
$mailer->Subject  = mb_convert_encoding($title,"UTF-8","AUTO");

$mailer->AddAddress($to);
?>

<!DOCTYPE_html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>Robot Payment 振替結果取り込み</title>
<?php
    $smarty->display( 'common/head_inc.tpl');
?>
</head>

<body>
<?php
$smarty->display( 'common/header.tpl' );

#キックバックパラメータ
$gid = htmlspecialchars($_GET["gid"]);
$rst = htmlspecialchars($_GET["rst"]);
$ap = htmlspecialchars($_GET["ap"]);
$ec = htmlspecialchars(strval($_GET["ec"]));
$god = htmlspecialchars($_GET["god"]);
$cod = htmlspecialchars(strval($_GET["cod"]));
$am = htmlspecialchars($_GET["am"]);
$tx = htmlspecialchars($_GET["tx"]);
$sf = htmlspecialchars($_GET["sf"]);
$ta = htmlspecialchars($_GET["ta"]);
$em = htmlspecialchars(strval($_GET["em"]));
$nm = htmlspecialchars(strval($_GET["nm"]));


#$godが0出なければ強制終了
if($god != 0){ exit; }

#$original_pid = ltrim(mb_substr($cod,0,8),"0");
#$srm = mb_substr($cod,-6);


$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/*
$sql = "INSERT INTO acc_result (gid,rst,ap,ec,god,cod,am,tx,sf,ta,em,nm,original_pid,srm)
                    VALUES ('$gid','$rst','$ap','$ec','$god','$cod','$am','$tx','$sf','$ta','$em','$nm','$original_pid','$srm')";
*/

#対象のoriginal_pidを抽出
#rst = 1の場合、関連するレコードのstatusを4
#rst = 2の場合、関連するレコードのstatusを5
$sql = "SELECT original_pid,targetym FROM acc_result WHERE cod = '$cod';";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);
$original_pid = $data[0]['original_pid'];
$manageperiod_targetym = $data[0]['targetym'];
$manageperiod_status = "";

$rp_errorflag = 0;
switch($rst){
  case 1:
    $rp_errorflag = 9;
    $manageperiod_status = 4;
    break;
  case 2:
    $rp_errorflag = 1;
    $manageperiod_status = 5;
    break;
  default:
    break;
}
$sql = "UPDATE re_shinryo SET manageperiod_status = '$manageperiod_status' WHERE original_pid = '$original_pid' and manageperiod_targetym = '$manageperiod_targetym' and manageperiod_status = 3 ;";
$message .= $sql."\n";
$dbh->query($sql);

$sql = "UPDATE rek_service SET manageperiod_status = '$manageperiod_status' WHERE original_pid = '$original_pid' and manageperiod_targetym = '$manageperiod_targetym' and manageperiod_status = 3 ;";
$message .= $sql."\n";
$dbh->query($sql);

$sql = "UPDATE appendix SET manageperiod_status = '$manageperiod_status' WHERE original_pid = '$original_pid' and manageperiod_targetym = '$manageperiod_targetym' and manageperiod_status = 3 ;";
$message .= $sql."\n";
$dbh->query($sql);


$sql = "UPDATE acc_result SET gid='$gid',rst='$rst',ap='$ap',ec='$ec',god='$god',tx='$tx',sf='$sf',ta='$ta',em='$em',nm='$nm',rp_errorflag = '$rp_errorflag' where cod = '$cod'";
$message .= $sql."\n";
$dbh->query($sql);

$mailer->Body     = mb_convert_encoding($message,"UTF-8","AUTO");
//送信する
if($mailer->Send()){}
else{
}
$mailer->ClearAllRecipients();

echo "完了";
?>

</body>
</html>
