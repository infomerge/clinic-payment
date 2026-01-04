<?php
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>領収データ</title>
    
    <?php
    $smarty->display( 'common/head_inc.tpl');
    ?>
</head>
    
<body>

<?php
$smarty->display( 'common/header.tpl' );
?>

<div id="wrap">
    <div class="content">

        <div id="breadcrumb">
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="./generate.php">請求書・領収書</a>&nbsp;&gt;&nbsp;出力結果
        </div>
        
        <h2 class="title_name">請求・領収データ</h2>
        
<?php
    
//対象の期間と患者名を取得
$srd_start = $_GET["srd_start"];
$srd_end = $_GET["srd_end"];
$name = $_GET["name"];

//DB接続
$dbh = new PDO('mysql:dbname=ns_crossline;host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//DBから対象患者のデータを抽出
$sql = "SELECT * 
        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid 
        WHERE name = '$name'";
$stmt = $dbh->query($sql);
$result = $stmt->fetchALL(PDO::FETCH_ASSOC);
    

//患者ID&氏名&負担区分&患者種別&負担割合を表示
$pid = $result[0]['pid'];
echo "患者番号：$pid<br/>\n";
    
echo "患者氏名：$name<br/>\n";
    
$futan_code = $result[0]['futan'];
$sql = "SELECT * FROM futan_code WHERE code = $futan_code";
$stmt = $dbh->query($sql);
$futan = $stmt->fetch(PDO::FETCH_ASSOC);
$futan_kubun = $futan['futan'];
echo "負担区分：$futan_kubun<br/>\n";

$syubetsu_code = $result[0]['syubetsu'];
$sql = "SELECT * FROM syubetsu_code WHERE code = $syubetsu_code";
$stmt = $dbh->query($sql);
$syubetsu = $stmt->fetch(PDO::FETCH_ASSOC);
$patient_syubetsu = $syubetsu['syubetsu'];
echo "患者種別：$patient_syubetsu<br/>\n";    
    
$ratio = $result[0]['ratio'];
echo "負担割合：$ratio"."%<br/><br/>\n\n";
    


//DBから対象の診療日一覧を抽出
$sql = "SELECT DISTINCT srd 
        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid 
        WHERE name = '$name' AND srd >= '$srd_start' AND srd <= '$srd_end'
        ORDER BY srd";
$stmt = $dbh->query($sql);
$srdlist = $stmt->fetchALL(PDO::FETCH_ASSOC);
$srdcount = sizeof($srdlist);


//診療日毎に診療データを抽出／表示
for($i = 0; $i < $srdcount; $i++){
    
    //診療日と患者負担額を表示
    $srd = $srdlist[$i]['srd'];
    $year = substr($srd,0,4);
    $month = substr($srd,4,2);
    $day = substr($srd,6,2);
    echo "診療日：$year"."年$month"."月$day"."日";
    echo "<br/>\n";
    
    $sql = "SELECT sum(copayment) 
            FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid 
            WHERE name = '$name' AND srd = '$srd'";
    $stmt = $dbh->query($sql);
    $dailycp = $stmt->fetch(PDO::FETCH_ASSOC);
    //日毎に四捨五入
    $dailycopayment = round($dailycp['sum(copayment)'],-1);
    echo "患者負担額：$dailycopayment"."円<br/>\n";
    echo "<br/>\n";
    
    //DBから対象日の診療データ一覧抽出
    $sql = "SELECT * 
            FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid 
            WHERE name = '$name' AND srd = '$srd'";
    $stmt = $dbh->query($sql);
    $shinryolist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $shinryocount = sizeof($shinryolist);

    //診療行為カテゴリごとの点数を表示
    $array_category = array(array('kigo'=>'A', 'title'=>'初・再診料', 'tensu'=>0),
                            array('kigo'=>'B', 'title'=>'医学管理等', 'tensu'=>0),
                            array('kigo'=>'C', 'title'=>'在宅医療', 'tensu'=>0),
                            array('kigo'=>'D', 'title'=>'検査', 'tensu'=>0),
                            array('kigo'=>'E', 'title'=>'画像診断', 'tensu'=>0),
                            array('kigo'=>'F', 'title'=>'投薬', 'tensu'=>0),
                            array('kigo'=>'G', 'title'=>'注射', 'tensu'=>0),
                            array('kigo'=>'H', 'title'=>'リハビリテーション', 'tensu'=>0),
                            array('kigo'=>'I', 'title'=>'処置', 'tensu'=>0),
                            array('kigo'=>'J', 'title'=>'手術', 'tensu'=>0),
                            array('kigo'=>'K', 'title'=>'麻酔', 'tensu'=>0),
                            array('kigo'=>'L', 'title'=>'放射線治療', 'tensu'=>0),
                            array('kigo'=>'M', 'title'=>'歯冠修復及び欠損補綴', 'tensu'=>0),
                            array('kigo'=>'N', 'title'=>'歯科矯正', 'tensu'=>0),
                            array('kigo'=>'O', 'title'=>'病院診断', 'tensu'=>0),
                            array('kigo'=>'-', 'title'=>'その他', 'tensu'=>0)
                      );
    $category_count = sizeof($array_category);
    for($j = 0; $j < $category_count; $j++){
        $category = $array_category[$j]['kigo'];

        $sql = "SELECT sum(tensu) as tensu
                FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid 
                WHERE name = '$name' AND srd = '$srd' AND category = '$category'";
        $stmt = $dbh->query($sql);
        $tensubycat = $stmt->fetch(PDO::FETCH_ASSOC);
        $array_category[$j]['tensu'] = $tensubycat['tensu'];

        $kigo = $array_category[$j]['kigo'];
        $title = $array_category[$j]['title'];
        $tensu = $array_category[$j]['tensu'];
        if($tensu==""){$tensu = 0;}

        echo "$kigo: ";
        echo "$title ⇛ ";
        echo "$tensu 点<br/>\n";
    }

    echo "<br/>\n";
    echo "<hr>";
    echo "<br/>\n";

    //診療日毎の患者負担額を$totalcopaymentに加算
    $totalcopayment += $dailycopayment;
}

//対象機関の患者負担額を表示

$year_start = substr($srd_start,0,4);
$month_start = substr($srd_start,4,2);
$day_start = substr($srd_start,6,2);   
$year_end = substr($srd_end,0,4);
$month_end = substr($srd_end,4,2);
$day_end = substr($srd_end,6,2);

echo "対象期間：$year_start"."年$month_start"."月$day_start"."日〜$year_end"."年$month_end"."月$day_end"."日<br/>\n";
echo "患者負担金額合計：$totalcopayment"."円<br/>\n";
    
?>

    </div>
</div>  
</body>
</html>