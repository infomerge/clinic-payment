<?php
ini_set("display_errors",1);
session_start();
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//GET original and target pid
$original_pid = $_GET['original_pid'];
$target_pid = $_GET['target_pid'];

//SELECT kaigo hoken elements
$sql = "SELECT patient_kaigo_hihoban,patient_kaigo_jukyuban
        FROM patient_info
        WHERE original_pid = '$target_pid'";
$stmt = $dbh->query($sql);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$patient_kaigo_hihoban = $result['patient_kaigo_hihoban'];
$patient_kaigo_jukyuban = $result['patient_kaigo_jukyuban'];

//UPDATE original record
$sql = "UPDATE patient_info
        SET patient_kaigo_hihoban = '$patient_kaigo_hihoban',
            patient_kaigo_jukyuban = '$patient_kaigo_jukyuban'
        WHERE original_pid = '$original_pid'";
$dbh->query($sql);

#まずはrek_serviceからpidを抽出
$sql = "SELECT *
        FROM rek_service
        WHERE original_pid = '$target_pid'";
$stmt = $dbh->query($sql);
#$result = $stmt->fetch(PDO::FETCH_ASSOC);
$result = $stmt->fetchALL(PDO::FETCH_ASSOC);

#ユニークなpidを収集
$pids = array();
foreach($result as $v):
        $pids[] = $v['pid'];
endforeach;
$pids = array_unique($pids);
#print_r($pids);exit;
#echo $sql;print_r($result);exit;
$pid = $result['pid'];

//UPDATE rek_patient
/*$sql = "UPDATE rek_patient
        SET original_pid = '$original_pid'
        WHERE original_pid = '$target_pid'";*/

#pidの数だけ処理
foreach($pids as $pid):
        $sql = "UPDATE rek_patient
                SET original_pid = '$original_pid'
                WHERE pid = '$pid'";
        $dbh->query($sql);
endforeach;

//UPDATE rek_service
$sql = "UPDATE rek_service
        SET original_pid = '$original_pid'
        WHERE original_pid = '$target_pid'";
$dbh->query($sql);

//HIDE target record
$sql = "UPDATE patient_info
        SET disp = '1'
        WHERE original_pid = '$target_pid'";
$dbh->query($sql);

header("Location: patient_info_list.php");
exit;

?>
</body>
</html>
