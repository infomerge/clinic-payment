<?php
ini_set("display_errors",0);
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
?>

<!DOCTYPE_html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>回収票の出力</title>

<?php
$smarty->display( 'common/head_inc.tpl');
?>

</head>

<body>

<?php
$smarty->display( 'common/header.tpl' );

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "select distinct targetym from acc_result;";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);
?>

<div id="wrap">
    <div class="content">

        <div id="breadcrumb">
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;回収票
        </div>

        <h2 class="title_name">対象の診療期間で検索</h2>

        <form method="get" action="kaisyu-pdf.php" enctype="multipart/form-data">
            <p>対象診療期間</p>
            <input type="text" size="8" name="srd_start" value="20180101">
             〜
            <input type="text" size="8" name="srd_end" value="20181231">
            <br /><br />

            <!--
            <p>対象患者名</p>
            <input type="text" size="20" name="name" value="">
            <br /><br />
            -->
            <input type="submit" name="submit" value="OK" />
        </form>

        <br /><br />
締め年月<br>
<?php foreach($data as $v){
?>
<div><a href="kaisyu-pdf.php?targetym=<?php echo $v['targetym'];?>" target="_blank"><?php echo $v['targetym'];?></a></div>
<?php
}
?>

        <?php
        $srd_start = $_GET['srd_start'];
        if(!$srd_start){$srd_start = "00000000";}
        $srm_start = substr($srd_start,0,6);

        $srd_end = $_GET['srd_end'];
        if(!$srd_end){$srd_end = "99999999";}
        $srm_end = substr($srd_end,0,6);

        $name = $_GET["name"];
        if(!$name){$name = "NULL";}
        $submit = $_GET["submit"];

        if($submit){
            $sql = "SELECT DISTINCT name
                    FROM re_patient INNER JOIN re_shinryo ON re_patient.pid = re_shinryo.pid
                    WHERE srd >= '$srd_start' AND srd <= '$srd_end' OR name LIKE '%{$name}%'";
            $stmt = $dbh->query($sql);
            $namelist = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $namecount = sizeof($namelist);

            #echo "<h2 class='title_name'>対象の患者と診療月の選択</h2>";
            #echo "該当する患者が $namecount 名いました<br/>\n<br/>\n";

            #echo "<form action='generate-receipt-all-pdf.php' method='post'>";
            #echo "<input type='submit' value='全て出力する'>";
            #echo "</form>";

            echo "<a href=generate-receipt-all-pdf.php?srd_start={$srd_start}&srd_end={$srd_end}&format=seikyu target=\"_blank\">請求書出力</a>";
            echo "<br><br>";
            echo "<a href=generate-receipt-all-pdf.php?srd_start={$srd_start}&srd_end={$srd_end}&format=ryosyu target=\"_blank\">領収書出力</a>";


            /*
            echo "<table class=\"list_body\">";

            for ($i = 0; $i < $namecount; $i++) {
                $name = $namelist[$i]['name'];

                $sql = "SELECT DISTINCT SUBSTRING(srd,1,6)
                        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                        WHERE name = '$name' AND srd >= '$srd_start' AND srd <= '$srd_end'
                        ORDER BY srd";
                $stmt = $dbh->query($sql);
                $srmlist = $stmt->fetchALL(PDO::FETCH_ASSOC);
                $srmcount = sizeof($srmlist);

                for ($j = 0; $j < $srmcount; $j++) {
                    $srm = $srmlist[$j]['SUBSTRING(srd,1,6)'];
                    $year = substr($srm,0,4);
                    $month = substr($srm,4,2);

                    echo "<tr><td>$name</td>";
                    echo "<td>$year"."年$month"."月 診療分</td>";

                    if ($srm_start != $srm_end) {
                        if ($srm == $srm_start) {
                            $adj_srd_start = $srd_start;
                            $adj_srd_end = $srm."31";
                        } elseif ($srm == $srm_end) {
                            $adj_srd_start = $srm."01";
                            $adj_srd_end = $srd_end;
                        } else {
                            $adj_srd_start = $srm."01";
                            $adj_srd_end = $srm."31";
                        }
                    } else {
                        $adj_srd_start = $srd_start;
                        $adj_srd_end = $srd_end;
                    }
                    echo "<td><a href=generate-receipt.php?srd_start={$adj_srd_start}&srd_end={$adj_srd_end}&name={$name}>請求書出力</a></td>";
                    echo "<td><a href=generate-receipt.php?srd_start={$adj_srd_start}&srd_end={$adj_srd_end}&name={$name}>領収書出力</a></td></tr>";
                }
            }
            echo "</table>";
            */
        }
        ?>


    </div>
</div>
</body>
</html>
