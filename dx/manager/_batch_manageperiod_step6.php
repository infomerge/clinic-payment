<?php
#ロボペイの結果がエラー = 1のデータを繰越処理


ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select * from manageperiod where status = 3 ";
#$sql = "select * from manageperiod where status <> 0 ";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);
print_r($data);exit;

$flag = true;
if(count($data) == 1){
  #echo "here";exit;

  $targetym = $data[0]['targetym'];
  $id = $data[0]['id'];

  $sql = "SELECT * FROM acc_result WHERE targetym = '{$targetym}' AND rp_errorflag = 1";
  $stmt = $dbh->query($sql);
  $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
  #print_r($data);

  foreach($data as $val){
    #$sql = "SELECT * FROM re_shinryo WHERE original_pid = '{$val['original_pid']}' and manageperiod_targetym = '{$targetym}' ;";
    $sql = "UPDATE re_shinryo SET manageperiod_status = 5 WHERE  original_pid = '{$val['original_pid']}' and manageperiod_targetym = '{$targetym}' ;";
    $stmt = $dbh->query($sql);
    #$data2 = $stmt->fetchALL(PDO::FETCH_ASSOC);
    #print_r($data2);
    $sql = "UPDATE rek_service SET manageperiod_status = 5 WHERE  original_pid = '{$val['original_pid']}' and manageperiod_targetym = '{$targetym}' ;";
    $stmt = $dbh->query($sql);

    $sql = "UPDATE appendix SET manageperiod_status = 5 WHERE  original_pid = '{$val['original_pid']}' and manageperiod_targetym = '{$targetym}' ;";
    $stmt = $dbh->query($sql);
  }


/*
  $sql = "SELECT * FROM re_shinryo WHERE manageperiod_status = 3 and manageperiod_targetym = '{$targetym}' ;";
  echo $sql;
  $stmt = $dbh->query($sql);
  $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
  if(count($data)>0){
    $flag = false;
  }

  $sql = "SELECT * FROM rek_service WHERE manageperiod_status = 3 and manageperiod_targetym = '{$targetym}' ;";
  echo $sql;
  $stmt = $dbh->query($sql);
  $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
  if(count($data)>0){
    $flag = false;
  }

  $sql = "SELECT * FROM appendix WHERE manageperiod_status = 3 and manageperiod_targetym = '{$targetym}' ;";
  echo $sql;
  $stmt = $dbh->query($sql);
  $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
  if(count($data)>0){
    $flag = false;
  }

  if($flag){
    $sql = "UPDATE manageperiod SET status = 9 WHERE id = '{$id}' ;";
    $dbh->query($sql);
  }
*/
}
?>
