<?php
include_once "../common/smarty_settings.php";
include_once "../class/config.php";



include("../pdf/mpdf/mpdf.php");


#$html = file_get_contents( $protocol."://application.audition-debut.com/member/display_entrysheet.php?id={$id}");

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
$srd_start = $_GET["srd_start"];
$srd_end = $_GET["srd_end"];
$name = $_GET["name"];

//DB接続
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//DBから対象患者のデータを抽出
$sql = "SELECT *
        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
        WHERE srd >= '$srd_start' AND srd <= '$srd_end'";
$stmt = $dbh->query($sql);
$result = $stmt->fetchALL(PDO::FETCH_ASSOC);

$data = array();
foreach($result as $v){
  if(isset($data[$v['pid']]['srd'][$v['srd']]['category'][$v['category']]))
    $data[$v['pid']]['srd'][$v['srd']]['category'][$v['category']] += intval($v['tensu']);
  else
    $data[$v['pid']]['srd'][$v['srd']]['category'][$v['category']] = intval($v['tensu']);

  if(isset($data[$v['pid']]['srd'][$v['srd']]['copayment']))
    $data[$v['pid']]['srd'][$v['srd']]['copayment'] += round($v['copayment'],-1);
  else
    $data[$v['pid']]['srd'][$v['srd']]['copayment'] = round($v['copayment'],-1);

  $data[$v['pid']]['data'] = $v;
}

foreach ($data as $pid => $value) {
  foreach($value as $srd => $val){

    if(isset($data[$pid]['sum']))
      $data[$pid]['sum'] += $val['copayment'];
    else
      $data[$pid]['sum'] = $val['copayment'];

  }
}

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
#print_r($syubetsu);
foreach($syubetsu as $value){
  $m_syubetsu[$value['code']]['syubetsu'] = $value['syubetsu'];
  $m_syubetsu[$value['code']]['ratio'] = $value['ratio'];
}
#カテゴリマスター
$m_category['A'] = array('title'=>'初・再診料', 'tensu'=>0);
$m_category['B'] = array('title'=>'医学管理等', 'tensu'=>0);
$m_category['C'] = array('title'=>'在宅医療', 'tensu'=>0);
$m_category['D'] = array('title'=>'検査', 'tensu'=>0);
$m_category['E'] = array('title'=>'画像診断', 'tensu'=>0);
$m_category['F'] = array('title'=>'投薬', 'tensu'=>0);
$m_category['G'] = array('title'=>'注射', 'tensu'=>0);
$m_category['H'] = array('title'=>'リハビリテーション', 'tensu'=>0);
$m_category['I'] = array('title'=>'処置', 'tensu'=>0);
$m_category['J'] = array('title'=>'手術', 'tensu'=>0);
$m_category['K'] = array('title'=>'麻酔', 'tensu'=>0);
$m_category['L'] = array('title'=>'放射線治療', 'tensu'=>0);
$m_category['M'] = array('title'=>'歯冠修復及び欠損補綴', 'tensu'=>0);
$m_category['N'] = array('title'=>'歯科矯正', 'tensu'=>0);
$m_category['O'] = array('title'=>'病院診断', 'tensu'=>0);
$m_category['-'] = array('title'=>'その他', 'tensu'=>0);

#print_r($m_syubetsu);
#個人毎PDFデータ生成

$cnt = 1;
foreach ($data as $pid => $value) {
  $html = "";
  $html .= "患者番号：".$pid."<br/>\n";
  $html .= "患者氏名：".$value['data']['name']."<br/>\n";
  $html .= "負担区分：".$m_futan[$value['data']['futan']]."<br/>\n";
  $html .= "患者種別：".$m_syubetsu[$value['data']['syubetsu']]['syubetsu']."<br/>\n";
  $html .= "負担割合：".$m_syubetsu[$value['data']['syubetsu']]['ratio']."%<br/><br/>\n\n";

  foreach($value['srd'] as $srd => $value2){
    $html .= "診療日：".date('Y年m月d日',strtotime($srd))."<br>\n";
    $html .= "患者負担額：".$value2['copayment']."円<br/><br>\n";

    foreach($m_category as $k => $v){
      $html .= $k."：";
      if(array_key_exists($k , $value2['category'])){
        $html .= $value2['category'][$k]."<br>\n";
      }else{
        $html .= "0<br>\n";
      }
    }
  }


  $mpdf->WriteHTML($html);
  if($cnt < count($data))
    $mpdf->AddPage();

  $cnt++;
}
$mpdf->Output();


#print_r($data);
exit;




//患者ID&氏名&負担区分&患者種別&負担割合を表示
$pid = $result[0]['pid'];
echo "患者番号：$pid<br/>\n";

echo "患者氏名：$name<br/>\n";

$futan_code = $result[0]['futan'];
$sql = "SELECT * FROM futan_code WHERE code = $futan_code";
$stmt = $dbh->query($sql);
$futan = $stmt->fetch(PDO::FETCH_ASSOC);
$futan_kubun = $futan['futan'];
echo "負担区分：$futan_kubun<br/>\n";

$syubetsu_code = $result[0]['syubetsu'];
$sql = "SELECT * FROM syubetsu_code WHERE code = $syubetsu_code";
$stmt = $dbh->query($sql);
$syubetsu = $stmt->fetch(PDO::FETCH_ASSOC);
$patient_syubetsu = $syubetsu['syubetsu'];
echo "患者種別：$patient_syubetsu<br/>\n";

$ratio = $result[0]['ratio'];
echo "負担割合：$ratio"."%<br/><br/>\n\n";



//DBから対象の診療日一覧を抽出
$sql = "SELECT DISTINCT srd
        FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
        WHERE name = '$name' AND srd >= '$srd_start' AND srd <= '$srd_end'
        ORDER BY srd";
$stmt = $dbh->query($sql);
$srdlist = $stmt->fetchALL(PDO::FETCH_ASSOC);
$srdcount = sizeof($srdlist);


//診療日毎に診療データを抽出／表示
for($i = 0; $i < $srdcount; $i++){

    //診療日と患者負担額を表示
    $srd = $srdlist[$i]['srd'];
    $year = substr($srd,0,4);
    $month = substr($srd,4,2);
    $day = substr($srd,6,2);
    echo "診療日：$year"."年$month"."月$day"."日";
    echo "<br/>\n";

    $sql = "SELECT sum(copayment)
            FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
            WHERE name = '$name' AND srd = '$srd'";
    $stmt = $dbh->query($sql);
    $dailycp = $stmt->fetch(PDO::FETCH_ASSOC);
    //日毎に四捨五入
    $dailycopayment = round($dailycp['sum(copayment)'],-1);
    echo "患者負担額：$dailycopayment"."円<br/>\n";
    echo "<br/>\n";

    //DBから対象日の診療データ一覧抽出
    $sql = "SELECT *
            FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
            WHERE name = '$name' AND srd = '$srd'";
    $stmt = $dbh->query($sql);
    $shinryolist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $shinryocount = sizeof($shinryolist);

    //診療行為カテゴリごとの点数を表示
    $array_category = array(array('kigo'=>'A', 'title'=>'初・再診料', 'tensu'=>0),
                            array('kigo'=>'B', 'title'=>'医学管理等', 'tensu'=>0),
                            array('kigo'=>'C', 'title'=>'在宅医療', 'tensu'=>0),
                            array('kigo'=>'D', 'title'=>'検査', 'tensu'=>0),
                            array('kigo'=>'E', 'title'=>'画像診断', 'tensu'=>0),
                            array('kigo'=>'F', 'title'=>'投薬', 'tensu'=>0),
                            array('kigo'=>'G', 'title'=>'注射', 'tensu'=>0),
                            array('kigo'=>'H', 'title'=>'リハビリテーション', 'tensu'=>0),
                            array('kigo'=>'I', 'title'=>'処置', 'tensu'=>0),
                            array('kigo'=>'J', 'title'=>'手術', 'tensu'=>0),
                            array('kigo'=>'K', 'title'=>'麻酔', 'tensu'=>0),
                            array('kigo'=>'L', 'title'=>'放射線治療', 'tensu'=>0),
                            array('kigo'=>'M', 'title'=>'歯冠修復及び欠損補綴', 'tensu'=>0),
                            array('kigo'=>'N', 'title'=>'歯科矯正', 'tensu'=>0),
                            array('kigo'=>'O', 'title'=>'病院診断', 'tensu'=>0),
                            array('kigo'=>'-', 'title'=>'その他', 'tensu'=>0)
                      );
    $category_count = sizeof($array_category);
    for($j = 0; $j < $category_count; $j++){
        $category = $array_category[$j]['kigo'];

        $sql = "SELECT sum(tensu) as tensu
                FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                WHERE name = '$name' AND srd = '$srd' AND category = '$category'";
        $stmt = $dbh->query($sql);
        $tensubycat = $stmt->fetch(PDO::FETCH_ASSOC);
        $array_category[$j]['tensu'] = $tensubycat['tensu'];

        $kigo = $array_category[$j]['kigo'];
        $title = $array_category[$j]['title'];
        $tensu = $array_category[$j]['tensu'];
        if($tensu==""){$tensu = 0;}

        echo "$kigo: ";
        echo "$title ⇛ ";
        echo "$tensu 点<br/>\n";
    }

    echo "<br/>\n";
    echo "<hr>";
    echo "<br/>\n";

    //診療日毎の患者負担額を$totalcopaymentに加算
    $totalcopayment += $dailycopayment;
}

//対象機関の患者負担額を表示

$year_start = substr($srd_start,0,4);
$month_start = substr($srd_start,4,2);
$day_start = substr($srd_start,6,2);
$year_end = substr($srd_end,0,4);
$month_end = substr($srd_end,4,2);
$day_end = substr($srd_end,6,2);

echo "対象期間：$year_start"."年$month_start"."月$day_start"."日〜$year_end"."年$month_end"."月$day_end"."日<br/>\n";
echo "患者負担金額合計：$totalcopayment"."円<br/>\n";

?>

    </div>
</div>
</body>
</html>
