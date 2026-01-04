<?php
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if($_SERVER["REQUEST_METHOD"] == "POST"){

    //対象の患者IDを取得
    $original_pid = $_POST['original_pid'];
    $app_id = isset($_POST['app_id']) ? $_POST['app_id'] : "";
    //新規追加された内容を取得
    $app_date = $_POST['app_date'];
    $app_cat = $_POST['app_cat'];
    $app_item = $_POST['app_item'];
    $app_price = $_POST['app_price'];
    $disp = $_POST['disp'];

    if($app_id != ""){
      $sql = "UPDATE appendix SET app_date = '{$app_date}' , app_cat = '{$app_cat}' , app_item = '{$app_item}' , app_price = '{$app_price}' , disp = '{$disp}'
        WHERE app_id = '{$app_id}' and original_pid = '{$original_pid}' ;";
    }else{
      //appendixテーブルに登録
      $sql = "INSERT INTO appendix (original_pid,app_date,app_cat,app_item,app_price)
            VALUES ('$original_pid','$app_date','$app_cat','$app_item','$app_price')";
    }
    $dbh->query($sql);

    header("Location: ./appendix-list.php?original_pid=" . $_REQUEST['original_pid']);
    exit;
}
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



//対象の患者IDを取得
$original_pid = $_GET['original_pid'];
$app_id = isset($_REQUEST['app_id']) ? $_REQUEST['app_id'] : "";
if($app_id != ""){
  $sql = "SELECT *
          FROM appendix
          WHERE app_id = {$app_id}";
  $stmt = $dbh->query($sql);
  $app_list = $stmt->fetch(PDO::FETCH_ASSOC);
#  echo $sql;
#  print_r($app_list);
  $app_date = $app_list['app_date'];
  $app_cat = $app_list['app_cat'];
  $app_item = $app_list['app_item'];
  $app_price = $app_list['app_price'];

}else{
  $app_date = "";
  $app_cat = "";
  $app_item = "";
  $app_price = "";
}

  ?>

<div id="wrap">
    <div class="content">

        <div id="breadcrumb">
            <a href="../">トップページ</a>&nbsp;&gt;&nbsp;自由診療／物販／金額調整
        </div>

        <h2 class="title_name"><?php if($app_id != ""){ echo "編集"; }else{ echo "新規追加";} ?></h2>

<?php /*        <form method="post" action="./appendix-list.php" enctype="multipart/form-data"> */?>
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="original_pid" value="<?=$original_pid?>" />
            <input type="hidden" name="app_id" value="<?=$app_id?>" />

            <div class="tbldetail_wrap" align="center">
                <table class="tbldetail">

                <tr>
                <th>日付</th>
                <td class="bg_odd">
                <input type="text" name="app_date" class="frmtxt" style="width:100px;" value="<?php echo $app_date; ?>" />
                </td></tr>

                <tr>
                <th>タイプ</th>
                <td class="bg_odd">
                <!--<input type="text" name="app_cat" class="frmtxt" style="width:100px;" />-->
                <input type="radio" name="app_cat" value="1"<?php if($app_cat == 1){echo " checked='checked'"; }?>> 自由診療
                <input type="radio" name="app_cat" value="2"<?php if($app_cat == 2){echo " checked='checked'"; }?>> 販売品
                <input type="radio" name="app_cat" value="3"<?php if($app_cat == 3){echo " checked='checked'"; }?>> その他
                </td></tr>

                <tr>
                <th>項目</th>
                <td class="bg_odd">
                <input type="text" name="app_item" class="frmtxt" style="width:400px;" value="<?php echo $app_item; ?>" />
                </td></tr>

                <tr>
                <th>金額</th>
                <td class="bg_odd">
                <input type="text" name="app_price" class="frmtxt" style="width:100px;" value="<?php echo $app_price; ?>" />
                </td></tr>

                <tr>
                <th>削除</th>
                <td class="bg_odd">
                <input type="checkbox" name="disp" value="1" />&nbsp;削除する
                </td></tr>

                </table>
            </div>

            <br /><br />

            <div align="center">
                <input type="submit" class="btn_submit" value="<?php if($app_id == ""): ?>追加<?php else: ?>更新<?php endif;?>" />
            </div>

        </form>

    </div>
</div>
</body>
</html>
