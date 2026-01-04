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

$targetym = $cl->getTargetymFromManageperiod();


#$cl->targetym = '202201';
$cl->targetym = $targetym;
#$cl->manageperiod_debug_flag = true;
$cl->manageperiod_debug_flag = false;

$cl->format = "seikyu";
$data = $cl->generateRPdata();

#220420 新シメ処理
$cl->pickupTargetymRecords();
#$data = $cl->getPaymentData();

#print_r($data);

/*
foreach($data as $original_pid => $patient_data):
    echo $original_pid."---".$patient_data['data']['name']."---".$patient_data['total_copayment']."---".$patient_data['data']['direct_debit']."<br>\n";
endforeach;
*/



?>