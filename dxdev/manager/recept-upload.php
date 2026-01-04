<?php
include_once "../class/config.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>レセプト取込データ</title>
</head>

<body>

<?php session_start();

//DB Connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


//Common Part
$file = $_FILES["upfile"]["tmp_name"];
$handle = fopen ( $file, "r" );
$i = 1;

echo '<form method="post" action="recept-upload-result.php" enctype="multipart/form-data">';

//Shinryo Record List Header
echo "<table border = '1'><th>取り込み対象レコード</th><th>診療レコード番号</th><th>医療機関コード</th><th>患者番号</th><th>レセプト内患者番号</th><th>患者氏名</th><th>診療日</th><th>識別番号</th><th>診療行為コード</th><th>点数</th><th>負担コード</th><th>種別コード</th><th>負担比率</th><th>負担金額</th><th>上限</th></tr>\n";
$sid = 0;

while ( ( $data = fgetcsv ( $handle, 200) ) !== FALSE ) {
    $array = array();

    if ($data[0] == "UK") {

        $name = mb_convert_encoding("{$data[6]}", "UTF-8", "SJIS");

        $array_uk = array('レコード名'=>'受付レコード',
                       '審査支払機関'=>$data[1],
                       '都道府県'=>$data[2],
                       '点数表'=>$data[3],
                       '医療機関コード'=>$data[4],
                       '予備項目'=>$data[5],
                       '医療機関名'=>$name,
                       '請求年月'=>$data[7],
                       '届出'=>$data[8],
                       'マルチボリューム識別情報'=>$data[9],
                      );

    }   elseif ($data[0] == "IR") {

        $pid = $skm = $srm = $hoid = $koid = $hoban = $hihoki = $hihoba = $futansya = $jukyusya = "";

        $i++;
        $skm = $data[6];

        $array_ir = array('レコード名'=>'医療機関情報レコード',
                       '審査支払機関'=>$data[1],
                       '都道府県'=>$data[2],
                       '点数表'=>$data[3],
                       '医療機関コード'=>$data[4],
                       '予備'=>$data[5],
                       '請求年月'=>$data[6],
                       '電話番号'=>$data[7],
                       '届出'=>$data[8],
                      );

    } elseif ($data[0] == "RE") {

        //名前の文字化け回避して$nameに格納
        $name = mb_convert_encoding("{$data[4]}", "UTF-8", "SJIS");
        //生年月日を西暦に変換して$birthに格納
        $src = $data[6];
        $g = mb_substr($src, 0, 1, 'UTF-8');
        $y = mb_substr($src, 1, 2, 'UTF-8');
        $m = mb_substr($src, 3, 2, 'UTF-8');
        $d = mb_substr($src, 5, 2, 'UTF-8');
        if ($g === '4') $y += 1988;
        elseif ($g === '3') $y += 1925;
        elseif ($g === '2') $y += 1911;
        elseif ($g === '1') $y += 1868;
        $birth = $y.$m.$d;

        $array_re = array('レコード名'=>'レセプト共通レコード',
                       'レセプト番号'=>$data[1],
                       'レセプト種別'=>$data[2],
                       '診療年月'=>$data[3],
                       '患者氏名'=>$name,
                       '性別'=>$data[5],
                       '生年月日'=>$birth,
                       '給付割合(%)'=>$data[7],
                       '入院年月日'=>$data[8],
                       '診療開始日'=>$data[9],
                       '転帰区分'=>$data[10],
                       '病棟区分コード'=>$data[11],
                       '負担区分コード'=>$data[12],
                       'レセプト特記事項'=>$data[13],
                       '予備'=>$data[14],
                       'カルテ番号'=>$data[15],
                       '請求情報1'=>$data[16],
                       '予備'=>$data[17],
                       '未来院請求コード'=>$data[18],
                       '検索番号'=>$data[19],
                       '記録条件仕様公表年月(GYYMM)'=>$data[20],
                       '請求情報2'=>$data[21],
                       '予備'=>$data[22],
                       '予備'=>$data[23],
                       '予備'=>$data[24],
                      );


    } elseif ($data[0] == "HO") {

        $array_ho = array('レコード名'=>'保険者レコード',
                       '保険者番号'=>$data[1],
                       '被保険者記号'=>$data[2],
                       '被保険者番号'=>$data[3],
                       '診療実日数'=>$data[4],
                       '合計点数'=>$data[5],
                       '食事療養回数'=>$data[6],
                       '食事療養合計金額'=>$data[7],
                       '職務上の事由コード'=>$data[8],
                       '証明書番号'=>$data[9],
                       '医療保険金額(円)'=>$data[10],
                       '減免区分コード'=>$data[11],
                       '減額割合(%)'=>$data[12],
                       '減額金額(円)'=>$data[13],
                      );



        //患者DBに格納
        $payer = $array_uk['審査支払機関'];
        $prefecture = $array_uk['都道府県'];
        $irkkcode = $array_uk['医療機関コード'];
        $irkkname = $array_uk['医療機関名'];
        $skm = $array_ir['請求年月'];
        $srm = $array_re['診療年月'];
        $name = $array_re['患者氏名'];
        $sex = $array_re['性別'];
        $birth = $array_re['生年月日'];
        $hoban = $array_ho['保険者番号'];
        $hihoki = $array_ho['被保険者記号'];
        $hihoban = $array_ho['被保険者番号'];

        $sql = "INSERT INTO re_patient (payer,prefecture,irkkcode,irkkname,skm,srm,name,sex,birth,hoban,hihoki,hihoban) SELECT '$payer','$prefecture','$irkkcode','$irkkname','$skm','$srm','$name','$sex','$birth','$hoban','$hihoki','$hihoban' FROM dual WHERE NOT EXISTS (SELECT pid FROM re_patient WHERE irkkcode = '$irkkcode' AND skm = '$skm' AND name = '$name' AND birth = '$birth')";
        $dbh->query($sql);


    } elseif ($data[0] == "KO") {

        $koid = $data[1];

        $array_ko = array('レコード名'=>'公費レコード',
                       '負担者番号'=>$data[1],
                       '受給者番号'=>$data[2],
                       '任意給付区分'=>$data[3],
                       '診療実日数'=>$data[4],
                       '合計点数'=>$data[5],
                       '公費'=>$data[6],
                       '一部負担金'=>$data[7],
                       '食事療養回数'=>$data[8],
                       '食事療養合計金額'=>$data[9],
                      );

        //患者DBに格納
        $payer = $array_uk['審査支払機関'];
        $prefecture = $array_uk['都道府県'];
        $irkkcode = $array_uk['医療機関コード'];
        $irkkname = $array_uk['医療機関名'];
        $skm = $array_ir['請求年月'];
        $srm = $array_re['診療年月'];
        $name = $array_re['患者氏名'];
        $sex = $array_re['性別'];
        $birth = $array_re['生年月日'];
        $futansya = $array_ko['負担者番号'];
        $jukyusya = $array_ko['受給者番号'];

        //PID検索
        $sql = "SELECT pid FROM re_patient WHERE irkkcode = '$irkkcode' AND skm = '$skm' AND name = '$name' AND birth = '$birth'";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
        $pid = $row['pid'];
        }

        if ($pid == 0) {
            //KO only - INSERT
            $sql = "INSERT INTO re_patient (payer,prefecture,irkkcode,irkkname,skm,srm,name,sex,birth,futansya,jukyusya) VALUES ('$payer','$prefecture','$irkkcode','$irkkname','$skm','$srm','$name','$sex','$birth','$futansya','$jukyusya')";
            $dbh->query($sql);
        } else {
            //HO&KO - UPDATE
            $sql = "UPDATE re_patient SET futansya = '$futansya' , jukyusya = '$jukyusya' WHERE pid = '$pid'";
            $dbh->query($sql);
        }


    } elseif ($data[0] == "HS") {

        $iryokikan = $patient = "";
        $array = array('レコード名'=>'傷病名部位レコード',
                       '診療開始日'=>$data[1],
                       '転帰区分コード'=>$data[2],
                       '歯式（傷病名）'=>'-(表示省略)-',
                       '傷病名'=>$data[4],
                       '修飾語コード'=>$data[5],
                       '傷病名称'=>$data[6],
                       '併存傷病名数'=>$data[7],
                       '病態移行コード'=>$data[8],
                       '主傷病コード'=>$data[9],
                       'コメントコード'=>$data[10],
                       '補足コメント'=>$data[11],
                       '歯式(補足コメント)'=>$data[12],
                      );

        //irkkname検索
        $sql = "SELECT original_irkkcode FROM account_info WHERE irkkname = '$irkkname'";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
        $iryokikan = $row['original_irkkcode'];
        }

        //original_irkk新規登録
        if ($iryokikan== "") {
            $sql = "INSERT INTO account_info (irkkname) VALUES ('$irkkname')";
            $dbh->query($sql);
        }

        //patient検索
        $sql = "SELECT original_pid FROM patient_info WHERE patient_name = '$name' AND patient_birth = '$birth'";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
        $patient = $row['original_pid'];
        }

        //original_patient新規登録
        if ($patient == "") {
            $sql = "INSERT INTO patient_info (patient_name,patient_birth) VALUES ('$name','$birth')";
            $dbh->query($sql);
        }

    } elseif ($data[0] == "SS") {

        $syubetsu = $array_re['レセプト種別'];
        $sql = "SELECT * FROM syubetsu_code WHERE code = $syubetsu";
        $stmt = $dbh->query($sql);
        $syubetsu_copy = $stmt->fetchALL(PDO::FETCH_ASSOC);
        $ratio = $syubetsu_copy[0]['ratio'];
        $upper = $syubetsu_copy[0]['upper'];

        for ($j = 78 ; $j <= 108; $j++) {
            if ( $data[$j] == 1 ) {
                $sid += 1;
                $original_irkkcode = $original_pid = $pid = "";
                $dt = $j - 77;
                $srd = $srm . $dt;
                if ($data[1] != "") {
                    $sql = "SELECT * FROM shikibetsu_code WHERE code = $data[1]";
                    $stmt = $dbh->query($sql);
                    $shikibetsu = $stmt->fetchALL(PDO::FETCH_ASSOC);
                } else {
                    $shikibetsu = "";
                }

                $array = array('レコード名'=>'歯科診療行為レコード',
                               '診療日'=>$srd,
                               '診療識別'=>$data[1],
                               '負担区分'=>$data[2],
                               '診療行為'=>$data[3],
                               '点数'=>$data[76],
                               '種別'=>$syubetsu,
                               '割合'=>$ratio,
                               '上限'=>$upper,
                              );

                $sbt = $array['診療識別'];
                $srk = $array['診療行為'];
                $tns = $array['点数'];
                $ftn = $array['負担区分'];

                $copayment = $tns * 10 * $ratio / 100;
                $copayment = round($copayment,-2);


                //original_irkkcode抽出
                $sql = "SELECT original_irkkcode FROM account_info WHERE irkkname = '$irkkname'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_irkkcode = $row['original_irkkcode'];
                }

                //original_pid抽出
                $sql = "SELECT original_pid FROM patient_info WHERE patient_name = '$name' AND patient_birth = '$birth'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_pid = $row['original_pid'];
                }

                //recept_pid抽出
                $sql = "SELECT pid FROM re_patient WHERE irkkcode = '$irkkcode' AND skm = '$skm' AND name = '$name' AND birth = '$birth'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $pid = $row['pid'];
                }


                //SESSIONに格納

                $shinryo_array = array($sid,$original_irkkcode,$original_pid,$pid,$name,$srd,$sbt,$srk,$tns,$ftn,$syubetsu,$ratio,$copayment,$upper);
                $_SESSION["shinryo_".$sid] = $shinryo_array;

                //診療レコード一覧を表示
                echo "<tr>";
                echo '<td><input type="checkbox" name="check_list[]" value="'.$sid.'"></td>';
                foreach($_SESSION["shinryo_".$sid] as $key=>$value){
                    echo "<td>".$value."</td>\n";
                }
                echo "</tr>\n";

                //診療詳細DBに格納
                /*
                if ($pid!=0) {
                    $sql = "INSERT INTO re_shinryo (original_irkkcode,original_pid,pid,srd,shikibetsu,koui,tensu,futan,syubetsu,ratio,copayment,upper) SELECT '$original_irkkcode','$original_pid','$pid','$srd','$sbt','$srk','$tns','$ftn','$syubetsu','$ratio','$copayment','$upper'  FROM dual WHERE NOT EXISTS (SELECT pid FROM re_shinryo WHERE original_irkkcode = '$original_irkkcode' AND original_pid = '$original_pid' AND pid = '$pid' AND srd = '$srd' AND shikibetsu = '$sbt' AND koui = '$srk' AND tensu = '$tns' AND futan = '$ftn' AND syubetsu = '$syubetsu' AND ratio = '$ratio' AND copayment = '$copayment' AND upper = '$upper')";
                    $dbh->query($sql);
                }
                */
            }
        }



    } elseif ($data[0] == "CO") {
        $name = mb_convert_encoding("{$data[4]}", "UTF-8", "SJIS");
        $array = array('レコード名'=>'コメントレコード',
                       '診療識別'=>$data[1],
                       '負担区分'=>$data[2],
                       'コメント'=>$data[3],
                       '文字データ'=>$name,
                       '歯式(コメント)'=>$data[5],
                       '予備'=>$data[6],
                       '予備'=>$data[7],
                       '予備'=>$data[8],
                       '予備'=>$data[9],
                       '予備'=>$data[10],
                      );

        //PID抽出
        $sql = "SELECT pid FROM re_patient WHERE irkkcode = '$irkkcode' AND skm = '$skm' AND name = '$name' AND birth = '$birth'";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
        $pid = $row['pid'];
        }

        if ($pid!=0) {
            //コメントDBに格納
            $sql = "INSERT INTO re_comment (pid,comment) SELECT '$pid','$name'  FROM dual WHERE NOT EXISTS (SELECT pid FROM re_comment WHERE pid = '$pid' AND comment = '$name')";
            $dbh->query($sql);
        }

    } elseif ($data[0] == "GO") {
        $array = array('レコード名'=>'診療報酬請求書レコード',
                       '総件数'=>$data[1],
                       '総合計点数'=>$data[2],
                       'マルチボリューム識別情報'=>$data[3],
                      );
    }
}

echo '<input type="submit" name="submit" value="SUBMIT"/></form>';



?>

</body>
</html>
