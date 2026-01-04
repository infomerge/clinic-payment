<?php
ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

$master_direct_debit = array(
  0=> "口座振替",
  1=> "銀振他",
);
$master_error = array(
  0 => "実行待ち",
  9 => "正常終了",
  1 => "エラー",
);
$master_error_status = array(
  "J001" => "振替結果未判定",
  "J002" => "資金不足",
  "J003" => "取引無し",
  "J004" => "預金者都合",
  "J005" => "口座振替無し",
  "J006" => "依頼者都合",
  "J007" => "その他振替失敗",

);

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$targetym = $_REQUEST['targetym'];

$sql = "select * from acc_result as a, patient_info as b where a.original_pid = b.original_pid and targetym = '{$targetym}'";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);

#print_r($data);
?>
<!DOCTYPE_html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>請求情報／領収情報の出力</title>

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
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;請求書・領収書
        </div>




        <h2 class="title_name">【実験中】締めの実施</h2>

<table class="list_body">
<tr>
  <th>氏名</th><th>金額</th><th>口振/銀振</th><th>状態</th><th>エラー名</th><th>入金処理</th>
</tr>
<?php
foreach($data as $v):
?>
<tr>
<td><?=$v['patient_name'];?></td>
<td><?=number_format($v['am'])."円";?></td>
<td><?=$master_direct_debit[$v['direct_debit']];?></td>
<td><?=$master_error[$v['rp_errorflag']];?></td>
<td><?php
if($v['rp_errorflag'] == 1):
echo $master_error_status[$v['ec']];
endif;
?></td>
<td><input type="button" value="入金OK処理実行"></td>
</tr>
<?php
endforeach;
?>
</table>

      </div>
  </div>
  </body>
  </html>
