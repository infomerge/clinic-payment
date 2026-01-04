<?php
ini_set( 'display_errors', 1 );
ini_set("memory_limit", "5120M");
set_time_limit(0);

include_once "../common/smarty_settings.php";
include_once "../class/config.php";


$path2mpdf = dirname(__DIR__).'/mpdf-development'.'/vendor/autoload.php';
require_once $path2mpdf;

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'ja+aCJK',
    'format' => [257,364],
    'dpi' => 150,
    'img_dpi' => 150,
    'margin_left' => 10, // 左の余白
  	'margin_right' => 10, // 右の余白
  	'margin_top' => 6, //上の余白
  	'margin_bottom' => 0, //下の余白
  	'margin_header' => 0, //ヘッダーの余白
  	'margin_footer' => 0, //フッターの余白

    'debug' => true,
    'debugfonts' => true,
    'fontdata' => [
	    'ipa' => [
		    'R' => 'ipag.ttf'
  		]
    ]
]);
$newpage_offset = 18;
$newpage_offset2 = 56;
$newpage_offset3 = 20;

#カテゴリマスター
$m_category['A'] = array('kigo'=>'A', 'title'=>'初・再診料', 'tensu'=>0);
$m_category['B'] = array('kigo'=>'B', 'title'=>'医学管理等', 'tensu'=>0);
$m_category['C'] = array('kigo'=>'C', 'title'=>'在宅医療', 'tensu'=>0);
$m_category['D'] = array('kigo'=>'D', 'title'=>'検査', 'tensu'=>0);
$m_category['E'] = array('kigo'=>'E', 'title'=>'画像診断', 'tensu'=>0);
$m_category['F'] = array('kigo'=>'F', 'title'=>'投薬', 'tensu'=>0);
$m_category['G'] = array('kigo'=>'G', 'title'=>'注射', 'tensu'=>0);
$m_category['H'] = array('kigo'=>'H', 'title'=>'リハビリテーション', 'tensu'=>0);
$m_category['I'] = array('kigo'=>'I', 'title'=>'処置', 'tensu'=>0);
$m_category['J'] = array('kigo'=>'J', 'title'=>'手術', 'tensu'=>0);
$m_category['K'] = array('kigo'=>'K', 'title'=>'麻酔', 'tensu'=>0);
$m_category['L'] = array('kigo'=>'L', 'title'=>'放射線治療', 'tensu'=>0);
$m_category['M'] = array('kigo'=>'M', 'title'=>'歯冠修復及び欠損補綴', 'tensu'=>0);
$m_category['N'] = array('kigo'=>'N', 'title'=>'歯科矯正', 'tensu'=>0);
$m_category['O'] = array('kigo'=>'O', 'title'=>'病理診断', 'tensu'=>0);

#未定義カテゴリを暫定で用意
$m_category['P'] = array('kigo'=>'P', 'title'=>'不明１', 'tensu'=>0);
$m_category['Q'] = array('kigo'=>'Q', 'title'=>'不明２', 'tensu'=>0);
$m_category['R'] = array('kigo'=>'R', 'title'=>'不明３', 'tensu'=>0);
$m_category['S'] = array('kigo'=>'S', 'title'=>'不明４', 'tensu'=>0);
$m_category['T'] = array('kigo'=>'T', 'title'=>'不明５', 'tensu'=>0);
$m_category['U'] = array('kigo'=>'U', 'title'=>'不明６', 'tensu'=>0);
$m_category['V'] = array('kigo'=>'V', 'title'=>'不明７', 'tensu'=>0);
$m_category['W'] = array('kigo'=>'W', 'title'=>'不明８', 'tensu'=>0);
$m_category['X'] = array('kigo'=>'X', 'title'=>'不明９', 'tensu'=>0);
$m_category['Y'] = array('kigo'=>'Y', 'title'=>'不明１０', 'tensu'=>0);
$m_category['Z'] = array('kigo'=>'Z', 'title'=>'不明１１', 'tensu'=>0);

$m_category['-'] = array('kigo'=>'-', 'title'=>'その他', 'tensu'=>0);
#$mpdf->WriteHTML('<h1>ご請求金額</h1>');
#$mpdf->Output();
#exit;
/*
include("../pdf/mpdf/mpdf.php");

$newpage_offset = 18;
$newpage_offset2 = 56;
$newpage_offset3 = 20;

$mpdf=new mPDF('ja+aCJK',array(257,364),
0, #フォントサイズ default 0
'', #フォントファミリー
10, #左マージン
10, #右マージン
6, #トップマージン
0, #ボトムマージン
0, #ヘッダーマージン
''
);
$mpdf->dpi = 150;
$mpdf->img_dpi = 150;
$mpdf->debug = true;
$mpdf->debugfonts = true;
*/

//対象の期間と患者名を取得
#POST
if (isset($GLOBALS['argv'][1])) {
  $srd_start = $GLOBALS['argv'][1];
}else{
  $srd_start = $_GET["srd_start"];
}
if (isset($GLOBALS['argv'][2])) {
  $srd_end = $GLOBALS['argv'][2];
}else{
  $srd_end = $_GET["srd_end"];
}
if (isset($GLOBALS['argv'][3])) {
  $format = $GLOBALS['argv'][3];
}else{
  $format = $_GET["format"];
}
#echo $srd_start."---".$srd_end."---".$format."\n";exit;
#$srd_start = $_GET["srd_start"];
#$srd_end = $_GET["srd_end"];
#$format = $_GET["format"];

#echo $srd_start."---".$srd_end."---".$format;exit;



$srm = mb_substr($srd_start,0,6);

/*
if($srd_start==""){
  #GET(確認用)
  $srd_start = $_GET["srd_start"];
  $srd_end = $_GET["srd_end"];
}
*/



#DB接続
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#医療保険マスター
$sql = "SELECT *
        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                        INNER JOIN patient_info ON re_shinryo.original_pid = patient_info.original_pid
                        INNER JOIN account_info on re_shinryo.original_irkkcode = account_info.original_irkkcode
        WHERE srd >= '$srd_start' AND srd <= '$srd_end' AND patient_info.disp = 0 order by srd,category";

$stmt = $dbh->query($sql);
$iryo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
#print_r($iryo_data);exit;
/*
#介護保険マスター
$sql = "SELECT *
        FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
        #WHERE srd >= '$srd_start' AND srd <= '$srd_end'
        ";
$stmt = $dbh->query($sql);
$kaigo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

#自由診療マスター
$sql = "SELECT *
        FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid
        WHERE app_date >= '$srd_start' AND app_date <= '$srd_end' order by app_date";
$stmt = $dbh->query($sql);
$app_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
*/

#負担区分マスター
$sql = "SELECT * FROM futan_code ";
$stmt = $dbh->query($sql);
$futan = $stmt->fetchALL(PDO::FETCH_ASSOC);
$m_futan = array();
foreach($futan as $value){
  $m_futan[$value['code']] = $value['futan'];
}
#種別コードマスター
$sql = "SELECT * FROM syubetsu_code";
$stmt = $dbh->query($sql);
$syubetsu = $stmt->fetchALL(PDO::FETCH_ASSOC);
$m_syubetsu = array();
foreach($syubetsu as $value){
  $m_syubetsu[$value['code']]['syubetsu'] = $value['syubetsu'];
  $m_syubetsu[$value['code']]['ratio'] = $value['ratio'];
}
#サービス名マスター
$sql = "SELECT * FROM service_code";
$stmt = $dbh->query($sql);
$service = $stmt->fetchALL(PDO::FETCH_ASSOC);
$m_service = array();
foreach($service as $value){
  $m_service[$value['code']] = $value['service_name'];
}
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
  #診療日＞保険カテゴリーごとの点数
  if(isset($data[$v['original_pid']]['srd'][$v['srd']]['category'][$v['category']]))
    $data[$v['original_pid']]['srd'][$v['srd']]['category'][$v['category']] += intval($v['tensu']) * $v['kaisu'];
  else
    $data[$v['original_pid']]['srd'][$v['srd']]['category'][$v['category']] = intval($v['tensu']) * $v['kaisu'];
  #負担率
  $data[$v['original_pid']]['srd'][$v['srd']]['ratio'] = $v['ratio'];
  #診療日ごとの点数
  if(isset($data[$v['original_pid']]['srd'][$v['srd']]['tensu']))
    $data[$v['original_pid']]['srd'][$v['srd']]['tensu'] += intval($v['tensu']) * $v['kaisu'];
  else
    $data[$v['original_pid']]['srd'][$v['srd']]['tensu'] = intval($v['tensu']) * $v['kaisu'];
  #診療日ごとの負担額
  if(isset($data[$v['original_pid']]['srd'][$v['srd']]['copayment']))
    #$data[$v['original_pid']]['srd'][$v['srd']]['copayment'] += round($v['copayment'],-1);
    $data[$v['original_pid']]['srd'][$v['srd']]['copayment'] += $v['copayment'] * $v['kaisu'];
  else
    #$data[$v['original_pid']]['srd'][$v['srd']]['copayment'] = round($v['copayment'],-1);
    $data[$v['original_pid']]['srd'][$v['srd']]['copayment'] = $v['copayment'] * $v['kaisu'];

    #echo $v['srd']."---".round($v['copayment'],-1)."---".$v['copayment']."<br>\n";
  #明細データ
  $data[$v['original_pid']]['srd'][$v['srd']]['sid'][$v['sid']]['category'] = $v['category'];
  $data[$v['original_pid']]['srd'][$v['srd']]['sid'][$v['sid']]['shinryo_name'] = $v['shinryo_name'];
  $data[$v['original_pid']]['srd'][$v['srd']]['sid'][$v['sid']]['tensu'] = intval($v['tensu']);
  $data[$v['original_pid']]['srd'][$v['srd']]['sid'][$v['sid']]['kaisu'] = intval($v['kaisu']);
  #その他データ
  $data[$v['original_pid']]['data'] = $v;





  #介護保険マスター
  $sql = "SELECT *
          FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
          WHERE rek_service.original_pid = '".$v['original_pid']."'
          ";
  $stmt = $dbh->query($sql);
  $kaigo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

#echo $sql."<br>\n";
  #print_r($kaigo_data);exit;
  #介護保険データを$dataに格納
  foreach($kaigo_data as $v){
    #合計点数
    if(isset($data[$v['original_pid']]['srm'][$v['srm']]['tensu']))
      $data[$v['original_pid']]['srm'][$v['srm']]['tensu'] += intval($v['service_unit']) * $v['kaisu'];
    else
      $data[$v['original_pid']]['srm'][$v['srm']]['tensu'] = intval($v['service_unit']) * $v['kaisu'];
    #負担率
    if(isset($data[$v['original_pid']]['srm'][$v['srm']]['rate']))
      $data[$v['original_pid']]['srm'][$v['srm']]['rate'] += ((100 - $v['hoken_rate']) * (100 - $v['kouhi_rate']))/100;
    else
      $data[$v['original_pid']]['srm'][$v['srm']]['rate'] = ((100 - $v['hoken_rate']) * (100 - $v['kouhi_rate']))/100;
    #合計負担額
    if(isset($data[$v['original_pid']]['srm'][$v['srm']]['copayment']))
      $data[$v['original_pid']]['srm'][$v['srm']]['copayment'] += $v['service_unit'] * 10 * $v['kaisu'];
    else
      $data[$v['original_pid']]['srm'][$v['srm']]['copayment'] = $v['service_unit'] * 10 * $v['kaisu'];
    #明細データ
    $data[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['service_name'] = $v['service_name'];
    $data[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['service_unit'] = $v['service_unit'];
    $data[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['kaisu'] = $v['kaisu'];
    $data[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['tekiyo'] = $v['tekiyo'];
    #その他データ
  }
/*
  #自由診療マスター
  $sql = "SELECT *
          FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid
          WHERE app_date >= '$srd_start' AND app_date <= '$srd_end' and appendix.original_pid = '".$v['original_pid']."' and patient_info.disp = 0 and appendix.disp = 0 order by app_date";

  $stmt = $dbh->query($sql);
  $app_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
  print_r($app_data);
  #自由診療データを$dataに格納
  foreach($app_data as $v){
      #カテゴリーごとの合計金額
      if(isset($data[$v['original_pid']]['app_cat'][$v['app_cat']])){
          $data[$v['original_pid']]['app_cat'][$v['app_cat']] += intval($v['app_price']);
          $data[$v['original_pid']]['app_item'][$v['app_cat']] .= "/".$v['app_item'];
      }else{
          $data[$v['original_pid']]['app_cat'][$v['app_cat']] = intval($v['app_price']);
          $data[$v['original_pid']]['app_item'][$v['app_cat']] = $v['app_item'];
      }
  }
*/

}

foreach($data as $original_pid => $dt) {

  #自由診療マスター
  $sql = "SELECT *
          FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid
          WHERE app_date >= '$srd_start' AND app_date <= '$srd_end' and appendix.original_pid = '".$original_pid."' and patient_info.disp = 0 and appendix.disp = 0 order by app_date";

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
  $total_service_unit = 0;
foreach ($data as $original_pid => $patient_data) {

  #dataキーがない配列は名前なしなのでスルー。またoriginal_pid=0もスルー
/*  if(!isset($patient_data['data'])){
    continue;
  }elseif($original_pid == 0){
    continue;
  }
*/


  #original_pid=0はスルー
  if($original_pid == 0){
    continue;
  }
  if(!isset($patient_data['data']) ){
    continue;
  }
  if( isset($_REQUEST['testview']) && $_REQUEST['testview'] == 2){
    print_r($patient_data);
    #echo "aaa\n";
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

    $html = "<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">
<meta http-equiv=\"Content-Script-Type\" content=\"text/javascript\">
<title></title>
</head>
<body>";


    #請求番号
    $inv_id = $patient_data['data']['irkkcode'] . "-" . $srm . "-" . sprintf('%07d', strval($original_pid));


    ### ---------- 封筒窓 ---------- ###

    ## 封筒表紙（左窓）##
    $html .= "<div class=\"wrap\"></div>";
    if($format == "seikyu"){
        $html .= "<p class=\"header-left\">請求書｜訪問歯科診療</p>";
    } else if($format == "ryosyu"){
        $html .= "<p class=\"header-left\">領収書｜訪問歯科診療</p>";
    }

    #顧客情報
    $html .= "<p class=\"patient-address\">〒".$patient_data['data']['postal_code']."-".$patient_data['data']['postal_code2']."<br>".$patient_data['data']['prefecture']."<br>".$patient_data['data']['address1']."<br>".$patient_data['data']['address2']."</p>";

    if($patient_data['data']['shipto_name']){
        $html .= "<p class=\"patient-name\">".$patient_data['data']['shipto_name']." 様<br><span class=\"patient-name-sub\">（".$patient_data['data']['name']." 様分）</span></p>";
    } else {
        $html .= "<p class=\"patient-name\">".$patient_data['data']['name']." 様</p>";
    }

    $html .= "<p class=\"patient-id\"><span>No.$inv_id</span></p>";


    #注意書き
    if($format == "seikyu"){
        $html .= "<p class=\"notes\">※診療費(自己負担金）を、ご請求申し上げます。<br>
                ※保険証の変更等ございましたらご連絡いただきますよう宜しくお願い申し上げます。</p>";
    } else if($format == "ryosyu"){
        $html .= "<p class=\"notes\">※印紙税法、第5条　第1項により非課税。<br>
                ※医療費控除を受けるために必要です再発行はできませんので、大切に保管して下さい。</p>";
    }

    ## 封筒表紙（右窓）##
    $html .= "<p class=\"header-right\">医療機関名 <span class=\"header-right-sub\">※お問い合わせはこちらへ</span></p>";
    $html .= "<p class=\"irkk-name\">".$patient_data['data']['irkkname']."</p>";
    $html .= "<p class=\"irkk-address\">〒".$patient_data['data']['irkk_postal_code']."<br>".$patient_data['data']['irkk_prefecture']."<br>".$patient_data['data']['irkk_address1']."<br>".$patient_data['data']['irkk_address2']."<br>".$patient_data['data']['irkk_tel']."</p><br>";

    if($format == "seikyu"){
        $html .= "<p class=\"irkk-account\">".$patient_data['data']['irkk_bank_name']." ".$patient_data['data']['irkk_bank_branch']." ".$patient_data['data']['irkk_bank_clasification']." ".$patient_data['data']['irkk_bank_no']."</p>";
    }


    ## 折位置表示 ##
    $html .= "<p class=\"fold-point\">▶</p>";
    ### ---------- 点数表 ---------- ###
    #カテゴリーごとの合計点数を$m_category[$k]['tensu']に格納／医療保険の合計金額を$total_copaymentに加算
    #定義
    #「O：病理診断」カテゴリに、「保険：その他」を合算する
    #「T」カテゴリとまだみぬ「P,Q,R,S,U,V,W,X,Z」も用意しておいて、「O：病理診断 = 保険：その他」とする
    #「Y」は「F：投薬」に合算する
    #保険外の「その他」は保険のその他とは異なる
    #カテゴリの定義は、歯科のものである。医科とは異なる
    foreach($patient_data['srd'] as $key => $shinryo_cat){
        foreach($m_category as $k => $v){
            if(array_key_exists($k , $shinryo_cat['category'])){
              #Yは「F：投薬」に合算
              if($k == 'Y'){
                $m_category['F']['tensu'] += $shinryo_cat['category'][$k];
              }elseif($k == 'P' ||
                      $k == 'Q' ||
                      $k == 'R' ||
                      $k == 'S' ||
                      $k == 'T' ||
                      $k == 'U' ||
                      $k == 'V' ||
                      $k == 'W' ||
                      $k == 'X' ||
                      $k == 'Z'){
                $m_category['O']['tensu'] += $shinryo_cat['category'][$k];
              #「T」カテゴリとまだみぬ「P,Q,R,S,U,V,W,X,Z」は「O：病理診断 = 保険：その他」に合算
              }else{
                $m_category[$k]['tensu'] += $shinryo_cat['category'][$k];
              }
                $total_tensu += $shinryo_cat['category'][$k];
            }
        }
        $total_copayment += $shinryo_cat['copayment'];
    }

    #介護保険の合計点数を$total_service_unitに格納／介護保険の合計金額を$total_copaymentに加算
    foreach($patient_data['srm'] as $k => $v){
        $total_service_unit += $v['tensu'];
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


    #請求額
    $html .= "<p id=\"shinryo-month\">".date('Y年m月',strtotime($patient_data['data']['srm']))."分</p>";
    if($format == "seikyu"){
        $html .= "<p id=\"total-copayment\">ご請求額　".number_format($total_copayment)." 円</p>";
    } else if($format == "ryosyu"){
        $html .= "<p id=\"total-copayment\">領収額　".number_format(round($total_copayment,-1))." 円</p>";
        $html .= "<p id=\"ryosyu-date\">領収日<br>2019/07/07</p>";
    }

    #保険
    $html .= "<table class='disp_table' id=\"hoken-table\"><tr>
            <th rowspan=\"6\" class=\"side-header border_rb\">保険</th>";
    $html .= "<th class=\"color333 hoken-col border_rb\">".$m_category['A']['title']."</th>
            <th class=\"color333 hoken-col border_rb\">".$m_category['B']['title']."</th>
            <th class=\"color333 hoken-col border_rb\">".$m_category['C']['title']."</th>
            <th class=\"color333 hoken-col border_rb\">".$m_category['D']['title']."</th>
            <th class=\"color333 hoken-col border_rb\">".$m_category['E']['title']."</th>
            <th class=\"color333 hoken-col border_rb\">".$m_category['F']['title']."</th></tr>";
    $html .= "<tr>
            <td class=\"tensu-row border_rb\">".number_format($m_category['A']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['B']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['C']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['D']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['E']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['F']['tensu'])."点</td></tr>";
    $html .= "<tr>
            <th class=\"color333 border_rb\">".$m_category['G']['title']."</th>
            <th class=\"color333 font18 border_rb\">".$m_category['H']['title']."</th>
            <th class=\"color333 border_rb\">".$m_category['I']['title']."</th>
            <th class=\"color333 border_rb\">".$m_category['J']['title']."</th>
            <th class=\"color333 border_rb\">".$m_category['K']['title']."</th>
            <th class=\"color333 border_rb\">".$m_category['L']['title']."</th></tr>";
    $html .= "<tr>
            <td class=\"tensu-row border_rb\">".number_format($m_category['G']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['H']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['I']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['J']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['K']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['L']['tensu'])."点</td></tr>";
    $html .= "<tr>
            <th class=\"color333 font16 border_rb\">".$m_category['M']['title']."</th>
            <th class=\"color333 border_rb\">".$m_category['N']['title']."</th>
            <th class=\"color333 border_rb\">".$m_category['O']['title']."</th>
            <th class=\"color333 border-border-top border_b\">合計</th>
            <th class=\"color333 border-border-top font14 border_b\">居宅療養管理指導(介護保険)</th>
            <th class=\"color333 border-border-top border_b\">一部負担金</th></tr>";

    #介護保険は1円まで金額出す。一部負担金の四捨五入を解除
    $html .= "<tr>
            <td class=\"tensu-row border_rb\">".number_format($m_category['M']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['N']['tensu'])."点</td>
            <td class=\"tensu-row border_rb\">".number_format($m_category['O']['tensu'])."点</td>
            <td class=\"tensu-row border-border-bottom\">".number_format($total_tensu)."点</td>
            <td class=\"tensu-row border-border-bottom\">".number_format($total_service_unit)."単位</td>
            <td class=\"tensu-row border-border-bottom\">".number_format($ichibufutankin)."円</td></tr>";
    $html .= "</table><br/>\n";

    #保険外負担
    $html .= "<div id=\"hokengai-table\"><table class='disp_table'>";
    $html .= "<tr><th rowspan=\"3\" class=\"side-header border_rb\">保険外負担</th>";
    $html .= "<th class=\"hokengai-col border_rb\">自由診療</th>
            <th class=\"hokengai-col border_rb\">販売品</th>
            <th class=\"hokengai-col border_rb\">その他</th></tr>";

    $app_cat1 = isset($patient_data['app_cat']['1']) ? $patient_data['app_cat']['1'] : 0;
    $app_cat2 = isset($patient_data['app_cat']['2']) ? $patient_data['app_cat']['2'] : 0;
    $app_cat3 = isset($patient_data['app_cat']['3']) ? $patient_data['app_cat']['3'] : 0;
    $html .= "<tr><td class='border_r'>".number_format($app_cat1)."円</td>
            <td class='border_r'>".number_format($app_cat2)."円</td>
            <td class='border_r'>".number_format($app_cat3)."円</td></tr>";
/*
    $html .= "<tr><td class='border_r'>".number_format($patient_data['app_cat']['1'])."円</td>
            <td class='border_r'>".number_format($patient_data['app_cat']['2'])."円</td>
            <td class='border_r'>".number_format($patient_data['app_cat']['3'])."円</td></tr>";
*/
    if(isset($patient_data['app_item']['1']) && $patient_data['app_item']['1'] != "") $app_item1 = $patient_data['app_item']['1']; else $app_item1 = "<br>";
    if(isset($patient_data['app_item']['2']) && $patient_data['app_item']['2'] != "") $app_item2 = $patient_data['app_item']['2']; else $app_item2 = "<br>";
    if(isset($patient_data['app_item']['3']) && $patient_data['app_item']['3'] != "") $app_item3 = $patient_data['app_item']['3']; else $app_item3 = "<br>";
    /*$html .= "<tr><td class=\"uchiwake border_rb\">".$patient_data['app_item']['1']."\n</td>
            <td class=\"uchiwake border_rb\">".$patient_data['app_item']['2']."\n</td>
            <td class='border_rb'>".$patient_data['app_item']['3']."\n</td></tr>";*/
    $html .= "<tr><td class=\"uchiwake border_rb\">".$app_item1."\n</td>
            <td class=\"uchiwake border_rb\">".$app_item2."\n</td>
            <td class='border_rb'>".$app_item3."\n</td></tr>";
    $html .= "</table></div>";

    #未収金／過剰金
    #介護保険は1円まで金額出す。
    $html .= "<div id=\"misyu-kajo-table\"><table class='disp_table'>";
    $html .= "<tr><th class=\"color333 border_rb\">前回未収金</th>
                <th class=\"color333 border_rb\">前回過剰金</th>
                <th class=\"color333 border_rb\">今回ご請求額</th></tr>";
    $html .= "<tr><td class='border_r'>0円</td><td class='border_r'>0円</td><td class='border_r'>".number_format($total_copayment)."円</td></tr>";
    $html .= "<tr><td class='border_rb'>&nbsp;</td><td class='border_rb'>&nbsp;</td><td class='border_rb'>&nbsp;</td></tr>";
    $html .= "</table></div>";

    #clearfix
    $html .= "<div class=\"clearfix\"></div><br/>\n";


    ### ----------- 明細 ---------- ###

    #タイトル
    $html .= "<p id=\"shinryo-meisai\">診療明細書</p>";

    $row_count = 0;
    $global_count = 0;
    $first_flag = true;

    #医療保険（左列）
    $html .= "<div id=\"iryo-meisai-table\">
            <table class='disp_table'><tr><th colspan=\"5\" class='border_rb'>医療保険</th></tr><tr>
            <th class=\"category-col border_b\">部</th>
            <th class=\"border_b\">項目</th>
            <th class=\"tensu-col border_b\">点数</th>
            <th class=\"x-col border_b\"></th>
            <th class=\"kaisu-col border_rb\">回数</th></tr>";

    foreach($patient_data['srd'] as $k => $v){


      if($row_count == 0 && $global_count > 0){

          $html .= "{$tmp}<div id=\"iryo-meisai-table\"><table class='disp_table'><tr><th colspan=\"5\" class='border_rb'>医療保険</th></tr><tr>
          <th class=\"category-col border_b\">部</th>
          <th class=\"border_b\">項目</th>
          <th class=\"tensu-col border_b\">点数</th>
          <th class=\"x-col border_b\"></th>
          <th class=\"kaisu-col border_rb\">回数</th></tr>";

      }

      #ルーティン①：日付の行の処理
        $html .= "<tr><td colspan=\"5\" class=\"date-row border_r\">●".date('Y年m月d日',strtotime($k))."</td></tr>";

        $row_count++;

        if($global_count < 2){
            $check_offset = $newpage_offset;
        }else{
            $check_offset = $newpage_offset2;
        }

        if($row_count == $check_offset){
            $row_count = 0;
            $global_count++;
            if($global_count%2 == 0){
                $tmp = "<div class='clearfix'>&nbsp;</div>";
            }else{
                $tmp = "";
            }

            $html .= "<tr><td colspan=\"5\" class='border_rb'></td></tr></table></div>{$tmp}<div id=\"iryo-meisai-table\"><table class='disp_table'><tr><th colspan=\"5\" class='border_rb'>医療保険</th></tr><tr>
            <th class=\"category-col border_b\">部</th>
            <th class=\"border_b\">項目</th>
            <th class=\"tensu-col border_b\">点数</th>
            <th class=\"x-col border_b\"></th>
            <th class=\"kaisu-col border_rb\">回数</th></tr>";
        }

        #ルーティン②：診療行の処理
        foreach($patient_data['srd'][$k]['sid'] as $meisai){
            $tmp_shinryo = explode("（",$meisai['shinryo_name']);
/*
            $html .= "<tr>
            <td class=\"category-col non-border\">".$meisai['category']."</td>
            <td class=\"item-col non-border\">".$tmp_shinryo[0]."</td>
            <td class=\"tensu-col non-border\">".number_format($meisai['tensu'])."</td>
            <td class=\"x-col non-border\">×</td>
            <td class=\"kaisu-col border_r\">".$meisai['kaisu']."</td></tr>";
*/
            $html .= "<tr>
            <td class=\"category-col non-border\">".$meisai['category']."</td>
            <td class=\"item-col non-border\">".$meisai['shinryo_name']."</td>
            <td class=\"tensu-col non-border\">".number_format($meisai['tensu'])."</td>
            <td class=\"x-col non-border\">×</td>
            <td class=\"kaisu-col border_r\">".$meisai['kaisu']."</td></tr>";

            $row_count++;

            if($global_count < 2){
                $check_offset = $newpage_offset;
            }else{
                $check_offset = $newpage_offset2;
            }
            if($row_count == $check_offset){
                $row_count = 0;
                $global_count++;
            if($global_count%2 == 0){
                $tmp = "<div class='clearfix'>&nbsp;</div>";
            }else{
                $tmp = "";
            }

            $html .= "<tr><td colspan=\"5\" class='border_rb'></td></tr></table></div>{$tmp}<div id=\"iryo-meisai-table\"><table class='disp_table'><tr><th colspan=\"5\" class='border_rb'>医療保険</th></tr><tr>
            <th class=\"category-col border_b\">部</th>
            <th class=\"border_b\">項目</th>
            <th class=\"tensu-col border_b\">点数</th>
            <th class=\"x-col border_b\"></th>
            <th class=\"kaisu-col border_rb\">回数</th></tr>";
            }
        }

        #ルーティン③：小計行の処理：この行で全診療レコードが完了する可能性があるため完了時の処理
        $html .= "<tr><td class=\"sum-row border_r\" colspan=\"5\"><p>小計:".number_format($patient_data['srd'][$k]['tensu'])."点 　 ".number_format($patient_data['srd'][$k]['copayment'])."円 　 負担:".$patient_data['srd'][$k]['ratio']."%</p></td></tr>";

        $row_count++;

        if($global_count < 2){
            $check_offset = $newpage_offset;
        }else{
            $check_offset = $newpage_offset2;
        }
        if($row_count == $check_offset){
            $row_count = 0;
            $global_count++;
            if($global_count%2 == 0){
                $tmp = "<div class='clearfix'>&nbsp;</div>";
            }else{
                $tmp = "";
            }

            #ここが原因？ここをコメントアウトすることによる問題は？
            #$html .= "<tr><td colspan=\"5\" class='border_rb'></td></tr></table></div>";

            /*
            $html .= "{$tmp}<div id=\"iryo-meisai-table\"><table class='disp_table'><tr><th colspan=\"5\" class='border_rb'>医療保険</th></tr><tr>
            <th class=\"category-col non-border\">部</th>
            <th class=\"non-border\">項目</th>
            <th class=\"tensu-col non-border\">点数</th>
            <th class=\"x-col non-border\"></th>
            <th class=\"kaisu-col border_r\">回数</th></tr>";
            */
        }
    }

    $html .= "<tr><td colspan=\"5\" class='border_rb'></td></tr></table></div>";


    #介護保険（右列）
    foreach($patient_data['srm'] as $k => $v){
        $html .= "<div id=\"iryo-meisai-table\">
        <table class='disp_table'><tr><th colspan=\"5\" class='border_rb'>介護保険</th></tr><tr>
        <th class=\"border_b meisai-title-row\">項目</th>
        <th class=\"tensu-col border_b meisai-title-row\">単位</th>
        <th class=\"x-col border_b meisai-title-row\"></th>
        <th class=\"kaisu-col border_b meisai-title-row\">回数</th>
        <th class=\"border_rb meisai-title-row\">算定日</th></tr>";
        foreach ($patient_data['srm'][$k]['sid'] as $meisai){
            $copeyment = 10 * $patient_data['srm'][$k]['tensu'] * $patient_data['srm'][$k]['rate'] /100;
            $html .= "<tr><td class=\"item-col non-border\">".$meisai['service_name']."</td><td class=\"tensu-col non-border\">".$meisai['service_unit']."</td>
            <td class=\"x-col non-border\">×</td>
            <td class=\"kaisu-col non-border\">".$meisai['kaisu']."</td>
            <td class=\"date-col border_r\" align=center>".$meisai['tekiyo']."</td></tr>";
        }
        $html .= "<tr><td class=\"sum-row-kaigo border_rb\" colspan=\"5\"><p>小計:".number_format($patient_data['srm'][$k]['tensu'])."単位　　 ".number_format($copeyment)."円 　負担:".$patient_data['srm'][$k]['rate']."%</p></td></tr></table></div>";
    }

    #clearfix
    $html .= "<div class=\"clearfix\"></div>";
    $total_tensu = 0;
    $total_copayment = 0;
    $total_service_unit = 0;


    #CSS
    $html .= "<style>
            .wrap{
              position:relative;
              width: 1400px;
              height: 665px;
            }
            .header-left{
              position:absolute;
              top: 100px;
              left: 86.74px;
              width: 433.71px;
              background-color: #555;
              border-radius: 5px;
              font-size:24px;
              letter-spacing:0.25em;
              padding:5px;
              color: #fff;
              text-align:center;
            }
            .patient-address{
              position:absolute;
              top:202.4px;
              left:195.17px;
              font-size:20px;
              font-weight:bold;
              isplay: inline-block;
              vertical-align:top;
            }
            .patient-name{
              position:absolute;
              top:346.97px;
              left:195.17px;
              font-size:32px;
              font-weight:bold;
            }
            .patient-id{
              position:absolute;
              top:448.17px;
              left:195.17px;
              font-size:20px;
              font-weight:bold;
            }
            .notes{
              position:absolute;
              top:549.37px;
              left:104.57px;
              font-size:18px;
            }

            .header-right{
              position:absolute;
              top: 390.34px;
              right: 93.97px;
              width: 433.71px;
              background-color: #555;
              border-radius: 5px;
              font-size:24px;
              padding:5px;
              text-align:center;
              color: #fff;
            }
            .irkk-name{
              position:absolute;
              top: 448.17px;
              left: 990px;
              border-radius: 5px;
              font-size:18px;
              font-weight:bold;
              width:425px;
              letter-spacing:-1px;
            }
            .irkk-address{
              position:absolute;
              top:484.31px;
              left:990px;
              font-size:16px;
              font-weight:bold;
              width:425px;
            }
            .irkk-account{
              position:absolute;
              top:600px;
              left:990px;
              font-size:18px;
              font-weight:bold;
            }
            .fold-point{
              position:absolute;
              top:680px;
              left: 30px;
              font-size:32px;
              font-weight:bold;
            }

            .patient-name-sub{
              font-size: 20px;
              font-weight:bold;
            }
            .header-right-sub{
              font-size: 20px;
            }


            #shinryo-month{
                position:absolute;
                top:730px;
                left:120px;
                font-size:36px;
                text-align:center;
                width:400px;
            }
            #total-copayment{
                margin: 30px auto;
                font-size:36px;
                text-align:center;
                width:400px;
                border-bottom:1px solid #262626;
            }
            #ryosyu-date{
                position:absolute;
                top:730px;
                left: 1080px;
                font-size: 20px;
                text-align:center;
            }

            .disp_table{
              border-top:1px solid #262626;
              border-left:1px solid #262626;
              border-spacing:0;
              border-collapse:none;
              text-align:center;
              font-size:20px;
            }
            .disp_th,.disp_td{
              /*border-right:1px solid #262626;
              border-bottom:1px solid #262626;*/
              padding:5px;
              font-size:20px;
            }
            .border_rb{
              border-right:1px solid #262626;
              border-bottom:1px solid #262626;
            }
            .border_r{
              border-right:1px solid #262626;
            }
            .border_b{
              border-bottom:1px solid #262626;
            }
            th{
              background-color: #EEEDED;
            }
            th,td{padding:5px;}

            #hoken-table{
              table-layout: fixed;
              width: 1400px;
            }
            .hoken-col{
              width:15%;
            }
            .tensu-row{
                height:60px;
                font-size: 24px;
            }

            #hokengai-table{
              width:60%;
              float:left;
              padding-right:50px;
            }
            #hokengai-table table{
              table-layout: fixed;
              width:100%;
            }
            .hokengai-col{
              width:30%;
            }

            #misyu-kajo-table{
              width:auto;
              float:left;
            }
            #misyu-kajo-table table{
              width:100%;
            }

            .border-border-top{
              border-top:3px solid #262626;
            }
            .border-border-bottom{
              border-bottom:3px solid #262626;
            }
            .border-border-top,
            .border-border-bottom{
              border-left:3px solid #262626;
              border-right:3px solid #262626;
            }

            .side-header{
              width:3.8em;
            }
            .uchiwake{
              text-align:center;
            }

            #shinryo-meisai{
              font-size:36px;
              width:500px;
              margin: 50px auto 30px auto;
              text-align:center;
              border-bottom:3px solid #262626;
            }

            #iryo-meisai-table{
              width:665px;
              float:left;
              padding-right:30px;
            }
            #iryo-meisai-table table{
              width:100%;
            }
            .non-border{
              border:none;
            }
            .meisai-title-row{
              border-bottom:1px solid #262626;
            }
            .date-row{
              text-align:left;
              border-bottom:none;
              padding-bottom:0;
            }
            .category-col{width:80px}
            .item-col{text-align:left}
            .tensu-col{width:80px}
            .x-col{width:50px}
            .kaisu-col{width:80px}
            .sum-row{
              text-align:right;
              border-top:none;
            }
            .sum-row-kaigo{
              text-align:right;
              border-top:double;
            }
            .item-col{
              font-size:16px;
            }
            .sum-row p,
            .sum-row-kaigo p{
              padding-right:50px;
              border-bottom:1px solid #262626;
            }


            #kaigo-meisai-table{
              wiedth:auto;
              float:left;
            }
            #kaigo-meisai-table table{
              width:100%;
            }
            .date-col{
              width:150px;
              text-align:left;
            }
            .font14{
              font-size:14px;
            }
            .font16{
              font-size:16px;
            }
            .font18{
              font-size:18px;
            }
            .color333{
                color:#333;
            }

            </style>";

$html .= "</body></html>";
    #echo $html;
    #if($cnt == 4){
    #  echo $html;
    #  exit;
    #}

    #if($cnt > 4){
      $mpdf->WriteHTML($html);
    #}

    if($cnt < count($data)){
        $mpdf->AddPage();
    }
        $cnt++;
    if(!isset($_REQUEST['testview'])){
        #if($cnt > 5) break;
    }

}
$mpdf->Output("/var/www/html/cl.netstars.vision/public_html/downloadpdf/".substr($srd_start,0,6)."_".$format.".pdf",\Mpdf\Output\Destination::FILE);
#$mpdf->Output();
exit;

$srd_start = $srd_end = $format = "";

?>
