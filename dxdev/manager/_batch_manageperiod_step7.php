<?php
#対象targetymで、status3がなくなれば終了
#このファイルは、ロボペイの結果が帰ってきた、毎月17日くらいに実行

ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select * from manageperiod where status = 3 ";
#$sql = "select * from manageperiod where status <> 0 ";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);
print_r($data);

$flag = true;
if(count($data) == 1){
  #echo "here";exit;

  $targetym = $data[0]['targetym'];
  $id = $data[0]['id'];

#20210928:ロボペイ対象にない患者はstatus=3のままだから、この処理外す
/*
  $sql = "SELECT * FROM re_shinryo WHERE manageperiod_status = 3 and manageperiod_targetym = '{$targetym}' ;";
  #echo $sql;
  $stmt = $dbh->query($sql);
  $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
  print_r($data);
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
*/

  if($flag){
    $sql = "UPDATE manageperiod SET status = 9 WHERE id = '{$id}' ;";
    $dbh->query($sql);
  }

}
?>
