<?php
session_start();
ini_set( 'display_errors', 0 );
include_once "../common/smarty_settings.php";
include_once '../class/common.php';
require_once('../class/db_extension.php');
include_once "../class/config.php";

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
$account_name = isset($_REQUEST['account_name']) ? $_REQUEST['account_name'] : "";
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "1";

$from = isset($_REQUEST['from']) ? date('Y-m-d',strtotime($_REQUEST['from'])) : "";
$to = isset($_REQUEST['to']) ? $_REQUEST['to'] : "";

$table = "account_info";
$columns = '*';
$postfix = " where 1 = 1 ";
$postfix .= "order by regist_date desc";
$result = DbEx::select($dbname, $table, $columns, $postfix);


$smarty->assign( 'data',$result);

$smarty->assign( 'navi_type',14);
#$smarty->assign( 'master_account_name',$master_account_name);
$smarty->assign( 'account_name',$login_name);
$smarty->assign( 'from',$from);
$smarty->assign( 'to',$to);

$smarty->assign( 'current_url',"/manager/account_info_list.php");
$smarty->assign( 'category','aggregate');
$smarty->display( 'manager/account_info_list.tpl' );

?>
