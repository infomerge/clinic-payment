<?php
/*
$aaa = "００１８１２４８";
$aaa = mb_convert_kana($aaa, 'kvrn');
echo $aaa;
*/
#echo uniqid();
#echo date("Y-m-10");
ini_set("display_errors",1);

date_default_timezone_set("Asia/Tokyo");
//DB connect
$dbh = new PDO('mysql:dbname=ns_crosslinedeploy;host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/*
#$finish_date = "2020-06-26 23:03:00";
$finish_date = date("Y-m-d H:i:s");
echo $finish_date."<br>\n";

#PDF出力テーブル抽出
$sql = "select * from rp_schedule where deadline_datetime > '{$finish_date}' order by deadline_datetime asc limit 1";
$stmt = $dbh->query($sql);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($data['transfer_date']);
*/

/*
$tejimai_date = "2020-10-26 22:03:00";
#$tejimai_date = date("Y-m-d H:i:s");
echo $tejimai_date."<br>\n";
$year = date("Y",strtotime($tejimai_date));
$month = date("m",strtotime($tejimai_date));


$sql = "select * from rp_schedule where YEAR(deadline_datetime) = '{$year}' and MONTH(deadline_datetime) = '{$month}' order by deadline_datetime asc limit 1";
$stmt = $dbh->query($sql);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if(strtotime($data['deadline_datetime']) > strtotime($tejimai_date)){
  echo date("Ym",strtotime($tejimai_date));
}else{
  echo date("Ym",strtotime($tejimai_date."+1month"));
}



#ロボペイの振替スケジュールテーブルから次回の振替日を取得
$finish_date = date("Y-m-d H:i:s"); #手仕舞い時刻
$sql = "select * from rp_schedule where deadline_datetime > '{$finish_date}' order by deadline_datetime asc limit 1";
echo $sql;
$stmt = $dbh->query($sql);
$transfer_date_array = $stmt->fetch(PDO::FETCH_ASSOC);
$transfer_date = $transfer_date_array['transfer_date'];

echo "---".$transfer_date;
*/

$sql = "SELECT acc_detail.contents FROM acc_detail , acc_result where acc_detail.rid = acc_result.rid and acc_result.targetym = 202203 and acc_result.original_pid = 98";
$stmt = $dbh->query($sql);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$patient_data = unserialize($data['contents']);
print_r($patient_data);
?>
