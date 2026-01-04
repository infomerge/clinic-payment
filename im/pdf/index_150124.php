<?php
include("./mpdf/mpdf.php");

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";

if($id == ""){
	echo "<div align='center'>エラー：PDFを出力できません";
	exit;	
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
$mpdf->Output();
exit;