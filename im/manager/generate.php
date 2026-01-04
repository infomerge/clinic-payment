<?php
ini_set( 'display_errors', 0 );
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#PDF出力テーブル抽出
/*
$sql = "select * from managepdf order by id desc";
$stmt = $dbh->query($sql);
$pdfdata = $stmt->fetchALL(PDO::FETCH_ASSOC);
*/
$dir = "../downloadpdf/";


if($_SERVER["REQUEST_METHOD"] == "POST"){
  if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == "setpdf"){
    $sql = "insert into managepdf values(NULL,0,0,0,'".$_REQUEST['outputtarget']."',CURRENT_TIMESTAMP);";
    #echo $sql;exit;
    $dbh->query($sql);
  }elseif(isset($_REQUEST['mode']) && $_REQUEST['mode'] == "setperiod"){

    #$tejimai_date = "2020-10-26 22:03:00";
    $tejimai_date = date("Y-m-d H:i:s");
    $year = date("Y",strtotime($tejimai_date));
    $month = date("m",strtotime($tejimai_date));

    $sql = "select * from rp_schedule where YEAR(deadline_datetime) = '{$year}' and MONTH(deadline_datetime) = '{$month}' order by deadline_datetime asc limit 1";
    $stmt = $dbh->query($sql);
    $tmp = $stmt->fetch(PDO::FETCH_ASSOC);

    if(strtotime($tmp['deadline_datetime']) > strtotime($tejimai_date)){
      $targetym = date("Ym",strtotime($tejimai_date));
    }else{
      $targetym = date("Ym",strtotime($tejimai_date."+1month"));
    }

    #220420 すでに手仕舞い開始してないかチェック
    $sql = "SELECT * FROM manageperiod WHERE targetym = {$targetym}";
    $stmt = $dbh->query($sql);
    if($stmt->rowCount() == 0):
      #$sql = "insert into manageperiod values(NULL,'".$_REQUEST['targetym']."',0,CURRENT_TIMESTAMP);";
      $sql = "insert into manageperiod values(NULL,'".$targetym."',0,CURRENT_TIMESTAMP);";
    endif;
    $dbh->query($sql);
  }
  header("Location: ./generate.php");
}
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

        <h2 class="title_name">対象の診療期間で検索</h2>

        <form name="form1" method="get" action="generate.php" enctype="multipart/form-data">
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


        <h2 class="title_name">【実験中】締めの実施</h2>

        <form name="form1" method="post" action="generate.php">
          <input type="hidden" name="mode" value="setperiod">
          <!--締める年月を入力（YYYYMM形式）：<input type="text" name="targetym" value="" placeholder="例：202007"><br>-->
            <input type="submit" name="submit" value="締めを実施" />
        </form>
        <br><br>
        締めの処理待ち<br>
<?php
$sql = "select * from manageperiod where status = 0 ";
$stmt = $dbh->query($sql);
$atdata = $stmt->fetchALL(PDO::FETCH_ASSOC);
#print_r($atdata);
 ?>
 <table class="pdftable">
   <tr>
     <td>対象年月</td>
   </tr>
<?php
foreach($atdata as $v):
?>
<tr>
<td><?php echo $v['targetym']; ?></td>
</tr>
<?php endforeach; ?>
</table>

        <br /><br />

        <?php
        $srd_start = isset($_GET['srd_start']) ? $_GET['srd_start'] : "";
        if(!$srd_start){$srd_start = "00000000";}
        $srm_start = substr($srd_start,0,6);

        $srd_end = isset($_GET['srd_end']) ? $_GET['srd_end'] : "";
        if(!$srd_end){$srd_end = "99999999";}
        $srm_end = substr($srd_end,0,6);

        $name = isset($_GET["name"]) ? $_GET["name"] : "";
        if(!$name){$name = "NULL";}
        $submit = isset($_GET["submit"]) ? $_GET["submit"] : "";

        if($submit !== ""){
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

            echo "<a href=generate-receipt-all-pdf_renew.php?srd_start={$srd_start}&srd_end={$srd_end}&format=seikyu target=\"_blank\">請求書出力</a>";
            echo "<br><br>";
            echo "<a href=generate-receipt-all-pdf_renew.php?srd_start={$srd_start}&srd_end={$srd_end}&format=ryosyu target=\"_blank\">領収書出力</a>";


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

<?php

$sql = "select distinct targetym from acc_result;";
$stmt = $dbh->query($sql);
$data = $stmt->fetchALL(PDO::FETCH_ASSOC);
?>
締め年月<br>
<?php foreach($data as $v){
?>
<div><a href="generate-receipt-all-pdf_renew_period.php?targetym=<?php echo $v['targetym'];?>&format=seikyu" target="_blank"><?php echo $v['targetym'];?>請求書</a>｜<a href="generate-receipt-all-pdf_renew_period.php?targetym=<?php echo $v['targetym'];?>&format=ryosyu" target="_blank"><?php echo $v['targetym'];?>領収書｜</a>
<a href="generate-list.php?targetym=<?php echo $v['targetym'];?>" target="_blank"><?php echo $v['targetym'];?>対象者一覧</a>
</div>
<?php
}
?>

<br><br>

<?php /*
<h2>請求書／領収書PDF バックグラウンド作成処理（テスト中）</h2>
<div>請求書／領収書を出力したい年月を選択</div>
<form name="form2" method="post">
  <input type="hidden" name="mode" value="setpdf">
<select name="outputtarget">
  <option value="">-</option>
<?php
  for($i=2019;$i<2024;$i++){
    for($j=1;$j<=12;$j++){
  ?>
  <option value="<?php echo sprintf("%04d",$i).sprintf("%02d",$j); ?>"><?php echo sprintf("%04d",$i).sprintf("%02d",$j); ?></option>
<?php } } ?>
</select>
<input type="submit" value="登録">
</form>
*/ ?>

<style>
.pdftable{
  border-left:1px solid #999;
  border-top:1px solid #999;
}
.pdftable td{
  border-right:1px solid #999;
  border-bottom:1px solid #999;
  padding:5px;
}
</style>
<div style="margin-top:50px;">PDF出力状況</div>
<?php
// 既知のディレクトリをオープンし、その内容を読み込みます。
if (is_dir($dir)) {

  $res = glob($dir.'*');
  foreach($res as $v):
    $file = str_replace("../downloadpdf/","",$v);
    echo "<div style='margin-bottom:7px;'><a href='{$v}' target='_blank'>{$file}</a></div>\n";
  endforeach;
  #print_r($res);exit;

  /*
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
          if($file == ".") continue;
          if($file == "..") continue;
            echo "<div style='margin-bottom:7px;'><a href='{$dir}{$file}' target='_blank'>{$file}</a></div>\n";
        }
        closedir($dh);
    }
    */
}


/*
?>
<table class="pdftable">
  <tr>
    <td>出力対象年月</td><td>請求書</td><td>領収書</td>
  </tr>
<?php
 foreach($pdfdata as $k => $v): ?>
<tr>
<td><?php
  echo date("Y年m月度",strtotime($v['outputtarget']."01"));
 ?></td>
<td><?php if($v['seikyu_flag'] == 1){
  echo "<a href='/downloadpdf/{$v['outputtarget']}_seikyu.pdf' target='_blank'>請求書</a>";
}else{
  echo "出力待ち";
} ?></td>
<td><?php if($v['ryosyu_flag'] == 1){
  echo "<a href='/downloadpdf/{$v['outputtarget']}_ryosyu.pdf' target='_blank'>領収書</a>";
}else{
  echo "出力待ち";
} ?></td>
</tr>
<?php endforeach; ?>
</table>


<?php
*/
/*
$sql = "select a.*, b.patient_name from account_transfer as a,patient_info as b where a.original_pid = b.original_pid ";
$stmt = $dbh->query($sql);
$atdata = $stmt->fetchALL(PDO::FETCH_ASSOC);
#print_r($atdata);
 ?>
 <div style="margin-top:50px;">ロボペイへのデータ受け渡し参考画面</div>
 <table class="pdftable">
   <tr>
     <td>出力対象年月</td><td>名前</td><td>金額</td><td>ロボペイに送信</td>
   </tr>
<?php
foreach($atdata as $v):
 ?>
<tr>
<td><?php echo $v['target_ym'];?></td><td><?php echo $v['patient_name'];?></td><td><?php echo $v['price']; ?></td><td><a href="">ロボペイに送信</a></td>
</tr>
<?php endforeach; ?>
</table>
*/ ?>
    </div>
</div>
</body>
</html>
