<?php
session_start();
ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once '../class/common.php';
require_once('../class/db_extension.php');
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#変数受け取り
$original_pid = $_GET['original_pid'];

//SELECT current data
$sql = "SELECT shipto_name,postal_code,postal_code2,prefecture,address1,address2,shipto_name_sub,postal_code_sub,postal_code_sub2,prefecture_sub,address1_sub,address2_sub
        FROM patient_info
        WHERE original_pid = '$original_pid'";
$stmt = $dbh->query($sql);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

print_r($result);

$new_shipto_name = $result['shipto_name_sub'];
$new_postal_code = $result['postal_code_sub'];
$new_postal_code2 = $result['postal_code_sub2'];
$new_prefecture = $result['prefecture_sub'];
$new_address1 = $result['address1_sub'];
$new_address2 = $result['address2_sub'];
$new_shipto_name_sub = $result['shipto_name'];
$new_postal_code_sub = $result['postal_code'];
$new_postal_code_sub2 = $result['postal_code2'];
$new_prefecture_sub = $result['prefecture'];
$new_address1_sub = $result['address1'];
$new_address2_sub = $result['address2'];


#処理
//UPDATE main records
$sql = "UPDATE patient_info
        SET shipto_name = '$new_shipto_name',
            postal_code = '$new_postal_code',
            postal_code2 = '$new_postal_code2',
            prefecture = '$new_prefecture',
            address1 = '$new_address1',
            address2 = '$new_address2'
        WHERE original_pid = '$original_pid'";
$dbh->query($sql);

//UPDATE sub records
$sql = "UPDATE patient_info
        SET shipto_name_sub = '$new_shipto_name_sub',
            postal_code_sub = '$new_postal_code_sub',
            postal_code_sub2 = '$new_postal_code_sub2',
            prefecture_sub = '$new_prefecture_sub',
            address1_sub = '$new_address1_sub',
            address2_sub = '$new_address2_sub'
        WHERE original_pid = '$original_pid'";
$dbh->query($sql);


#ページ遷移
header("Location: patient_info.php?original_pid=$original_pid");
exit;


?>
