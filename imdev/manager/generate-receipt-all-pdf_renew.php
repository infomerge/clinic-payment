<?php
ini_set( 'display_errors', 1 );
ini_set("memory_limit", "5120M");
set_time_limit(0);

include_once "../class/clsystem.php";


$cl = new CLSYSTEM;
$cl->targetym = isset($_GET['targetym']) ? $_GET['targetym'] : "";
$cl->manageperiod_flag = 0;
#$cl->manageperiod_debug_flag = true;
$cl->srd_start = $_GET["srd_start"];
$cl->srd_end = $_GET["srd_end"];
$cl->format = $_GET["format"];

#$srm = mb_substr($srd_start,0,6);

#211204追加
$cl->ryosyu_date = isset($_GET["ryosyu_date"]) ? $_GET["ryosyu_date"] : "" ;
#$target_original_pid = isset($_GET["target_original_pid"]) ? $_GET["target_original_pid"] : "" ;
if(isset($_GET["target_original_pid"])){
  $cl->original_pid = $_GET["target_original_pid"];
}

$cl->generatePDF();

exit;
