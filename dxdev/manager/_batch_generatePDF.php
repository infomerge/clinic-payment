<?php
ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select * from managepdf where seikyu_flag = 0 limit 0,1";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);

if(count($data) > 0){
  $target_date = $data[0]['outputtarget']."01";

  $start_date = date('Ymd',strtotime($target_date));
  $end_date = date('Ymd', mktime(0, 0, 0, date('m',strtotime($target_date)) + 1, 0, date('Y',strtotime($target_date))));
  #echo $start_date."---".$end_date;exit;


  #exec( "cd /var/www/html/cl.netstars.vision/public_html/manager/ ; /usr/bin/php _batch_test.php ".$start_date." ".$end_date." ryosyu");
  $comm = "cd /var/www/html/cl.netstars.vision/public_html/manager/ ; /usr/bin/php generate-receipt-all-pdf_renew4dl.php ".$start_date." ".$end_date." ryosyu";
  exec( $comm );
  $comm = "cd /var/www/html/cl.netstars.vision/public_html/manager/ ; /usr/bin/php generate-receipt-all-pdf_renew4dl.php ".$start_date." ".$end_date." seikyu";
  exec( $comm );
  #exec( "cd /var/www/html/cl.netstars.vision/public_html/manager/ ; /usr/bin/php generate-receipt-all-pdf_renew4dl.php ".$start_date." ".$end_date." seikyu" );

  $sql = "update managepdf set ryosyu_flag = 1,seikyu_flag = 1 where id = ".$data[0]['id'].";";
  $stmt = $dbh->query($sql);
  #echo "実行完了";
}else{
  #echo "なし";
  exit;
}
