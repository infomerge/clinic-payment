<?php
session_start();

include("./mpdf/mpdf.php");
			
$hostname = "localhost";
$username = "root";
$password = "Kmj8Fi3T";
$dbname = "auditiondebut";
$tablename = "account";

$connect = mysql_connect($hostname, $username, $password) or die ("サーバに接続できません");
mysql_select_db($dbname) or die ("データベースに接続できません");
mysql_query("SET NAMES utf8");



#$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";


if($id == "" || $id == 0){
	#$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
	#if($id == ""){
		echo "<div align='center'>エラー：PDFを出力できません";
		exit;	
	#}	
}

if (empty($_SERVER['HTTPS'])) {
	$protocol = "http";
}else{
	$protocol = "https";
}

#$html = file_get_contents("https://application.audition-debut.com/member/display_entrysheet.php?id={$id}");
$html = file_get_contents( $protocol."://application.audition-debut.com/member/display_entrysheet.php?id={$id}");
$mpdf=new mPDF('ja+aCJK',array(364,257),
0,//フォントサイズ default 0
'',//フォントファミリー
10,//左マージン
10,//右マージン
6,//トップマージン
0,//ボトムマージン
0,//ヘッダーマージン
''
);
$mpdf->dpi = 150;
$mpdf->img_dpi = 150;
$mpdf->debug = true;
$mpdf->debugfonts = true;

$mpdf->WriteHTML($html);
$mpdf->Output();
exit;