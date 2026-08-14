<?php
ini_set( 'display_errors', 1 );
	include '../class/common.php';

/*
$id = $_POST['id'];
$password = $_POST['password'];
	$dbh = new PDO('mysql:dbname=ns_crossline;host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "select count(*),account_name,authority_id,account_id from _cms_account where login_id = '" . $id . "' and password = '" . $password . "'";
	$stmt = $dbh->query($sql);
	$row = $stmt->fetch();
*/
#print_r($res);exit;

	$common = new COMMON;

	$common->id = htmlspecialchars( $_POST['id'] );
	$common->password = htmlspecialchars( $_POST['password'] );
	$result = $common->checkid();

	$row = $result->fetch();
#print_r($row);exit;

	if($row[0] > 0) {
		session_start();
		$_SESSION['id'] = $common->id;
		$_SESSION['password'] = $common->password;

		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /manager/index.php");
	} else {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /index.php?error=error");
	}
?>
