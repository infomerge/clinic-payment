<?php
#ロボペイの結果がエラー = 1のデータを繰越処理


ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$sql = "select * from re_shinryo where manageperiod_status = 3";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);

$tmp = array();
foreach($data as $v):
  $tmp[$v['original_pid']][$v['manageperiod_targetym']] = $v['manageperiod_status'];
endforeach;
#print_r($tmp);

foreach($tmp as $original_pid => $v):
  foreach($v as $targetym => $vv):
    $sql = "select * from acc_result where targetym = {$targetym} and original_pid = {$original_pid};";
    #echo $sql;exit;
    $stmt = $dbh->query($sql);
    $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
    if(count($data) == 0):
      echo "targetym = {$targetym} and original_pid = {$original_pid} - 対象なし<br>\n";
    else:
      echo "targetym = {$targetym} and original_pid = {$original_pid} - rp_errorflag = {$data[0]['rp_errorflag']}<br>\n";
    endif;
  endforeach;
endforeach;

/*
$sql = "select * from acc_result where rp_errorflag = 9 ";
#$sql = "select * from manageperiod where status <> 0 ";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);
#print_r($data);exit;

$cnt = 0;
foreach($data as $v):
  $sql = "SELECT * from re_shinryo where manageperiod_targetym = {$v['targetym']} and original_pid = {$v['original_pid']};";
  #echo $sql;
  $stmt = $dbh->query($sql);
  $data2 = $stmt->fetchALL(PDO::FETCH_ASSOC);
  #print_r($data2);
  foreach($data2 as $vv):
    if($vv['manageperiod_status'] != 4):
      $cnt++;
      #echo "sid = {$vv['sid']} - original_pid = {$vv['original_pid']} - manageperiod_status = {$vv['manageperiod_status']}<br>\n";
      echo "UPDATE re_shinryo SET manageperiod_status = 4 WHERE sid = {$vv['sid']} ;<br>\n";
    endif;
  endforeach;
  

endforeach;

echo "合計：".$cnt;
*/
?>
