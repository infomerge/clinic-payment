<?php
ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select * from manageperiod where status = 0 ";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);

if(count($data) > 0){
  #print_r($data);
  foreach($data as $v):
    $sql = "select * from re_shinryo where manageperiod_status = 0 or manageperiod_status = 5;";
    #$sql = "select * from re_shinryo where manageperiod_status = 0 ;";
    $stmt = $dbh->query($sql);
    $data2 = $stmt->fetchALL(PDO::FETCH_ASSOC);

    $sql = "update re_shinryo set manageperiod_status = 1 , manageperiod_targetym = '{$v['targetym']}' where manageperiod_status = 0 or manageperiod_status = 5;";
    $stmt = $dbh->query($sql);
    $sql = "update rek_service set manageperiod_status = 1 , manageperiod_targetym = '{$v['targetym']}' where manageperiod_status = 0 or manageperiod_status = 5;";
    $stmt = $dbh->query($sql);
    $sql = "update appendix set manageperiod_status = 1 , manageperiod_targetym = '{$v['targetym']}' where manageperiod_status = 0 or manageperiod_status = 5;";
    $stmt = $dbh->query($sql);

    $sql = "update manageperiod set status = 1 where targetym = '{$v['targetym']}' and status = 0;";
    $stmt = $dbh->query($sql);

  endforeach;

}else{

  exit;
}
