<?php
session_start();
unset($_SESSION['kaigo_data']);

ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once '../class/common.php';
require_once('../class/db_extension.php');
include_once "../class/config.php";

$today = date('Y-m-d h:i:s');
// セッションチェック
$common = new COMMON;

$common->id = $_SESSION['id'];
$common->password = $_SESSION['password'];
$result = $common->checkid();

#$row = $result->fetchRow();
$row = $result->fetch();
$login_name = $row[1];
$authority_id = $row[2];
$account_id = $row[3];

if($row[0] == 0) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /index.php?error=error");
}






$smarty->assign( 'data',$result);
#$smarty->assign( 'm_partner',$m_partner);

$smarty->assign( 'navi_type',14);
#$smarty->assign( 'master_account_name',$master_account_name);
$smarty->assign( 'account_name',$login_name);
#$smarty->assign( 'from',$from);
#$smarty->assign( 'to',$to);

$smarty->assign( 'current_url',"/manager/kktp.php");
$smarty->assign( 'category','aggregate');
$smarty->display( 'manager/receipt_select.tpl' );

?>
