<?php
include_once "../class/config.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>診療レコード取込結果</title>
</head>

<body>

<?php session_start();

/* Check SESSION
$test = 158;
if(isset($_SESSION["shinryo_".$test])){
    echo "SESSION WORKS<br>\n";
    foreach($_SESSION["shinryo_".$test] as $key=>$value){
        echo $value." / ";
    }
} else {
    echo "DOESN'T WORK<br>\n";
}
echo "<br>\n";
*/

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Table header
echo "<table border = '1'><th>診療レコード番号</th><th>医療機関コード</th><th>患者番号</th><th>レセプト内患者番号</th><th>患者氏名</th><th>診療日</th><th>識別番号</th><th>診療行為コード</th><th>点数</th><th>負担コード</th><th>種別コード</th><th>負担比率</th><th>負担金額</th><th>上限</th></tr>\n";

//Display selected records
if(isset($_POST['submit'])){
    if(!empty($_POST['check_list'])) {
        foreach($_POST['check_list'] as $recordarray=>$record) {
                //echo $record." / ";
                $original_irkkcode = $_SESSION["shinryo_".$record][1];
                $original_pid = $_SESSION["shinryo_".$record][2];
                $pid = $_SESSION["shinryo_".$record][3];
                $name = $_SESSION["shinryo_".$record][4];
                $srd = $_SESSION["shinryo_".$record][5];
                $sbt = $_SESSION["shinryo_".$record][6];
                $srk = $_SESSION["shinryo_".$record][7];
                $tns = $_SESSION["shinryo_".$record][8];
                $ftn = $_SESSION["shinryo_".$record][9];
                $syubetsu = $_SESSION["shinryo_".$record][10];
                $ratio = $_SESSION["shinryo_".$record][11];
                $copayment = $_SESSION["shinryo_".$record][12];
                $upper = $_SESSION["shinryo_".$record][13];

                if ($pid != 0) {
                    $sql = "INSERT INTO re_shinryo (original_irkkcode,original_pid,pid,srd,shikibetsu,koui,tensu,futan,syubetsu,ratio,copayment,upper) SELECT '$original_irkkcode','$original_pid','$pid','$srd','$sbt','$srk','$tns','$ftn','$syubetsu','$ratio','$copayment','$upper'  FROM dual WHERE NOT EXISTS (SELECT pid FROM re_shinryo WHERE original_irkkcode = '$original_irkkcode' AND original_pid = '$original_pid' AND pid = '$pid' AND srd = '$srd' AND shikibetsu = '$sbt' AND koui = '$srk' AND tensu = '$tns' AND futan = '$ftn' AND syubetsu = '$syubetsu' AND ratio = '$ratio' AND copayment = '$copayment' AND upper = '$upper')";
                    $dbh->query($sql);
                }

                echo "<tr>";
                foreach($_SESSION["shinryo_".$record] as $contentarray=>$content){
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
