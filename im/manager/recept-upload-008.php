<?php
ini_set("display_errors",0);
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
include_once "../class/functions.php";
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

/**
 * レセプト特記事項から負担割合を決定する
 *
 * @param string $tokujikou     レセプト特記事項（例: "41", "4041", "014143"）
 * @param int    $defaultRatio  種別マスタ等から取得したデフォルト割合
 * @return int
 */
function getRatioByTokujikou(string $tokujikou, int $defaultRatio): int
{
    // 将来拡張用：特記事項コード → 強制割合
    $forceRatioMap = [
        '41' => 20,
        '43' => 20,
        // '45' => 30,
        // '99' => 0,
    ];

    if ($tokujikou === '') {
        return $defaultRatio;
    }

    // 2桁ずつ分割
    $codes = str_split($tokujikou, 2);

    foreach ($codes as $code) {
        if (isset($forceRatioMap[$code])) {
            return $forceRatioMap[$code];
        }
    }

    return $defaultRatio;
}

//DB Connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


//Common Part
$file = $_FILES["upfile"]["tmp_name"];
$handle = fopen ( $file, "r" );
$i = 1;

?>

<div id="breadcrumb">
        <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="./receipt_select.php">レセプトデータ取り込み</a>&nbsp;&gt;&nbsp;レセプト取込データ
        </div>

<h2 class="title_name">レセプトデータの取り込み</h2>

<form method="post" action="recept-upload-result-001.php" enctype="multipart/form-data">
<div class='tbl' align='center'>
システムに取り込む対象を選択の上「取り込み実行」ボタンを押してください
<br /><br />

<table border = '0' cellpadding=0 cellspacing=0><th>取込対象<br><input type="checkbox" id="all"></th><th>レセプト番号</th><th>患者氏名</th><th>診療月</th></tr>


<?php
$sid = 0;
$ko_flag = false;
while ( ( $data = fgetcsv ( $handle, 200) ) !== FALSE ) {
    $array_ss = $array_co = array();

    if ($data[0] == "UK") {

        $name = mb_convert_encoding("{$data[6]}", "UTF-8", "SJIS");

        $array_uk = array();
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

    }
    if ($data[0] == "IR") {

        $pid = $skm = $srm = $hoid = $koid = $hoban = $hihoki = $hihoba = $futansya = $jukyusya = "";
        $i++;

        //請求年月(GYYMM)を西暦に変換して$skmに格納
				/*
        $src = $data[6];
        $g = mb_substr($src, 0, 1, 'UTF-8');
        $y = mb_substr($src, 1, 2, 'UTF-8');
        $m = mb_substr($src, 3, 2, 'UTF-8');
        if ($g === '5') $y += 2018;
        elseif ($g === '4') $y += 1988;
        elseif ($g === '3') $y += 1925;
        elseif ($g === '2') $y += 1911;
        elseif ($g === '1') $y += 1868;
				*/
        #$skm = $y.$m;
				$skm = convertDate($data[6]);

        $array_ir = array();
        $array_ir = array('レコード名'=>'医療機関情報レコード',
                       '審査支払機関'=>$data[1],
                       '都道府県'=>$data[2],
                       '点数表'=>$data[3],
                       '医療機関コード'=>$data[4],
                       '予備'=>$data[5],
                       '請求年月'=>$skm,
                       '電話番号'=>$data[7],
                       '届出'=>$data[8],
                      );

    }
    if ($data[0] == "RE") {
        $ko_flag = false;
        //名前の文字化け回避して$nameに格納
        $name = mb_convert_encoding("{$data[4]}", "UTF-8", "SJIS");
        //診療年月(GYYMM)を西暦に変換して診療月($srm)に格納
				/*
        $src = $data[3];
        $g = mb_substr($src, 0, 1, 'UTF-8');
        $y = mb_substr($src, 1, 2, 'UTF-8');
        $m = mb_substr($src, 3, 2, 'UTF-8');
        $d = mb_substr($src, 5, 2, 'UTF-8');
        if ($g === '5') $y += 2018;
        elseif ($g === '4') $y += 1988;
        elseif ($g === '3') $y += 1925;
        elseif ($g === '2') $y += 1911;
        elseif ($g === '1') $y += 1868;
        $srm = $y.$m;
				*/
				$srm = convertDate($data[3]);
        //生年月日(GYYMMDD)を西暦に変換して$birthに格納
				/*
        $src = $data[6];
        $g = mb_substr($src, 0, 1, 'UTF-8');
        $y = mb_substr($src, 1, 2, 'UTF-8');
        $m = mb_substr($src, 3, 2, 'UTF-8');
        $d = mb_substr($src, 5, 2, 'UTF-8');
        if ($g === '5') $y += 2018;
        elseif ($g === '4') $y += 1988;
        elseif ($g === '3') $y += 1925;
        elseif ($g === '2') $y += 1911;
        elseif ($g === '1') $y += 1868;
        $birth = $y.$m.$d;
				*/
				$birth = convertDate($data[6]);

        $array_re = array();
        $array_re = array('レコード名'=>'レセプト共通レコード',
                       'レセプト番号'=>$data[1],
                       'レセプト種別'=>$data[2],
                       '診療年月'=>$srm,
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
        $rid = $array_re['レセプト番号'];

        $_SESSION["patientlist_".$rid] = array();
        $_SESSION["patient_".$rid] = array();
        $_SESSION["shinryo_".$rid] = array();

        //----------------------------------
        //  診療データ取込み対象患者の一覧を表示
        //----------------------------------

        //患者一覧をSESSIONに格納
        $patient_array = array($rid,$name,$srm);
        $_SESSION["patientlist_".$rid] = $patient_array;

        //患者一覧を表示
        echo "<tr>";

				$existing = "";
        //既に登録があるかのチェック(名前と診療月が重複のもの)
				$tmp_name = str_replace(array(" ", "　"), "", $name);
/*        $sql = "SELECT patient_name
                FROM patient_info INNER JOIN re_shinryo ON patient_info.original_pid = re_shinryo.original_pid
                WHERE patient_name = '$name'
                    AND SUBSTRING(srd,1,6) = '$srm'";
*/
#これ間違い
/*
				$sql = "SELECT patient_name
								FROM patient_info INNER JOIN re_shinryo ON patient_info.original_pid = re_shinryo.original_pid
								WHERE replace(replace(patient_name,' ',''),'　','') = '$tmp_name' ";
*/
/*
				$sql = "SELECT patient_name
								FROM patient_info INNER JOIN re_shinryo ON patient_info.original_pid = re_shinryo.original_pid
								WHERE patient_name = '$name'
										AND SUBSTRING(srd,1,6) = '$srm' AND patient_birth = '$birth'";
*/

                $sql = "SELECT patient_name FROM patient_info WHERE patient_name = '$name' AND patient_birth = '$birth' AND disp = 0 ";
                        
#echo $sql."<br>\n";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
            $existing = $row['patient_name'];
        }

#################################################################
#【レセプトデータ取り込み時の仕様】
#・既に患者登録が「ある」ものにチェックを入れる
#（背景）
#膨大な患者リストから取り込む人をピックアップするのは難しい。
#よって、登録が過去になくて、今度から登録する人に対して手動でチェックを入れる
#################################################################
        if ($existing){
						//ある場合は選択済のチェックボックスを表示
						echo '<td><input type="checkbox" class="cbgroup1" name="check_list[]" value="'.$rid.'" checked="checked"></td>';

            //ある場合は未選択のチェックボックスを表示
            #echo '<td><input type="checkbox" class="cbgroup1" name="check_list[]" value="'.$rid.'"></td>';
        } else {
						//ない場合は未選択のチェックボックス表示
						echo '<td><input type="checkbox" class="cbgroup1" name="check_list[]" value="'.$rid.'"></td>';

            //ない場合は選択済のチェックボックス表示
            #echo '<td><input type="checkbox" class="cbgroup1" name="check_list[]" value="'.$rid.'" checked="checked"></td>';
        }

        foreach($_SESSION["patientlist_".$rid] as $key=>$value){
            echo "<td>".$value."</td>\n";
        }
        echo "</tr>\n";

        //$_SESSION["patientlist_".$rid] = array();

    }
    if ($data[0] == "HO") {

        //保険配列に格納$name = mb_convert_encoding("{$data[4]}", "UTF-8", "SJIS");
        $array_ho = array();
        $array_ho = array('レコード名'=>'保険者レコード',
                       '保険者番号'=>$data[1],
                       '被保険者記号'=>$data[2],
                       '被保険者番号'=>mb_convert_kana(mb_convert_encoding($data[3], "UTF-8", "SJIS"), 'kvrn'),
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

        //変数に格納
        $payer = $array_uk['審査支払機関'];
        $prefecture = $array_uk['都道府県'];
        $irkkcode = $array_uk['医療機関コード'];
        $irkkname = $array_uk['医療機関名'];
        $irkk_tel = $array_ir['電話番号'];
        $skm = $array_ir['請求年月'];
        $srm = $array_re['診療年月'];
        $name = $array_re['患者氏名'];
        $sex = $array_re['性別'];
        $birth = $array_re['生年月日'];
        $hoban = $array_ho['保険者番号'];
        $hihoki = $array_ho['被保険者記号'];
        $hihoban = $array_ho['被保険者番号'];

        //医療保険金額が指定されている場合はデータ格納
        $max_copayment = $array_ho['医療保険金額(円)'];
        if($max_copayment != ""){
            $_SESSION["patientlist_".$rid][3] = $max_copayment;
        }
        $max_copayment = "";

        //-------------------------------------
        //レセプト患者DB（re_patient）にデータ格納
        //-------------------------------------

        /* 既に登録が無い場合のみINSERT（医療機関コード／請求月／名前／生年月日のAND） */
        $sql = "INSERT INTO re_patient (payer,
                                        prefecture,
                                        irkkcode,
                                        irkkname,
                                        irkk_postal_code,
                                        irkk_address,
                                        irkk_tel,
                                        skm,
                                        srm,
                                        name,
                                        sex,
                                        birth,
                                        hoban,
                                        hihoki,
                                        hihoban)
                SELECT '$payer',
                        '$prefecture',
                        '$irkkcode',
                        '$irkkname',
                        '$irkk_postal_code',
                        '$irkk_address',
                        '$irkk_tel',
                        '$skm',
                        '$srm',
                        '$name',
                        '$sex',
                        '$birth',
                        '$hoban',
                        '$hihoki',
                        '$hihoban'
                FROM dual WHERE NOT EXISTS (SELECT pid
                                            FROM re_patient
                                            WHERE name = '$name'
                                                AND birth = '$birth')";
        $dbh->query($sql);

    }
    if ($data[0] == "KO") {
        $ko_flag = true;

        $koid = $data[1];

        //公費配列に格納
        $array_ko = array();
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

        //変数に格納
        $payer = $array_uk['審査支払機関'];
        $prefecture = $array_uk['都道府県'];
        $irkkcode = $array_uk['医療機関コード'];
        $irkkname = $array_uk['医療機関名'];
        $irkk_tel = $array_ir['電話番号'];
        $skm = $array_ir['請求年月'];
        $srm = $array_re['診療年月'];
        $name = $array_re['患者氏名'];
        $sex = $array_re['性別'];
        $birth = $array_re['生年月日'];
        $futansya = $array_ko['負担者番号'];
        $jukyusya = $array_ko['受給者番号'];


        $max_copayment = $array_ko['公費'];
        if($max_copayment != ""){
            $_SESSION["patientlist_".$rid][3] = $max_copayment;
        }
        $max_copayment = "";


        //-------------------------------------
        //レセプト患者DB（re_patient）にデータ格納
        //-------------------------------------

        /* 既に登録があるか否かPID検索（名前／生年月日のAND） */
        $sql = "SELECT pid
                FROM re_patient
                WHERE name = '$name'
                    AND birth = '$birth'";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
            $pid = $row['pid'];
        }

        if ($pid == 0) {
            /* 登録が無かった場合は全項目INSERT */
            $sql = "INSERT INTO re_patient (payer,
                                            prefecture,
                                            irkkcode,
                                            irkkname,
                                            irkk_postal_code,
                                            irkk_address,
                                            irkk_tel,
                                            skm,
                                            srm,
                                            name,
                                            sex,
                                            birth,
                                            futansya,
                                            jukyusya)
                    VALUES ('$payer',
                            '$prefecture',
                            '$irkkcode',
                            '$irkkname',
                            '$irkk_postal_code',
                            '$irkk_address',
                            '$irkk_tel',
                            '$skm',
                            '$srm',
                            '$name',
                            '$sex',
                            '$birth',
                            '$futansya',
                            '$jukyusya')";
            $dbh->query($sql);
        } else {
            /* 登録があった場合は負担者／受給者コードをUPDATE */
            $sql = "UPDATE re_patient
                    SET futansya = '$futansya',
                        jukyusya = '$jukyusya'
                    WHERE pid = '$pid'";
            $dbh->query($sql);
        }

    }

    if ($data[0] == "HS") {

        $irkk_exists = $patient_exists = "";

        $array_hs = array();
        $array_hs = array('レコード名'=>'傷病名部位レコード',
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

        //---------------------------------------
        //  医療機関テーブル(account_info)へ登録
        //---------------------------------------

        //account_infoにirkknameがすでに登録あるか確認($original_irkkの有無)
        $sql = "SELECT original_irkkcode
                FROM account_info
                WHERE irkkname = '$irkkname'";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
            $irkk_exists = $row['original_irkkcode'];
        }

        ////account_infoに新規登録（既存であればUPDATE,新規であればINSERT）
        if ($irkk_exists) {
            $sql = "UPDATE account_info
                    SET irkkname = '$irkkname', tel = '$irkk_tel'
                    WHERE original_irkkcode = '$original_irkkcode'";
            $dbh->query($sql);
        } else {
            $sql = "INSERT INTO account_info (irkkname,tel)
                    VALUES ('$irkkname','$irkk_tel')";
            $dbh->query($sql);
        }


        //-----------------------------------------------------
        //  re_patientにaccount_infoの情報を持ってくる → !破棄予定!
        //-----------------------------------------------------

        #STEP1# re_patientより対象のirkkcode取得
        $sql = "SELECT irkkcode
                FROM re_patient
                WHERE name = '$name'
                    AND birth = '$birth'";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
            $irkkcode = $row['irkkcode'];
        }

        #STEP2# account_infoよりoriginal_irkkcode,postal_code,address,tel取得
        $sql = "SELECT original_irkkcode,irkk_postal_code,address,tel
                FROM account_info
                WHERE irkkname = '$irkkname'";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
            $original_irkkcode = $row['original_irkkcode'];
            $irkk_postal_code = $row['irkk_postal_code'];
            $irkk_address = $row['address'];
            $irkk_tel = $row['tel'];
        }

        #STEP3# re_patientにoriginal_irkkcodeを登録
        $sql = "UPDATE re_patient
                SET original_irkkcode = '$original_irkkcode',
                    irkk_postal_code = '$irkk_postal_code',
                    irkk_address = '$irkk_address',
                    irkk_tel = '$irkk_tel'
                WHERE irkkcode = '$irkkcode'";
        $dbh->query($sql);


        //----------------------------------
        //  患者テーブル（patient_info）へ登録
        //----------------------------------

        //患者一覧をSESSIONに格納（被保険者番号等含む）
        $patient_array = array($rid,$name,$birth,$hihoki,$hihoban,$jukyusya,$original_irkkcode);
        $_SESSION["patient_".$rid][$pid] = $patient_array;


        //-----------------------------------------------------
        //  re_patientにpatient_infoからoriginal_pidを持ってくる
        //-----------------------------------------------------

        #STEP1# re_patientより対象のpid取得
        $sql = "SELECT pid
                FROM re_patient
                WHERE name = '$name'
                    AND birth = '$birth'";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
            $pid = $row['pid'];
        }

        #STEP2# patient_infoよりoriginal_pid取得
        $sql = "SELECT original_pid
                FROM patient_info
                WHERE patient_name = '$name'
                    AND patient_birth = '$birth' AND disp = 0 ";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
            $original_pid = $row['original_pid'];
        }

        #STEP3# re_patientにoriginal_idを登録
        $sql = "UPDATE re_patient
                SET original_pid = '$original_pid'
                WHERE pid = '$pid'";
        $dbh->query($sql);



    }
    if ($data[0] == "SS") {

        $syubetsu = $array_re['レセプト種別'];
        $sql = "SELECT * FROM syubetsu_code WHERE code = $syubetsu";
        $stmt = $dbh->query($sql);
        $syubetsu_copy = $stmt->fetchALL(PDO::FETCH_ASSOC);
        /*switch($array_re['レセプト特記事項']){
            case "41":
            case "43":
            case "4041":
                $ratio = 20;    #これでまでの処理では1割と判定するが、今は2割と取る
                break;
            default:
                $ratio = isset($syubetsu_copy[0]['ratio']) ? intval($syubetsu_copy[0]['ratio']) : 0;
                break;
        }*/
        $defaultRatio = isset($syubetsu_copy[0]['ratio'])
            ? intval($syubetsu_copy[0]['ratio'])
            : 0;

        $ratio = getRatioByTokujikou(
            $array_re['レセプト特記事項'] ?? '',
            $defaultRatio
        );
        
        $upper = $syubetsu_copy[0]['upper'];

        for ($j = 78 ; $j <= 108; $j++) {
            if ( $data[$j] >= 1 ) {
                $sid += 1;
                $original_irkkcode = $original_pid = $pid = "";
                $dt = sprintf('%02d', $j - 77);
                $srd = $srm . $dt;
                if ($data[1] != "") {
                    $sql = "SELECT * FROM shikibetsu_code WHERE code = $data[1]";
                    $stmt = $dbh->query($sql);
                    $shikibetsu = $stmt->fetchALL(PDO::FETCH_ASSOC);
                } else {
                    $shikibetsu = "";
                }

                $array_ss = array();
                $array_ss = array('レコード名'=>'歯科診療行為レコード',
                               '診療日'=>$srd,
                               '診療識別'=>$data[1],
                               '負担区分'=>$data[2],
                               '診療行為'=>$data[3],
                               '点数'=>$data[76],
                               '回数'=>$data[$j],
                               '種別'=>$syubetsu,
                               '割合'=>$ratio,
                               '上限'=>$upper,
                              );

                $sbt = $array_ss['診療識別'];
                $srk = $array_ss['診療行為'];

                //$srkでkouiテーブルから$categoryを抽出
                $sql = "SELECT category,koui FROM koui_code WHERE code = '$srk'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $category = $row['category'];
                    $shinryo_name = $row['koui'];
                }
                if($category==''){
                    $category = '-';
                }

                $tns = isset($array_ss['点数']) ? intval($array_ss['点数']) : 0;
                $kaisu = isset($array_ss['回数']) ? intval($array_ss['回数']) : 0;
                $ftn = $array_ss['負担区分'];

                $copayment = $tns * 10 * $ratio / 100;




                //account_infoからoriginal_irkkcode抽出（←--重複？）
                $sql = "SELECT original_irkkcode
                        FROM account_info
                        WHERE irkkname = '$irkkname'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_irkkcode = $row['original_irkkcode'];
                }

                //patient_infoからoriginal_pid抽出（←--重複？）
                $sql = "SELECT original_pid
                        FROM patient_info
                        WHERE patient_name = '$name'
                            AND patient_birth = '$birth' AND disp = 0 ";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_pid = $row['original_pid'];
                }

                //re_patientからrecept_pid抽出（←--重複？）
                $sql = "SELECT pid
                        FROM re_patient
                        WHERE name = '$name'
                            AND birth = '$birth'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $pid = $row['pid'];
                }


                //SESSIONに診療行為データを格納
                if($ko_flag){
                    $ratio = 0;
                    $copayment = 0;
                }
                $shinryo_array = array($rid,$sid,$original_irkkcode,$original_pid,$pid,$name,$srd,$sbt,$srk,$category,$shinryo_name,$tns,$kaisu,$ftn,$syubetsu,$ratio,$copayment);
                $_SESSION["shinryo_".$rid][$sid] = $shinryo_array;

            }
        }
    }
    if ($data[0] == "SI") {

        $syubetsu = $array_re['レセプト種別'];
        $sql = "SELECT * FROM syubetsu_code WHERE code = $syubetsu";
        $stmt = $dbh->query($sql);
        $syubetsu_copy = $stmt->fetchALL(PDO::FETCH_ASSOC);
        #$ratio = $syubetsu_copy[0]['ratio'];
        /*switch($array_re['レセプト特記事項']){
            case "41":
            case "43":
            case "4041":
                $ratio = 20;    #これでまでの処理では1割と判定するが、今は2割と取る
                break;
            default:
                $ratio = isset($syubetsu_copy[0]['ratio']) ? intval($syubetsu_copy[0]['ratio']) : 0;
                break;
        }*/
        $defaultRatio = isset($syubetsu_copy[0]['ratio'])
            ? intval($syubetsu_copy[0]['ratio'])
            : 0;

        $ratio = getRatioByTokujikou(
            $array_re['レセプト特記事項'] ?? '',
            $defaultRatio
        );
        $upper = $syubetsu_copy[0]['upper'];

        for ($j = 7 ; $j <= 37; $j++) {
            if ( $data[$j] >= 1 ) {
                $sid += 1;
                $original_irkkcode = $original_pid = $pid = "";
                $dt = sprintf('%02d', $j - 6);
                $srd = $srm . $dt;
                if ($data[1] != "") {
                    $sql = "SELECT * FROM shikibetsu_code WHERE code = $data[1]";
                    $stmt = $dbh->query($sql);
                    $shikibetsu = $stmt->fetchALL(PDO::FETCH_ASSOC);
                } else {
                    $shikibetsu = "";
                }

                $array_si = array();
                $array_si = array('レコード名'=>'歯科診療行為レコード',
                               '診療日'=>$srd,
                               '診療識別'=>$data[1],
                               '負担区分'=>$data[2],
                               '診療行為'=>$data[3],
                               '数量データ'=>$data[4],
                               '点数'=>$data[5],
                               '回数'=>$data[$j],
                              );

                $sbt = $array_si['診療識別'];
                $srk = $array_si['診療行為'];

                //$srkでkouiテーブルから$categoryを抽出
                $sql = "SELECT category,koui FROM koui_code WHERE code = '$srk'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $category = $row['category'];
                    $shinryo_name = $row['koui'];
                }
                if($category==''){
                    $category = '-';
                }

                $tns = $array_si['点数'];
                $kaisu = $array_si['回数'];
                $ftn = $array_si['負担区分'];

                $copayment = $tns * 10 * $ratio / 100;




                //account_infoからoriginal_irkkcode抽出（←--重複？）
                $sql = "SELECT original_irkkcode
                        FROM account_info
                        WHERE irkkname = '$irkkname'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_irkkcode = $row['original_irkkcode'];
                }

                //patient_infoからoriginal_pid抽出（←--重複？）
                $sql = "SELECT original_pid
                        FROM patient_info
                        WHERE patient_name = '$name'
                            AND patient_birth = '$birth' AND disp = 0 ";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_pid = $row['original_pid'];
                }

                //re_patientからrecept_pid抽出（←--重複？）
                $sql = "SELECT pid
                        FROM re_patient
                        WHERE name = '$name'
                            AND birth = '$birth'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $pid = $row['pid'];
                }


                //SESSIONに診療行為データを格納
                if($ko_flag){
                    $ratio = 0;
                    $copayment = 0;
                }
                $shinryo_array = array($rid,$sid,$original_irkkcode,$original_pid,$pid,$name,$srd,$sbt,$srk,$category,$shinryo_name,$tns,$kaisu,$ftn,$syubetsu,$ratio,$copayment);
                $_SESSION["shinryo_".$rid][$sid] = $shinryo_array;

            }
        }
    }
    if ($data[0] == "IY") {

        $syubetsu = $array_re['レセプト種別'];
        $sql = "SELECT * FROM syubetsu_code WHERE code = $syubetsu";
        $stmt = $dbh->query($sql);
        $syubetsu_copy = $stmt->fetchALL(PDO::FETCH_ASSOC);
        #$ratio = $syubetsu_copy[0]['ratio'];
        /*switch($array_re['レセプト特記事項']){
            case "41":
            case "43":
            case "4041":
                $ratio = 20;    #これでまでの処理では1割と判定するが、今は2割と取る
                break;
            default:
                $ratio = isset($syubetsu_copy[0]['ratio']) ? intval($syubetsu_copy[0]['ratio']) : 0;
                break;
        }*/
        $defaultRatio = isset($syubetsu_copy[0]['ratio'])
            ? intval($syubetsu_copy[0]['ratio'])
            : 0;

        $ratio = getRatioByTokujikou(
            $array_re['レセプト特記事項'] ?? '',
            $defaultRatio
        );
        $upper = $syubetsu_copy[0]['upper'];

        for ($j = 8 ; $j <= 38; $j++) {
            if ( $data[$j] >= 1 ) {
                $sid += 1;
                $original_irkkcode = $original_pid = $pid = "";
                $dt = sprintf('%02d', $j - 7);
                $srd = $srm . $dt;
                if ($data[1] != "") {
                    $sql = "SELECT * FROM shikibetsu_code WHERE code = $data[1]";
                    $stmt = $dbh->query($sql);
                    $shikibetsu = $stmt->fetchALL(PDO::FETCH_ASSOC);
                } else {
                    $shikibetsu = "";
                }

                $array_iy = array();
                $array_iy = array('レコード名'=>'歯科診療行為レコード',
                               '診療日'=>$srd,
                               '診療識別'=>$data[1],
                               '負担区分'=>$data[2],
                               '医薬品コード'=>$data[3],
                               '使用量'=>$data[4],
                               '点数'=>$data[5],
                               '回数'=>$data[$j],
                               '医薬品区分'=>$data[7],
                              );

                $sbt = $array_iy['診療識別'];
                $srk = $array_iy['医薬品コード'];

                //$srkでkouiテーブルから$categoryを抽出
                $sql = "SELECT category,koui FROM koui_code WHERE code = '$srk'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $category = $row['category'];
                    $shinryo_name = $row['koui'];
                }
                if($category==''){
                    $category = '-';
                }

                $tns = isset($array_iy['点数']) ? intval($array_iy['点数']) : 0;
                $kaisu = isset($array_iy['回数']) ? intval($array_iy['回数']) : 0;
                $ftn = $array_iy['負担区分'];

                $copayment = $tns * 10 * $ratio / 100;




                //account_infoからoriginal_irkkcode抽出（←--重複？）
                $sql = "SELECT original_irkkcode
                        FROM account_info
                        WHERE irkkname = '$irkkname'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_irkkcode = $row['original_irkkcode'];
                }

                //patient_infoからoriginal_pid抽出（←--重複？）
                $sql = "SELECT original_pid
                        FROM patient_info
                        WHERE patient_name = '$name'
                            AND patient_birth = '$birth' AND disp = 0 ";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_pid = $row['original_pid'];
                }

                //re_patientからrecept_pid抽出（←--重複？）
                $sql = "SELECT pid
                        FROM re_patient
                        WHERE name = '$name'
                            AND birth = '$birth'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $pid = $row['pid'];
                }


                //SESSIONに診療行為データを格納
                if($ko_flag){
                    $ratio = 0;
                    $copayment = 0;
                }
                $shinryo_array = array($rid,$sid,$original_irkkcode,$original_pid,$pid,$name,$srd,$sbt,$srk,$category,$shinryo_name,$tns,$kaisu,$ftn,$syubetsu,$ratio,$copayment);
                $_SESSION["shinryo_".$rid][$sid] = $shinryo_array;

            }
        }
    }
    if ($data[0] == "TO") {

        $syubetsu = $array_re['レセプト種別'];
        $sql = "SELECT * FROM syubetsu_code WHERE code = $syubetsu";
        $stmt = $dbh->query($sql);
        $syubetsu_copy = $stmt->fetchALL(PDO::FETCH_ASSOC);
        #$ratio = $syubetsu_copy[0]['ratio'];
        /*switch($array_re['レセプト特記事項']){
            case "41":
            case "43":
            case "4041":
                $ratio = 20;    #これでまでの処理では1割と判定するが、今は2割と取る
                break;
            default:
                $ratio = $syubetsu_copy[0]['ratio'];
                break;
        }*/
        $defaultRatio = isset($syubetsu_copy[0]['ratio'])
            ? intval($syubetsu_copy[0]['ratio'])
            : 0;

        $ratio = getRatioByTokujikou(
            $array_re['レセプト特記事項'] ?? '',
            $defaultRatio
        );
        $upper = $syubetsu_copy[0]['upper'];

        for ($j = 14 ; $j <= 44; $j++) {
            if ( $data[$j] >= 1 ) {
                $sid += 1;
                $original_irkkcode = $original_pid = $pid = "";
                $dt = sprintf('%02d', $j - 13);
                $srd = $srm . $dt;
                if ($data[1] != "") {
                    $sql = "SELECT * FROM shikibetsu_code WHERE code = $data[1]";
                    $stmt = $dbh->query($sql);
                    $shikibetsu = $stmt->fetchALL(PDO::FETCH_ASSOC);
                } else {
                    $shikibetsu = "";
                }

                $array_to = array();
                $array_to = array('レコード名'=>'歯科診療行為レコード',
                               '診療日'=>$srd,
                               '診療識別'=>$data[1],
                               '負担区分'=>$data[2],
                               '特定器材コード'=>$data[3],
                               '使用量'=>$data[4],
                               '点数'=>$data[12],
                               '回数'=>$data[$j],
                              );

                $sbt = $array_to['診療識別'];
                $srk = $array_to['特定器材コード'];

                //$srkでkouiテーブルから$categoryを抽出
                $sql = "SELECT category,koui FROM koui_code WHERE code = '$srk'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $category = $row['category'];
                    $shinryo_name = $row['koui'];
                }
                if($category==''){
                    $category = '-';
                }

                $tns = $array_to['点数'];
                $kaisu = $array_to['回数'];
                $ftn = $array_to['負担区分'];

                $copayment = $tns * 10 * $ratio / 100;




                //account_infoからoriginal_irkkcode抽出（←--重複？）
                $sql = "SELECT original_irkkcode
                        FROM account_info
                        WHERE irkkname = '$irkkname'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_irkkcode = $row['original_irkkcode'];
                }

                //patient_infoからoriginal_pid抽出（←--重複？）
                $sql = "SELECT original_pid
                        FROM patient_info
                        WHERE patient_name = '$name'
                            AND patient_birth = '$birth' AND disp = 0 ";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $original_pid = $row['original_pid'];
                }

                //re_patientからrecept_pid抽出（←--重複？）
                $sql = "SELECT pid
                        FROM re_patient
                        WHERE name = '$name'
                            AND birth = '$birth'";
                $stmt = $dbh->query($sql);
                foreach ($stmt as $row) {
                    $pid = $row['pid'];
                }


                //SESSIONに診療行為データを格納
                if($ko_flag){
                    $ratio = 0;
                    $copayment = 0;
                }
                $shinryo_array = array($rid,$sid,$original_irkkcode,$original_pid,$pid,$name,$srd,$sbt,$srk,$category,$shinryo_name,$tns,$kaisu,$ftn,$syubetsu,$ratio,$copayment);
                $_SESSION["shinryo_".$rid][$sid] = $shinryo_array;

            }
        }
    }
    if ($data[0] == "CO") {
        $text_data = mb_convert_encoding("{$data[4]}", "UTF-8", "SJIS");

        $array_co = array();
        $array_co = array('レコード名'=>'コメントレコード',
                       '診療識別'=>$data[1],
                       '負担区分'=>$data[2],
                       'コメント'=>$data[3],
                       '文字データ'=>$text_data,
                       '歯式(コメント)'=>$data[5],
                       '予備'=>$data[6],
                       '予備'=>$data[7],
                       '予備'=>$data[8],
                       '予備'=>$data[9],
                       '予備'=>$data[10],
                      );

        //PID抽出
        $sql = "SELECT pid
                FROM re_patient
                WHERE name = '$name'
                    AND birth = '$birth'";
        $stmt = $dbh->query($sql);
        foreach ($stmt as $row) {
        $pid = $row['pid'];
        }

        if ($pid!=0) {
            //コメントDBに格納
            $sql = "INSERT INTO re_comment (pid,comment)
                    SELECT '$pid','$name'
                    FROM dual
                    WHERE NOT EXISTS (SELECT pid
                                                FROM re_comment
                                                WHERE pid = '$pid'
                                                    AND comment = '$name')";
            $dbh->query($sql);
        }

    }
    if ($data[0] == "GO") {
        $array_go = array('レコード名'=>'診療報酬請求書レコード',
                       '総件数'=>$data[1],
                       '総合計点数'=>$data[2],
                       'マルチボリューム識別情報'=>$data[3],
                      );
    }
}

?>

</table>
<br /><br /><br />
<input type="submit" name="submit" value="取り込み実行"/></form>

<br /><br /><br /><br />
</div>
</div>
</body>
</html>
