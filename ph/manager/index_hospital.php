<?php
session_start();
ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once '../class/common.php';
require_once('../class/db_extension.php');

// セッションチェック
$common = new COMMON;

$common->id = $_SESSION['id'];
$common->password = $_SESSION['password'];
$result = $common->checkid_hospital();

$row = $result->fetchRow();
$login_name = $row[1];
$authority_id = 0;
$account_id = "";

if($row[0] == 0) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /index_hospital.php?error=error");
}



$smarty->display( 'manager/index_hospital.tpl' );

?>