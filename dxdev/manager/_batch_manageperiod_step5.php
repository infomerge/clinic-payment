<?php
#口座振替以外のステータスを4に変更
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
#print_r($data);exit;

if(count($data) == 1){
  $targetym = $data[0]['targetym'];

  #当該targetymで、re_shinryoに、status=4があった場合=ロボペイからコールバックがあった場合処理する
  $sql = "SELECT * FROM re_shinryo WHERE manageperiod_status = 4;";
  $stmt = $dbh->query($sql);
  $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
  #print_r($data);exit;

  #if(count($data)>0){
    #対象となるoriginal_pidを抽出
    $sql = "SELECT *
            FROM re_shinryo INNER JOIN patient_info ON re_shinryo.original_pid = patient_info.original_pid
            WHERE patient_info.direct_debit = 1 AND re_shinryo.manageperiod_targetym = '{$targetym}'";
    $stmt = $dbh->query($sql);
    $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
    #print_r($data);exit;

    foreach($data as $v){
      $sql = "UPDATE re_shinryo SET manageperiod_status = 4 WHERE sid = '{$v['sid']}';";
      $dbh->query($sql);
    }

    #介護保険
    $sql = "SELECT *
            FROM rek_service INNER JOIN patient_info ON rek_service.original_pid = patient_info.original_pid
            WHERE patient_info.direct_debit = 1 AND rek_service.manageperiod_targetym = '{$targetym}'";
    $stmt = $dbh->query($sql);
    $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
    foreach($data as $v){
      $sql = "UPDATE rek_service SET manageperiod_status = 4 WHERE sid = '{$v['sid']}';";
      $dbh->query($sql);
    }

    $sql = "SELECT *
            FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid
            WHERE patient_info.direct_debit = 1 AND appendix.manageperiod_targetym = '{$targetym}'";
    $stmt = $dbh->query($sql);
    $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
    foreach($data as $v){
      $sql = "UPDATE appendix SET manageperiod_status = 4 WHERE app_id = '{$v['app_id']}';";
      $dbh->query($sql);
    }
    #echo "\n";
    #print_r($data);
  #}
}
?>
