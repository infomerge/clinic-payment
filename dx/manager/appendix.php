<?php
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
require_once('../class/db_extension.php');
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
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$dbname = "ns_crossline";
$table = "patient_info";
$columns = "original_pid,patient_name";
$postfix = " where disp = 0 ";

if(isset($_REQUEST['original_pid']) && $_REQUEST['original_pid'] != ""){
    $postfix .= " and original_pid = '".$_REQUEST['original_pid']."' ";
    $original_pid = $_REQUEST['original_pid'];
}
if(isset($_REQUEST['patient_name']) && $_REQUEST['patient_name'] != ""){
    $postfix .= " and patient_name like '%".$_REQUEST['patient_name']."%' ";
    $patient_name = $_REQUEST['patient_name'];
}

$postfix .= "order by patient_info.original_pid asc";
$patient_list = DbEx::select($dbname, $table, $columns, $postfix);

?>

<div id="wrap">
    <div class="content">

        <div id="breadcrumb">
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;自由診療／物販／金額調整
        </div>

        <h2 class="title_name">対象の患者を選択</h2>

        <div style="margin-bottom:30px;">
        <form action="./appendix.php" method="get">

        <table>
            <tr>
                <td>患者番号：</td>
                <td><input type="text" name="original_pid" value="<?=$original_pid?>"></td>
            </tr>
            <tr>
                <td>患者氏名：</td>
                <td><input type="text" name="patient_name" value="<?=$patient_name?>"></td>
            </tr>
        </table>

        <br>
        <input type="submit" value="検索">
        </div>

        </form>

        <?php

        echo "<table class=\"list_body\"><tr><th>患者番号</th><th>患者氏名</th></tr>";

        foreach ($patient_list as $v){
            echo "<tr><td><a href=appendix-list.php?original_pid={$v['original_pid']}>" . $v['original_pid'] . "</a></td>";
            echo "<td><a href=appendix-list.php?original_pid={$v['original_pid']}>" . $v['patient_name'] . "</a></td></tr>";
        }

        echo "<table>";
        ?>

    </div>
</div>
</body>
</html>
