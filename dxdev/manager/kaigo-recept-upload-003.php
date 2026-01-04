<?php
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

<script>
    // Shift選択機能
	$(function(){
		var checked_last = null;
		$('.cbgroup1').on('click', function(event){
			if (event.shiftKey && checked_last) {
				var $targets = $('.cbgroup1');
				var p1 = $targets.index(checked_last)
				var p2 = $targets.index(this)
				for (var i = Math.min(p1, p2); i <= Math.max(p1, p2); ++i) {
					$targets.get(i).checked = checked_last.checked;
				}
			} else {
				checked_last = this;
			}
		});
	});
    // 全選択&解除機能
    $(function() {
      $('#all').on("click",function(){
        $('.cbgroup1').prop("checked", $(this).prop("checked"));
      });
    });
</script>

<title>レセプト取込データ</title>
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
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Common Part
$file = $_FILES["upfile"]["tmp_name"];
$handle = fopen ( $file, "r" );
$i = 1;
$rid = 0;
$sid = 0;

?>


<div id="breadcrumb">
<a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="./receipt_select.php">レセプトデータ取り込み</a>&nbsp;&gt;&nbsp;レセプト取込データ
</div>

<h2 class="title_name">介護保険レセプトデータの取り込み</h2>

<div class='tbl' align='center'>
システムに取り込む対象を選択の上「取り込み実行」ボタンを押してください
<br /><br />
<?php

#echo "<table border = '0' cellpadding=0 cellspacing=0><th>取込対象<br><input type='checkbox' id='all' checked='checked'></th><th>レセプト患者番号</th><th>患者番号</th><th>診療月</th><th>患者名</th><th>保険者番号<th>被保険者番号</th><th>メモ</th></tr>\n";

#echo '<form method="post" action="kaigo-recept-upload-result-001.php" enctype="multipart/form-data">';

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
                       '診療月'=>$data[3],
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

        }   elseif ($data[2] == 7131 || $data[2] == 7132) {
            $sid++;

            if ($data[3] == 1 ) {
            //echo "<h3>患者データレコード</h3><br/>\n";
                //echo "<h4>被保険者情報データレコード</h4>\n";
                $array_2_7131_1 = array('レコード種別'=>$data[0],
                       'レコード番号'=>$data[1],
                       '2'=>$data[2],
                       '3'=>$data[3],
                       '診療月'=>$data[4],
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
                $srm =  $array_2_7131_1['診療月'];
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


                #すでにpatient_infoに紐付け済みのレコードを抽出
                $kaigo_hihoban_flag = false;
                $sql = "SELECT * FROM patient_info WHERE patient_kaigo_hihoban = '{$kaigo_hihoban}' and disp = 0 ";
                $stmt = $dbh->query($sql);
                $tmp = $stmt->fetchALL(PDO::FETCH_ASSOC);
                #echo "竹垣改変(これがおかしくしてる？)すでにpatient_infoに紐付け済みのレコードを抽出<br>\n";
                #print_r($tmp);
                $memo = "";
                $patient_name = "";
                $patient_kaigo_hihoban = "";
                if(count($tmp) == 1):
                    $kaigo_hihoban_flag = true;
                    foreach($tmp as $v):
                        $original_pid = $v['original_pid'];
                        $patient_kaigo_hihoban = $v['patient_kaigo_hihoban'];
                        $patient_name = $v['patient_name'];
                        
                    endforeach;
                elseif(count($tmp) > 1):
                    $memo = "複数人対象あり<br>\n";
                    foreach($tmp as $v):
                        $memo .= $v['original_pid']." ".$v['patient_name']." ".$v['patient_name']."<br>\n";
                        
                    endforeach;
                endif;

                //rek_patientにデータを格納
                #220224 kaigo_hihobanが存在する場合はUPDATE
                if($kaigo_hihoban_flag):
                    $sql = "UPDATE rek_patient SET original_pid = '{$original_pid}' WHERE kaigo_hihoban = '{$patient_kaigo_hihoban}'; ";
                else:
                    $sql = "INSERT INTO rek_patient (srm,jigyosya,kaigo_hoban,kaigo_hihoban,futansya,jukyusya,birth,sex,hoken_rate,kouhi_rate,totalcopayment) SELECT '$srm','$jigyosya','$kaigo_hoban','$kaigo_hihoban','$futansya','$jukyusya','$birth','$sex','$hoken_rate','$kouhi_rate','$totalcopayment' FROM dual WHERE NOT EXISTS (SELECT pid FROM rek_patient WHERE srm = '$srm' AND jigyosya = '$jigyosya' AND kaigo_hoban = '$kaigo_hoban' AND kaigo_hihoban = '$kaigo_hihoban' AND futansya = '$futansya' AND jukyusya = '$jukyusya' AND birth = '$birth' AND sex = '$sex')";
                endif;
                #echo $sql;
                #$dbh->query($sql);

                //-------------------------
                //  患者テーブルへ登録
                //-------------------------


                //既に登録があるかのチェック（既存のOriginal_pidを変数に格納）
                /*
                $sql = "SELECT original_pid FROM patient_info WHERE patient_birth = '$birth'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_pid = $row['original_pid'];
                }
                */

                $rid++;
                #####$_SESSION["k_patient_".$rid]=array();
                #####$_SESSION["service_".$rid]=array();
                $_SESSION['kaigo_data'][$rid]['original_pid'] = $original_pid;

                //rek_patientからpidを抽出→すでに登録がある場合紐付け
                /*$sql = "SELECT pid FROM rek_patient
                    WHERE srm = '{$rek_patient['srm']}' AND jigyosya = '{$rek_patient['jigyosya']}' AND kaigo_hoban = '{$rek_patient['kaigo_hoban']}' AND kaigo_hihoban = '{$rek_patient['kaigo_hihoban']}'";
                    */
                    $sql = "SELECT pid FROM rek_patient
                    WHERE jigyosya = '{$jigyosya}' AND kaigo_hoban = '{$kaigo_hoban}' AND kaigo_hihoban = '{$kaigo_hihoban}' AND delete_flag = 0 ";
                    #echo $sql."<br>\n";
                    $stmt = $dbh->query($sql);
                    $pid_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
                    $pid = "";
                    $pid_error_flag = 0;
                    if(count($pid_data) == 1){
                        $pid = $pid_data[0]['pid'];
                    }elseif(count($pid_data) > 1){
                        $pid_error_flag = 1;
                        $pid = $pid_data[0]['pid'];
                    }
                    $_SESSION['kaigo_data'][$rid]['pid'] = $pid;
                    $_SESSION['kaigo_data'][$rid]['pid_error_flag'] = $pid_error_flag;


                //患者一覧をSESSIONに格納
                #$patient_array = array($rid,$original_pid,$srm);
                #####$patient_array = array($rid,$original_pid,$srm, $patient_name,$patient_kaigo_hihoban,$memo);
                #####$_SESSION["patientlist_".$rid] = $patient_array;

                #220409
                # rek_patient	名前	コメント
                # 1	pid	患者ID
                # 2	original_pid	
                # 3	srm	診療月
                # 4	jigyosya	事業者番号
                # 5	kaigo_hoban	保険者番号
                # 6	kaigo_hihoban	被保険者番号
                # 7	futansya	負担者番号
                # 8	jukyusya	受給者番号
                # 9	birth	生年月日
                # 10 sex	性別コード
                # 11 hoken_rate	保険負担率
                # 12 kouhi_rate	公費負担率
                # 13 totalcopayment	利用者合計負担額

                $_SESSION['kaigo_data'][$rid]['rek_patient'] = array(
                    'srm' => $srm,
                    'jigyosya' => $jigyosya,
                    'kaigo_hoban' => $kaigo_hoban,
                    'kaigo_hihoban' => $kaigo_hihoban,
                    'futansya' => $futansya,
                    'jukyusya' => $jukyusya,
                    'birth' => $birth,
                    'sex' => $sex,
                    'hoken_rate' => $hoken_rate,
                    'kouhi_rate' => $kouhi_rate,
                    'totalcopayment' => $totalcopayment,

                    'patient_name' => $patient_name,
                    'memo' => $memo,
                );

                //患者一覧を表示
/*
                echo "<tr>";
                echo '<td><input type="checkbox" class="cbgroup1" name="check_list[]" value="'.$rid.'" checked="checked"></td>';
                
                #foreach($_SESSION["patientlist_".$rid] as $key=>$value){
                #    echo "<td>".$value."</td>\n";
                #}
                echo "<td>".$rid."</td>\n";
                echo "<td>".$_SESSION['kaigo_data'][$rid]['original_pid']."</td>\n";
                echo "<td>".$_SESSION['kaigo_data'][$rid]['rek_patient']['srm']."</td>\n";
                echo "<td>".$_SESSION['kaigo_data'][$rid]['rek_patient']['patient_name']."</td>\n";
                echo "<td>".$_SESSION['kaigo_data'][$rid]['rek_patient']['kaigo_hoban']."</td>\n";
                echo "<td>".$_SESSION['kaigo_data'][$rid]['rek_patient']['kaigo_hihoban']."</td>\n";
                echo "<td>".$_SESSION['kaigo_data'][$rid]['rek_patient']['memo']."</td>\n";
                
                echo "</tr>\n";*/

            }   elseif ($data[3] == 2 ) {
                //echo "<h4>給付費明細データレコード</h4>\n";
                $array_2_7131_2 = array('レコード種別'=>$data[0],
                       'レコード番号'=>$data[1],
                       '2'=>$data[2],
                       '3'=>$data[3],
                       '診療月'=>$data[4],
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
                $srm = $array_2_7131_2['診療月'];
                $service_code =  $array_2_7131_2['サービスコード上2桁'].$array_2_7131_2['サービスコード下4桁'];
                $service_unit =  $array_2_7131_2['単位数'];
                $kaisu =  $array_2_7131_2['回数'];
                $tekiyo =  $array_2_7131_2['摘要'];

                //tekiyoをカンマorピリオドで区切って配列$service_datesに格納
                #$service_dates = double_explode(',', '.', $tekiyo);
                #$service_times = sizeof($service_dates);

                //service_codeテーブルからservice_nameを抽出
                $service_name = "";
                $sql = "SELECT service_name FROM service_code
                        WHERE code = '$service_code'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $service_name = $row['service_name'];
                }

                //rek_patientからpidを抽出
                /*
                $sql = "SELECT pid FROM rek_patient
                        WHERE srm = '$srm' AND jigyosya = '$jigyosya' AND kaigo_hoban = '$kaigo_hoban' AND kaigo_hihoban = '$kaigo_hihoban'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $pid = $row['pid'];
                }*/
                


                //SESSIONに格納
                #####$patient_array = array($rid,$birth,$kaigo_hihoban,$jukyusya,$_SESSION['kaigo_data'][$rid]['original_pid']);
                #####$_SESSION["k_patient_".$rid][$pid] = $patient_array;

                #211212:srmを追加
                #$service_array = array($sid,$original_pid,$pid,$kaigo_hihoban,$birth,$service_code,$service_name,$service_unit,$kaisu,$tekiyo,$srm);
                #####$service_array = array($sid,$_SESSION['kaigo_data'][$rid]['original_pid'],$pid,$kaigo_hihoban,$birth,$service_code,$service_name,$service_unit,$kaisu,$tekiyo,$srm);
                #####$_SESSION["service_".$rid][$sid] = $service_array;

                #220409
                $_SESSION['kaigo_data'][$rid]['rek_service'][$sid] = array(

                    'srm' => $srm,
                    'service_code' => $service_code,
                    'service_name' => $service_name,
                    'service_unit' => $service_unit,
                    'kaisu' => $kaisu,
                    'tekiyo' => $tekiyo,
                );

                $original_pid = "";

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
#print_r($_SESSION);

echo "<table border = '0' cellpadding=0 cellspacing=0><th>取込対象<br><input type='checkbox' id='all' checked='checked'></th><th>レセプト患者番号</th><th>患者番号</th><th>診療月</th><th>患者名</th><th>保険者番号<th>被保険者番号</th><th>メモ</th></tr>\n";

echo '<form method="post" action="kaigo-recept-upload-result-001.php" enctype="multipart/form-data">';
foreach($_SESSION['kaigo_data'] as $rid => $rek):
//患者一覧を表示
echo "<tr>";
echo '<td><input type="checkbox" class="cbgroup1" name="check_list[]" value="'.$rid.'" checked="checked"></td>';
/*
foreach($_SESSION["patientlist_".$rid] as $key=>$value){
    echo "<td>".$value."</td>\n";
}*/
echo "<td>".$rid."</td>\n";
echo "<td>".$rek['original_pid']."</td>\n";
echo "<td>".$rek['rek_patient']['srm']."</td>\n";
echo "<td>".$rek['rek_patient']['patient_name']."</td>\n";
echo "<td>".$rek['rek_patient']['kaigo_hoban']."</td>\n";
echo "<td>".$rek['rek_patient']['kaigo_hihoban']."</td>\n";
echo "<td>";
if($rek['pid_error_flag'] == 1):
    echo "患者対象が2件以上重複しています<br>\n";
endif;
if($rek['original_pid'] != "" && $rek['pid'] != ""):
    echo "患者紐付け登録有り";
elseif($rek['original_pid'] == "" && $rek['pid'] != ""):
    echo "介護データ登録有り";
elseif($rek['original_pid'] == "" && $rek['pid'] == ""):
    echo "新規登録";
endif;
echo $rek['rek_patient']['memo']."</td>\n";

echo "</tr>\n";
endforeach;
?>
</table>
<br /><br /><br />
<input type="submit" name="submit" value="取り込み実行"/></form>
</div>

</div>
</div>
</body>
</html>
