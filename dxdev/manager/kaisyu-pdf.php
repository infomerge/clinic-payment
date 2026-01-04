<?php
ini_set("display_errors",1);

include_once "../class/clsystem.php";

$cl = new CLSYSTEM;
$cl->targetym = $_GET['targetym'];
$cl->generateKaisyuPDF();

exit;



###################

include_once "../common/smarty_settings.php";
include_once "../class/config.php";

include("../pdf/mpdf/mpdf.php");

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
#GET
if(isset($_GET['targetym']) && $_GET['targetym'] != ""){
  $targetym = $_GET['targetym'];
  $srm = $targetym;
}else{
  $srd_start = $_GET["srd_start"];
  $srd_end = $_GET["srd_end"];
  $srm = mb_substr($srd_start,0,6);
  $targetym = $srm;
}


$irkk_code = 1094;


#DB接続
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


#医療機関マスター
/*
$sql = "SELECT *
        FROM account_info
        WHERE original_irkkcode = '$irkk_code'";
$stmt = $dbh->query($sql);
$irkk_data = $stmt->fetch(PDO::FETCH_ASSOC);
*/
$sql = "SELECT *
        FROM account_info
        WHERE original_irkkcode ";
$stmt = $dbh->query($sql);
$tmp = $stmt->fetchALL(PDO::FETCH_ASSOC);
$irkk_data = array();
foreach($tmp as $v){
  $irkk_data[$v['original_irkkcode']] = $v;
}
#print_r($irkk_data);exit;




#新バージョン
if(isset($_GET['targetym']) && $_GET['targetym'] != ""){
  #$sql = "SELECT a.*, b.patient_birth, b.patient_name FROM acc_result as a , patient_info as b WHERE a.original_pid = b.original_pid and  a.targetym = '{$_GET['targetym']}';";
  $sql = "SELECT a.*, b.patient_birth, b.patient_name, c.original_irkkcode  
          FROM acc_result as a 
          , patient_info as b 
          , accountpatient_relation as c 
          WHERE a.original_pid = b.original_pid 
          and a.original_pid = c.original_pid 
          and  a.targetym = '{$_GET['targetym']}' order by a.rid asc";

  $stmt = $dbh->query($sql);
  $tmp_acc_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

  $acc_data_total = array();
  $monthly_copayment = array();
  foreach($tmp_acc_data as $v){
    $acc_data_total[$v['original_irkkcode']][] = $v;
    if( isset($monthly_copayment[$v['original_irkkcode']]) ){
      $monthly_copayment[$v['original_irkkcode']] += $v['ta'];
    }else{
      $monthly_copayment[$v['original_irkkcode']] = $v['ta'];
    }
  }
/*
  $monthly_copayment = 0;
  foreach($acc_data as $v){
    $monthly_copayment += $v['ta'];
  }
*/
#  print_r($acc_data_total);exit;

  #医療機関毎のPDFデータ生成
  
$cnt = 0;
      #システム上の管理番号
      #$inv_id = $patient_data['data']['irkkcode'] . "-" . $patient_data['data']['srm'] . "-" . sprintf('%07d', strval($original_pid));
  foreach($acc_data_total as $irkkcode => $acc_data):
    $cnt++;
    $html = "";
      ### ---------- 封筒窓 ---------- ###

        ## 封筒表紙（左窓）##
        $html .= "<div class=\"wrap\"></div>";
        $html .= "<p class=\"header-left\">回収明細票</p>";

        #顧客情報
        $html .= "<p class=\"patient-address\">〒".$irkk_data[$irkkcode]['irkk_postal_code']."<br>".$irkk_data[$irkkcode]['irkk_prefecture']."<br>".$irkk_data[$irkkcode]['irkk_address1']."<br>".$irkk_data[$irkkcode]['irkk_address2']."</p>";
        $html .= "<p class=\"patient-name\">".$irkk_data[$irkkcode]['irkkname']." 様</p>";
        #$html .= "<p class=\"patient-id\"><span>No.$inv_id</span></p>";

        ##封筒表紙（右窓）##
        $html .= "<p class=\"header-right\">発送元</p>";
        $html .= "<p class=\"irkk-name\">株式会社クロスライン</p>";
        $html .= "<p class=\"irkk-address\">〒105-0013<br>東京都港区浜松町２丁目２番１５号<br>浜松町ダイヤビル２Ｆ<br>請求エクスプレス事業担当</p><br>";

        ## 折位置表示 ##
        $html .= "<p class=\"fold-point\">▶</p>";

        #print_r($data);

      ### ---------- 回収表 ---------- ###


        $html .= "<p id=\"total-copayment\">回収明細一覧</p>";

        #回収明細まとめテーブル
        $html .= "<table id=\"hoken-table\">
                    <tr><th>歯科医院名</th><td>".$irkk_data[$irkkcode]['irkkname']." 様</td></tr>
                    <tr><th>処理年月</th><td>".date('Y年m月',strtotime($targetym."01"))."分</td></tr>
                    <tr><th>合計件数</th><td>".count($acc_data)."件</td></tr>
                    <tr><th>合計金額</th><td>¥".number_format($monthly_copayment[$irkkcode])."</td></tr>
                </table><br/>\n";

        #回収明細テーブル
        $html .= "<table id=\"hoken-table\">
                    <tr>
                        <th>No.</th>
                        <th>ステータス</th>
                        <th>患者名</th>
                        <th>生年月日</th>
                        <th>金額</th>
                        <th>回収方法</th>
                        <th>回収日</th>
                    </tr>";

    $cnt = 1;

    foreach ($acc_data as $v) {

        $total_copayment = 0;

        #医療保険の診療月合計金額を$total_copaymentに加算
        $total_copayment += $v['ta'];


        if($v['rp_disableflag'] == 0){$kaisyu_method = "口座振替";} else {$kaisyu_method = "振込/現金";}

        #回収状況
        if($v['rp_errorflag'] == 9){
            $status = "回収済";
            $date = date('Y年m月',strtotime($v['date']."+1month"))."10日";
            #$date = $v['date'];
        } else {
            $status = "未回収";
            $date = "---";
        }

        $html .= "<tr>
                    <td>".$cnt."</td>
                    <td>".$status."</td>
                    <td>".$v['patient_name']."</td>
                    <td>".date('Y年m月d日',strtotime($v['patient_birth']))."</td>
                    <td>¥".number_format($v['ta'])."</td>
                    <td>".$kaisyu_method."</td>
                    <td>".$date."</td>
                </tr>";

        if($cnt < count($acc_data)) $cnt++;

        if(!isset($_REQUEST['testview'])){
            if($cnt > 100) break;
        }

    }

    $html .= "</table><br/>\n";

    #clearfix
    $html .= "<div class=\"clearfix\"></div>";

    #data_clear
    $total_tensu = $total_copayment = $total_service_unit = "0";

        #style
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
#if($cnt < count($acc_data_total)){
  $mpdf->AddPage();
#}
  endforeach;


#旧バージョン
}else{
  #医療機関毎のPDFデータ生成
  $html = "";

      #システム上の管理番号
      #$inv_id = $patient_data['data']['irkkcode'] . "-" . $patient_data['data']['srm'] . "-" . sprintf('%07d', strval($original_pid));


    ### ---------- 封筒窓 ---------- ###

      ## 封筒表紙（左窓）##
      $html .= "<div class=\"wrap\"></div>";
      $html .= "<p class=\"header-left\">回収明細票</p>";

      #顧客情報
      $html .= "<p class=\"patient-address\">〒".$irkk_data['postal_code']."<br>".$irkk_data['irkk_prefecture']."<br>".$irkk_data['irkk_address1']."<br>".$irkk_data['irkk_address2']."</p>";
      $html .= "<p class=\"patient-name\">".$irkk_data['irkkname']." 様</p>";
      $html .= "<p class=\"patient-id\"><span>No.$inv_id</span></p>";

      ##封筒表紙（右窓）##
      $html .= "<p class=\"header-right\">発送元</p>";
      $html .= "<p class=\"irkk-name\">株式会社クロスライン</p>";
      $html .= "<p class=\"irkk-address\">〒000-0000<br>東京都港区新橋浜松町1235-5<br>ヂアドミール1120<br>請求エクスプレス事業担当</p><br>";

      ## 折位置表示 ##
      $html .= "<p class=\"fold-point\">▶</p>";

      #print_r($data);

    ### ---------- 回収表 ---------- ###


      $html .= "<p id=\"total-copayment\">回収明細一覧</p>";

      #回収明細まとめテーブル
      $html .= "<table id=\"hoken-table\">
                  <tr><th>歯科医院名</th><td>".$irkk_data['irkkname']." 様</td></tr>
                  <tr><th>処理年月</th><td>".date('Y年m月',strtotime($srd_start))."分</td></tr>
                  <tr><th>合計件数</th><td>".count($data)."件</td></tr>
                  <tr><th>合計金額</th><td>¥".number_format($monthly_copayment)."</td></tr>
              </table><br/>\n";

      #回収明細テーブル
      $html .= "<table id=\"hoken-table\">
                  <tr>
                      <th>No.</th>
                      <th>ステータス</th>
                      <th>患者名</th>
                      <th>生年月日</th>
                      <th>金額</th>
                      <th>回収方法</th>
                      <th>回収日</th>
                  </tr>";

#医療保険マスター
$sql = "SELECT *
        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                        INNER JOIN patient_info ON re_shinryo.original_pid = patient_info.original_pid
                        INNER JOIN account_info on re_shinryo.original_irkkcode = account_info.original_irkkcode
        WHERE srd >= '$srd_start' AND srd <= '$srd_end' order by srd,category";
$stmt = $dbh->query($sql);
$iryo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
#介護保険マスター
$sql = "SELECT *
        FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
        #WHERE srm = '$srm' AND original_pid != '0'
        ";
$stmt = $dbh->query($sql);
$kaigo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
#自由診療マスター
$sql = "SELECT *
        FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid
        WHERE app_date >= '$srd_start' AND app_date <= '$srd_end' order by app_date";
$stmt = $dbh->query($sql);
$app_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
#決済情報マスター
$sql = "SELECT *
        FROM acc_result
        WHERE targetym = '$targetym'";
$stmt = $dbh->query($sql);
$kessai_data = $stmt->fetchALL(PDO::FETCH_ASSOC);


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
#上限金額マスター
$sql = "SELECT * FROM max_copayment";
$stmt = $dbh->query($sql);
$max = $stmt->fetchALL(PDO::FETCH_ASSOC);
$m_max = array();
foreach($max as $value){
    $m_max[$value['original_pid']][$value['srm']] = $value['max_copayment'];
}


$data = array();
#医療保険データの点数を$dataに格納
foreach($iryo_data as $v){
    #点数（診療日毎）
    if(isset($data[$v['original_pid']]['srd'][$v['srd']]['tensu']))
        $data[$v['original_pid']]['srd'][$v['srd']]['tensu'] += intval($v['tensu']);
    else
        $data[$v['original_pid']]['srd'][$v['srd']]['tensu'] = intval($v['tensu']);
    #負担率
    $data[$v['original_pid']]['srd'][$v['srd']]['ratio'] = $v['ratio'];
    #負担額（診療日毎）
    if(isset($data[$v['original_pid']]['srd'][$v['srd']]['copayment']))
        $data[$v['original_pid']]['srd'][$v['srd']]['copayment'] += round($v['copayment'],-1);
    else
        $data[$v['original_pid']]['srd'][$v['srd']]['copayment'] = round($v['copayment'],-1);
    #明細データ
    $data[$v['original_pid']]['srd'][$v['srd']]['sid'][$v['sid']]['tensu'] = intval($v['tensu']);
    $data[$v['original_pid']]['srd'][$v['srd']]['sid'][$v['sid']]['kaisu'] = intval($v['kaisu']);
    #その他データ
    $data[$v['original_pid']]['data'] = $v;
}
#介護保険データを$dataに格納
foreach($kaigo_data as $v){
    #合計点数（診療月毎）
    if(isset($data[$v['original_pid']]['srm'][$v['srm']]['tensu']))
        $data[$v['original_pid']]['srm'][$v['srm']]['tensu'] += intval($v['service_unit']) * $v['kaisu'];
    else
        $data[$v['original_pid']]['srm'][$v['srm']]['tensu'] = intval($v['service_unit']) * $v['kaisu'];
    #負担率
    if(isset($data[$v['original_pid']]['srm'][$v['srm']]['rate']))
        $data[$v['original_pid']]['srm'][$v['srm']]['rate'] += ((100 - $v['hoken_rate']) * (100 - $v['kouhi_rate']))/100;
    else
        $data[$v['original_pid']]['srm'][$v['srm']]['rate'] = ((100 - $v['hoken_rate']) * (100 - $v['kouhi_rate']))/100;
    #合計負担額（診療月毎）
    if(isset($data[$v['original_pid']]['srm'][$v['srm']]['copayment']))
        $data[$v['original_pid']]['srm'][$v['srm']]['copayment'] += $v['service_unit'] * 10 * $v['kaisu'];
    else
        $data[$v['original_pid']]['srm'][$v['srm']]['copayment'] = $v['service_unit'] * 10 * $v['kaisu'];
    #明細データ
    $data[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['service_unit'] = $v['service_unit'];
    $data[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['kaisu'] = $v['kaisu'];
}
#自由診療データを$dataに格納
foreach($app_data as $v){
    #合計負担額（診療月毎）
    if(isset($data[$v['original_pid']]['srm'][$v['srm']]['copayment']))
        $data[$v['original_pid']]['srm'][$v['srm']]['copayment'] += $v['app_price'];
    else
        $data[$v['original_pid']]['srm'][$v['srm']]['copayment'] = $v['app_price'];
}
#決済情報：月ごとの引き落とし情報
foreach ($kessai_data as $v){
    $data[$v['original_pid']]['srm'][$v['srm']]['kessai_date'] = $v['date'];
}

$monthly_copayment = 0;
foreach ($data as $original_pid => $patient_data) {
    #医療保険の診療月合計金額を$total_copaymentに加算
    foreach($patient_data['srd'] as $dayily_copayment){
        $monthly_copayment += $dayily_copayment['copayment'];
    }
}





    $cnt = 1;

    foreach ($data as $original_pid => $patient_data) {

        $total_copayment = 0;

        #医療保険の診療月合計金額を$total_copaymentに加算
        foreach($patient_data['srd'] as $dayily_copayment){
            $total_copayment += $dayily_copayment['copayment'];
        }

        #介護保険/自由診療の合計金額を$total_copaymentに加算
        $total_copayment += $patient_data['srm'][$srm]['copayment'];

        #医療／公費負担額が存在する場合は$total_copaymentを上書き
        if($m_max[$original_pid][$srm]){
            $total_copayment = $m_max[$original_pid][$srm];
        }

        if($patient_data['data']['direct_debit'] == 0){$kaisyu_method = "口座振替";} else {$kaisyu_method = "振込/現金";}

        #回収状況
        if($patient_data['srm'][$srm]['kessai_date']){
            $status = "回収済";
            $date = date('Y年m月',strtotime($patient_data['srm'][$srm]['kessai_date']."+1 month"))."10日";
        } else {
            $status = "未回収";
            $date = "---";
        }

        $html .= "<tr>
                    <td>".$cnt."</td>
                    <td>".$status."</td>
                    <td>".$patient_data['data']['name']."</td>
                    <td>".date('Y年m月d日',strtotime($patient_data['data']['birth']))."</td>
                    <td>¥".number_format($total_copayment)."</td>
                    <td>".$kaisyu_method."</td>
                    <td>".$date."</td>
                </tr>";

        if($cnt < count($data)) $cnt++;

        if(!isset($_REQUEST['testview'])){
            if($cnt > 100) break;
        }

    }

    $html .= "</table><br/>\n";

    #clearfix
    $html .= "<div class=\"clearfix\"></div>";

    #data_clear
    $total_tensu = $total_copayment = $total_service_unit = "0";

        #style
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

}#if $_GET['targetym']



$mpdf->Output();
exit;

$srd_start = $srd_end = "";

?>
