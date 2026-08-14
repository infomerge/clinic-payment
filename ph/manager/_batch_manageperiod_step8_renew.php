<?php
#ロボペイ返却データがエラーだった医療保険／介護保険／自由診療の「manageperiod_status」を5に変更

ini_set( 'display_errors', 1 );

include_once "../class/config.php";
include_once "../class/clsystem.php";

$cl = new CLSYSTEM();
if (isset($GLOBALS['argv'][1])) {
  $cl->manageperiod_flag = $GLOBALS['argv'][1];
}
/*else{
  $cl->manageperiod_flag = $_GET["manageperiod_flag"];
}*/
if (isset($GLOBALS['argv'][2])) {
  $cl->targetym = $GLOBALS['argv'][2];
}
/*else{
  $cl->targetym = isset($_GET["targetym"]) ? $_GET["targetym"] : "";
}*/

$cl->carryForward2();


?>