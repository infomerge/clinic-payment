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

$holder_id = isset($_REQUEST['holder_id']) ? $_REQUEST['holder_id'] : "";

$dbname = DBNAME;

if($_SERVER["REQUEST_METHOD"] == "POST"){

	$table = "bank_info";

	#$data['holder_id'] = $_REQUEST['holder_id'];
	$data['holder_name'] = $_REQUEST['holder_name'];
	$data['bank_name'] = $_REQUEST['bank_name'];
	$data['branch_name'] = $_REQUEST['branch_name'];
	$data['classification'] = $_REQUEST['classification'];
	$data['account_number'] = $_REQUEST['account_number'];
	$data['others'] = $_REQUEST['others'];

	$data['update_date'] = $today;

	if($holder_id != ""){
		$postfix = " where holder_id = '{$holder_id}' ";
		DbEx::update($dbname, $table, $data, $postfix);
		header("Location: /manager/bank_info.php?holder_id={$holder_id}");
	}else{
		$data['regist_date'] = $today;
		DbEx::insert($dbname, $table, $data);
		header("Location: /manager/bank_info.php");
	}
	/*
	$data['holder_id'] = $_REQUEST['holder_id'];
	*/

}



if($holder_id != ""){
	$table = "bank_info";
	$columns = '*';
	$postfix = " where holder_id = '{$holder_id}' ";
	$result = DbEx::selectRow($dbname, $table, $columns, $postfix);
}else{
	$result = array();	
}

$table = "_tn_partner";
$columns = 'client_id,client_name';
$postfix = " where 1 = 1 ";
$partner = DbEx::select($dbname, $table, $columns, $postfix);

$m_partner = array();
foreach($partner as $val){
	$m_partner[$val['client_id']] = $val['client_name'];
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
$smarty->display( 'manager/bank_info.tpl' );

?>
