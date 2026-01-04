<?php
include_once "../class/clsystem.php";

$cl = new CLSYSTEM();
if (isset($GLOBALS['argv'][1])) {
    $cl->manageperiod_flag = $GLOBALS['argv'][1];
}else{
    $cl->manageperiod_flag = $_GET["manageperiod_flag"];
}
if (isset($GLOBALS['argv'][2])) {
    $cl->srd_start = $GLOBALS['argv'][2];
}else{
    $cl->srd_start = isset($_GET["srd_start"]) ? $_GET["srd_start"] : "";
}
if (isset($GLOBALS['argv'][3])) {
    $cl->srd_end = $GLOBALS['argv'][3];
}else{
    $cl->srd_end = isset($_GET["srd_end"]) ? $_GET["srd_end"] : "";
}



/*
$cl->format = "seikyu";
$data = $cl->getPaymentData();

$data2 = array();
$cnt = 0;
foreach($data as $original_pid => $patient_data){
    $cnt++;
    $data2[$original_pid] = $patient_data;
    if($cnt > 2) break;
}

$cl->generateRPdataDEBUG($data2);
*/

$cl->dummy();

?>