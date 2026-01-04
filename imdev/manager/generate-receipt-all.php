<?php
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>領収データ全出力</title>

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

        //対象の期間を取得
        $srd_start = $_POST["srd_start"];
        $srd_end = $_POST["srd_end"];

        //DB接続
        $dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //DBから対象患者の一覧を抽出
        $sql = "SELECT DISTINCT name
                FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                WHERE srd >= '$srd_start' AND srd <= '$srd_end'";
        $stmt = $dbh->query($sql);
        $patient_list = $stmt->fetchALL(PDO::FETCH_ASSOC);
        $patient_list_count = sizeof($patient_list);


        //患者毎に請求＆領収データを出力（開始）
        for($i = 0; $i < $patient_list_count; $i++){

            //$nameに患者名を格納
            $name = $patient_list[$i]['name'];
            //DBから対象患者の医療保険データを抽出
            $sql = "SELECT *
                    FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                    WHERE name = '$name'";
            $stmt = $dbh->query($sql);
            $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
            //$original_pidに患者番号を格納
            $original_pid = $result[$i]['original_pid'];

            //患者基本情報を表示
            echo "<table class=\"list_body\">";
            echo "<tr><th>患者番号</th><th>患者氏名</th></tr>";
            echo "<tr><td>$original_pid</td><td>$name</td></tr>";
            echo "</table><br/>\n";

            echo "<br/>\n";
            echo "<hr>";
            echo "<br/>\n";


            //負担区分と負担割合を抽出
            $futan_code = $result[$i]['futan'];
            $sql = "SELECT * FROM futan_code WHERE code = $futan_code";
            $stmt = $dbh->query($sql);
            $futan = $stmt->fetch(PDO::FETCH_ASSOC);
            $futan_kubun = $futan['futan'];
            $ratio = $result[$i]['ratio'];



            //-------------------------------------//　
            //           ＜＜＜医療保険＞＞＞　        //　
            //　           請求＆領収データ           //
            //-------------------------------------//　

            //DBから対象の診療日一覧を抽出
            $sql = "SELECT DISTINCT srd
                    FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                    WHERE name = '$name' AND srd >= '$srd_start' AND srd <= '$srd_end'
                    ORDER BY srd";
            $stmt = $dbh->query($sql);
            $srdlist = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $srdcount = sizeof($srdlist);

            //診療日毎に診療データを抽出／表示（開始）
            for($j = 0; $j < $srdcount; $j++){

                //診療日と患者負担額を表示
                $srd = $srdlist[$j]['srd'];
                $srd_full = date('Y年m月d日',strtotime($srd));

                //日毎の患者負担額を計算（日毎に四捨五入）して$dailycopaymentに格納
                $sql = "SELECT sum(copayment)
                        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                        WHERE name = '$name' AND srd = '$srd'";
                $stmt = $dbh->query($sql);
                $dailycp = $stmt->fetch(PDO::FETCH_ASSOC);
                $dailycopayment = round($dailycp['sum(copayment)'],-1);

                //DBから対象日の診療データ一覧抽出、$shinryocountに診療回数を格納
                $sql = "SELECT *
                        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                        WHERE name = '$name' AND srd = '$srd'";
                $stmt = $dbh->query($sql);
                $shinryolist = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $shinryocount = sizeof($shinryolist);

                //診療行為カテゴリマスタ
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




                //医療保険に関する患者情報を表示------------------
                echo "<table class=\"list_body\">";
                echo "<tr><th>負担区分</th><th>診療日</th><th>負担割合</th></tr>";
                echo "<tr><td>$futan_kubun</td><td>$srd_full</td><td>$ratio"."％</td></tr>";
                echo "</table><br/>\n";
                //-------------------------------------------


                //医療保険に関する診療行為カテゴリ別の点数を表示-----
                echo "<table class=\"list_body\"><tr>";
                for($k = 0; $k < $category_count; $k++){
                    $cat_title = $array_category[$k]['title'];
                    echo "<th>$cat_title</th>";
                }
                echo "</tr><tr>";
                for($k = 0; $k < $category_count; $k++){
                    $category = $array_category[$k]['kigo'];
                    $sql = "SELECT sum(tensu) as tensu
                            FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                            WHERE name = '$name' AND srd = '$srd' AND category = '$category'";
                    $stmt = $dbh->query($sql);
                    $tensubycat = $stmt->fetch(PDO::FETCH_ASSOC);
                    $cat_tensu = $tensubycat['tensu'];
                    if($cat_tensu==""){$cat_tensu = 0;}
                    echo "<td>$cat_tensu"."点</td>";

                    $total_tensu += $cat_tensu;
                }
                echo "</tr></table><br/>\n";
                //-------------------------------------------


                //医療保険に関する集計情報を表示------------------
                echo "<table class=\"list_body\">";
                echo "<tr><th>総点数</th><th>負担額</th></tr>";
                echo "<tr><td>$total_tensu"."点</td><td>$dailycopayment"."円</td></tr>";
                echo "</table><br/>\n";
                //-------------------------------------------

                $total_tensu = "0";

                echo "<br/>\n";
                echo "<hr>";
                echo "<br/>\n";

            }





            //-------------------------------------//　
            //          ＜＜＜介護保険＞＞＞　         //　
            //　          請求＆領収データ            //
            //-------------------------------------//　

            //DBから介護保険の全データを抽出して$kaigo_dataに格納、$srdcountに件数を格納
            $sql = "SELECT *
                    FROM rek_service INNER JOIN rek_patient
                        ON rek_service.pid = rek_patient.pid
                    WHERE srd >= '$srd_start'
                        AND srd <= '$srd_end'
                    ORDER BY srd";
            $stmt = $dbh->query($sql);
            $kaigo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $srdcount = sizeof($kaigo_data);

            //$kaigo_dataから対象患者のデータを抽出して配列に格納
            $target_kaigo_data = array();
            foreach($kaigo_data as $value){
                $pid_in_value = $value['original_pid'];
                if($original_pid == $pid_in_value){
                    $target_kaigo_data[] = $value;
                }
            }
            $kaigo_count = sizeof($target_kaigo_data);


            //$kaigo_dataに該当患者のデータがあれば、請求＆領収データ出力
            if($target_kaigo_data){
                $hoken_rate = $target_kaigo_data[0]['hoken_rate'];
                $kouhi_rate = $target_kaigo_data[0]['kouhi_rate'];


                //介護保険の患者データを表示------------
                echo "<table class=\"list_body\">";
                echo "<tr><th>負担区分</th><th>保険負担率</th><th>公費負担率</th></tr>";
                echo "<tr><td>介護</td><td>$hoken_rate"."％</td><td>$kouhi_rate"."％</td></tr>";
                echo "</table><br/>\n";
                //----------------------------------


                //介護保険のサービスデータを算定日毎に表示
                echo "<table class=\"list_body\">";
                echo "<tr><th>サービス算定日</th><th>サービス名</th><th>サービス単位数</th><th>負担額</th></tr>";
                for($j = 0; $j < $kaigo_count; $j++){
                    $srd = $target_kaigo_data[$j]['srd'];
                    $srd_full = date('Y年m月d日',strtotime($srd));
                    $service_code = $target_kaigo_data[$j]['service_code'];
                    //サービスコード抽出
                    $sql = "SELECT * FROM service_code WHERE code = $service_code";
                    $stmt = $dbh->query($sql);
                    $service = $stmt->fetch(PDO::FETCH_ASSOC);
                    $service_name = $service['service_name'];
                    $service_unit = $target_kaigo_data[$j]['service_unit'];
                    //利用者負担額 ＝ 本来の負担額 ✕ （100 - 保険負担率）(%）✕（100 - 公費負担率）(%)
                    $service_copayment = $service_unit * 10 * ( (100 - $hoken_rate) / 100 ) * ( (100 - $kouhi_rate) / 100 );
                    echo "<tr><td>$srd_full</td><td>$service_name</td><td>$service_unit"."点</td><td>$service_copayment"."円</td></tr>";
                    $total_tensu += $service_unit;
                    $total_copayment += $service_copayment;
                }
                echo "</table><br/>\n";
                //----------------------------------


                //介護保険の集計データを表示------------
                echo "<table class=\"list_body\">";
                echo "<tr><th>総点数</th><th>負担額</th></tr>";
                echo "<tr><td>$total_tensu"."点</td><td>$total_copayment"."円</td></tr>";
                echo "</table><br/>\n";
                //----------------------------------

                $total_tensu = "0";
                $total_copayment = "0";

                echo "<br/>\n";
                echo "<hr>";
                echo "<br/>\n";

            }

        }
        //患者毎に請求＆領収データを出力（終了）

        ?>

    </div>
</div>

</body>
</html>
