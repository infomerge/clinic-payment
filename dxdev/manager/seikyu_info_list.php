<?php
session_start();
ini_set( 'display_errors', 0 );
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
?>

<!DOCTYPE_html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>請求情報一覧</title>
<?php
    $smarty->display( 'common/head_inc.tpl');
?>
</head>

<body>

<?php
    //Header Template
    $smarty->display( 'common/header.tpl' );
    //DB Definition
    $dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>

<div id="wrap">
    <div class="content">
        <div id="breadcrumb">
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="patient_info_list.php">患者情報一覧</a>&nbsp;&gt;&nbsp;請求情報一覧
        </div>

        <?php
        #対象の診療日（診療月）を定義
        $srd_start = "00000000";
        $srm_start = substr($srd_start,0,6);
        $srd_end = "99999999";
        $srm_end = substr($srd_end,0,6);

        #Robot Payment用情報
        $original_pid =  intval($_GET["original_pid"]);

        #患者情報マスター
        $sql = "SELECT *
                FROM patient_info
                WHERE original_pid = '$original_pid'";
        $stmt = $dbh->query($sql);
        $patient_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

        #医療保険マスター
        $sql = "SELECT *
                FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                WHERE re_shinryo.original_pid = '$original_pid'";
        $stmt = $dbh->query($sql);
        $iryo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

        #介護保険マスター
        $sql = "SELECT *
                FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
                WHERE rek_patient.original_pid = '$original_pid'";
        $stmt = $dbh->query($sql);
        $kaigo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

        #自由診療マスター
        $sql = "SELECT *
                FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid
                WHERE appendix.original_pid = '$original_pid'";
        $stmt = $dbh->query($sql);
        $app_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

        #決済情報マスター
        $sql = "SELECT *
                FROM acc_result
                WHERE original_pid = '$original_pid'";
        $stmt = $dbh->query($sql);
        $kessai_data = $stmt->fetchALL(PDO::FETCH_ASSOC);



        $srmlist_iryo = $srmlist_kaigo = $srmlist= array();

        #診療月抽出（医療保険）
        $sql = "SELECT DISTINCT SUBSTRING(srd,1,6) as srm
                FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                WHERE re_shinryo.original_pid = '$original_pid'
                ORDER BY srd";
        $stmt = $dbh->query($sql);
        $srmlist_iryo = $stmt->fetchALL(PDO::FETCH_ASSOC);

        #診療月抽出（介護保険）
        $sql = "SELECT DISTINCT rek_service.srm
                FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
                WHERE rek_service.original_pid = '$original_pid'
                ORDER BY rek_service.srm";
        $stmt = $dbh->query($sql);
        $srmlist_kaigo = $stmt->fetchALL(PDO::FETCH_ASSOC);

        #診療月抽出（自由診療）
        $sql = "SELECT DISTINCT SUBSTRING(app_date,1,6) as srm
                FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid
                WHERE appendix.original_pid = '$original_pid'
                ORDER BY srm";
        $stmt = $dbh->query($sql);
        $srmlist_app = $stmt->fetchALL(PDO::FETCH_ASSOC);

        $srmlist = array_merge($srmlist_iryo,$srmlist_kaigo,$srmlist_app);
        $srmlist = array_unique($srmlist, SORT_REGULAR);

        #患者名／ロボペイ顧客番号／支払方法取得
        $name =  $patient_data[0]['patient_name'];
        $rp_cid = $patient_data[0]['rp_cid'];
        if($patient_data[0]['direct_debit'] == 0){$direct_debit = "口座振替";} else {$direct_debit = "振込/現金";}



        #日毎の点数と診療月を$dataに格納
        $data = array();
        #医療保険：日ごとの請求金額計算
        foreach ($iryo_data as $v){
            $data['srd'][$v['srd']]['copaymentbysrd'] += round($v['copayment'],-1);
            $data['srd'][$v['srd']]['srm'] = substr($v['srd'],0,6);
            $data['srd'][$v['srd']]['rp_reqid'] = strval($v['rp_reqid']);
        }

        #自由診療：日ごとの請求金額計算
        foreach ($app_data as $t){
            $data['srd'][$t['app_date']]['copaymentbysrd'] += round($t['app_price'],-1);
            $data['srd'][$t['app_date']]['srm'] = substr($t['app_date'],0,6);
            $data['srd'][$t['app_date']]['rp_reqid'] = strval($t['rp_reqid']);
        }


        #医療保険/自由診療：月ごとの請求金額計算
        foreach ($data['srd'] as $w){
            $data['srm'][$w['srm']]['copaymentbysrm'] += intval($w['copaymentbysrd']);
            $data['srm'][$w['srm']]['rp_reqid'] = strval($w['rp_reqid']);
        }

        #介護保険：月ごとの請求金額計算
        foreach ($kaigo_data as $x){
            $data['srm'][$x['srm']]['copaymentbysrm'] += $x['service_unit'] * 10 * $x['kaisu'] * ((100 - $x['hoken_rate']) / 100) * ((100 - $x['kouhi_rate']) / 100);
            $data['srm'][$x['srm']]['rp_reqid'] = strval($x['rp_reqid']);
        }


        #決済情報：月ごとの引き落とし情報
        foreach ($kessai_data as $y){
            $data['srm'][$y['srm']]['kessai_date'] = $y['date'];
        }


        #請求情報一覧表示
        echo "<h2 class='title_name'> $name 様の請求情報一覧</h2>";
        echo "<table class=\"list_body\">";
        echo "<tr><th>診療年月</th><th>請求金額</th><th>種別</th><th>回収日</th><th>未回収残高</th></tr>";


        #print_r($data['srm']);

        foreach ($srmlist as $y){
            $srm = $y['srm'];
            $year = substr($srm,0,4);
            $month = substr($srm,4,2);
            $amo = $data['srm'][$srm]['copaymentbysrm'];
            $rp_reqid = $data['srm'][$srm]['rp_reqid'];
            $date = date('Y年m月d日',strtotime($data['srm'][$srm]['kessai_date']));

            echo "<tr><td>$year"."年$month"."月</td>";
            echo "<td>$amo</td>";
            echo "<td>$direct_debit</td>";
            echo "<td>$date</td>";
            if ($rp_reqid == 0){
                echo "<td><a href=payment-test-exe.php?original_pid={$original_pid}&req_type=2&cid={$rp_cid}&amo={$amo}&srm={$srm}>未登録</a></td></tr>";
            } else {
                echo "<td>$rp_reqid&nbsp;<a href=payment-test-exe.php?original_pid={$original_pid}&req_type=3&reqid={$rp_reqid}&amo={$amo}&srm={$srm}>請求情報変更</a></td></tr>";
            }
        }

        echo "</table>";
        ?>


    </div>
</div>

</body>
</html>
