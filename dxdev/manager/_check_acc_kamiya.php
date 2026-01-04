<?php
ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select * from acc_detail where id = 1061";
#$sql = "SELECT a.*, b.* FROM acc_result as a , acc_detail as b , patient_info as c WHERE a.rid = b.rid and a.original_pid = c.original_pid AND a.targetym = '202309' AND a.original_pid = 392 AND c.invoice_output = 0";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);

$data = unserialize($data[0]['contents']);
print_r($data);