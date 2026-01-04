<?php
######################
#
#　ロボペイへのデータ受け渡し
#
######################

ini_set( 'display_errors', 1 );
ini_set("memory_limit", "5120M");
set_time_limit(0);

include_once "../class/config.php";

#DB接続
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#ロボペイの振替スケジュールテーブルから次回の振替日を取得
$finish_date = date("Y-m-d H:i:s"); #手仕舞い時刻
$sql = "select * from rp_schedule where deadline_datetime > '{$finish_date}' order by deadline_datetime asc limit 1";
$stmt = $dbh->query($sql);
$transfer_date_array = $stmt->fetch(PDO::FETCH_ASSOC);
$transfer_date = $transfer_date_array['transfer_date'];

#echo $transfer_date;exit;

#締めテーブルからロボペイ未送信一覧を取得
#211025: b.direct_debit = 0 を追加
$sql = "SELECT * FROM acc_result as a inner join patient_info as b on a.original_pid = b.original_pid WHERE a.reqid is null AND b.rp_cid is not null and b.direct_debit = 0;";
#$sql = "SELECT * FROM acc_result WHERE acc_result.reqid is null;";
$stmt = $dbh->query($sql);
$acc_result = $stmt->fetchALL(PDO::FETCH_ASSOC);

/*
foreach($acc_result as $k => $v){
  echo $v['original_pid']." ".$v['patient_name']." ".$v['am']."\n";
}
exit;
*/
#print_r($acc_result);exit;

########################################

$url = "https://credit.j-payment.co.jp/gateway/at_gateway.aspx";

foreach($acc_result as $v):
  $data = array(
    "aid" => AID,
    "cmd" => 2, #請求追加
    "tday" => TDAY,
    "cid" => $v['rp_cid'],
    "amo" => $v['am'],
    "date" => $transfer_date,
    "type" => 1,	#動作タイプ(1:単発 2:連続 3:従量)
    "stat" => BILLINGSTATUS,	#課金状態（0:停止中　1:稼働中）
    "cod" => $v['cod'],
  );
  print_r($data);

  $data = http_build_query($data, "", "&");
  $header = array(
  	"Content-Type: application/x-www-form-urlencoded",
  	"Content-Length: ".strlen($data)
  );
  $context = array(
  	"http" => array(
  		"method"  => "POST",
  		'header'=> "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36\r\n",
  		"header"  => implode("\r\n", $header),
  		"content" => $data
  	)
  );

  $res = file_get_contents($url, false, stream_context_create($context));
  $res = mb_convert_encoding($res,"utf-8","sjis,EUC-JP");
  $res = explode(',',$res);
  $disc = mb_substr($res[0],0,2);

  if ($disc == "ER"){
      #acc_resultを更新
      $sql = "UPDATE acc_result SET reqid = 0, rp_errorflag = 1,rp_errormsg = '{$res[0]}' where rid = '{$v['rid']}';";
      $dbh->query($sql);
  } else {
    #echo "下記の請求追加が完了しました<br><br>";
    #echo "請求ID：".$res[0]."<br>";
    #echo "振替金額：".$res[1]."<br>";
    //請求追加の場合、reqidをre_shinryo/rek_serviceに登録（月ごとに）
    $sql = "UPDATE re_shinryo SET rp_reqid = '{$res[0]}' WHERE original_pid = '{$v['original_pid']}' AND manageperiod_status = 2 AND manageperiod_targetym = '{$v['targetym']}';";
    $dbh->query($sql);

    $sql = "UPDATE rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid SET rp_reqid = '$res[0]' WHERE rek_patient.original_pid = '{$v['original_pid']}' AND manageperiod_targetym = '{$v['targetym']}';";
    $dbh->query($sql);

    $sql = "UPDATE appendix SET rp_reqid = '$res[0]' WHERE original_pid = '{$v['original_pid']}' AND manageperiod_targetym = '{$v['targetym']}';";
    $dbh->query($sql);

    #acc_resultを更新
    $sql = "UPDATE acc_result SET reqid = '{$res[0]}' WHERE rid = '{$v['rid']}';";
    $dbh->query($sql);
  }



endforeach;
