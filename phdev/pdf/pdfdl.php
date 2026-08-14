<?php
ini_set( 'display_errors', 0 );

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
if($id == ""){
	echo "<div align='center'>エラー：PDFを出力できません";
	exit;	
}	

$a = file_get_contents("http://application.audition-debut.com/pdf/index_outputPDF.php?id={$id}");