<?php
#ロボペイ返却データがエラーだった医療保険／介護保険／自由診療の「manageperiod_status」を5に変更

ini_set( 'display_errors', 1 );

include_once "../class/config.php";
include_once "../class/clsystem.php";

$cl = new CLSYSTEM();
if (isset($GLOBALS['argv'][1])) {
  $cl->manageperiod_flag = $GLOBALS['argv'][1];
}else{
  $cl->manageperiod_flag = $_GET["manageperiod_flag"];
}
if (isset($GLOBALS['argv'][2])) {
  $cl->targetym = $GLOBALS['argv'][2];
}else{
  $cl->targetym = isset($_GET["targetym"]) ? $_GET["targetym"] : "";
}

$cl->carryForward();


exit;

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if( $manageperiod_flag == 0 ):
  $sql = "SELECT b.sid FROM acc_result as a inner join re_shinryo as b on a.original_pid = b.original_pid  WHERE a.targetym = '{$targetym}' and a.rp_errorflag = 1 ";
endif;
#$sql = "select * from manageperiod where status <> 0 ";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);


foreach($data as $v):
  $sql = "update re_shinryo set manageperiod_status = 5 where sid = '{$v['sid']}';";
  echo $sql."\n";
  $dbh->query($sql);
endforeach;

#介護保険
if( $manageperiod_flag == 0 ):
  $sql = "SELECT b.sid FROM acc_result as a inner join rek_service as b on a.original_pid = b.original_pid  WHERE a.targetym = '{$targetym}' and a.rp_errorflag = 1 ";
endif;
#$sql = "select * from manageperiod where status <> 0 ";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);


foreach($data as $v):
  $sql = "update rek_service set manageperiod_status = 5 where sid = '{$v['sid']}';";
  echo $sql."\n";
  $dbh->query($sql);
endforeach;

#自由診療
if( $manageperiod_flag == 0 ):
  $sql = "SELECT b.app_id FROM acc_result as a inner join appendix as b on a.original_pid = b.original_pid  WHERE a.targetym = '{$targetym}' and a.rp_errorflag = 1 ";
endif;
#$sql = "select * from manageperiod where status <> 0 ";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);


foreach($data as $v):
  $sql = "update appendix set manageperiod_status = 5 where app_id = '{$v['app_id']}';";
  echo $sql."\n";
  $dbh->query($sql);
endforeach;

?>
