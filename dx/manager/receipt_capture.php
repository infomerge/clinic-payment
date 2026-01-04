<?php
session_start();
ini_set( 'display_errors',1);
include_once "../common/smarty_settings.php";
include_once '../class/common.php';
require_once('../class/db_extension.php');

$today = date('Y-m-d h:i:s');
// セッションチェック
$common = new COMMON;

$common->id = $_SESSION['id'];
$common->password = $_SESSION['password'];
$result = $common->checkid();

$row = $result->fetchRow();
$login_name = $row[1];
$authority_id = $row[2];
$account_id = $row[3];


if($row[0] == 0) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: /index.php?error=error");
}


$allstr = "";
$file = $_FILES["upfile"]["tmp_name"];
if (($handle = fopen ( $file, "r" )) !== FALSE) {
    while ( ( $data = fgetcsv ($handle, 1000, ",",'"' )) !== FALSE ){
        for ($i = 0; $i < count ($data); $i++) {
            $str = "{$data[$i]},";
            $str = mb_convert_encoding($str, "UTF-8", "SJIS");
            $allstr = $allstr . $str;
        }
    }
    //echo $allstr;
    fclose ( $handle );
}


//$array = explode("IR,", $allstr);
//    for ($i = 1; $i < count($array); $i++) {
//        $array[$i] = explode(",,,,,,,,,", $array[$i]);
//    }



$i = 0 ;
//allstrで最初のIRがある位置を探し、それ以降の文字列をtmpstrに格納
$tmpstr = mb_strstr($allstr,"IR");
//tmpstrで最初のIRがある位置を探し、1番目と2番目のIRの間の文字列を配列arrayの$iに格納
$ir_next = mb_strpos($tmpstr, "IR");
$array[$i] = "IR," . mb_substr($tmpstr,0,$ir_next,UTF-8);

//echo $allstr;
//echo $ir_first;
echo $array[$i];
//echo $ir_next;
//echo $array[$i];


//echo "<pre>";
//print_r($array);
//echo "</pre>";


//$ir =
//$re = explode("IR,",$array);




//    for ($i = 1; $i < count($array); $i++) {
//        $patient = explode(",", $array[$i]);
//    }
//print_r($patient);
                






$smarty->assign( 'data',$result);
$smarty->assign( 'navi_type',14);
$smarty->assign( 'from',$from);
$smarty->assign( 'to',$to);

$smarty->assign( 'category','aggregate');

?>