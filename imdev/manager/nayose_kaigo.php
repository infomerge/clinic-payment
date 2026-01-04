<?php
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

$selected_pid = $_GET['original_pid'];
if($selected_pid){
    $_SESSION['selected_pid'] = $selected_pid;
    $original_pid = $selected_pid;
} else {
    $original_pid = $_SESSION['selected_pid'];
}

?>

<div id="wrap">
    <div class="content">

        <div id="breadcrumb">
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="./patient_info_list.php">患者情報一覧</a>&nbsp;&gt;&nbsp;患者名寄せ
        </div>

        <?php

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

        <form method="get" action="nayose_kaigo.php" enctype="multipart/form-data">
            <p>医療/社会保険被保険者番号で名寄せ対象の患者レコードを検索</p>
            <input type="text" size="20" name="patient_hihoban" value="">
            <input type="submit" name="submit" value="検索" />
        </form>

        <br /><br />

        <?php
        $patient_hihoban = $_GET['patient_hihoban'];

        if($patient_hihoban){
            $sql = "SELECT original_pid as target_pid,patient_name,patient_hihoki,patient_hihoban,patient_jukyuban,patient_kaigo_jukyuban
                    FROM patient_info
                    WHERE patient_hihoban = '$patient_hihoban' AND disp = '0'";
            $stmt = $dbh->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $target_pid = $result['target_pid'];
            $name = $result['patient_name'];
            $patient_hihoki = $result['patient_hihoki'];
            $patient_jukyuban = $result['patient_jukyuban'];
            $patient_kaigo_hihoban = $result['patient_kaigo_hihoban'];
            $patient_kaigo_jukyuban	= $result['patient_kaigo_jukyuban'];

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
                echo "<tr>
                        <td>$target_pid</td>
                        <td>$name</td>
                        <td>$patient_hihoki</td>
                        <td>$patient_hihoban</td>
                        <td>$patient_jukyuban</td>
                        <td>$patient_kaigo_hihoban</td>
                        <td>$patient_kaigo_jukyuban</td>
                        <td><a href=nayose_kaigo_execute.php?original_pid={$original_pid}&target_pid={$target_pid}>名寄せ実行</a></td>
                      </tr>";
                echo "</table>";
            } else {
                echo "対象の患者レコードはありませんでした。";
            }
        }
        ?>

    </div>
</div>
</body>
</html>
