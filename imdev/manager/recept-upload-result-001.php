<?php
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>診療レコード取込結果</title>
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
<?php session_start();

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Table header
?>
		<div id="breadcrumb">
        <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="./receipt_select.php">レセプトデータ取り込み</a>&nbsp;&gt;&nbsp;診療レコード取込結果
        </div>

<h2 class="title_name">診療レコード取込結果</h2>

<div align="center">下記のレセプトデータを取り込みました</div>

<div align="center" class="tbl">
<table border = '0' cellpadding=0 cellspacing=0><th>レセプト番号</th><th>診療レコード番号</th><th>医療機関コード</th><th>患者番号</th><th>レセプト内患者番号</th><th>患者氏名</th><th>診療日</th><th>識別番号</th><th>診療行為コード</th><th>診療行為カテゴリ</th><th>診療行為名</th><th>点数</th><th>回数</th><th>負担コード</th><th>種別コード</th><th>負担比率</th><th>負担金額</th></tr>

<?php


if(isset($_POST['submit'])){
    if(!empty($_POST['check_list'])) {
        foreach($_POST['check_list'] as $record) {

            //患者データの登録（patient_info）
            foreach($_SESSION["patient_".$record] as $j) {
                $name = strval($j[1]);
                $birth = strval($j[2]);
                $hihoki = strval($j[3]);
                $hihoban = strval($j[4]);
                $jukyusya = strval($j[5]);
								$original_irkkcode = strval($j[6]);
                #$hihoki = mb_convert_kana($j[3], 'kvrn');
                #$hihoban = mb_convert_kana($j[4], 'kvrn');
                #$jukyusya = mb_convert_kana($j[5], 'kvrn');

                //patient_infoに既に登録があるか確認
                $sql = "SELECT original_pid
                        FROM patient_info
                        WHERE patient_name = '$name'
                            AND patient_birth = '$birth' AND disp = 0 ";
                $stmt = $dbh->query($sql);

                foreach ($stmt as $row) {
                    $patient_exists = $row['original_pid'];
                }


                //patient_infoに新規登録（既存であればUPDATE,新規であればINSERT）
								$relation_original_pid = 0;
                if ($patient_exists) {
                    $sql = "UPDATE patient_info
                            SET patient_hihoki = '$hihoki',
                                patient_hihoban = '$hihoban',
                                patient_jukyuban = '$jukyusya'
                            WHERE original_pid = '$patient_exists'";
                    $dbh->query($sql);

										$relation_original_pid = $patient_exists;
                } else {
                    $sql = "INSERT INTO patient_info (patient_name,
                                                      patient_birth,
                                                      patient_hihoki,
                                                      patient_hihoban,
                                                      patient_jukyuban)
                            VALUES ('$name',
                                    '$birth',
                                    '$hihoki',
                                    '$hihoban',
                                    '$jukyusya')";
                    $dbh->query($sql);

										$relation_original_pid = $dbh->lastInsertId('original_pid');
                }

								#accountpatient_relationにすでに医療機関番号と患者番号の登録があるか確認
								$sql = "SELECT * FROM accountpatient_relation WHERE original_pid = '{$relation_original_pid}' and original_irkkcode = '{$original_irkkcode}'";
								$stmt = $dbh->query($sql);
								$row = $stmt->fetchALL(PDO::FETCH_ASSOC);
								#echo "ここ".$sql;echo count($row);print_r($row);
								if(count($row) == 0){
									$sql = "INSERT INTO accountpatient_relation (original_pid,original_irkkcode) VALUES ('{$relation_original_pid}','{$original_irkkcode}')";
									#echo $sql."---<br>\n";
									$dbh->query($sql);
								}

                $hihoki = $hihoban = $jukyusya = $patient_exists = "";
            }


            //original_pid抽出
            $sql = "SELECT original_pid FROM patient_info WHERE patient_name = '$name' AND patient_birth = '$birth' AND disp = 0 ";
            $stmt = $dbh->query($sql);
            foreach ($stmt as $row) {
                $original_pid = $row['original_pid'];
            }


            //上限金額の登録（max_copayment）
            $srm = strval($_SESSION["patientlist_".$record][2]);
            $max_copayment = strval($_SESSION["patientlist_".$record][3]);

            if ($max_copayment) {
                $sql = "INSERT INTO max_copayment (original_pid,srm,max_copayment)
                            SELECT '$original_pid','$srm','$max_copayment'
                            FROM dual WHERE NOT EXISTS (SELECT max_copayment
                                                        FROM max_copayment
                                                        WHERE original_pid = '$original_pid'
                                                            AND srm = '$srm')";
                $dbh->query($sql);
            }


            //診療データの登録（re_shinryo）・表示
            foreach($_SESSION["shinryo_".$record] as $k) {

                $rid = $k[0];
                $sid = $k[1];
                $original_irkkcode = $k[2];
                $pid = $k[4];
                $name = $k[5];
                $srd = $k[6];
                $sbt = $k[7];
                $srk = $k[8];
                $category = $k[9];
                $shinryo_name = $k[10];
                $tns = $k[11];
                $kaisu = $k[12];
                $ftn = $k[13];
                $syubetsu = $k[14];
                $ratio = $k[15];
                $copayment = $k[16];


                if ($pid != 0) {
                    // Insert into re_shinryo
										/*
                    $sql = "INSERT INTO re_shinryo (original_irkkcode,
                                                    original_pid,
                                                    pid,srd,
                                                    shikibetsu,
                                                    koui,
                                                    category,
                                                    shinryo_name,
                                                    tensu,
                                                    kaisu,
                                                    futan,
                                                    syubetsu,
                                                    ratio,
                                                    copayment)
                            SELECT '$original_irkkcode',
                                    '$original_pid',
                                    '$pid',
                                    '$srd',
                                    '$sbt',
                                    '$srk',
                                    '$category',
                                    '$shinryo_name',
                                    '$tns',
                                    '$kaisu',
                                    '$ftn',
                                    '$syubetsu',
                                    '$ratio',
                                    '$copayment'
                            FROM dual WHERE NOT EXISTS (SELECT pid
                                                        FROM re_shinryo
                                                        WHERE original_irkkcode = '$original_irkkcode'
                                                            AND original_pid = '$original_pid'
                                                            AND pid = '$pid'
                                                            AND srd = '$srd'
                                                            AND shikibetsu = '$sbt'
                                                            AND koui = '$srk'
                                                            AND category = '$category'
                                                            AND shinryo_name = '$shinryo_name'
                                                            AND tensu = '$tns'
                                                            AND kaisu = '$kaisu'
                                                            AND futan = '$ftn'
                                                            AND syubetsu = '$syubetsu'
                                                            AND ratio = '$ratio'
                                                            AND copayment = '$copayment')";
										*/
										$sql = "INSERT INTO re_shinryo (original_irkkcode,
                                                    original_pid,
                                                    pid,srd,
                                                    shikibetsu,
                                                    koui,
                                                    category,
                                                    shinryo_name,
                                                    tensu,
                                                    kaisu,
                                                    futan,
                                                    syubetsu,
                                                    ratio,
                                                    copayment)
                            VALUES ('$original_irkkcode',
                                    '$original_pid',
                                    '$pid',
                                    '$srd',
                                    '$sbt',
                                    '$srk',
                                    '$category',
                                    '$shinryo_name',
                                    '$tns',
                                    '$kaisu',
                                    '$ftn',
                                    '$syubetsu',
                                    '$ratio',
                                    '$copayment');";
										echo $sql."<br>\n";
                    $dbh->query($sql);

                    echo "<tr>";
                    echo "<td>".$rid."</td>\n";
                    echo "<td>".$sid."</td>\n";
                    echo "<td>".$original_irkkcode."</td>\n";
                    echo "<td>".$original_pid."</td>\n";
                    echo "<td>".$pid."</td>\n";
                    echo "<td>".$name."</td>\n";
                    echo "<td>".$srd."</td>\n";
                    echo "<td>".$sbt."</td>\n";
                    echo "<td>".$srk."</td>\n";
                    echo "<td>".$category."</td>\n";
                    echo "<td>".$shinryo_name."</td>\n";
                    echo "<td>".$tns."</td>\n";
                    echo "<td>".$kaisu."</td>\n";
                    echo "<td>".$ftn."</td>\n";
                    echo "<td>".$syubetsu."</td>\n";
                    echo "<td>".$ratio."</td>\n";
                    echo "<td>".$copayment."</td>\n";
                    echo "</tr>\n";

                }

            }

            $_SESSION["patientlist_".$record] = array();
            $_SESSION["patient_".$record] = array();
            $_SESSION["shinryo_".$record] = array();

        }
        echo "<br>\n";
    }
}

?>

</table>

<br /><br /><br />

<a href="./">トップページに戻る</a>

</div>

</div>
</div>
</body>
</html>
