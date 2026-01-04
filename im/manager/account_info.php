<?php
session_start();
ini_set( 'display_errors', 0 );
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

$dbname = DBNAME;

$original_irkkcode = isset($_REQUEST['original_irkkcode']) ? $_REQUEST['original_irkkcode'] : "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

	$table = "account_info";

	#$data['original_irkkcode'] = $_REQUEST['original_irkkcode'];
	$data['irkkname'] = $_REQUEST['irkkname'];
	$data['login_id'] = $_REQUEST['login_id'];
	$data['password'] = $_REQUEST['password'];
	$data['irkk_postal_code'] = $_REQUEST['irkk_postal_code'];
    $data['irkk_prefecture'] = $_REQUEST['irkk_prefecture'];
    $data['irkk_address1'] = $_REQUEST['irkk_address1'];
    $data['irkk_address2'] = $_REQUEST['irkk_address2'];
	$data['tel'] = $_REQUEST['tel'];
    $data['irkk_bank_name'] = $_REQUEST['irkk_bank_name'];
    $data['irkk_bank_branch'] = $_REQUEST['irkk_bank_branch'];
    $data['irkk_bank_clasification'] = $_REQUEST['irkk_bank_clasification'];
    $data['irkk_bank_no'] = $_REQUEST['irkk_bank_no'];
	$data['others'] = $_REQUEST['others'];

	$data['update_date'] = $today;

	if($original_irkkcode != ""){
		#$postfix = " where account_id = '{$account_id}' ";
		$postfix = " where original_irkkcode = '{$original_irkkcode}' ";
		DbEx::update($dbname, $table, $data, $postfix);
		header("Location: /manager/account_info.php?original_irkkcode={$original_irkkcode}");
	}else{
		$data['regist_date'] = $today;
		DbEx::insert($dbname, $table, $data);
		header("Location: /manager/account_info_list.php");
	}
	/*
	$data['original_irkkcode'] = $_REQUEST['original_irkkcode'];
	*/

}



if($original_irkkcode != ""){
	$table = "account_info";
	$columns = '*';
	#$postfix = " where account_id = '{$account_id}' ";
	$postfix = " where original_irkkcode = '{$original_irkkcode}' ";
	$result = DbEx::selectRow($dbname, $table, $columns, $postfix);
}else{
	$result = array();
}



$smarty->assign( 'data',$result);

$smarty->assign( 'navi_type',14);
#$smarty->assign( 'master_account_name',$master_account_name);
#$smarty->assign( 'account_name',$account_name);
#$smarty->assign( 'from',$from);
#$smarty->assign( 'to',$to);

$smarty->assign( 'current_url',"/manager/kktp.php");
$smarty->assign( 'category','aggregate');
$smarty->display( 'manager/account_info.tpl' );

?>
