<?php
include_once "../class/config.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>介護保険月別請求データ</title>
</head>

<body>

<?php

$skm = $_POST["skm"];
$kaigo_hihoban = $_POST["kaigo_hihoban"];

//DB接続用
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//DBからデータ抽出
$sql = "SELECT * FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid WHERE skm = '$skm' AND kaigo_hihoban = '$kaigo_hihoban'";
$stmt = $dbh->query($sql);
$result = $stmt->fetchALL(PDO::FETCH_ASSOC);


echo "<h3>被保険者番号： $kaigo_hihoban 様 <br> $skm 請求分 <br> 請求情報</h3>\n";

echo "<table border = '1'><th>被保険者番号</th><th>サービスコード</th><th>サービス単位数</th><th>保険負担率</th><th>公費負担率</th><th>利用者負担金額</th></tr>\n";

$length = sizeof($result);
for($i = 0; $i < $length; $i++){

    echo "<tr>";

    $kaigo_hihoban = $result[$i]['kaigo_hihoban'];
    echo "<td>$kaigo_hihoban</td>\n";

    $service_code = $result[$i]['service_code'];
    echo "<td>$service_code</td>\n";

    $service_unit = $result[$i]['service_unit'];
    echo "<td>$service_unit</td>\n";

    $hoken_rate = $result[$i]['hoken_rate'];
    echo "<td>$hoken_rate</td>\n";

    $kouhi_rate = $result[$i]['kouhi_rate'];
    echo "<td>$kouhi_rate</td>\n";

    $copayment = $service_unit * 10 * (100 - $hoken_rate)/100 * (100 - $kouhi_rate)/100;
    echo "<td>$copayment</td>\n";

    echo "</tr>\n";

    $totalunit += $service_unit;
    $totalcopayment += $copayment;

}

echo "</table>\n";

//総点数

echo "合計サービス単位数：$totalunit<br/>\n";
echo "合計利用者負担金額(円)：$totalcopayment<br/>\n";


?>

</body>
</html>
