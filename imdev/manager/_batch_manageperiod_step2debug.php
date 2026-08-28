<?php
ini_set( 'display_errors', 1 );
ini_set("memory_limit", "5120M");
set_time_limit(0);

include_once "../common/smarty_settings.php";
include_once "../class/config.php";

/*
 * imdev 向け step2 デバッグ
 *
 * プレビュー（DB書き込みなし）:
 *   ?manageperiod_flag=2&original_pid=587&targetym=202607&testview=1
 *
 * acc_result のみ INSERT（status 一括更新なし）:
 *   ?manageperiod_flag=2&original_pid=587&targetym=202607
 *
 * acc_result + manageperiod_status=2 まで実行:
 *   上記に &commit=1
 *
 * manageperiod_flag: 1=締め対象全体, 2=original_pid 指定
 */

//対象の期間と患者名を取得
if (isset($GLOBALS['argv'][1])) {
  $manageperiod_flag = $GLOBALS['argv'][1];
}else{
  $manageperiod_flag = isset($_GET["manageperiod_flag"]) ? $_GET["manageperiod_flag"] : "";
}
if (isset($GLOBALS['argv'][2])) {
  $original_pid = $GLOBALS['argv'][2];
}else{
  $original_pid = isset($_GET["original_pid"]) ? $_GET["original_pid"] : "";
}
if (isset($GLOBALS['argv'][3])) {
  $targetym = $GLOBALS['argv'][3];
}else{
  $targetym = isset($_GET["targetym"]) ? $_GET["targetym"] : "";
}

$testview = isset($_REQUEST['testview']) && $_REQUEST['testview'] == 1;
$commit = isset($_REQUEST['commit']) && $_REQUEST['commit'] == 1;
$srd_start = isset($_GET["srd_start"]) ? $_GET["srd_start"] : "";
$srd_end = isset($_GET["srd_end"]) ? $_GET["srd_end"] : "";

/**
 * max_copayment の srm キーは RE の診療年月(202607) で、re_shinryo.srd の先頭6桁(202606) と
 * ずれることがあるため、targetym / srd 先頭 / 登録済みキーを順に探す。
 */
function step2debug_resolve_max_copayment($m_max, $original_pid, $patient_data, $targetym) {
  if (!isset($m_max[$original_pid]) || !is_array($m_max[$original_pid])) {
    return null;
  }
  $candidates = array($targetym);
  if (isset($patient_data['srd']) && is_array($patient_data['srd'])) {
    foreach (array_keys($patient_data['srd']) as $srd) {
      $candidates[] = mb_substr($srd, 0, 6);
    }
  }
  if (isset($patient_data['data']['srd'])) {
    $candidates[] = mb_substr($patient_data['data']['srd'], 0, 6);
  }
  $candidates = array_unique($candidates);
  foreach ($candidates as $srm) {
    if ($srm === '') {
      continue;
    }
    if (isset($m_max[$original_pid][$srm]) && $m_max[$original_pid][$srm] !== '' && $m_max[$original_pid][$srm] !== null) {
      return $m_max[$original_pid][$srm];
    }
  }
  foreach ($m_max[$original_pid] as $val) {
    if ($val !== '' && $val !== null && (float)$val > 0) {
      return $val;
    }
  }
  return null;
}

function step2debug_calc_total_copayment($patient_data, $m_max, $kaigo_trans, $original_pid, $targetym) {
  $total_copayment = 0;
  if (isset($patient_data['srd']) && is_array($patient_data['srd'])) {
    foreach ($patient_data['srd'] as $shinryo_cat) {
      $total_copayment += $shinryo_cat['copayment'];
    }
  }
  $max_copayment = step2debug_resolve_max_copayment($m_max, $original_pid, $patient_data, $targetym);
  if ($max_copayment !== null && (float)$max_copayment > 0) {
    $total_copayment = $max_copayment;
  }
  if (isset($kaigo_trans[$original_pid]['srm']) && is_array($kaigo_trans[$original_pid]['srm'])) {
    foreach ($kaigo_trans[$original_pid]['srm'] as $v) {
      $total_copayment += 10 * $v['tensu'] * $v['rate'] / 100;
    }
  }
  for ($i = 1; $i <= 3; $i++) {
    if (isset($patient_data['app_cat'][$i])) {
      $total_copayment += $patient_data['app_cat'][$i];
    }
  }
  return $total_copayment;
}

#$srm = mb_substr($srd_start,0,6);
$srm = "";

#DB接続（DB名=DBNAME / ユーザ名=xs547384_dx … imdev 他スクリプトと同じ）
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($targetym === "") {
  $stmt = $dbh->query("SELECT targetym, status FROM manageperiod WHERE status <> 9 ORDER BY targetym DESC LIMIT 1");
  $manageperiod_row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($manageperiod_row && isset($manageperiod_row['targetym'])) {
    $targetym = $manageperiod_row['targetym'];
  }
}

if ($targetym === "") {
  echo "targetym が未指定です。URL に targetym=202607 等を付けてください。";
  exit;
}

if ($manageperiod_flag === "" || !in_array((string)$manageperiod_flag, array('1', '2'), true)) {
  echo "manageperiod_flag は 1（全体）または 2（患者指定）を指定してください。";
  exit;
}

if ((string)$manageperiod_flag === '2' && $original_pid === "") {
  echo "manageperiod_flag=2 の場合は original_pid が必要です。";
  exit;
}

if ($testview) {
  echo "DB: ".DBNAME." / targetym: {$targetym} / manageperiod_flag: {$manageperiod_flag}";
  if ($original_pid !== "") {
    echo " / original_pid: {$original_pid}";
  }
  echo " / mode: preview (testview=1)\n\n";
}


#医療保険マスター（step2 getPaymentData と同じ JOIN）
$sql = "SELECT *
        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                        INNER JOIN patient_info ON re_shinryo.original_pid = patient_info.original_pid
                        INNER JOIN account_info ON re_shinryo.original_irkkcode = account_info.original_irkkcode ";

if($manageperiod_flag == 1):
  $sql .= "WHERE (re_shinryo.manageperiod_status = 1 OR re_shinryo.manageperiod_status = 5) AND re_shinryo.manageperiod_targetym = '{$targetym}'";
elseif($manageperiod_flag == 2):
  $sql .= "WHERE re_shinryo.original_pid = '{$original_pid}' AND re_shinryo.manageperiod_targetym = '{$targetym}'";
else:
  $sql .= "WHERE srd >= '$srd_start' AND srd <= '$srd_end'";
endif;

$sql .= " AND patient_info.disp = 0 AND patient_info.invoice_output = 0 order by re_shinryo.srd,re_shinryo.category";

#echo $sql."<br>\n";exit;

$stmt = $dbh->query($sql);
$iryo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

if (count($iryo_data) === 0) {
  echo "re_shinryo が 0 件です（JOIN 条件または manageperiod_targetym を確認してください）。\n";
  echo "SQL: {$sql}\n";
  exit;
}
#print_r($iryo_data);

  #介護保険マスター
  $sql = "SELECT *
          FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
          WHERE 1 = 1 ";
  if($manageperiod_flag == 1):
    $sql .= "AND (rek_service.manageperiod_status = 1 OR rek_service.manageperiod_status = 5) AND rek_service.manageperiod_targetym = '{$targetym}'";
  elseif($manageperiod_flag == 2):
    $sql .= "AND rek_service.original_pid = '{$original_pid}' AND rek_service.manageperiod_targetym = '{$targetym}'";
  endif;
#echo $sql;exit;
  $stmt = $dbh->query($sql);
  $kaigo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
#print_r($kaigo_data);exit;

$kaigo_trans = array();
foreach($kaigo_data as $v){
    #合計点数
    if(isset($kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['tensu']))
      $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['tensu'] += intval($v['service_unit']) * $v['kaisu'];
    else
      $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['tensu'] = intval($v['service_unit']) * $v['kaisu'];
    #負担率
    #echo (100 - $v['hoken_rate']) ."---".((100 - $v['kouhi_rate'])/100)."aaa";
    $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['rate'] = (100 - $v['hoken_rate']);
    #合計負担額
    if(isset($kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['copayment']))
      $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['copayment'] += $v['service_unit'] * 10 * $v['kaisu'] * ((100 - $v['hoken_rate'])/100);
    else
      $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['copayment'] = $v['service_unit'] * 10 * $v['kaisu'] * ((100 - $v['hoken_rate'])/100);
    #明細データ
    $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['service_name'] = $v['service_name'];
    $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['service_unit'] = $v['service_unit'];
    $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['kaisu'] = $v['kaisu'];
    $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['tekiyo'] = $v['tekiyo'];
    #その他データ
}
#print_r($kaigo_trans);exit;

  #上限金額マスター
  $sql = "SELECT * FROM max_copayment";
  $stmt = $dbh->query($sql);
  $max = $stmt->fetchALL(PDO::FETCH_ASSOC);
  $m_max = array();
  foreach($max as $value){
      $m_max[$value['original_pid']][$value['srm']] = $value['max_copayment'];
  }
#print_r($m_max);


#print_r($iryo_data);exit;
#医療保険データの保険カテゴリーごとの点数と、診療日ごとの負担額と、その他データを$dataに格納
$data = array();
$buf_srd = "";
foreach($iryo_data as $v){

  #診療日ごとの負担額
  if(isset($data[$v['original_pid']]['srd'][$v['srd']]['copayment']))
    #$data[$v['original_pid']]['srd'][$v['srd']]['copayment'] += round($v['copayment'],-1);
    $data[$v['original_pid']]['srd'][$v['srd']]['copayment'] += $v['copayment'] * $v['kaisu'];
  else
    #$data[$v['original_pid']]['srd'][$v['srd']]['copayment'] = round($v['copayment'],-1);
    $data[$v['original_pid']]['srd'][$v['srd']]['copayment'] = $v['copayment'] * $v['kaisu'];

  #その他データ
  $data[$v['original_pid']]['data'] = $v;







#echo $sql."<br>\n";
  #print_r($kaigo_data);exit;
  #介護保険データを$dataに格納
  foreach($kaigo_data as $v){

    #合計負担額
    if(isset($data[$v['original_pid']]['srm'][$v['srm']]['copayment']))
      $data[$v['original_pid']]['srm'][$v['srm']]['copayment'] += $v['service_unit'] * 10 * $v['kaisu'];
    else
      $data[$v['original_pid']]['srm'][$v['srm']]['copayment'] = $v['service_unit'] * 10 * $v['kaisu'];

  }

}
#print_r($data);exit;

foreach($data as $original_pid => $dt) {

  #自由診療マスター
  $sql = "SELECT *
          FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid ";

  if($manageperiod_flag == 1):
    $sql .= "WHERE (appendix.manageperiod_status = 1 OR appendix.manageperiod_status = 5) AND appendix.manageperiod_targetym = '{$targetym}' ";
  elseif($manageperiod_flag == 2):
    $sql .= "WHERE appendix.manageperiod_targetym = '{$targetym}' ";
  else:
    $sql .= "WHERE app_date >= '$srd_start' AND app_date <= '$srd_end' ";
  endif;

  $sql .= "and appendix.original_pid = '".$original_pid."' and patient_info.disp = 0 and appendix.disp = 0 order by app_date";


  $stmt = $dbh->query($sql);
  $app_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
  #自由診療データを$dataに格納
  foreach($app_data as $v){
      #カテゴリーごとの合計金額
      if(isset($data[$original_pid]['app_cat'][$v['app_cat']])){
          $data[$original_pid]['app_cat'][$v['app_cat']] += intval($v['app_price']);
          $data[$original_pid]['app_item'][$v['app_cat']] .= "/".$v['app_item'];
      }else{
          $data[$original_pid]['app_cat'][$v['app_cat']] = intval($v['app_price']);
          $data[$original_pid]['app_item'][$v['app_cat']] = $v['app_item'];
      }
  }

}




foreach($data as $original_pid => $v) {

  if(isset($v['srd'])){
    foreach($v['srd'] as $kk => $vv){
      $data[$original_pid]['srd'][$kk]['copayment'] = round($vv['copayment'],-1);
    }
  }else{
    $data[$original_pid]['srd'] = array();
  }
  if(isset($v['srm'])){
    foreach($v['srm'] as $kk => $vv){
      $data[$original_pid]['srm'][$kk]['copayment'] = round($vv['copayment'],-1);
    }
  }else{
    $data[$original_pid]['srm'] = array();
  }
}

if ($testview) {
  foreach ($data as $opid => $patient_data) {
    $preview_total = step2debug_calc_total_copayment($patient_data, $m_max, $kaigo_trans, $opid, $targetym);
    $max_val = step2debug_resolve_max_copayment($m_max, $opid, $patient_data, $targetym);
    $pname = isset($patient_data['data']['name']) ? $patient_data['data']['name'] : '';
    echo "original_pid={$opid}\t{$pname}\ttotal_copayment={$preview_total}\tmax_copayment=" . ($max_val !== null ? $max_val : 'なし') . "\n";
  }
  echo "\n--- raw data ---\n";
  print_r($data);
  exit;
}


#個人毎PDFデータ生成
$cnt = 1;
$insert_count = 0;
$skipped_messages = array();
foreach ($data as $original_pid => $patient_data) {
#print_r($patient_data);exit;
  #original_pid=0はスルー
  if($original_pid == 0){
    continue;
  }
  if(!isset($patient_data['data']) ){
    continue;
  }


  $name_flag = false;
  if(isset($patient_data['data']['shipto_name']) && $patient_data['data']['shipto_name'] != ""){
    $name_flag = true;
  }elseif( isset($patient_data['data']['name']) && $patient_data['data']['name'] != ""){
    $name_flag = true;
  }else{
    #echo "---shipto_name:".$patient_data['data']['shipto_name']."---name:".$patient_data['data']['name'];exit;
  }
  #if($name_flag == false){continue;}

    $total_copayment = step2debug_calc_total_copayment($patient_data, $m_max, $kaigo_trans, $original_pid, $targetym);

    #支払総額が「0」の場合はスキップ
    if ($total_copayment == 0) {
      $max_hint = step2debug_resolve_max_copayment($m_max, $original_pid, $patient_data, $targetym);
      $skipped_messages[] = "original_pid={$original_pid}: total_copayment=0 のためスキップ（max_copayment=" . ($max_hint !== null ? $max_hint : '未登録') . "）";
      continue;
    }

    #請求番号
    $tmp_rand = uniqid();
    $inv_id = $patient_data['data']['irkkcode'] . "-" . sprintf('%07d', strval($original_pid)) . "-" . $targetym ."-".$tmp_rand;

echo $original_pid."---".$patient_data['data']['name']."---".$total_copayment."---".$patient_data['data']['direct_debit']."<br>\n";
#exit;
#$cod = uniqid();
#acc_resultに登録
/*$sql = "INSERT INTO acc_result (gid,rst,ap,ec,god,cod,am,tx,sf,ta,em,nm,original_pid,srm,targetym,reqid,rp_disableflag,rp_errorflag,rp_errormsg)
                    VALUES (0,0,0,0,0,'$cod','$total_copayment',0,0,0,'','','$original_pid',0,'{$targetym}',null,'{$patient_data['data']['direct_debit']}',0,'');";*/
$sql = "INSERT INTO acc_result (gid,rst,ap,ec,god,cod,am,tx,sf,ta,em,nm,original_pid,srm,targetym,reqid,rp_disableflag,rp_errorflag,rp_errormsg)
                    VALUES (0,0,0,0,0,'$inv_id','$total_copayment',0,0,0,'','','$original_pid',0,'{$targetym}',null,'{$patient_data['data']['direct_debit']}',0,'');";

$dbh->query($sql);
    $insert_count++;

#echo $sql."<br>\n";

}

# 処理完了後、manageperiod_status=2 に変更（imdev では commit=1 のときのみ）
if ($commit) {
  $sql = "UPDATE manageperiod SET status = 2 where status = 1 and targetym = '{$targetym}';";
  $dbh->query($sql);
  $sql = "UPDATE re_shinryo SET manageperiod_status = 2 where manageperiod_status = 1 and manageperiod_targetym = '{$targetym}';";
  $dbh->query($sql);
  $sql = "UPDATE rek_service SET manageperiod_status = 2 where manageperiod_status = 1 and manageperiod_targetym = '{$targetym}';";
  $dbh->query($sql);
  $sql = "UPDATE appendix SET manageperiod_status = 2 where manageperiod_status = 1 and manageperiod_targetym = '{$targetym}';";
  $dbh->query($sql);
  echo "commit=1: manageperiod / re_shinryo 等を status=2 に更新しました。\n";
}

if ($insert_count > 0) {
  echo "acc_result を {$insert_count} 件 INSERT しました。";
  if (!$commit) {
    echo " status 一括更新は commit=1 指定時のみ実行します。";
  }
  echo "\n";
} else {
  echo "acc_result は INSERT されませんでした。\n";
  foreach ($skipped_messages as $msg) {
    echo $msg . "\n";
  }
  if (empty($skipped_messages)) {
    echo "対象患者データがありません。\n";
  }
}


exit;

?>
