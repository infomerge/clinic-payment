<?php
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

$m_cat = array(1 => "自由診療","販売品","その他");
?>

<!DOCTYPE_html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>自由診療／物販／金額調整</title>

<?php
$smarty->display( 'common/head_inc.tpl');
?>

</head>

<body>

<?php
$smarty->display( 'common/header.tpl' );

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/*
if($_SERVER["REQUEST_METHOD"] == "POST"){

    #対象の患者IDを取得
    $original_pid = $_POST['original_pid'];
    $app_id = isset($_POST['app_id']) ? $_POST['app_id'] : "";
    //新規追加された内容を取得
    $app_date = $_POST['app_date'];
    $app_cat = $_POST['app_cat'];
    $app_item = $_POST['app_item'];
    $app_price = $_POST['app_price'];

    if($app_id != ""){
      $sql = "UPDATE appendix SET app_date = '{$app_date}' , app_cat = '{$app_cat}' , app_item = '{$app_item}' , app_price = '{$app_price}'
        WHERE app_id = '{$app_id}' and original_pid = '{$original_pid}' ;";
    }else{
      //appendixテーブルに登録
      $sql = "INSERT INTO appendix (original_pid,app_date,app_cat,app_item,app_price)
            VALUES ('$original_pid','$app_date','$app_cat','$app_item','$app_price')";
    }
    $dbh->query($sql);

    header("Location: ./appendix-list.php?original_pid=" . $_REQUEST['original_pid']);

} else {

$original_pid = $_GET['original_pid'];

}
*/
$original_pid = $_GET['original_pid'];
?>

<div id="wrap">
    <div class="content">

        <div id="breadcrumb">
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="patient_info_list.php">患者情報一覧</a>&nbsp;&gt;&nbsp;自由診療／物販／金額調整
        </div>

        <h2 class="title_name">対象の項目を選択</h2>

        <a href="appendix_edit.php?original_pid=<?=$original_pid?>" class="btn">新規追加</a>

        <br><br>
        <div class="mb20"></div>

        <?php
/*
        $sql = "SELECT srd, shinryo_name, copayment
                FROM re_shinryo
                WHERE original_pid = $original_pid";
        $stmt = $dbh->query($sql);
        $shinryo_list = $stmt->fetchALL(PDO::FETCH_ASSOC);
*/
        $sql = "SELECT *
                FROM appendix
                WHERE original_pid = $original_pid and disp = 0";
        $stmt = $dbh->query($sql);
        $app_list = $stmt->fetchALL(PDO::FETCH_ASSOC);

        echo "<table class=\"list_body\">
                <tr><th></th><th>日付</th>
                    <th>タイプ</th>
                    <th>項目</th>
                    <th>金額</th></tr>";
/*
        foreach ($shinryo_list as $v){
            echo "<tr><td><td>" . $v['srd'] . "</td>";
            echo "<td>診療</td>";
            echo "<td>" . $v['shinryo_name'] . "</td>";
            echo "<td>" . $v['copayment'] . "点</td></tr>";
        }
*/
        foreach ($app_list as $v){
            echo "<tr><td><a href='appendix_edit.php?original_pid=".$original_pid."&app_id=".$v['app_id']."'>編集</a></td></td><td>" . $v['app_date'] . "</td>";
            echo "<td>" . $m_cat[$v['app_cat']] . "</td>";
            echo "<td>" . $v['app_item'] . "</td>";
            echo "<td>" . $v['app_price'] . "円</td></tr>";
        }

        echo "<table>";
        ?>


    </div>
</div>
</body>
</html>
