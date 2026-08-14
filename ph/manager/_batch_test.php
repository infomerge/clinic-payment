<?php
ini_set( 'display_errors', 1 );
include_once "../class/clsystem.php";

$cl = new CLSYSTEM();
/*
if (isset($GLOBALS['argv'][1])) {
  $srd_start = $GLOBALS['argv'][1];
}else{
  $srd_start = $_GET["srd_start"];
}
if (isset($GLOBALS['argv'][2])) {
  $srd_end = $GLOBALS['argv'][2];
}else{
  $srd_end = $_GET["srd_end"];
}
if (isset($GLOBALS['argv'][3])) {
  $format = $GLOBALS['argv'][3];
}else{
  $format = $_GET["format"];
}
*/
#echo "テスト".$srd_start."---".$srd_end."---"$format;



#print_r($data);
$cl->irregularAdjust();

?>
