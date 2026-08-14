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
$result = $common->checkid();

$row = $result->fetchRow();
$login_name = $row[1];
$authority_id = $row[2];
$account_id = $row[3];

if($row[0] == 0) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /index.php?error=error");
}

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";

$tmp = "";
$search = array();

if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	$client_id = isset($_REQUEST['client_id']) ? $_REQUEST['client_id'] : "";
	
	if($client_id != ""){
		$dbname = "charisma";
		$table = "users";
		
		$data['collection_status'] = isset($_REQUEST['collection_status']) ? $_REQUEST['collection_status'] : "";
		$data['memo'] = isset($_REQUEST['memo']) ? addslashes($_REQUEST['memo']) : "";
		
		$postfix = " where client_id = '{$client_id}' ";	
		DbEx::update($dbname, $table, $data, $postfix);
	}
	header("Location: /manager/");
}else{
	
	if($_REQUEST['sei']){
		$tmp .= " and sei like '%{$_REQUEST['sei']}%' ";
		$search['sei'] = $_REQUEST['sei'];
	}
	if($_REQUEST['mei']){
		$tmp .= " and mei like '%{$_REQUEST['mei']}%' ";
		$search['mei'] = $_REQUEST['mei'];
	}
	if($_REQUEST['biz_name']){
		$tmp .= " and biz_name like '%{$_REQUEST['biz_name']}%' ";
		$search['biz_name'] = $_REQUEST['biz_name'];
	}
	if($_REQUEST['email']){
		$tmp .= " and email like '%{$_REQUEST['email']}%' ";
		$search['email'] = $_REQUEST['email'];
	}
	if($_REQUEST['summary']){
		$tmp .= " and summary like '%{$_REQUEST['summary']}%' ";
		$search['summary'] = $_REQUEST['summary'];
	}
	
	if( isset($_REQUEST['collection_status']) ){
		$tmp2 = array();
		foreach(	$_REQUEST['collection_status'] as $k => $v){
				$tmp2[] = " collection_status = '{$k}' ";
		}
		$tmp .= " and (".implode(" or ",$tmp2).") ";
		$search['collection_status'] = $_REQUEST['collection_status'];
	}
	
	if( !isset($_REQUEST['pr_flag']) || $_REQUEST['pr_flag'] == 1 ){
		$tmp .= " and pr_flag = 1 ";
		$search['pr_flag'] = 1;
	}elseif( isset($_REQUEST['pr_flag']) && $_REQUEST['pr_flag'] == 0 ){
		$search['pr_flag'] = 0;
	}
	
}



$dbname = "charisma";
$table = "users";
$columns = '*';
$postfix = "where 1 = 1 " . $tmp;	
$postfix .= " order by update_date desc";

$result = DbEx::select($dbname, $table, $columns, $postfix);



$m_status = array(
	"完済",
	"退会",
	"休止／離脱",
	"自動送金",
	"手動送金",
	"調整中",
	"ペナルティ",
	"未調整",
);


$smarty->assign( 'data',$result);
$smarty->assign( 'partner',$partner);
$smarty->assign( 'm_status',$m_status);
$smarty->assign( 'search',$search);


$smarty->assign( 'navi_type',1);
$smarty->assign( 'type',$type);

$smarty->assign( 'current_url',"/manager/");
$smarty->assign( 'category','users');
$smarty->display( 'manager/index.tpl' );

?>