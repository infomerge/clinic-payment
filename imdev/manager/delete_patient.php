<?php
ini_set("display_errors",1);
include_once "../class/config.php";
#DB接続
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$original_pid = isset($_REQUEST['original_pid']) ? htmlspecialchars($_REQUEST['original_pid']) : "";

if(isset($_REQUEST['original_pid']) && $_REQUEST['original_pid'] != ""){
#医療保険マスター
$sql = "update patient_info set disp = 1 where original_pid = ".$original_pid.";";
$stmt = $dbh->query($sql);
$dbh->query($sql);

}
$flag = false;
$parse = "";
$patient_name = "";
$patient_hihoban = "";
$patient_kaigo_hihoban = "";
$page = "";
if(isset($_REQUEST['patient_name'])){
  $patient_name = $_REQUEST['patient_name'];
  $flag = true;
}
if(isset($_REQUEST['patient_hihoban'])){
  $patient_name = $_REQUEST['patient_hihoban'];
  $flag = true;
}
if(isset($_REQUEST['patient_kaigo_hihoban'])){
  $patient_name = $_REQUEST['patient_kaigo_hihoban'];
  $flag = true;
}
if(isset($_REQUEST['page'])){
  $page = $_REQUEST['page'];
  $flag = true;
}
if($flag){
  $parse = "?patient_name={$patient_name}&patient_hihoban={$patient_hihoban}&patient_kaigo_hihoban={$patient_kaigo_hihoban}&page={$page}";
}

header("Location: patient_info_list.php{$parse}");
?>
