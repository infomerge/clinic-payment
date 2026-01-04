<?php
session_start();
ini_set( 'display_errors', 1 );
include_once "../common/smarty_settings.php";
include_once '../class/common.php';
require_once('../class/db_extension.php');
include_once "../class/config.php";

// セッションチェック
$common = new COMMON;

$common->id = $_SESSION['id'];
$common->password = $_SESSION['password'];
$result = $common->checkid();

$row = $result->fetchRow();
$login_name = $row[1];
$authority_id = $row[2];
$account_id = $row[3];

if($row[0] == 0) {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: /index.php?error=error");
}

$account_name = isset($_REQUEST['account_name']) ? $_REQUEST['account_name'] : "";
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "1";


//対象の期間と患者名を取得
/*
#POST
$srd_start = $_POST["srd_start"];
$srd_end = $_POST["srd_end"];
if($srd_start==""){
  #GET(確認用)
  $srd_start = $_GET["srd_start"];
  $srd_end = $_GET["srd_end"];
}
*/
$srd_start = "20180101";
$srd_end = "20181231";
$original_pid = "1313";

/*

#DB接続
$dbh = new PDO('mysql:dbname=ns_crossline;host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#医療保険マスター
$sql = "SELECT *
        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
        WHERE srd >= '$srd_start' AND srd <= '$srd_end'";
$stmt = $dbh->query($sql);
$iryo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);


#医療保険データの保険カテゴリーごとの点数と、診療日ごとの負担額と、その他データを$dataに格納
$data = array();
foreach($iryo_data as $v){
  #診療日＞保険カテゴリーごとの点数
  if(isset($data[$v['original_pid']]['srd'][$v['srd']]['category'][$v['category']]))
    $data[$v['original_pid']]['srd'][$v['srd']]['category'][$v['category']] += intval($v['tensu']);
  else
    $data[$v['original_pid']]['srd'][$v['srd']]['category'][$v['category']] = intval($v['tensu']);
  #負担率
  $data[$v['original_pid']]['srd'][$v['srd']]['ratio'] = $v['ratio'];
  #診療日ごとの点数
  if(isset($data[$v['original_pid']]['srd'][$v['srd']]['tensu']))
    $data[$v['original_pid']]['srd'][$v['srd']]['tensu'] += intval($v['tensu']);
  else
    $data[$v['original_pid']]['srd'][$v['srd']]['tensu'] = intval($v['tensu']);
  #診療日ごとの負担額
  if(isset($data[$v['original_pid']]['srd'][$v['srd']]['copayment']))
    $data[$v['original_pid']]['srd'][$v['srd']]['copayment'] += round($v['copayment'],-1);
  else
    $data[$v['original_pid']]['srd'][$v['srd']]['copayment'] = round($v['copayment'],-1);
  #その他データ
  $data[$v['original_pid']]['data'] = $v;
}

#カテゴリーごとの合計点数を$m_category[$k]['tensu']に格納
foreach($patient_data['srd'] as $key => $shinryo_cat){
    foreach($m_category as $k => $v){
      if(array_key_exists($k , $shinryo_cat['category'])){
        $m_category[$k]['tensu'] += $shinryo_cat['category'][$k];
      }
    }
    $total_tensu += $shinryo_cat['tensu'];
    $total_copayment += $shinryo_cat['copayment'];
}
*/

####################################

#対象DB
$dbname = DBNAME;
$table = "re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid";

#全体数の取得
$columns = 'count(*) as cnt';
$postfix = " WHERE re_shinryo.original_pid = $original_pid ";
$postfix .= "order by re_shinryo.srd asc";
$result = DbEx::selectRow($dbname, $table, $columns, $postfix);

$res_data = array();
$res_data['count'] = $result['cnt'];
$res_data['max_page'] = ceil($result['cnt'] / LISTOFFSET);
$res_data['current_page'] = $page;
$res_data['count_from'] = ($page -1) * LISTOFFSET + 1;
if( ($result['cnt'] - ($page -1) * LISTOFFSET ) > LISTOFFSET):
	$res_data['count_to'] = ($page) * LISTOFFSET;
else:
	$res_data['count_to'] = $result['cnt'];
endif;

#ページング
$columns = '*';
$postfix = " where re_shinryo.original_pid = $original_pid ";
$postfix .= "group by re_shinryo.srd desc limit ".($page -1) * LISTOFFSET . " , " . LISTOFFSET . " " ;
$result = DbEx::select($dbname, $table, $columns, $postfix);
$res_data['result'] = $result;

$base_url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . "/manager/patient_seikyu_list.php";
$pagination = "";
for($i=1;$i<=$res_data['max_page']; $i++){
	if($i == $page):
		$pagination .= " $i ";
	else:
		$pagination .= " <a href='{$base_url}?page={$i}'>{$i}</a> ";
	endif;
}



#$smarty->assign( 'data',$result);
$smarty->assign( 'data',$res_data);
$smarty->assign( 'navi_type',14);
$smarty->assign( 'master_account_name',$master_account_name);
$smarty->assign( 'account_name',$account_name);

$smarty->assign( 'current_url',"/manager/patient_seikyu_list.php");
$smarty->assign( 'category','aggregate');
$smarty->assign( 'pagination',$pagination);
$smarty->display( 'manager/patient_seikyu_list.tpl' );

?>
