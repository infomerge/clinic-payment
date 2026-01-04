<?php
session_start();
ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once '../class/common.php';
include_once '../class/config.php';
require_once('../class/db_extension.php');

// セッションチェック
$common = new COMMON;

$common->id = $_SESSION['id'];
$common->password = $_SESSION['password'];
$result = $common->checkid_hospital();

$row = $result->fetchRow();
$login_name = $row[1];
#$authority_id = $row[2];
#$account_id = $row[3];
$account_id = $row[2];

if($row[0] == 0) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /index_hospital.php?error=error");
}

$account_name = isset($_REQUEST['account_name']) ? $_REQUEST['account_name'] : "";
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "1";

$from = isset($_REQUEST['from']) ? date('Y-m-d',strtotime($_REQUEST['from'])) : "";
$to = isset($_REQUEST['to']) ? $_REQUEST['to'] : "";


$dbname = DBNAME;
$table = "patient_info as a , accountpatient_relation as b";
#$table = "patient_info inner join patient_address on patient_info.patient_id = patient_address.patient_id";
$columns = 'a.*';
$postfix = " where a.patient_id = b.patient_id and b.account_id = {$account_id} ";
$postfix .= "order by a.regist_date desc";
$result = DbEx::select($dbname, $table, $columns, $postfix);

/*
$data = array();
foreach($result as $k => $v){
	$data[$v['patient_id']][] = $v;
}
*/

#print_r($result);
$smarty->assign( 'data',$result);

$smarty->assign( 'navi_type',14);
$smarty->assign( 'master_account_name',$master_account_name);
$smarty->assign( 'account_name',$account_name);
$smarty->assign( 'from',$from);
$smarty->assign( 'to',$to);

$smarty->assign( 'current_url',"/manager/patient_info_list.php");
$smarty->assign( 'category','aggregate');
$smarty->display( 'manager/patient_info_list_hospital.tpl' );

?>
