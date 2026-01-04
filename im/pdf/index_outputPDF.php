<?php
session_start();

include("./mpdf/mpdf.php");

ini_set( 'display_errors', 1 );

$hostname = "localhost";
$username = "root";
$password = "Kmj8Fi3T";
$dbname = "auditiondebut";
$tablename = "account";



$connect = mysql_connect($hostname, $username, $password) or die ("サーバに接続できません");
mysql_select_db($dbname) or die ("データベースに接続できません");
mysql_query("SET NAMES utf8");



#$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$login_id = isset($_SESSION['id']) ? $_SESSION['id'] : "";


$sql = "select account_name,account_id from account where login_id = '".$login_id."'";


$result = mysql_query($sql, $connect) or die ("クエリーを実行できません");
mysql_close($connect);

$row = mysql_fetch_array($result, MYSQL_ASSOC);

$id = $row['account_id'];


if($id == "" || $id == 0){
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
	if($id == ""){
		echo "<div align='center'>エラー：PDFを出力できません";
		exit;	
	}	
}

#ディレクトリ分けオフセット
$file_offset = 10000;
$base_directory = "/var/www/html/application.audition-debut.com/public_html/userfiles/";
$parent_directory = floor(intval($id) / $file_offset);
	
	
#親ディレクトリの確認
if(!is_dir($base_directory . $parent_directory . "/") ){
	mkdir($base_directory . $parent_directory . "/");
	@chmod($base_directory . $parent_directory, 0777);
}
#子ディレクトリ
if(!is_dir($base_directory . $parent_directory . "/" . $id . "/") ){
	mkdir($base_directory . $parent_directory . "/" . $id . "/");
	@chmod($base_directory . $parent_directory . "/" . $id, 0777);
}


$html = file_get_contents("http://application.audition-debut.com/member/display_entrysheet.php?id={$id}");
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
$mpdf->Output($base_directory . $parent_directory . "/" . $id . "/entry.pdf",'F');
exit;