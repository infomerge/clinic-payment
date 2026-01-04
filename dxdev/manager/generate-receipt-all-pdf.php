<?php
include_once "../common/smarty_settings.php";
include_once "../class/config.php";

include("../pdf/mpdf/mpdf.php");

$html = "アイウエオあいうえお";

$mpdf=new mPDF('ja+aCJK',array(257,364),
0,//フォントサイズ default 0
'',//フォントファミリー
10,//左マージン
10,//右マージン
6,//トップマージン
0,//ボトムマージン
0,//ヘッダーマージン
''
);
$mpdf->dpi = 150;
$mpdf->img_dpi = 150;
$mpdf->debug = true;
$mpdf->debugfonts = true;


//対象の期間と患者名を取得
#POST
$srd_start = $_GET["srd_start"];
$srd_end = $_GET["srd_end"];
$format = $_GET["format"];
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
        WHERE srd >= '$srd_start' AND srd <= '$srd_end' order by srd";
$stmt = $dbh->query($sql);
$iryo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
#介護保険マスター
$sql = "SELECT *
        FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
        #WHERE srd >= '$srd_start' AND srd <= '$srd_end'
        ";
$stmt = $dbh->query($sql);
$kaigo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
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

#print_r($iryo_data);exit;
#医療保険データの保険カテゴリーごとの点数と、診療日ごとの負担額と、その他データを$dataに格納
$data = array();
$buf_srd = "";
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
    #$data[$v['original_pid']]['srd'][$v['srd']]['copayment'] += round($v['copayment'],-1);
    $data[$v['original_pid']]['srd'][$v['srd']]['copayment'] += $v['copayment'];
  else
    #$data[$v['original_pid']]['srd'][$v['srd']]['copayment'] = round($v['copayment'],-1);
    $data[$v['original_pid']]['srd'][$v['srd']]['copayment'] = $v['copayment'];

    #echo $v['srd']."---".round($v['copayment'],-1)."---".$v['copayment']."<br>\n";
  #明細データ
  $data[$v['original_pid']]['srd'][$v['srd']]['sid'][$v['sid']]['category'] = $v['category'];
  $data[$v['original_pid']]['srd'][$v['srd']]['sid'][$v['sid']]['shinryo_name'] = $v['shinryo_name'];
  $data[$v['original_pid']]['srd'][$v['srd']]['sid'][$v['sid']]['tensu'] = intval($v['tensu']);
  $data[$v['original_pid']]['srd'][$v['srd']]['sid'][$v['sid']]['kaisu'] = intval($v['kaisu']);
  #その他データ
  $data[$v['original_pid']]['data'] = $v;
}
#print_r($data);exit;
#介護保険データを$dataに格納
#$data_k = array();
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
foreach($data as $original_pid => $v) {
  foreach($v['srd'] as $kk => $vv){
    #echo $original_pid."--".$kk."--".$vv['copayment']."---".round($vv['copayment'],-1)."<br>\n";
    $data[$original_pid]['srd'][$kk]['copayment'] = round($vv['copayment'],-1);
  }
  foreach($v['srm'] as $kk => $vv){
    #echo $original_pid."--".$kk."--".$vv['copayment']."---".round($vv['copayment'],-1)."<br>\n";
    $data[$original_pid]['srm'][$kk]['copayment'] = round($vv['copayment'],-1);
  }
}

if(isset($_REQUEST['testview'])){
  print_r($data);exit;
}

#個人毎PDFデータ生成
$cnt = 1;
foreach ($data as $original_pid => $patient_data) {
  $html = "";
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
  $m_category['-'] = array('kigo'=>'-', 'title'=>'その他', 'tensu'=>0);

  #請求番号
  $inv_id = $patient_data['data']['irkkcode'] . "-" . $patient_data['data']['srm'] . "-" . sprintf('%07d', strval($original_pid));


  ### ---------- 封筒窓 ---------- ###

  ## 封筒表紙（左窓）##
  $html .= "<div class=\"wrap\"></div>";
  if($format == "seikyu"){
    $html .= "<p class=\"header-left\">請求書｜訪問歯科診療</p>";
  } else if($format == "ryosyu"){
    $html .= "<p class=\"header-left\">領収書｜訪問歯科診療</p>";
  }
  #顧客情報
  $html .= "<p class=\"patient-address\">〒".$patient_data['data']['postal_code']."<br>".$patient_data['data']['address']."<br>".$patient_data['data']['address']."<br>".$patient_data['data']['address']."</p>";
  $html .= "<p class=\"patient-name\">".$patient_data['data']['name']." 様<br><span class=\"patient-name-sub\">（".$patient_data['data']['name']." 様分）</span></p>";
  $html .= "<p class=\"patient-id\"><span>No.$inv_id</span></p>";
  #注意書き
  if($format == "seikyu"){
    $html .= "<p class=\"notes\">※診療費(自己負担金）を、ご請求申し上げます。<br>
                ※また、保険証の変更等ございましたらご連絡いただきますよう宜しくお願い申し上げます。</p>";
  } else if($format == "ryosyu"){
    $html .= "<p class=\"notes\">※印紙税法、第5条　第1項により非課税。<br>
                ※医療費控除を受けるために必要です再発行はできませんので、大切に保管して下さい。</p>";
  }

  ## 封筒表紙（右窓）##
  $html .= "<p class=\"header-right\">医療機関名 <span class=\"header-right-sub\">※お問い合わせはこちらへ</span></p>";
  $html .= "<p class=\"irkk-name\">".$patient_data['data']['irkkname']."</p>";
  $html .= "<p class=\"irkk-address\">〒".$patient_data['data']['irkk_postal_code']."<br>".$patient_data['data']['irkk_address']."<br>".$patient_data['data']['irkk_address']."<br>".$patient_data['data']['irkk_address']."<br>".$patient_data['data']['irkk_tel']."</p><br>";

  if($format == "seikyu"){
    $html .= "<p class=\"irkk-account\">金融機関名　支店名　普通　口座番号</p>";
  }


  ## 折位置表示 ##
  $html .= "<p class=\"fold-point\">▶</p>";

  ### ---------- 点数表 ---------- ###

  #カテゴリーごとの合計点数を$m_category[$k]['tensu']に格納
  #$total_tensu = 0;
  #$total_copayment = 0;
  foreach($patient_data['srd'] as $key => $shinryo_cat){
    #print_r($shinryo_cat);
    #print_r($shinryo_cat);
    foreach($m_category as $k => $v){
      if(array_key_exists($k , $shinryo_cat['category'])){

        #echo $shinryo_cat['category'][$k]."---".$shinryo_cat['copayment']."<br>\n";

        $m_category[$k]['tensu'] += $shinryo_cat['category'][$k];
        $total_tensu += $shinryo_cat['category'][$k];
        #$total_copayment += $shinryo_cat['category'][$k];
      }
    }
    #$total_tensu += number_format($shinryo_cat['tensu']);
    $total_copayment += $shinryo_cat['copayment'];
  }

  #介護保険の合計点数を$total_service_unitに格納
  foreach($patient_data['srm'] as $k => $v){
    $total_service_unit += number_format($patient_data['srm'][$k]['tensu']);
  }
  #exit;
#print_r($patient_data['srd']);
#echo $total_tensu;exit;

  #請求額
  $html .= "<p id=\"shinryo-month\">".date('Y年m月',strtotime($patient_data['data']['srm']))."分</p>";
  if($format == "seikyu"){
    $html .= "<p id=\"total-copayment\">ご請求額　".number_format(round($total_copayment,-1))." 円</p>";
  } else if($format == "ryosyu"){
    $html .= "<p id=\"total-copayment\">領収額　".number_format(round($total_copayment,-1))." 円</p>";
    $html .= "<p id=\"ryosyu-date\">領収日<br>2019/07/07</p>";
  }
  #保険
  $html .= "<table id=\"hoken-table\"><tr>
            <th rowspan=\"6\" class=\"side-header\">保険</th>";
  $html .= "<th class=\"color333 hoken-col\">".$m_category['A']['title']."</th>
            <th class=\"color333 hoken-col\">".$m_category['B']['title']."</th>
            <th class=\"color333 hoken-col\">".$m_category['C']['title']."</th>
            <th class=\"color333 hoken-col\">".$m_category['D']['title']."</th>
            <th class=\"color333 hoken-col\">".$m_category['E']['title']."</th>
            <th class=\"color333 hoken-col\">".$m_category['F']['title']."</th></tr>";
  $html .= "<tr>
            <td class=\"tensu-row\">".number_format($m_category['A']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['B']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['C']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['D']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['E']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['F']['tensu'])."点</td></tr>";
  $html .= "<tr>
            <th class=\"color333\">".$m_category['G']['title']."</th>
            <th class=\"color333 font18\">".$m_category['H']['title']."</th>
            <th class=\"color333\">".$m_category['I']['title']."</th>
            <th class=\"color333\">".$m_category['J']['title']."</th>
            <th class=\"color333\">".$m_category['K']['title']."</th>
            <th class=\"color333\">".$m_category['L']['title']."</th></tr>";
  $html .= "<tr>
            <td class=\"tensu-row\">".number_format($m_category['G']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['H']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['I']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['J']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['K']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['L']['tensu'])."点</td></tr>";
  $html .= "<tr>
            <th class=\"color333 font16\">".$m_category['M']['title']."</th>
            <th class=\"color333\">".$m_category['N']['title']."</th>
            <th class=\"color333\">".$m_category['O']['title']."</th>
            <th class=\"color333 border-border-top\">合計</th>
            <th class=\"color333 border-border-top font14\">居宅療養管理指導(介護保険)</th>
            <th class=\"color333 border-border-top\">一部負担金</th></tr>";
  $html .= "<tr>
            <td class=\"tensu-row\">".number_format($m_category['M']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['N']['tensu'])."点</td>
            <td class=\"tensu-row\">".number_format($m_category['O']['tensu'])."点</td>
            <td class=\"tensu-row border-border-bottom\">".number_format($total_tensu)."点</td>
            <td class=\"tensu-row border-border-bottom\">".number_format($total_service_unit)."単位</td>
            <td class=\"tensu-row border-border-bottom\">".number_format(round($total_copayment,-1))."円</td></tr>";
  $html .= "</table><br/>\n";
  #保険外負担
  $html .= "<div id=\"hokengai-table\"><table>";
  $html .= "<tr><th rowspan=\"4\" class=\"side-header\">保険外負担</th></tr>";
  $html .= "<th class=\"hokengai-col\">保険外併用療養等</th>
            <th class=\"hokengai-col\">その他</th>
            <th class=\"hokengai-col\">販売品</th></tr>";
  $html .= "<tr><td>0円</td>
            <td>0円</td>
            <td>0円</td></tr>";
  $html .= "<tr><td class=\"uchiwake\"><br/>\n</td>
            <td class=\"uchiwake\"><br/>\n</td>
            <td><br/>\n</td></tr>";
  $html .= "</table></div>";
  #未収金／過剰金
  $html .= "<div id=\"misyu-kajo-table\"><table>";
  $html .= "<tr><th class=\"color333\">前回未収金</th>
                <th class=\"color333\">前回過剰金</th>
                <th class=\"color333\">今回ご請求額</th></tr>";
  $html .= "<tr><td>0円</td><td>0円</td><td>0円</td></tr>";
  $html .= "</table></div>";
  #clearfix
  $html .= "<div class=\"clearfix\"></div><br/>\n";


  ### ----------- 明細 ---------- ###

  #タイトル
  $html .= "<p id=\"shinryo-meisai\">診療明細書</p>";

  #医療保険（左列）
  $html .= "<div id=\"iryo-meisai-table\">
            <table><tr><th colspan=\"5\">医療保険</th></tr><tr>
            <th class=\"category-col non-border\">部</th>
            <th class=\"non-border\">項目</th>
            <th class=\"tensu-col non-border\">点数</th>
            <th class=\"x-col non-border\"></th>
            <th class=\"kaisu-col non-border\">回数</th></tr>";
  foreach($patient_data['srd'] as $k => $v){
    $html .= "<tr><td colspan=\"5\" class=\"date-row\">●".date('Y年m月d日',strtotime($k))."</td></tr>";
    foreach($patient_data['srd'][$k]['sid'] as $meisai){
      $html .= "<tr class=\"non-border\">
        <td class=\"category-col non-border\">".$meisai['category']."</td>
        <td class=\"item-col non-border\">".$meisai['shinryo_name']."</td>
        <td class=\"tensu-col non-border\">".number_format($meisai['tensu'])."</td>
        <td class=\"x-col non-border\">×</td>
        <td class=\"kaisu-col non-border\">".$meisai['kaisu']."</td></tr>";
    }
    $html .= "<tr><td class=\"sum-row\" colspan=\"5\"><p>小計:".number_format($patient_data['srd'][$k]['tensu'])."点 　 ".number_format($patient_data['srd'][$k]['copayment'])."円 　 負担:".$patient_data['srd'][$k]['ratio']."%</p></td></tr>";
  }
  $html .= "</table></div>";

  #介護保険（右列）
  foreach($patient_data['srm'] as $k => $v){
    $html .= "<div id=\"iryo-meisai-table\">
                <table><tr><th colspan=\"5\">介護保険</th></tr><tr>
                <th class=\"non-border meisai-title-row\">項目</th>
                <th class=\"tensu-col non-border meisai-title-row\">単位</th>
                <th class=\"x-col non-border meisai-title-row\"></th>
                <th class=\"kaisu-col non-border meisai-title-row\">回数</th>
                <th class=\"non-border meisai-title-row\">算定日</th></tr>";
    foreach ($patient_data['srm'][$k]['sid'] as $meisai){
      $copeyment = 10 * $patient_data['srm'][$k]['tensu'] * $patient_data['srm'][$k]['rate'] /100;
      $html .= "<tr><td class=\"item-col non-border\">".$meisai['service_name']."</td><td class=\"tensu-col non-border\">".$meisai['service_unit']."</td>
      <td class=\"x-col non-border\">×</td>
      <td class=\"kaisu-col non-border\">".$meisai['kaisu']."</td>
      <td class=\"date-col non-border\">".$meisai['tekiyo']."</td></tr>";
    }
    $html .= "<tr><td class=\"sum-row-kaigo\" colspan=\"5\"><p>小計:".number_format($patient_data['srm'][$k]['tensu'])."点　　 ".number_format($copeyment)."円 　負担:".$patient_data['srm'][$k]['rate']."%</p></td></tr></table></div>";
  }

  #clearfix
  $html .= "<div class=\"clearfix\"></div>";


  $total_tensu = $total_copayment = $total_service_unit = "0";

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
              left:144.57px;
              font-size:20px;
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
              left: 1020px;
              border-radius: 5px;
              font-size:26px;
              font-weight:bold;
            }
            .irkk-address{
              position:absolute;
              top:484.31px;
              left:1020px;
              font-size:16px;
              font-weight:bold;
            }
            .irkk-account{
              position:absolute;
              top:600px;
              left:1020px;
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

            table{
              border:1px solid #262626;
              border-spacing:0;
              border-collapse:none;
              text-align:center;
              font-size:20px;
            }
            th,td{
              border:1px solid #262626;
              padding:5px;
            }
            th{
              background-color: #EEEDED;
            }

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
              text-align:left;
            }

            #shinryo-meisai{
              font-size:36px;
              width:500px;
              margin: 50px auto 30px auto;
              text-align:center;
              border-bottom:3px solid #262626;
            }

            #iryo-meisai-table{
              width:675px;
              float:left;
              padding-right:50px;
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

  $mpdf->WriteHTML($html);
  if($cnt < count($data))
    $mpdf->AddPage();
  $cnt++;
}
$mpdf->Output();
exit;

$srd_start = $srd_end = $format = "";

?>
