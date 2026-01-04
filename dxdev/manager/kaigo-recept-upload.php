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

//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Common Part
$file = $_FILES["upfile"]["tmp_name"];
$handle = fopen ( $file, "r" );
$i = 1;
$sid = 0;


//Service Record List Header
echo "<table border = '1'><th>取り込み対象レコード</th><th>診療レコード番号</th><th>患者番号</th><th>被保険者番号</th><th>患者生年月日</th><th>サービスコード</th><th>サービス単位数</th><th>摘要</th></tr>\n";

echo '<form method="post" action="kaigo-recept-upload-result.php" enctype="multipart/form-data">';

while ( ( $data = fgetcsv ( $handle, 200) ) !== FALSE ) {
    $array = array();

    if ($data[0] == 1) {
        //echo "<h3>コントロールレコード</h3><br/>\n";
        $array_1 = array('レコード種別'=>$data[0],
                       'レコード番号'=>$data[1],
                       'ボリューム通番'=>$data[2],
                       'レコード件数'=>$data[3],
                       'データ種別'=>$data[4],
                       '福祉事業所特定番号'=>$data[5],
                       '保険者番号'=>$data[6],
                       '事業所番号'=>$data[7],
                       '都道府県番号'=>$data[8],
                       '媒体区分'=>$data[9],
                       '対象処理年月'=>$data[9],
                       'ファイル管理番号'=>$data[9],
                      );

    }   elseif ($data[0] == 2) {

        if ($data[2] == 7111) {
            //echo "<h4>医保・公費総計データレコード</h4><br/>\n";
                $array_2_7111 = array('レコード種別'=>$data[0],
                       'レコード番号'=>$data[1],
                       '2'=>$data[2],
                       '請求月'=>$data[3],
                       '事業者番号'=>$data[4],
                       '種別'=>$data[5],
                       '6'=>$data[6],
                       '7'=>$data[7],
                       '対象患者数'=>$data[8],
                       '給付単位数合計'=>$data[9],
                       '合計金額'=>$data[10],
                       '保険請求学合計'=>$data[11],
                       '公費単位数合計'=>$data[12],
                       '利用者負担額合計'=>$data[13],
                       '14'=>$data[14],
                       '16'=>$data[16],
                       '17'=>$data[17],
                       '18'=>$data[18],
                       '19'=>$data[19],
                      );

        }   elseif ($data[2] == 7131) {

            if ($data[3] == 1 ) {
            //echo "<h3>患者データレコード</h3><br/>\n";
                //echo "<h4>被保険者情報データレコード</h4>\n";
                $array_2_7131_1 = array('レコード種別'=>$data[0],
                       'レコード番号'=>$data[1],
                       '2'=>$data[2],
                       '3'=>$data[3],
                       '請求月'=>$data[4],
                       '事業者番号'=>$data[5],
                       '保険者番号'=>$data[6],
                       '被保険者番号'=>$data[7],
                       '公費負担者番号'=>$data[8],
                       '公費受給者番号'=>$data[9],
                       '生年月日(西暦)'=>$data[14],
                       '性別コード'=>$data[15],
                       '要介護状態区分'=>$data[16],
                       '認定有効期間(start)'=>$data[18],
                       '認定有効期間(end)'=>$data[19],
                       '27'=>$data[27],
                       '28'=>$data[28],
                       '保険給付率'=>$data[30],
                       '公費給付率'=>$data[31],
                       '32'=>$data[32],
                       '33'=>$data[33],
                       '給付単位数'=>$data[34],
                       '保険請求額合計'=>$data[35],
                       '利用者負担額合計'=>$data[36],
                       '37'=>$data[37],
                       '38'=>$data[38],
                       '39'=>$data[39],
                       '公費分単位数'=>$data[40],
                       '公費請求額合計'=>$data[41],
                       '42'=>$data[42],
                       '43'=>$data[43],
                       '44'=>$data[44],
                       '45'=>$data[45],
                       '46'=>$data[46],
                       '47'=>$data[47],
                       '48'=>$data[48],
                       '49'=>$data[49],
                       '50'=>$data[50],
                       '51'=>$data[51],
                       '52'=>$data[52],
                       '53'=>$data[53],
                       '54'=>$data[54],
                       '55'=>$data[55],
                       '56'=>$data[56],
                       '57'=>$data[57],
                      );

                //rek_patientに必要なデータを変数に代入
                $skm =  $array_2_7131_1['請求月'];
                $jigyosya =  $array_2_7131_1['事業者番号'];
                $kaigo_hoban =  $array_2_7131_1['保険者番号'];
                $kaigo_hihoban =  $array_2_7131_1['被保険者番号'];
                $futansya =  $array_2_7131_1['公費負担者番号'];
                $jukyusya =  $array_2_7131_1['公費受給者番号'];
                $birth =  $array_2_7131_1['生年月日(西暦)'];
                $sex =  $array_2_7131_1['性別コード'];
                $hoken_rate =  $array_2_7131_1['保険給付率'];
                $kouhi_rate =  $array_2_7131_1['公費給付率'];
                $totalcopayment =  $array_2_7131_1['利用者負担額合計'];


                //rek_patientにデータを格納
                $sql = "INSERT INTO rek_patient (skm,jigyosya,kaigo_hoban,kaigo_hihoban,futansya,jukyusya,birth,sex,hoken_rate,kouhi_rate,totalcopayment) SELECT '$skm','$jigyosya','$kaigo_hoban','$kaigo_hihoban','$futansya','$jukyusya','$birth','$sex','$hoken_rate','$kouhi_rate','$totalcopayment' FROM dual WHERE NOT EXISTS (SELECT pid FROM rek_patient WHERE skm = '$skm' AND jigyosya = '$jigyosya' AND kaigo_hoban = '$kaigo_hoban' AND kaigo_hihoban = '$kaigo_hihoban' AND futansya = '$futansya' AND jukyusya = '$jukyusya' AND birth = '$birth' AND sex = '$sex')";
                $dbh->query($sql);



            }   elseif ($data[3] == 2 ) {
                //echo "<h4>給付費明細データレコード</h4>\n";
                $sid += 1;
                $array_2_7131_2 = array('レコード種別'=>$data[0],
                       'レコード番号'=>$data[1],
                       '2'=>$data[2],
                       '3'=>$data[3],
                       '請求月'=>$data[4],
                       '事業者番号'=>$data[5],
                       '保険者番号'=>$data[6],
                       '被保険者番号'=>$data[7],
                       'サービスコード上2桁'=>$data[8],
                       'サービスコード下4桁'=>$data[9],
                       '単位数'=>$data[10],
                       '回数'=>$data[11],
                       '12'=>$data[12],
                       '13'=>$data[13],
                       '14'=>$data[14],
                       'サービス単位数'=>$data[15],
                       '16'=>$data[16],
                       '17'=>$data[17],
                       '18'=>$data[18],
                       '摘要'=>$data[19],
                      );

                //rek_serviceに必要なデータを変数に代入
                $service_code =  $array_2_7131_2['サービスコード上2桁'].$array_2_7131_2['サービスコード下4桁'];
                $service_unit =  $array_2_7131_2['サービス単位数'];
                $tekiyo =  $array_2_7131_2['摘要'];

                //rek_pid抽出
                $sql = "SELECT pid FROM rek_patient WHERE skm = '$skm' AND jigyosya = '$jigyosya' AND kaigo_hoban = '$kaigo_hoban' AND kaigo_hihoban = '$kaigo_hihoban'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $pid = $row['pid'];
                }

                //SESSIONに格納
                $service_array = array($sid,$pid,$kaigo_hihoban,$birth,$service_code,$service_unit,$tekiyo);
                $_SESSION["service_".$sid] = $service_array;

                //Display Service Records
                echo "<tr>";
                echo '<td><input type="checkbox" name="check_list[]" value="'.$sid.'"></td>';
                foreach($_SESSION["service_".$sid] as $key=>$value){
                    echo "<td>".$value."</td>\n";
                }
                echo "</tr>\n";

                //rek_serviceにデータを格納
                /*
                if ($pid!=0) {
                    $sql = "INSERT INTO rek_service (pid,service_code,service_unit,tekiyo) SELECT '$pid','$service_code','$service_unit','$tekiyo'  FROM dual WHERE NOT EXISTS (SELECT pid FROM rek_service WHERE pid = '$pid' AND service_code = '$service_code' AND service_unit = '$service_unit' AND tekiyo = '$tekiyo')";
                    $dbh->query($sql);
                }
                */


            }   elseif ($data[3] == 10 ) {
                //echo "<h4>請求額集計データレコード</h4>\n";
                $array_2_7131_10 = array('レコード種別'=>$data[0],
                       'レコード番号'=>$data[1],
                       '2'=>$data[2],
                       '3'=>$data[3],
                       '請求月'=>$data[4],
                       '事業者番号'=>$data[5],
                       '保険者番号'=>$data[6],
                       '被保険者番号'=>$data[7],
                       'サービス種類コード'=>$data[8],
                       '9'=>$data[9],
                       '10'=>$data[10],
                       '給付単位数'=>$data[15],
                       '単位数単価'=>$data[16],
                       '保険請求額'=>$data[17],
                       '利用者負担額'=>$data[18],
                       '公費分単位数'=>$data[19],
                       '公費請求額'=>$data[20],
                       '21'=>$data[21],
                       '22'=>$data[22],
                       '23'=>$data[23],
                       '24'=>$data[24],
                       '25'=>$data[25],
                       '26'=>$data[26],
                       '27'=>$data[27],
                       '28'=>$data[28],
                       '29'=>$data[29],
                       '30'=>$data[30],
                       '31'=>$data[31],
                       '32'=>$data[32],
                       '33'=>$data[33],
                       '34'=>$data[34],
                       '35'=>$data[35],
                       '36'=>$data[36],
                       '37'=>$data[37],
                       '38'=>$data[38],
                       '39'=>$data[39],
                      );
            }
        }

    }   elseif ($data[0] == 3) {
        //echo "<h3>エンドレコード</h3><br/>\n";
        $array_3 = array('レコード種別'=>$data[0],
                       'レコード番号'=>$data[1],
                      );
    }
}

echo '<input type="submit" name="submit" value="SUBMIT"/></form>';

?>

</body>
</html>
