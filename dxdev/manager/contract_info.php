<?php
session_start();
ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once '../class/common.php';
include_once '../class/config.php';
require_once('../class/db_extension.php');

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

$account_id = isset($_REQUEST['account_id']) ? $_REQUEST['account_id'] : "";

$dbname = DBNAME;

if($_SERVER["REQUEST_METHOD"] == "POST"){

	$table = "account_info";

	#$data['account_id'] = $_REQUEST['account_id'];
	$data['postal_code'] = $_REQUEST['postal_code'];
	$data['address'] = $_REQUEST['address'];
	$data['tel'] = $_REQUEST['tel'];
	$data['others'] = $_REQUEST['others'];

	$data['update_date'] = $today;

	if($account_id != ""){
		$postfix = " where account_id = '{$account_id}' ";
		DbEx::update($dbname, $table, $data, $postfix);
		header("Location: /manager/contract_info.php?account_id={$account_id}");
	}else{
		$data['regist_date'] = $today;
		DbEx::insert($dbname, $table, $data);
		header("Location: /manager/contract_info.php");
	}
	/*
	$data['account_id'] = $_REQUEST['account_id'];
	*/

}



if($account_id != ""){
	$table = "account_info";
	$columns = '*';
	$postfix = " where account_id = '{$account_id}' ";
	$result = DbEx::selectRow($dbname, $table, $columns, $postfix);
}else{
	$result = array();
}

$table = "account_info";
$columns = 'account_id,inst_name';
$postfix = " where 1 = 1 ";
$account = DbEx::select($dbname, $table, $columns, $postfix);

$account_info = array();
foreach($account as $val){
	$account_info[$val['account_id']] = $val['inst_name'];
}



$smarty->assign( 'data',$result);
$smarty->assign( 'm_partner',$m_partner);

$smarty->assign( 'navi_type',14);
$smarty->assign( 'master_account_name',$master_account_name);
$smarty->assign( 'account_name',$account_name);
$smarty->assign( 'from',$from);
$smarty->assign( 'to',$to);

$smarty->assign( 'current_url',"/manager/kktp.php");
$smarty->assign( 'category','aggregate');
$smarty->display( 'manager/contract_info.tpl' );

?>
