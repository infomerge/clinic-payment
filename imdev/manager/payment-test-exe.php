<?php
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
date_default_timezone_set("Asia/Tokyo");

$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#ロボペイの振替スケジュールテーブルから次回の振替日を取得
$finish_date = date("Y-m-d H:i:s"); #手仕舞い時刻
$sql = "select * from rp_schedule where deadline_datetime > '{$finish_date}' order by deadline_datetime asc limit 1";
$stmt = $dbh->query($sql);
$transfer_date_array = $stmt->fetch(PDO::FETCH_ASSOC);
$transfer_date = $transfer_date_array['transfer_date'];
?>

<!DOCTYPE_html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>Robot Payment 送信テスト</title>
<?php
    $smarty->display( 'common/head_inc.tpl');
?>
</head>

<body>

<?php
    //Header Template
    $smarty->display( 'common/header.tpl' );
?>



<div id="wrap">
    <div class="content">
        <div id="breadcrumb">
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="patient_info_list.php">患者情報一覧</a>&nbsp;&gt;&nbsp;請求情報一覧&nbsp;&gt;&nbsp;RobotPayment送信処理結果
        </div>
        <h2 class="title_name">RobotPayment送信処理結果</h2>

<?php
$original_pid = $_GET["original_pid"];

$req_type = intval($_GET["req_type"]); #処理タイプ
$aid = "115106"; #店舗ID
$tday = "1"; #振替日

#患者情報
$nm = strval($_GET["nm"]);
$cid = strval($_GET["cid"]);
$reqid = strval($_GET["reqid"]);
#口座情報
$bac = strval($_GET["bac"]);
$brc = strval($_GET["brc"]);
$atype = intval($_GET["atype"]);
$anum = strval($_GET["anum"]);
$anm = strval($_GET["anm"]);
#住所情報
$po = strval($_GET["po"]);
$pre = strval($_GET["pre"]);
$ad1 = strval($_GET["ad1"]);
$ad2 = strval($_GET["ad2"]);
#振替情報
$amo = intval($_GET["amo"]);
$srm = strval($_GET["srm"]);

#$cod = sprintf('%07d', strval($original_pid)). "-" .$srm;
$cod = uniqid();

switch ($req_type) {
    /*
	case 0: #顧客番号発番
		$data = array(
			"aid" => $aid,
			"cmd" => $req_type,
			"tday" => $tday,
			"cod" => time(),
		);
		break;
    */
	case 1: #顧客登録
		$data = array(
			"aid" => $aid,
			"cmd" => $req_type,
			"tday" => $tday,
			"nm" => "$nm",
			"em" => DUMMYEMAIL,
			"bac" => $bac,
			"brc" => $brc,
			"atype" => $atype,
			"anum" => $anum,
			"anm" => $anm,
			"amo" => 0,
			"date" => $transfer_date,
			"type" => 1,
			"stat" => 0,
			"po" => $po,
			"pre" => $pre,
			"ad1" => $ad1,
			"ad2" => $ad2,
			"cod" => $cod,
		);
		break;
	case 2: #請求追加
		$data = array(
			"aid" => $aid,
			"cmd" => $req_type,
			"tday" => $tday,
			"cid" => $cid,
			"amo" => $amo,
			"date" => $transfer_date,
			"type" => 1,	#動作タイプ(1:単発 2:連続 3:従量)
			"stat" => BILLINGSTATUS,	#課金状態（0:停止中　1:稼働中）
      "cod" => $cod,
		);
		break;
    case 3: #請求情報変更
		$data = array(
			"aid" => $aid,
			"cmd" => $req_type,
			"tday" => $tday,
			"reqid" => $reqid,
			"amo" => $amo,
			"date" => $transfer_date,
			"stat" => BILLINGSTATUS,	#課金状態（0:停止中　1:稼働中）
      "cod" => $cod,
		);
		break;
    case 4: #顧客情報変更
		$data = array(
			"aid" => $aid,
			"cmd" => $req_type,
			"tday" => $tday,
      "cid" => $cid,
			"nm" => "$nm",
			"em" => DUMMYEMAIL,
			"bac" => $bac,
			"brc" => $brc,
			"atype" => $atype,
			"anum" => $anum,
			"anm" => $anm,
			"po" => $po,
			"pre" => $pre,
			"ad1" => $ad1,
			"ad2" => $ad2,
			"cod" => $cod,
		);
		break;
	default:
		echo "処理タイプが不正です";
}


########################################

$url = "https://credit.j-payment.co.jp/gateway/at_gateway.aspx";
$data = http_build_query($data, "", "&");
$header = array(
	"Content-Type: application/x-www-form-urlencoded",
	"Content-Length: ".strlen($data)
);
$context = array(
	"http" => array(
		"method"  => "POST",
		'header'=> "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36\r\n",
		"header"  => implode("\r\n", $header),
		"content" => $data
	)
);

########################################

$res = file_get_contents($url, false, stream_context_create($context));
$res = mb_convert_encoding($res,"utf-8","sjis,EUC-JP");
$res = explode(',',$res);
$disc = mb_substr($res[0],0,2);

if ($disc == "ER"){
    echo "エラー！<br>※コード：".$res[0]."<br>";
} else {
    //結果表示
    switch ($req_type) {
        case 0: #顧客番号発番
            echo "下記の顧客番号が発番されました<br><br>";
            echo "顧客番号：".$res[0]."<br>";
            echo "請求ID：".$res[1]."<br>";
            break;
        case 1: #顧客登録
            echo "下記の顧客登録が完了しました<br><br>";
            echo "顧客番号：".$res[0]."<br>";
            echo "金融機関：".$res[2]."<br>";
            echo "支店名：".$res[3]."<br>";
            //cidをpatient_infoに登録
            $sql = "UPDATE patient_info
            SET rp_cid = '$res[0]'
            WHERE original_pid = '$original_pid'";
            $dbh->query($sql);
            break;
        case 2: #請求追加
            echo "下記の請求追加が完了しました<br><br>";
            echo "請求ID：".$res[0]."<br>";
            echo "振替金額：".$res[1]."<br>";
            //請求追加の場合、reqidをre_shinryo/rek_serviceに登録（月ごとに）
            $sql = "UPDATE re_shinryo
            SET rp_reqid = '$res[0]'
            WHERE original_pid = '$original_pid' AND SUBSTRING(srd,1,6) = '$srm'";
            $dbh->query($sql);
            $sql = "UPDATE rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
            SET rp_reqid = '$res[0]'
            WHERE rek_patient.original_pid = '$original_pid' AND srm = '$srm'";
            $dbh->query($sql);
            $sql = "UPDATE appendix
            SET rp_reqid = '$res[0]'
            WHERE original_pid = '$original_pid' AND SUBSTRING(app_date,1,6) = '$srm'";
            $dbh->query($sql);

            #acc_resultに登録
            $sql = "INSERT INTO acc_result (gid,rst,ap,ec,god,cod,am,tx,sf,ta,em,nm,original_pid,srm,reqid)
                                VALUES (0,0,0,0,0,'$cod','$res[1]',0,0,0,'','','$original_pid','$srm',$res[0])";
            $dbh->query($sql);

            break;
        case 3: #請求情報変更
            echo "下記の請求情報変更が完了しました<br><br>";
            echo "請求ID：".$res[0]."<br>";
            echo "振替金額：".$res[1]."<br>";
            //請求追加の場合、reqidをre_shinryo/rek_serviceに登録（月ごとに）
            $sql = "UPDATE re_shinryo
            SET rp_reqid = '$res[0]'
            WHERE original_pid = '$original_pid' AND SUBSTRING(srd,1,6) = '$srm'";
            $dbh->query($sql);
            $sql = "UPDATE rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
            SET rp_reqid = '$res[0]'
            WHERE rek_patient.original_pid = '$original_pid' AND srm = '$srm'";
            $dbh->query($sql);
            $sql = "UPDATE appendix
            SET rp_reqid = '$res[0]'
            WHERE original_pid = '$original_pid' AND SUBSTRING(app_date,1,6) = '$srm'";
            $dbh->query($sql);

            $sql = "UPDATE acc_result set am = '$amo' where reqid = '$reqid'";
            $dbh->query($sql);

            break;
        case 4: #口座情報変更
            echo "下記の口座情報変更が完了しました<br><br>";
            echo "顧客番号：".$res[0]."<br>";
            #echo "金融機関：".$res[2]."<br>";
            #echo "支店名：".$res[3]."<br>";
            echo "金融機関：".$res[1]."<br>";
            echo "支店名：".$res[2]."<br>";
            break;
        default:
            echo "処理タイプが不正です<br>";
    }
}
echo "<br><a href=patient_info_list.php>".患者情報一覧に戻る."</a><br>";

$res = $data = array();

?>

</body>
</html>
