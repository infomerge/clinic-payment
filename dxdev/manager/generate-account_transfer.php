<?php
ini_set( 'display_errors', 1 );
ini_set("memory_limit", "5120M");
set_time_limit(0);

include_once "../common/smarty_settings.php";
include_once "../class/config.php";


//対象の期間と患者名を取得
#POST
if (isset($GLOBALS['argv'][1])) {
  $manageperiod_flag = $GLOBALS['argv'][1];
}else{
  $manageperiod_flag = $_GET["manageperiod_flag"];
}
if (isset($GLOBALS['argv'][2])) {
  $srd_start = $GLOBALS['argv'][2];
}else{
  $srd_start = isset($_GET["srd_start"]) ? $_GET["srd_start"] : "";
}
if (isset($GLOBALS['argv'][3])) {
  $srd_end = $GLOBALS['argv'][3];
}else{
  $srd_end = isset($_GET["srd_end"]) ? $_GET["srd_end"] : "";
}



$srm = mb_substr($srd_start,0,6);



#DB接続
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#医療保険マスター
$sql = "SELECT * FROM manageperiod where status = 0;";
$stmt = $dbh->query($sql);
$manageperiod = $stmt->fetchALL(PDO::FETCH_ASSOC);
$status = $manageperiod[0]['status'];
$targetym = $manageperiod[0]['targetym'];
#echo $status."---".$targetym."---";
#print_r($manageperiod);exit;


#医療保険マスター
$sql = "SELECT *
        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                        INNER JOIN patient_info ON re_shinryo.original_pid = patient_info.original_pid ";

if($manageperiod_flag == 1):
  $sql .= "WHERE re_shinryo.manageperiod_status = 1 AND re_shinryo.manageperiod_targetym = '{$targetym}'";
else:
  $sql .= "WHERE srd >= '$srd_start' AND srd <= '$srd_end'";
endif;

$sql .= " AND patient_info.disp = 0 order by re_shinryo.srd,re_shinryo.category";

#echo $sql."<br>\n";exit;

$stmt = $dbh->query($sql);
$iryo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
#print_r($iryo_data);

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





  #介護保険マスター
  $sql = "SELECT *
          FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
          WHERE rek_service.original_pid = '".$v['original_pid']."' ";
  $sql .= "AND rek_service.manageperiod_status = 1 AND rek_service.manageperiod_targetym = '{$targetym}'";
  $stmt = $dbh->query($sql);
  $kaigo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

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
#print_r($data);

foreach($data as $original_pid => $dt) {

  #自由診療マスター
  $sql = "SELECT *
          FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid ";

  if($manageperiod_flag == 1):
    $sql .= "WHERE appendix.manageperiod_status = 1 AND appendix.manageperiod_targetym = '{$targetym}' ";
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

if( isset($_REQUEST['testview']) && $_REQUEST['testview'] == 1){
  print_r($data);exit;
}


#個人毎PDFデータ生成
$cnt = 1;
  $total_tensu = 0;
  $total_copayment = 0;
  #$total_service_unit = 0;
foreach ($data as $original_pid => $patient_data) {

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

    #請求番号
    $inv_id = $patient_data['data']['irkkcode'] . "-" . $srm . "-" . sprintf('%07d', strval($original_pid));

    ### ---------- 点数表 ---------- ###
    #カテゴリーごとの合計点数を$m_category[$k]['tensu']に格納／医療保険の合計金額を$total_copaymentに加算
    foreach($patient_data['srd'] as $key => $shinryo_cat){
        $total_copayment += $shinryo_cat['copayment'];
    }

    #介護保険の合計点数を$total_service_unitに格納／介護保険の合計金額を$total_copaymentに加算
    foreach($patient_data['srm'] as $k => $v){
        #$total_service_unit += $v['tensu'];
        $total_copayment += 10 * $v['tensu'] * $v['rate'] / 100;
    }

    #医療／公費負担額が存在する場合は$total_copaymentを上書き
    if(isset($m_max[$original_pid][$srm])){
      if($m_max[$original_pid][$srm]){
        $total_copayment = $m_max[$original_pid][$srm];
      }
    }else{
      #$total_copayment = 0;
    }

    #2020/02/12 一部負担金と支払い総額を分ける必要あり
    $ichibufutankin = $total_copayment;
    for($i=1;$i<=3;$i++){
      if(isset($patient_data['app_cat'][$i])){
        $total_copayment += $patient_data['app_cat'][$i];
      }
    }


#支払総額が「0」の場合はスキップ
if($total_copayment == 0) continue;

echo $original_pid."---".$patient_data['data']['name']."---".$total_copayment."---".$patient_data['data']['direct_debit']."<br>\n";

$cod = uniqid();
#acc_resultに登録
$sql = "INSERT INTO acc_result (gid,rst,ap,ec,god,cod,am,tx,sf,ta,em,nm,original_pid,srm,targetym,reqid,rp_disableflag)
                    VALUES (0,0,0,0,0,'$cod','$total_copayment',0,0,0,'','','$original_pid',0,'{$targetym}',null,'{$patient_data['data']['direct_debit']}');";
$dbh->query($sql);

#echo $sql."<br>\n";
#医療保険マスター
#$sql = "SELECT * FROM account_transfer WHERE target_ym = {$srm} and original_pid = {$original_pid}";
#$stmt = $dbh->query($sql);
#$target_data = $stmt->fetchALL(PDO::FETCH_ASSOC);


/*
if(count($target_data)>0){
  $sql = "UPDATE account_transfer SET price WHERE target_ym = {$srm} and original_pid = {$original_pid}";
  $dbh->query($sql);
}else{
  $sql = "INSERT into account_transfer values(NULL,{$original_pid},{$total_copayment},{$srm});";
  $dbh->query($sql);
}
*/
    $total_tensu = 0;
    $total_copayment = 0;
    #$total_service_unit = 0;




}

#処理完了後、manageperiod_status=2に変更する

$sql = "UPDATE re_shinryo SET manageperiod_status = 2 where manageperiod_status = 1 and manageperiod_targetym = '{$targetym}';";
$dbh->query($sql);
$sql = "UPDATE rek_service SET manageperiod_status = 2 where manageperiod_status = 1 and manageperiod_targetym = '{$targetym}';";
$dbh->query($sql);
$sql = "UPDATE appendix SET manageperiod_status = 2 where manageperiod_status = 1 and manageperiod_targetym = '{$targetym}';";
$dbh->query($sql);


exit;

?>
