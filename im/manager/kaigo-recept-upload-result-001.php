<?php
ini_set("display_errors",1);
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>介護保険レセプトデータ取込結果</title>
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

#print_r($_SESSION);exit;
//DB connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Table header
?>
<div id="breadcrumb">
        <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="./receipt_select.php">レセプトデータ取り込み</a>&nbsp;&gt;&nbsp;介護保険レセプトデータ取込結果
        </div>

<h2 class="title_name">介護保険レセプトデータの取り込み</h2>

<div align="center">下記の介護保険レセプトデータを取り込みました</div>

<div align="center" class="tbl">
<table border = '0' cellpadding=0 cellspacing=0><th>サービスレコード番号</th><th>患者番号</th><th>レセプト患者番号</th><th>被保険者番号</th><th>患者生年月日</th><th>サービスコード</th><th>サービス名</th><th>サービス単位数</th><th>回数</th><th>摘要（診療日）</th><th>利用者合計負担額</th></tr>

<?php
#print_r($_POST['check_list']);exit;
#print_r($_SESSION['kaigo_data']);
#print_r($_SESSION);
$html = "";

if(isset($_POST['submit'])){
    if(!empty($_POST['check_list'])) {

        foreach($_POST['check_list'] as $rid) {

            $kaigo_data = $_SESSION['kaigo_data'][$rid];
            $rek_patient = $kaigo_data['rek_patient'];
            #$rek_service = $kaigo_data['rek_service'];  #220418 複数化

            #初期化
            $original_pid = 0;
            $pid = 0;


            #チェック①pid無し→ 完全新規 → patient_info、rek_patient、rek_service、rek_monthly新規insert


            #チェック②pid有り＆original_pid無し → patient_infoと紐付け無し → rek_patientに存在→ rek_service、rek_monthlyにinsert
            #チェック③pidかつoriginal_pidが設定→rek_patientをselecctしてoriginal_pid=0の場合update
            
            #echo $rid."まずはデータ確認";
            #print_r($_SESSION['kaigo_data'][$rid]);

            if($kaigo_data['pid'] == ""):
                #echo "チェック①完全新規 → patitne_infoとrek_patientに登録<br>\n";

                #patient_info
                $sql = "INSERT INTO patient_info (patient_birth,patient_kaigo_hoban,patient_kaigo_hihoban,patient_kaigo_jukyuban,regist_date) 
                            VALUES ('{$rek_patient['birth']}','{$rek_patient['kaigo_hoban']}','{$rek_patient['kaigo_hihoban']}','{$rek_patient['jukyusya']}',now() ); ";
                #echo $sql."<br>\n";
                $stmt = $dbh->query($sql);

                #original_pidの取得
                $sql = "SELECT LAST_INSERT_ID();";
                $stmt = $dbh->query($sql);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                #echo "LAST_INSERT_ID：";
                #print_r($result);
                $original_pid = $result['LAST_INSERT_ID()'];

                #rek_patient
                $sql = "INSERT INTO rek_patient (original_pid,srm,jigyosya,kaigo_hoban,kaigo_hihoban,futansya,jukyusya,birth,sex,hoken_rate,kouhi_rate,totalcopayment,regist_date) 
                VALUES ( '{$original_pid}', '{$rek_patient['srm']}','{$rek_patient['jigyosya']}','{$rek_patient['kaigo_hoban']}','{$rek_patient['kaigo_hihoban']}','{$rek_patient['futansya']}','{$rek_patient['jukyusya']}','{$rek_patient['birth']}','{$rek_patient['sex']}','{$rek_patient['hoken_rate']}','{$rek_patient['kouhi_rate']}','{$rek_patient['totalcopayment']}',now() );";
                #echo $sql."<br>\n";
                $stmt = $dbh->query($sql);

                #pidの取得
                $sql = "SELECT LAST_INSERT_ID();";
                $stmt = $dbh->query($sql);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                #echo "LAST_INSERT_ID：";
                #print_r($result);
                $pid = $result['LAST_INSERT_ID()'];

                #rek_serviceにinsert
                #220418 rek_service複数化
                $rek_srm = "";  #患者毎のsrmの初期化
                foreach($kaigo_data['rek_service'] as $rek_service):
                    if($rek_srm != $rek_service['srm']):
                        $rek_srm = $rek_service['srm'];
                        
                        #rek_monthlyにinsert
                        $sql = "INSERT INTO rek_monthly (pid,srm,futansya,jukyusya,hoken_rate,kouhi_rate,totalcopayment,delete_flag,regist_date) VALUES 
                        ( '{$pid}','{$rek_srm}','{$rek_patient['futansya']}','{$rek_patient['jukyusya']}','{$rek_patient['hoken_rate']}','{$rek_patient['kouhi_rate']}','{$rek_patient['totalcopayment']}',0,now() );";
                        #echo $sql."<br>\n";
                        $stmt = $dbh->query($sql);
                    endif;

                    $sql = "INSERT INTO rek_service (original_pid,pid,srm,service_code,service_name,service_unit,kaisu,tekiyo,rp_reqid,manageperiod_status,manageperiod_targetym,regist_date) VALUES 
                    ( '{$original_pid}','{$pid}','{$rek_service['srm']}','{$rek_service['service_code']}','{$rek_service['service_name']}','{$rek_service['service_unit']}','{$rek_service['kaisu']}','{$rek_service['tekiyo']}','',0,0,now() );";
                    #echo $sql."<br>\n";
                    $stmt = $dbh->query($sql);

                    

                    
                endforeach;

            
            #pidに値が存在
            else:
                #$sql = "SELECT * FROM rek_patient WHERE pid = '{$kaigo_data['pid']}';";
                #$stmt = $dbh->query($sql);
                #$data = $stmt->fetchALL(PDO::FETCH_ASSOC);
                

                #チェック②
                if($kaigo_data['original_pid'] == ""):
                    #echo "チェック②pid有り＆original_pid無し「patient_infoと紐付けなし」<br>\n";
                    #print_r($data);
                
                #チェック③
                else:
                    #echo "チェック③patient_infoと紐付け済み<br>\n";
                    #print_r($data);

                endif;

                $original_pid = $kaigo_data['original_pid'];
                $pid = $kaigo_data['pid'];

                #ループ上、srmの値が異なる場合
                foreach($kaigo_data['rek_service'] as $rek_service):
                    $sql = "SELECT * FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid WHERE 
                    rek_service.srm = '{$rek_service['srm']}' AND rek_patient.kaigo_hoban = '{$rek_patient['kaigo_hoban']}' AND rek_patient.kaigo_hihoban = '{$rek_patient['kaigo_hihoban']}' 
                    AND rek_service.service_code = '{$rek_service['service_code']}' AND rek_service.service_unit = '{$rek_service['service_unit']}' AND rek_service.kaisu = '{$rek_service['kaisu']}' AND rek_service.tekiyo = '{$rek_service['tekiyo']}' ";
                    #echo $sql."<br>\n";
                    $stmt = $dbh->query($sql);
                    $check_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
                    #存在しない場合のみinsert
                    if(count($check_data) == 0):
                        #rek_serviceにinsert
                        $sql = "INSERT INTO rek_service (original_pid,pid,srm,service_code,service_name,service_unit,kaisu,tekiyo,rp_reqid,manageperiod_status,manageperiod_targetym,regist_date) VALUES 
                        ( '{$kaigo_data['original_pid']}','{$kaigo_data['pid']}','{$rek_service['srm']}','{$rek_service['service_code']}','{$rek_service['service_name']}','{$rek_service['service_unit']}','{$rek_service['kaisu']}','{$rek_service['tekiyo']}','',0,0,now() );";
                        #echo $sql."<br>\n";
                        $stmt = $dbh->query($sql);

                    endif;


                    $sql = "SELECT * FROM rek_monthly INNER JOIN rek_patient ON rek_monthly.pid = rek_patient.pid WHERE rek_patient.kaigo_hoban = '{$rek_patient['kaigo_hoban']}' AND rek_patient.kaigo_hihoban = '{$rek_patient['kaigo_hihoban']}' 
                    AND rek_monthly.srm = '{$rek_service['srm']}' AND rek_monthly.futansya = '{$rek_patient['futansya']}' AND rek_monthly.jukyusya = '{$rek_patient['jukyusya']}' AND rek_monthly.hoken_rate = '".intval($rek_patient['hoken_rate'])."' AND rek_monthly.kouhi_rate = '".intval($rek_patient['kouhi_rate'])."' AND rek_monthly.totalcopayment = '".intval($rek_patient['totalcopayment'])."'";
                    #echo $sql."<br>\n";
                    $stmt = $dbh->query($sql);
                    $check_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
                    #存在しない場合のみinsert
                    if(count($check_data) == 0):
                        #rek_monthlyにinsert
                        $sql = "INSERT INTO rek_monthly (pid,srm,futansya,jukyusya,hoken_rate,kouhi_rate,totalcopayment,delete_flag,regist_date) VALUES 
                        ( '{$kaigo_data['pid']}','{$rek_service['srm']}','{$rek_patient['futansya']}','{$rek_patient['jukyusya']}','{$rek_patient['hoken_rate']}','{$rek_patient['kouhi_rate']}','{$rek_patient['totalcopayment']}',0,now() );";
                        #echo $sql."<br>\n";
                        $stmt = $dbh->query($sql);
                    
                    endif;
                    
                endforeach;

                





                

                


            endif;

                        //取り込んだレコードの表示
                        foreach($kaigo_data['rek_service'] as $rek_service):
                            $html .= "<tr>";
                            $html .= "<td>".$rid."</td>\n";
                            $html .= "<td>".$original_pid."</td>\n";
                            $html .= "<td>".$pid."</td>\n";
                            $html .= "<td>".$rek_patient['kaigo_hihoban']."</td>\n";
                            $html .= "<td>".$rek_patient['birth']."</td>\n";
                            $html .= "<td>".$rek_service['service_code']."</td>\n";
                            $html .= "<td>".$rek_service['service_name']."</td>\n";
                            $html .= "<td>".$rek_service['service_unit']."</td>\n";
                            $html .= "<td>".$rek_service['kaisu']."</td>\n";
                            $html .= "<td>".$rek_service['tekiyo']."</td>\n";
                            $html .= "<td>".$rek_patient['totalcopayment']."</td>\n";
                            $html .= "</tr>\n";
                        endforeach;

            
        }
    }
}

echo $html;

unset($_SESSION['kaigo_data']);
?>

</table>

<br /><br /><br />

<a href="./">トップページに戻る</a>

</div>

</div>
</div>
</body>
</html>