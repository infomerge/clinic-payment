<?php
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

#$targetym = $cl->getTargetymFromManageperiod();


$cl->manageperiod_debug_flag = true;
#$cl->manageperiod_debug_flag = false;

#$cl->format = "seikyu";
$data = $cl->generateAccDetail();
#$data = $cl->getPaymentData();

#print_r($data);

/*
foreach($data as $original_pid => $patient_data):
    echo $original_pid."---".$patient_data['data']['name']."---".$patient_data['total_copayment']."---".$patient_data['data']['direct_debit']."<br>\n";
endforeach;
*/



?>