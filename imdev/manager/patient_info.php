<?php
session_start();
ini_set( 'display_errors', 0 );
include_once "../common/smarty_settings.php";
include_once '../class/common.php';
require_once('../class/db_extension.php');
include_once "../class/config.php";

$today = date('Y-m-d h:i:s');
// セッションチェック
$common = new COMMON;
$common->id = $_SESSION['id'];
$common->password = $_SESSION['password'];
$result = $common->checkid();
#$row = $result->fetchRow();
$row = $result->fetch();
$login_name = $row[1];
$authority_id = $row[2];
$account_id = $row[3];
if($row[0] == 0) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /index.php?error=error");
}

$dbname = DBNAME;

// 対象患者
$original_pid = isset($_REQUEST['original_pid']) ? $_REQUEST['original_pid'] : "";
$rp_cid = isset($_REQUEST['rp_cid']) ? $_REQUEST['rp_cid'] : "";


if($_SERVER["REQUEST_METHOD"] == "POST"){

  //Table定義
	$table = "patient_info";

    // $data定義
		$data['original_pid'] = $_REQUEST['original_pid'];
		$data['patient_name'] = $_REQUEST['patient_name'];
		$data['patient_birth'] = $_REQUEST['patient_birth'];
		$data['patient_hihoban'] = $_REQUEST['patient_hihoban'];
		$data['email'] = $_REQUEST['email'];
		$data['shipto_name'] = $_REQUEST['shipto_name'];
		$data['postal_code'] = $_REQUEST['postal_code'];
		$data['postal_code2'] = $_REQUEST['postal_code2'];
		$data['prefecture'] = $_REQUEST['prefecture'];
		$data['address1'] = $_REQUEST['address1'];
		$data['address2'] = $_REQUEST['address2'];
		$data['shipto_name_sub'] = $_REQUEST['shipto_name_sub'];
		$data['postal_code_sub'] = $_REQUEST['postal_code_sub'];
		$data['postal_code_sub2'] = $_REQUEST['postal_code_sub2'];
		$data['prefecture_sub'] = $_REQUEST['prefecture_sub'];
		$data['address1_sub'] = $_REQUEST['address1_sub'];
		$data['address2_sub'] = $_REQUEST['address2_sub'];
		$data['patient_hihoki'] = $_REQUEST['patient_hihoki'];
		$data['patient_hihoban'] = $_REQUEST['patient_hihoban'];
		$data['patient_jukyuban'] = $_REQUEST['patient_jukyuban'];
		$data['patient_kaigo_hihoban'] = $_REQUEST['patient_kaigo_hihoban'];
		$data['patient_kaigo_jukyuban'] = $_REQUEST['patient_kaigo_jukyuban'];
		$data['bac'] = $_REQUEST['bac'];
		$data['brc'] = $_REQUEST['brc'];
		$data['classification'] = $_REQUEST['classification'];
		$data['account_number'] = $_REQUEST['account_number'];
		$data['holder_name'] = $_REQUEST['holder_name'];
		$data['others'] = $_REQUEST['others'];
		$data['others2'] = $_REQUEST['others2'];
		$data['others3'] = $_REQUEST['others3'];
		$data['direct_debit'] = $_REQUEST['direct_debit'];
		$data['invoice_output'] = $_REQUEST['invoice_output'];
		$data['receipt_output'] = $_REQUEST['receipt_output'];
		$data['update_date'] = $today;

		$data['disp'] = $_REQUEST['disp'];

		#200821氏名変更があった場合、レセプトにも反映
		#$sql = "select * from re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid where re_shinryo.original_pid = '{$data['original_pid']}'; ";
		#echo $sql;
		#$re_shinryo = DbEx::query($sql);

		$table2 = "re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid";
		$columns2 = "re_patient.name,re_patient.pid";
		$postfix2 = "where re_shinryo.original_pid = '{$original_pid}'";
		$re_shinryo = DbEx::selectRow($dbname, $table2, $columns2, $postfix2);
		$target_name = $re_shinryo['name'];
		$pid = $re_shinryo['pid'];

		#保存する名前とレセプトの名前が異なる場合、レセプトの名前を更新
		$postfix2 = "where re_shinryo.original_pid = '{$original_pid}' and re_patient.name = '{$data['patient_name']}'";
		$re_shinryo = DbEx::select($dbname, $table2, $columns2, $postfix2);

		if(count($re_shinryo) == 0){
			#echo "名前変更あり";
			#re_patientの対象レコードを抽出
			$table3 = "re_patient";
			$columns3 = "name_history";
			$postfix3 = "where pid = '{$pid}'";
			$re_patient = DbEx::selectRow($dbname, $table3, $columns3, $postfix3);
			#print_r($re_patient);exit;
			$name_history = $re_patient['name_history'];
			if($name_history != ""){
				$name_historyArr = explode(",",$name_history);
			}else{
				$name_historyArr = array();
			}
			$name_add = $target_name."_".date("ymd");
			array_push($name_historyArr,$name_add);

			$name_history_new = implode(",",$name_historyArr);

			$table2 = "re_patient INNER JOIN re_shinryo ON re_shinryo.pid = re_patient.pid";
			$data2['name'] = $data['patient_name'];
			$data2['name_history'] = $name_history_new;
			$columns2 = "re_patient.name";
			$postfix2 = "where re_shinryo.original_pid = '{$original_pid}'";
			DbEx::update($dbname, $table2, $data2, $postfix2);
		}else{
			#echo "名前変更なしなのでスルー<br>\n";
		}
		#exit;

    // 更新
	if($original_pid != ""){
		$postfix = " where original_pid = '{$original_pid}' ";
		DbEx::update($dbname, $table, $data, $postfix);
        // DB更新＋RP送信
        if($_REQUEST['rp_new']){
            // RP新規登録
            header("Location: /manager/payment-test-exe.php?original_pid={$original_pid}&req_type=1&nm={$data['patient_name']}&bac={$data['bac']}&brc={$data['brc']}&atype={$data['classification']}&anum={$data['account_number']}&anm={$data['holder_name']}&po={$data['postal_code']}{$data['postal_code2']}&pre={$data['prefecture']}&ad1={$data['address1']}&ad2={$data['address2']}");
        }elseif($_REQUEST['rp_update']){
            // 新規追加した患者のoriginal_pidを取得
            $columns = 'rp_cid';
            $res = DbEx::select($dbname, $table, $columns, $postfix);
            $rp_cid = $res[0]['rp_cid'];
            // RP情報更新
            header("Location: /manager/payment-test-exe.php?original_pid={$original_pid}&req_type=4&cid={$rp_cid}&nm={$data['patient_name']}&bac={$data['bac']}&brc={$data['brc']}&atype={$data['classification']}&anum={$data['account_number']}&anm={$data['holder_name']}&po={$data['postal_code']}{$data['postal_code2']}&pre={$data['prefecture']}&ad1={$data['address1']}&ad2={$data['address2']}");
        // DB更新のみ
        }else{
            header("Location: /manager/patient_info.php?original_pid={$original_pid}");
        }
	}else{
		$data['regist_date'] = $today;
		DbEx::insert($dbname, $table, $data);
        // 新規追加した患者のoriginal_pidを取得
        $columns = 'original_pid';
        $postfix = " where patient_name = '{$patient_name}'
                        and patient_birth = '{$patient_birth}";
        $res = DbEx::select($dbname, $table, $columns, $postfix);
        $original_pid = $res[0]['original_pid'];

				// DB新規追加＋RP追加
        if($_REQUEST['rp_new']){
            header("Location: /manager/payment-test-exe.php?original_pid={$original_pid}&req_type=1&nm={$data['patient_name']}&bac={$data['bac']}&brc={$data['brc']}&atype={$data['classification']}&anum={$data['account_number']}&anm={$data['holder_name']}&po={$data['postal_code']}{$data['postal_code2']}&pre={$data['prefecture']}&ad1={$data['address1']}&ad2={$data['address2']}");
        // DB新規追加のみ＋RP追加
				}else{
            header("Location: /manager/patient_info.php?original_pid={$original_pid}");
        }
	}
}


// $resultに患者データ格納
if($original_pid != ""){
	$table = "patient_info";
	$columns = '*';
	$postfix = " where original_pid = '{$original_pid}' ";
	$result = DbEx::selectRow($dbname, $table, $columns, $postfix);

}else{
	$result = array();
}


if(isset($result['bac']) && $result['bac'] != ""){
	$table = "bank_master";
	$columns = "name";
	$postfix = " where bank_id = '{$result['bac']}'";
	$bank = DbEx::selectRow($dbname, $table, $columns, $postfix);
	$result['bank_name'] = $bank['name'];
}else{
	$result['bank_name'] = "";
}
if(isset($result['bac']) && $result['bac'] != "" && isset($result['brc']) && $result['brc'] != ""){
	$table = "bank_master";
	$columns = "name";
	$postfix = " where bank_id = '{$result['bac']}' and branch_id = '{$result['brc']}'";
	$bank = DbEx::selectRow($dbname, $table, $columns, $postfix);
	$result['branch_name'] = $bank['name'];
}else{
	$result['branch_name'] = "";
}

#print_r($result);

$table = "bank_master";
$columns = "*";
$postfix = " where class = 1";
$bank_master = DbEx::select($dbname, $table, $columns, $postfix);
#print_r($bank_master);



$smarty->assign( 'data',$result);

$smarty->assign( 'navi_type',14);
#$smarty->assign( 'master_account_name',$master_account_name);
#$smarty->assign( 'account_name',$account_name);
#$smarty->assign( 'from',$from);
#$smarty->assign( 'to',$to);

$smarty->assign( 'current_url',"/manager/kktp.php");
$smarty->assign( 'category','aggregate');
$smarty->assign( 'bank_master',$bank_master);
$smarty->display( 'manager/patient_info.tpl' );

?>
