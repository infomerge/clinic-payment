<?php session_start();
ini_set("display_errors",1);
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//GET original and target pid
$original_pid = $_GET['original_pid'];
$target_pid = $_GET['target_pid'];

//SELECT　iryo hoken elements
$sql = "SELECT patient_name,patient_hihoki,patient_hihoban,patient_jukyuban
        FROM patient_info
        WHERE original_pid = '$target_pid'";
$stmt = $dbh->query($sql);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$patient_name = $result['patient_name'];
$patient_hihoki = $result['patient_hihoki'];
$patient_hihoban = $result['patient_hihoban'];
$patient_jukyuban = $result['patient_jukyuban'];

//UPDATE patient_info original record
$sql = "UPDATE patient_info
        SET patient_name = '$patient_name',
            patient_hihoki = '$patient_hihoki',
            patient_hihoban = '$patient_hihoban',
            patient_jukyuban = '$patient_jukyuban'
        WHERE original_pid = '$original_pid'";
$dbh->query($sql);

//HIDE target record
$sql = "UPDATE patient_info
        SET disp = '1'
        WHERE original_pid = '$target_pid'";
$dbh->query($sql);


//UPDATE rek_patient
$sql = "UPDATE rek_patient
        SET original_pid = '$original_pid'
        WHERE original_pid = '$target_pid'";
$dbh->query($sql);


//UPDATE re_shinryo ではなく、rek_service
/*
$sql = "UPDATE rek_shinryo
        SET original_pid = '$original_pid'
        WHERE original_pid = '$target_pid'";
*/
$sql = "UPDATE rek_service
        SET original_pid = '$original_pid'
        WHERE original_pid = '$target_pid'";
$dbh->query($sql);

header("Location: patient_info_list.php");
exit;

?>
</body>
</html>
