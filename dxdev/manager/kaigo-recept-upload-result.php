<?php
include_once "../class/config.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>介護保険サービスレコード取込結果</title>
</head>

<body>

<?php session_start();

/* Check SESSION
$test = 5;
if(isset($_SESSION["service_".$test])){
    echo "SESSION WORKS<br>\n";
    foreach($_SESSION["service_".$test] as $key=>$value){
        echo $value." / ";
    }
} else {
    echo "DOESN'T WORK<br>\n";
}
echo "<br>\n";
*/

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Table header
echo "<table border = '1'><th>サービスレコード番号</th><th>患者番号</th><th>被保険者番号</th><th>患者生年月日</th><th>サービスコード</th><th>サービス単位数</th><th>摘要</th></tr>\n";

// Display selected records
if(isset($_POST['submit'])){
    if(!empty($_POST['check_list'])) {
        foreach($_POST['check_list'] as $recordarray=>$record) {
                //echo $record." / ";

                $pid = $_SESSION["service_".$record][1];
                $kaigo_hihoban = $_SESSION["service_".$record][2];
                $birth = $_SESSION["service_".$record][3];
                $service_code = $_SESSION["service_".$record][4];
                $service_unit = $_SESSION["service_".$record][5];
                $tekiyo = $_SESSION["service_".$record][6];

                if ($pid!=0) {
                    $sql = "INSERT INTO rek_service (pid,service_code,service_unit,tekiyo) SELECT '$pid','$service_code','$service_unit','$tekiyo'  FROM dual WHERE NOT EXISTS (SELECT pid FROM rek_service WHERE pid = '$pid' AND service_code = '$service_code' AND service_unit = '$service_unit' AND tekiyo = '$tekiyo')";
                    $dbh->query($sql);
                }


                echo "<tr>";
                foreach($_SESSION["service_".$record] as $contentarray=>$content){
                    echo "<td>".$content."</td>\n";
                }
                echo "</tr>\n";
        }
        echo "<br>\n";
    }
}

?>

</body>
</html>
