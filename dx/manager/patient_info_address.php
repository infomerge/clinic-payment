<?php
session_start();
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

$row = $result->fetchRow();
$login_name = $row[1];
$authority_id = $row[2];
$account_id = $row[3];

if($row[0] == 0) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /index.php?error=error");
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$original_pid = isset($_REQUEST['original_pid']) ? $_REQUEST['original_pid'] : "";
if($original_pid == ""){
	header("Location: /index.php?error=error");
}

$dbname = DBNAME;

if($_SERVER["REQUEST_METHOD"] == "POST"){

	$table = "patient_address";

	#$data['original_pid'] = $_REQUEST['original_pid'];
	$data['original_pid'] = $_REQUEST['original_pid'];

	$data['postal_code'] = $_REQUEST['postal_code'];
	$data['address'] = $_REQUEST['address'];


	$data['update_date'] = $today;

	if($original_pid != ""){
		$postfix = " where original_pid = '{$original_pid}' ";
		DbEx::update($dbname, $table, $data, $postfix);
		header("Location: /manager/patient_info.php?original_pid={$original_pid}");
	}else{
		$data['regist_date'] = $today;
		DbEx::insert($dbname, $table, $data);
		header("Location: /manager/patient_info_a.php");
	}
	/*
	$data['original_pid'] = $_REQUEST['original_pid'];
	*/

}



if($id != ""){
	$table = "patient_info_address";
	$columns = '*';
	$postfix = " where id = '{$id}' ";
	$result = DbEx::selectRow($dbname, $table, $columns, $postfix);
}else{
	$result = array();
}


$smarty->assign( 'data',$result);

$smarty->assign( 'navi_type',14);
$smarty->assign( 'master_account_name',$master_account_name);
$smarty->assign( 'account_name',$account_name);
$smarty->assign( 'from',$from);
$smarty->assign( 'to',$to);

$smarty->assign( 'current_url',"/manager/kktp.php");
$smarty->assign( 'category','aggregate');
$smarty->display( 'manager/patient_info_address.tpl' );

?>
