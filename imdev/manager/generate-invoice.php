<?php
include_once "../class/config.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>月別請求データ</title>
</head>

<body>

<?php

$skm = $_POST["skm"];
$name = $_POST["name"];

//DB接続用
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//DBからデータ抽出
$sql = "SELECT * FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid WHERE skm = '$skm' AND name = '$name' ORDER BY name,srd";
$stmt = $dbh->query($sql);
$result = $stmt->fetchALL(PDO::FETCH_ASSOC);

$gcode = substr($skm,0,1);
$year = substr($skm,1,2);
$month = substr($skm,3,2);
$sql = "SELECT * FROM gengo_code WHERE code = $gcode";
$stmt = $dbh->query($sql);
$gengo = $stmt->fetchALL(PDO::FETCH_ASSOC);
$shinryo_month = $gengo[0]['gengo'] . $year ."年". $month ."月";

echo "<h3> $name 様 <br> $shinryo_month 請求分 <br> 請求情報</h3>\n";

echo "<table border = '1'><th>患者氏名</th><th>診療日</th><th>診療識別</th><th>診療行為</th><th>負担区分</th><th>点数</th><th>負担割合(%)</th><th>負担金額(円)</th></tr>\n";

$length = sizeof($result);
for($i = 0; $i < $length; $i++){

    echo "<tr>";

    $name = $result[$i]['name'];
    echo "<td>$name</td>\n";

    $srd = $result[$i]['srd'];
    $day = substr($srd,5,2);
    $shinryo_date = $shinryo_month . $day."日";
    echo "<td>$shinryo_date</td>\n";

    $shikibetsu_code = $result[$i]['shikibetsu'];
    $sql = "SELECT * FROM shikibetsu_code WHERE code = $shikibetsu_code";
    $stmt = $dbh->query($sql);
    $shikibetsu = $stmt->fetchALL(PDO::FETCH_ASSOC);
    $shinryo_shikibetsu = $shikibetsu[0]['shikibetsu'];
    echo "<td>$shinryo_shikibetsu</td>\n";

    $koui_code = $result[$i]['koui'];
    $sql = "SELECT * FROM koui_code WHERE code = $koui_code";
    $stmt = $dbh->query($sql);
    $koui = $stmt->fetchALL(PDO::FETCH_ASSOC);
    $shinryo_koui = $koui[0]['koui'];
    echo "<td>$shinryo_koui</td>\n";

    $futan_code = $result[$i]['futan'];
    $sql = "SELECT * FROM futan_code WHERE code = $futan_code";
    $stmt = $dbh->query($sql);
    $futan = $stmt->fetchALL(PDO::FETCH_ASSOC);
    $futan_kubun = $futan[0]['futan'];
    echo "<td>$futan_kubun</td>\n";

    $tensu = $result[$i]['tensu'];
    echo "<td>$tensu</td>\n";

    $ratio = $result[$i]['ratio'];
    echo "<td>$ratio</td>\n";

    $copayment = $result[$i]['copayment'];
    echo "<td>$copayment</td>\n";

    echo "</tr>\n";

}

echo "</table>\n";

//総点数
$sql = "SELECT SUM(tensu),SUM(copayment) FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid WHERE name = '$name' AND skm = '$skm'";
$stmt = $dbh->query($sql);
$result = $stmt->fetchALL(PDO::FETCH_ASSOC);

$totaltensu = $result[0]['SUM(tensu)'];
$totalcopayment = $result[0]['SUM(copayment)'];
echo "合計点数：$totaltensu<br/>\n";
echo "合計負担金額(円)：$totalcopayment<br/>\n";


?>

</body>
</html>
