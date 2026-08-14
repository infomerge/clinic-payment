<?php
session_start();
ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once '../class/common.php';
#require_once('../class/db_extension.php');
include_once "../class/config.php";

#print_r($_SESSION);exit;

// セッションチェック
$common = new COMMON;

$common->id = $_SESSION['id'];
$common->password = $_SESSION['password'];
$result = $common->checkid();

#$row = $result->fetchRow();
$row = $result->fetch();

#print_r($row);exit;

$login_name = $row[1];
$authority_id = $row[2];
$account_id = $row[3];

if($row[0] == 0) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /index.php?error=error");
}



$smarty->display( 'manager/index.tpl' );

?>
