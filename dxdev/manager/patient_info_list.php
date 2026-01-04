<?php
session_start();
ini_set( 'display_errors', 1 );
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
$account_name = isset($_REQUEST['account_name']) ? $_REQUEST['account_name'] : "";
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "1";


// DB,Table定義
$dbname = DBNAME;
$table = "patient_info";

#全体数の取得
$columns = 'count(*) as cnt';
$postfix = " where disp != 1 ";
if(isset($_REQUEST['patient_name']) && $_REQUEST['patient_name'] != ""){
	$postfix .= " and patient_name like '%".$_REQUEST['patient_name']."%' ";
}
if(isset($_REQUEST['patient_hihoban']) && $_REQUEST['patient_hihoban'] != ""){
	$postfix .= " and patient_hihoban = '".$_REQUEST['patient_hihoban']."' ";
}
if(isset($_REQUEST['patient_kaigo_hihoban']) && $_REQUEST['patient_kaigo_hihoban'] != ""){
	$postfix .= " and patient_kaigo_hihoban = '".$_REQUEST['patient_kaigo_hihoban']."' ";
}

$postfix .= "order by patient_info.original_pid asc";
$result = DbEx::selectRow($dbname, $table, $columns, $postfix);

$res_data = array();
$res_data['count'] = $result['cnt'];
$res_data['max_page'] = ceil($result['cnt'] / LISTOFFSET);
$res_data['current_page'] = $page;
$res_data['count_from'] = ($page -1) * LISTOFFSET + 1;
if( ($result['cnt'] - ($page -1) * LISTOFFSET ) > LISTOFFSET):
	$res_data['count_to'] = ($page) * LISTOFFSET;
else:
	$res_data['count_to'] = $result['cnt'];
endif;

#ページング
$columns = '*';
$postfix = " where disp != 1 ";
if(isset($_REQUEST['patient_name']) && $_REQUEST['patient_name'] != ""){
	$postfix .= " and patient_name like '%".$_REQUEST['patient_name']."%' ";
}
if(isset($_REQUEST['patient_hihoban']) && $_REQUEST['patient_hihoban'] != ""){
	$postfix .= " and patient_hihoban = '".$_REQUEST['patient_hihoban']."' ";
}
if(isset($_REQUEST['patient_kaigo_hihoban']) && $_REQUEST['patient_kaigo_hihoban'] != ""){
	$postfix .= " and patient_kaigo_hihoban = '".$_REQUEST['patient_kaigo_hihoban']."' ";
}
$postfix .= "order by patient_info.original_pid asc limit ".($page -1) * LISTOFFSET . " , " . LISTOFFSET . " " ;
$result = DbEx::select($dbname, $table, $columns, $postfix);

$res_data['result'] = $result;

$base_url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . "/manager/patient_info_list.php";
$pagination = "";
for($i=1;$i<=$res_data['max_page']; $i++){
	if($i == $page):
		$pagination .= " $i ";
	else:
		$pagination .= " <a href='{$base_url}?page={$i}'>{$i}</a> ";
	endif;
}

$params['patient_name'] = isset($_REQUEST['patient_name']) ? $_REQUEST['patient_name'] : "";
$params['patient_hihoban'] = isset($_REQUEST['patient_hihoban']) ? $_REQUEST['patient_hihoban'] : "";
$params['patient_kaigo_hihoban'] = isset($_REQUEST['patient_kaigo_hihoban']) ? $_REQUEST['patient_kaigo_hihoban'] : "";

$smarty->assign( 'data',$res_data);
$smarty->assign( 'navi_type',14);
#$smarty->assign( 'master_account_name',$master_account_name);
$smarty->assign( 'account_name',$account_name);

$smarty->assign( 'current_url',"/manager/patient_info_list.php");
$smarty->assign( 'category','aggregate');
$smarty->assign( 'pagination',$pagination);
$smarty->assign( 'params',$params);
$smarty->display( 'manager/patient_info_list.tpl' );

?>
