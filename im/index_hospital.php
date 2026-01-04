<?php
ini_set( 'display_errors', 0 );
include_once "./common/smarty_settings.php";

if(isset($_GET['error'])) {
	$smarty->assign('message','エラー：ID／パスワードを正しく入力してください');
} else {
	$smarty->assign('message','');
}

$smarty->display( 'index_hospital.tpl' );
?>