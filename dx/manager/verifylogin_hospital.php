<?php
ini_set( 'display_errors', 1 );
	include '../class/common.php';
	
	$common = new COMMON;

	$common->id = mysql_real_escape_string( $_POST['id'] );
	$common->password = mysql_real_escape_string( $_POST['password'] );
	$result = $common->checkid_hospital();
	
	$row = $result->fetchRow();
	
	if($row[0] > 0) {
		session_start();
		$_SESSION['id'] = $common->id;
		$_SESSION['password'] = $common->password;
		
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /manager/patient_info_list_hospital.php");
	} else {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /index_hospital.php?error=error");
	}
?>