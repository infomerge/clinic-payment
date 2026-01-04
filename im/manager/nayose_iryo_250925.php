<?php
ini_set( 'display_errors', 0 );
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
?>

<!DOCTYPE_html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>患者情報の名寄せ</title>

<?php
$smarty->display( 'common/head_inc.tpl');
?>

</head>

<body>

<?php session_start();
$smarty->display( 'common/header.tpl' );

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/*
$selected_pid = $_GET['original_pid'];
if($selected_pid){
    $_SESSION['selected_pid'] = $selected_pid;
    $original_pid = $selected_pid;
} else {
    $original_pid = $_SESSION['selected_pid'];
}
*/

$original_pid = isset($_REQUEST['original_pid']) ? $_REQUEST['original_pid'] : "";

$sql = "SELECT patient_name,patient_hihoki,patient_hihoban,patient_jukyuban,patient_kaigo_hihoban,patient_kaigo_jukyuban
            FROM patient_info
            WHERE original_pid = '$original_pid' AND disp = '0'";
    $stmt = $dbh->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $name = $result['patient_name'];
    $patient_hihoki = $result['patient_hihoki'];
    $patient_hihoban = $result['patient_hihoban'];
    $patient_jukyuban = $result['patient_jukyuban'];
    $patient_kaigo_hihoban = $result['patient_kaigo_hihoban'];
    $patient_kaigo_jukyuban	= $result['patient_kaigo_jukyuban'];
?>

<div id="wrap">
    <div class="content">

        <div id="breadcrumb">
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="./patient_info_list.php">患者情報一覧</a>&nbsp;&gt;&nbsp;患者名寄せ
        </div>

<?php if($original_pid == ""): echo "パラメータが不正です"; else: ?>

        <h2 class="title_name">名寄せ元の患者レコード</h2>

        <?php
        echo "<table class=\"list_body\">";
        echo "<tr>
                <th>患者番号</th>
                <th>患者名</th>
                <th>医療保険被保険者記号</th>
                <th>医療保険被保険者番号</th>
                <th>医療保険受給者番号</th>
                <th>介護保険被保険者番号</th>
                <th>介護保険受給者番号</th>
              </tr>";
        echo "<tr>
                <td>$original_pid</td>
                <td>$name</td>
                <td>$patient_hihoki</td>
                <td>$patient_hihoban</td>
                <td>$patient_jukyuban</td>
                <td>$patient_kaigo_hihoban</td>
                <td>$patient_kaigo_jukyuban</td>
            　</tr>";
        echo "</table>";
        ?>

        <br />

        <form method="get" action="nayose_iryo.php" enctype="multipart/form-data">
        <input type="hidden" name="original_pid" value="<?=$original_pid;?>">
            <p>介護保険被保険者番号で名寄せ対象の患者レコードを検索</p>

            <input type="text" size="20" name="patient_kaigo_hihoban" value="">
            <input type="submit" name="submit" value="検索" />
        </form>

        <br /><br />

        <?php
        $patient_kaigo_hihoban = $_GET['patient_kaigo_hihoban'];

        if($patient_kaigo_hihoban){
            /*
            $sql = "SELECT original_pid as target_pid,patient_name,patient_hihoki,patient_hihoban,patient_jukyuban,patient_kaigo_jukyuban
                    FROM patient_info
                    WHERE patient_kaigo_hihoban = '$patient_kaigo_hihoban' AND disp = '0'";
                    */
                    $sql = "SELECT original_pid as target_pid,patient_name,patient_hihoki,patient_hihoban,patient_jukyuban,patient_kaigo_jukyuban
                    FROM patient_info
                    WHERE patient_kaigo_hihoban = '$patient_kaigo_hihoban'";
                    #echo $sql;

            $stmt = $dbh->query($sql);
            #$result = $stmt->fetch(PDO::FETCH_ASSOC);
            $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
            #print_r($result);

            $target_pid = $result['target_pid'];
            $name = $result['patient_name'];
            $patient_hihoki = $result['patient_hihoki'];
            $patient_hihoban = $result['patient_hihoban'];
            $patient_jukyuban = $result['patient_jukyuban'];
            $patient_kaigo_jukyuban	= $result['patient_kaigo_jukyuban'];
#print_r($result);
            if($result){
                echo "<h2 class='title_name'>名寄せ対象の患者レコード</h2>";
                echo "<table class=\"list_body\">";
                echo "<tr>
                        <th>患者番号</th>
                        <th>患者名</th>
                        <th>医療保険被保険者記号</th>
                        <th>医療保険被保険者番号</th>
                        <th>医療保険受給者番号</th>
                        <th>介護保険被保険者番号</th>
                        <th>介護保険受給者番号</th>
                        <th></th>
                      <tr>";

                foreach($result as $v):
                    echo "<tr>
                    <td>{$v['target_pid']}</td>
                    <td>{$v['name']}</td>
                    <td>{$v['patient_hihoki']}</td>
                    <td>{$v['patient_hihoban']}</td>
                    <td>{$v['patient_jukyuban']}</td>
                    <td>{$patient_kaigo_hihoban}</td>
                    <td>{$v['patient_kaigo_jukyuban']}</td>
                    <td><a href=nayose_iryo_execute.php?original_pid={$original_pid}&target_pid={$v['target_pid']}>名寄せ実行</a></td>
                  </tr>";
                endforeach;

                
                
                echo "</table>";
            } else {
                echo "対象の患者レコードはありませんでした。";
            }
        }
        ?>



        <!--
        <?php
        /*
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

            echo "<h2 class='title_name'>対象の患者と診療月の選択</h2>";
            echo "該当する患者が $namecount 名いました<br/>\n<br/>\n";
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
        }
        */
        ?>



        session_start();
        $_SESSION['key'] = $key;
        header("Location: information.php");
        exit;
        -->
<?php endif; ?>

    </div>
</div>
</body>
</html>
