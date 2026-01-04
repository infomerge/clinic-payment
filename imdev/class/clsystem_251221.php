<?php
ini_set( 'display_errors', 0 );
ini_set("memory_limit", "5120M");
set_time_limit(0);

require_once '../common/database.php';
require_once '../class/config.php';
require_once '../class/commonconst.php';
#require_once '../pdf/mpdf/mpdf.php';
require_once __DIR__.'/../vendor/autoload.php';

class CLSYSTEM{
    var $db;
    var $manageperiod_flag;
    var $manageperiod_debug_flag;
    var $targetym;
    var $srd_start;
    var $srd_end;
    var $format;
    var $commonconst;
    var $ryosyu_date;
    var $original_pid;
    var $pdf_path;
    var $readFromAccDetail;

    function __construct() {
        // MySQLと接続する
        $this->db = new DATABASE;
        $this->db->connectdb();

        $this->commonconst = new COMMONCONST;
    }
    function __destruct(){
        // MySQLの接続を解除する
        $this->db->disconnect();
    }

    function getPaymentData(){
        

        #220327修正：acc_detailで配列化データ保存されている場合はそちらを取得
        
        #if($this->manageperiod_flag == 1 && $this->targetym > 0):
        if($this->readFromAccDetail):
            $sql = "SELECT a.*, b.* FROM acc_result as a , acc_detail as b , patient_info as c WHERE a.rid = b.rid and a.original_pid = c.original_pid AND a.targetym = '{$this->targetym}'";
            if($this->original_pid > 0):
                $sql .= " AND a.original_pid = {$this->original_pid} ";
            endif;
            if($this->format == "ryosyu"):
                $sql .= " AND ( (a.rp_errorflag = 9 AND c.direct_debit = 0) OR c.direct_debit = 1) ";
            endif;
            if($this->format == "seikyu"){
                $sql .= " AND c.invoice_output = 0 ";
            }else if($this->format == "ryosyu"){
                $sql .= " AND c.receipt_output = 0 ";
            }
            #echo $sql;exit;
            $stmt = $this->db->databasequery($sql);
            $acc_detail_count = $stmt->rowCount();
            $acc_detail_data = array();
            if($acc_detail_count > 0):
                $tmp = $stmt->fetchALL(PDO::FETCH_ASSOC);
                foreach($tmp as $v):
                    $acc_detail_data[$v['original_pid']] = unserialize($v['contents']);
                endforeach;
            endif;

            return $acc_detail_data;
        
        else:

            #医療保険マスター
            $sql = "SELECT *
                    FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid
                                    INNER JOIN patient_info ON re_shinryo.original_pid = patient_info.original_pid 
                                    INNER JOIN account_info on re_shinryo.original_irkkcode = account_info.original_irkkcode ";

            if($this->manageperiod_flag == 1):

                if($this->manageperiod_debug_flag == true):
                    $sql .= "WHERE re_shinryo.manageperiod_targetym = '{$this->targetym}'";
                else:
                    $sql .= "WHERE (re_shinryo.manageperiod_status = 1 OR re_shinryo.manageperiod_status = 5) AND re_shinryo.manageperiod_targetym = '{$this->targetym}'";
                endif;

            else:
                $sql .= "WHERE srd >= '$this->srd_start' AND srd <= '$this->srd_end'";

                #from-to期間中のsrmを算出
                $srm_from = substr($this->srd_start, 0 , 6);
                $srm_to = substr($this->srd_end, 0 , 6);
                
            endif;

            if($this->format == "seikyu"){
                $sql .= " AND patient_info.invoice_output = 0 ";
            }else if($this->format == "ryosyu"){
                $sql .= " AND patient_info.receipt_output = 0 ";
            }

            if($this->original_pid != ""){
                $sql .= " AND re_shinryo.original_pid = '{$this->original_pid}' ";
            }

            $sql .= " AND patient_info.disp = 0 ";
            #$sql .= " order by re_shinryo.srd,re_shinryo.category";
            $sql .= " order by re_shinryo.sid";

            #echo $sql."<br>\n";
            #exit;

            $stmt = $this->db->databasequery($sql);
            $iryo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
            #print_r($iryo_data);exit;
            /*
            foreach($iryo_data as $v):
                echo $v['sid']."-srd:".$v['srd']."-manageperiod_targetym:".$v['manageperiod_targetym']."<br>\n";
            endforeach;
            exit;*/

            #################

            ##########
            #
            # targetymに該当するre_shinryoデータを抽出した上で、ループ回して、srcの左6桁を抽出して、original_pidに対するmax_copaymentテーブルの該当するsrmを算出する必要がある
            #
            ##########
            $iryo_srm = array();
            foreach($iryo_data as $v){
                if(!isset($iryo_srm[$v['original_pid']][mb_substr($v['srd'],0,6)])){
                    $iryo_srm[$v['original_pid']][mb_substr($v['srd'],0,6)] = 1;
                }
            }
            #print_r($iryo_srm);exit;


            ##########
            #
            # 上限金額マスター
            #
            ##########
            $sql = "SELECT * FROM max_copayment";
            $stmt = $this->db->databasequery($sql);
            $max = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $m_max = array();
            foreach($max as $value){
                $m_max[$value['original_pid']][$value['srm']] = $value['max_copayment'];
            }
            #print_r($m_max);
            #介護保険マスター
            ##########
            #
            # targetymに該当する介護保険レセプトデータを
            #
            ##########
            $sql = "SELECT rek_service.*, rek_patient.jigyosya,rek_patient.kaigo_hoban,rek_patient.kaigo_hihoban,rek_patient.futansya,rek_patient.jukyusya,rek_patient.birth,rek_patient.sex,rek_patient.hoken_rate,rek_patient.kouhi_rate,rek_patient.totalcopayment 
                    FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
                    WHERE 1 = 1 ";
            $sql .= "AND rek_service.original_pid <> 0 AND rek_patient.original_pid <> 0 ";

            if($this->manageperiod_flag == 1):
                $sql .= "AND rek_service.manageperiod_targetym = '{$this->targetym}' ";
                #$sql .= "AND (rek_service.manageperiod_status = 1 OR rek_service.manageperiod_status = 5)";
                if($this->manageperiod_debug_flag == true):
                
                else:
                    $sql .= "AND (rek_service.manageperiod_status = 1 OR rek_service.manageperiod_status = 5)";
                endif;

            else:
                $sql .= "AND rek_service.srm >= '{$srm_from}' AND rek_service.srm <= '{$srm_to}' ";
            endif;

            if($this->original_pid != ""){
                $sql .= " AND rek_service.original_pid = '{$this->original_pid}' ";
            }
           #echo $sql;exit; 
            /*
            if($this->manageperiod_debug_flag == true):
                
            else:
                $sql .= "AND (rek_service.manageperiod_status = 1 OR rek_service.manageperiod_status = 5)";
            endif;
            */

            #echo $sql ;
            #exit;
            $stmt = $this->db->databasequery($sql);
            $kaigo_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

            #print_r($kaigo_data);exit;

            ##########
            #
            # 介護保険を患者ID単位に調整
            #
            ##########
            $kaigo_trans = array();
            foreach($kaigo_data as $v){
                #print_r($v);
                #合計点数
                if(isset($kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['tensu']))
                    $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['tensu'] += intval($v['service_unit']) * $v['kaisu'];
                else
                    $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['tensu'] = intval($v['service_unit']) * $v['kaisu'];
                #負担率
                #echo (100 - $v['hoken_rate']) ."---".((100 - $v['kouhi_rate'])/100)."aaa";


                #220402 公費レートに値が存在する場合のロジック組み込むkouhi_rate
                if($v['kouhi_rate'] != 0):
                    $kaigo_rate = $v['kouhi_rate'];
                else:
                    $kaigo_rate = $v['hoken_rate'];
                endif;

                #レート
                $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['rate'] = (100 - $kaigo_rate);

                #合計負担額
                if(isset($kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['copayment'])):
                    #$kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['copayment'] += $v['service_unit'] * 10 * $v['kaisu'] * ((100 - $v['hoken_rate'])/100);
                    $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['copayment'] += $v['service_unit'] * 10 * $v['kaisu'] * ((100 - $kaigo_rate)/100);
                else:
                    #$kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['copayment'] = $v['service_unit'] * 10 * $v['kaisu'] * ((100 - $v['hoken_rate'])/100);
                    $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['copayment'] = $v['service_unit'] * 10 * $v['kaisu'] * ((100 - $kaigo_rate)/100);
                endif;
                ####### 保険/公費レートここまで
                
                
                #明細データ
                $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['service_name'] = $v['service_name'];
                $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['service_unit'] = $v['service_unit'];
                $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['kaisu'] = $v['kaisu'];
                $kaigo_trans[$v['original_pid']]['srm'][$v['srm']]['sid'][$v['sid']]['tekiyo'] = $v['tekiyo'];
                #その他データ
            }
            #print_r($kaigo_trans);exit;

            ##########
            #
            # targetymに該当する、医療保険データの保険カテゴリーごとの点数と、診療日ごとの負担額と、その他データを$dataに格納
            #
            ##########
            $data = array();
            $buf_srd = "";

            $m_category = $this->commonconst->m_category;
            foreach($iryo_data as $v){


                # 請求書のために追加 ここから
                #診療日＞保険カテゴリーごとの点数
                if(isset($data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['category'][$v['category']]))
                    $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['category'][$v['category']] += intval($v['tensu']) * $v['kaisu'];
                else
                    $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['category'][$v['category']] = intval($v['tensu']) * $v['kaisu'];
                #負担率
                $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['ratio'] = $v['ratio'];
                #診療日ごとの点数
                if(isset($data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['tensu']))
                    $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['tensu'] += intval($v['tensu']) * $v['kaisu'];
                else
                    $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['tensu'] = intval($v['tensu']) * $v['kaisu'];
                # 請求書のために追加 ここまで

                # [診療月]→診療日ごとの負担額
                if(isset($data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['copayment'])):
                    #$data[$v['original_pid']]['srd'][$v['srd']]['copayment'] += round($v['copayment'],-1);
                    $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['copayment'] += $v['copayment'] * $v['kaisu'];
                else:
                    #$data[$v['original_pid']]['srd'][$v['srd']]['copayment'] = round($v['copayment'],-1);
                    $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['copayment'] = $v['copayment'] * $v['kaisu'];
                endif;

                # 請求書のために追加 ここから
                #明細データ
                $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['sid'][$v['sid']]['category'] = $v['category'];
                $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['sid'][$v['sid']]['shinryo_name'] = $v['shinryo_name'];
                $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['sid'][$v['sid']]['tensu'] = intval($v['tensu']);
                $data[$v['original_pid']]['srd'][mb_substr($v['srd'],0,6)][$v['srd']]['sid'][$v['sid']]['kaisu'] = intval($v['kaisu']);
                # 請求書のために追加 ここまで





                #その他データ
                $data[$v['original_pid']]['data'] = $v;


                
                foreach($m_category as $k => $value){
                    #echo "m_category<br>\n";
                    #if(array_key_exists($k , $v['category'])){
                    if($k == $v['category']){
                    #Yは「F：投薬」に合算
                    if($k == 'Y'){
                        #$m_category['F']['tensu'] += $value['tensu'];
                        if(isset($data[$v['original_pid']]['category']['F'])){
                            $data[$v['original_pid']]['category']['F'] += intval($v['tensu']) * intval($v['kaisu']);
                        }else{
                            $data[$v['original_pid']]['category']['F'] = intval($v['tensu']) * intval($v['kaisu']);
                        }

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
                        #$m_category['O']['tensu'] += $value['tensu'];

                        if(isset($data[$v['original_pid']]['category']['O'])){
                            $data[$v['original_pid']]['category']['O'] += intval($v['tensu']) * intval($v['kaisu']);
                        }else{
                            $data[$v['original_pid']]['category']['O'] = intval($v['tensu']) * intval($v['kaisu']);
                        }   

                    #「T」カテゴリとまだみぬ「P,Q,R,S,U,V,W,X,Z」は「O：病理診断 = 保険：その他」に合算
                    }else{
                        #$m_category[$k]['tensu'] += $value['tensu'];
                        if(isset($data[$v['original_pid']]['category'][$k])){
                            $data[$v['original_pid']]['category'][$k] += intval($v['tensu']) * intval($v['kaisu']);
                        }else{
                            $data[$v['original_pid']]['category'][$k] = intval($v['tensu']) * intval($v['kaisu']);
                        }   

                    }
                        if(isset($data[$v['original_pid']]['total_tensu'])){
                            $data[$v['original_pid']]['total_tensu'] += intval($v['tensu']) * intval($v['kaisu']);
                        }else{
                            $data[$v['original_pid']]['total_tensu'] = intval($v['tensu']) * intval($v['kaisu']);
                        }
                    }
                }


            }
            #print_r($data[95]);exit;


            ##########
            #
            # targetymに該当する、自由診療
            #
            ##########
            foreach($data as $original_pid => $dt) {

                #自由診療マスター
                $sql = "SELECT *
                        FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid ";

                if($this->manageperiod_flag == 1):
                    if($this->manageperiod_debug_flag == true):
                        $sql .= "WHERE appendix.manageperiod_targetym = '{$this->targetym}' ";
                    else:
                        $sql .= "WHERE (appendix.manageperiod_status = 1 OR appendix.manageperiod_status = 5) AND appendix.manageperiod_targetym = '{$this->targetym}' ";
                    endif;

                else:
                    $sql .= "WHERE app_date >= '$this->srd_start' AND app_date <= '$this->srd_end' ";
                endif;

                $sql .= "and appendix.original_pid = '".$original_pid."' and patient_info.disp = 0 and appendix.disp = 0 order by app_date";


                $stmt = $this->db->databasequery($sql);
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
            #exit;
            #print_r($data);

            ##########
            #
            # 医療保険と介護保険を結合
            #
            ##########
            foreach($data as $original_pid => $v) {

                #医療保険：1の位を四捨五入
                if(isset($v['srd'])){
                    foreach($v['srd'] as $srm => $v2){
                    foreach($v2 as $kk => $vv){
                        $data[$original_pid]['srd'][$srm][$kk]['copayment'] = round($vv['copayment'],-1);
                    }
                    }
                }else{
                    $data[$original_pid]['srd'][$srm] = array();
                }

                if(isset($kaigo_trans[$original_pid]['srm'])){
                    #echo $original_pid."はkaigo_trans存在\n";
#print_r($kaigo_trans[$original_pid]['srm']);
                    #【要確認】ここで1の位を四捨五入する必要あるか？
                    foreach($kaigo_trans[$original_pid]['srm'] as $srm => $v){
                        if( isset($data[$original_pid]['srm']['copayment']) ){
                            $data[$original_pid]['srm']['copayment'] += $v['copayment'];
                        }else{
                            $data[$original_pid]['srm']['copayment'] = $v['copayment'];
                        }

                        if( isset($data[$original_pid]['srm']['total_service_unit']) ){
                            $data[$original_pid]['srm']['total_service_unit'] += $v['tensu'];
                        }else{
                            $data[$original_pid]['srm']['total_service_unit'] = $v['tensu'];
                        }

                        $data[$original_pid]['srm']['data'][$srm] = $v;
                    }

                    

                }
            }

            #print_r($data);
            #print_r($data[151]);
            #print_r($kaigo_trans);
            #exit;

            #個人毎PDFデータ生成
            ##########
            #
            # データ集計
            #
            ##########
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
                    #echo $original_pid."はデータないので飛ばし\n";
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

                #請求番号
                $tmp_rand = uniqid();
                $inv_id = $patient_data['data']['irkkcode'] . "-" . sprintf('%07d', strval($original_pid)) . "-" . $this->targetym ."-".$tmp_rand;

                ### ---------- 点数表 ---------- ###
                #カテゴリーごとの合計点数を$m_category[$k]['tensu']に格納／医療保険の合計金額を$total_copaymentに加算
                foreach($patient_data['srd'] as $iryo_srm => $v2){

                    $tmp_copayment = 0;
                    foreach($v2 as $key => $shinryo_cat){
                        #$total_copayment += $shinryo_cat['copayment'];
                        $tmp_copayment += $shinryo_cat['copayment'];
                    }

                    #医療／公費負担額が存在する場合は$total_copaymentを上書き
                    if(isset($m_max[$original_pid][$iryo_srm]) && $m_max[$original_pid][$iryo_srm] != ""){
                        #if($m_max[$original_pid][$iryo_srm]){
                            #$total_copayment = $m_max[$original_pid][$iryo_srm];
                            $total_copayment += $m_max[$original_pid][$iryo_srm];   #公費は1ヶ月単位。だから足す。
                        #} 
                    }else{
                        $total_copayment += $tmp_copayment;
                    }
                }

                

                #介護保険の合計点数を$total_service_unitに格納／介護保険の合計金額を$total_copaymentに加算
                if(isset($patient_data['srm'])){
                    #echo $original_pid."は介護保険あり\n";
                    #foreach($patient_data['srm'] as $k => $v){
                        #$total_service_unit += $v['tensu'];
                        #$total_copayment += 10 * $v['tensu'] * $v['rate'] / 100;
                    #}
                    $total_copayment += $patient_data['srm']['copayment'];
                }


                #2020/02/12 一部負担金と支払い総額を分ける必要あり
                $ichibufutankin = $total_copayment;
                for($i=1;$i<=3;$i++){
                if(isset($patient_data['app_cat'][$i])){
                    $total_copayment += $patient_data['app_cat'][$i];
                }
                }


                #支払総額が「0」の場合はスキップ
                if($total_copayment == 0){
                #  echo $original_pid."---".$patient_data['data']['name']."は支払金額0のためスルー\n";
                #continue;
                } 
                
                $data[$original_pid]['total_copayment'] = $total_copayment;
                $data[$original_pid]['ichibufutankin'] = $ichibufutankin;

                #echo $original_pid."---".$patient_data['data']['name']."---".$total_copayment."---".$patient_data['data']['direct_debit']."<br>\n";


                $total_tensu = 0;
                $total_copayment = 0;





            }#foreach $data
            #$aaa = serialize($data[392]);
            #$sql = "update acc_detail set contents = '{$aaa}' where id = 1061;";
            #$this->db->databasequery($sql);exit;
            #echo $sql;exit;
            #print_r($data[392]);exit;

            return $data;
        



        endif;
        


        

    }
    function getCategoryMaster(){
        $m_category = $this->commonconst->m_category;
        return $m_category;
    }
    #負担区分マスター
    function getFutanCode(){
        $sql = "SELECT * FROM futan_code ";
        $stmt = $this->db->databasequery($sql);
        $futan = $stmt->fetchALL(PDO::FETCH_ASSOC);
        $m_futan = array();
        foreach($futan as $value){
            $m_futan[$value['code']] = $value['futan'];
        }
        return $m_futan;
    }
    #種別コードマスター
    function getSyubetsuCode(){
        $sql = "SELECT * FROM syubetsu_code";
        $stmt = $this->db->databasequery($sql);
        $syubetsu = $stmt->fetchALL(PDO::FETCH_ASSOC);
        $m_syubetsu = array();
        foreach($syubetsu as $value){
          $m_syubetsu[$value['code']]['syubetsu'] = $value['syubetsu'];
          $m_syubetsu[$value['code']]['ratio'] = $value['ratio'];
        }
        return $m_syubetsu;
    }
    #サービス名マスター
    function getServiceCode(){
        $sql = "SELECT * FROM service_code";
        $stmt = $this->db->databasequery($sql);
        $service = $stmt->fetchALL(PDO::FETCH_ASSOC);
        $m_service = array();
        foreach($service as $value){
          $m_service[$value['code']] = $value['service_name'];
        }
        return $m_service;
    }

    function generateRPdata(){
        $data = $this->getPaymentData();
        #print_r($data);
        foreach($data as $original_pid => $patient_data):
            $tmp_rand = uniqid();
            $inv_id = $patient_data['data']['irkkcode'] . "-" . sprintf('%07d', strval($original_pid)) . "-" . $this->targetym ."-".$tmp_rand;
            $sql = "INSERT INTO acc_result (gid,rst,ap,ec,god,cod,am,tx,sf,ta,em,nm,original_pid,srm,targetym,reqid,rp_disableflag,rp_errorflag,rp_errormsg,carryforward_flag)
                    VALUES (0,0,0,0,0,'$inv_id','{$patient_data['total_copayment']}',0,0,0,'','','$original_pid',0,'{$this->targetym}',null,'{$patient_data['data']['direct_debit']}',0,'',0);";
            #echo $sql."\n";
            $this->db->databasequery($sql);
            echo $original_pid."\t".$patient_data['data']['name']."\t".$patient_data['total_copayment']."\n";

            #220327
            #idを取得
            $sql = "SELECT LAST_INSERT_ID();";
            $stmt = $this->db->databasequery($sql);
            $result = $stmt->fetch();

            $contents = serialize($patient_data);
            $sql = "INSERT INTO acc_detail (rid,original_pid,contents) VALUES ('{$result['LAST_INSERT_ID()']}','{$original_pid}','{$contents}');";
            $this->db->databasequery($sql);
           
        endforeach;

        $sql = "UPDATE manageperiod SET status = 2 where status = 1 and targetym = '{$this->targetym}';";
        $this->db->databasequery($sql);
        $sql = "UPDATE re_shinryo SET manageperiod_status = 2 where manageperiod_status = 1 and manageperiod_targetym = '{$this->targetym}';";
        $this->db->databasequery($sql);
        $sql = "UPDATE rek_service SET manageperiod_status = 2 where manageperiod_status = 1 and manageperiod_targetym = '{$this->targetym}';";
        $this->db->databasequery($sql);
        $sql = "UPDATE appendix SET manageperiod_status = 2 where manageperiod_status = 1 and manageperiod_targetym = '{$this->targetym}';";
        $this->db->databasequery($sql);
    }

    #イレギュラー操作
    function generateAccDetail(){
        #指定のtargetymのacc_resultデータ抽出
        $sql = "SELECT * FROM acc_result WHERE targetym = '{$this->targetym}';";
        $stmt = $this->db->databasequery($sql);
        $tmp_data = $stmt->fetchALL(PDO::FETCH_ASSOC);

        $acc_data = array();
        foreach($tmp_data as $v):
            $acc_data[$v['original_pid']]['acc_result'] = $v['am'];
        endforeach;

        $data = $this->getPaymentData();
        #print_r($data);
        
        foreach($data as $original_pid => $patient_data):
            #$acc_data[$original_pid]['generate'] = $patient_data['total_copayment'];

            $sql = "SELECT rid FROM acc_result WHERE targetym = '{$this->targetym}' AND original_pid = {$original_pid}";
            $stmt = $this->db->databasequery($sql);
            $count = $stmt->rowCount();

            if($count > 1) echo "2箇所以上存在！！！\n";
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            #echo $data['rid']."\t".$original_pid."\n";
            $contents = serialize($patient_data);
            $sql = "INSERT INTO acc_detail (rid,original_pid,contents) VALUES ('{$data['rid']}','{$original_pid}','{$contents}');";
            #print_r($sql);
            $this->db->databasequery($sql);

            /*
            if($acc_data[$original_pid]['acc_result'] != $acc_data[$original_pid]['generate']):
                $sql = "SELECT patient_name from patient_info where original_pid = {$original_pid}";
                $stmt = $this->db->databasequery($sql);
                $tmp_data = $stmt->fetch(PDO::FETCH_ASSOC);

                echo $original_pid."\t".$tmp_data['patient_name']."\tacc_result\t".$acc_data[$original_pid]['acc_result']."\tgenerate\t".$acc_data[$original_pid]['generate']."\n";
            endif;
            */

        endforeach;
      
    }

/*
    function generateAccDetailDEBUG($data){
        $sql = "select * from acc_result where targetym = '{$this->targetym}'";
        $stmt = $this->db->databasequery($sql);
        $result = $stmt->fetchALL(PDO::FETCH_ASSOC);
        
        $acc = array();
        foreach($result as $v):
            $acc[$v['original_pid']]['rid'] = $v['rid'];
            $acc[$v['original_pid']]['ta'] = $v['ta'];
        endforeach;

        foreach($data as $original_pid => $patient_data):
            #echo "rid:".$acc[$original_pid]['rid']."---original_pid:".$original_pid."---ta:".$acc[$original_pid]['ta']."---total_copayment".$patient_data['total_copayment']."<br>\n";

            $contents = serialize($patient_data);
            $sql = "INSERT INTO acc_detail (rid,original_pid,contents) VALUES ('{$acc[$original_pid]['rid']}','{$original_pid}','{$contents}');";
            $this->db->databasequery($sql);
        endforeach;

    }
*/

    function generateRPdataDEBUG($data){
        
        foreach($data as $original_pid => $patient_data):
            $tmp_rand = uniqid();
            $inv_id = $patient_data['data']['irkkcode'] . "-" . sprintf('%07d', strval($original_pid)) . "-" . $this->targetym ."-".$tmp_rand;
            $sql = "INSERT INTO acc_result (gid,rst,ap,ec,god,cod,am,tx,sf,ta,em,nm,original_pid,srm,targetym,reqid,rp_disableflag,rp_errorflag,rp_errormsg,carryforward_flag)
                    VALUES (0,0,0,0,0,'$inv_id','{$patient_data['total_copayment']}',0,0,0,'','','$original_pid',0,'{$this->targetym}',null,'{$patient_data['data']['direct_debit']}',0,'',0);";
            #echo $sql."\n";
            $this->db->databasequery($sql);
            echo $original_pid."\t".$patient_data['data']['name']."\t".$patient_data['total_copayment']."\n";

            #220327
            #idを取得
            $sql = "SELECT LAST_INSERT_ID();";
            $stmt = $this->db->databasequery($sql);
            $result = $stmt->fetch();

            $contents = serialize($patient_data);
            $sql = "INSERT INTO acc_detail (rid,original_pid,contents) VALUES ('{$result['LAST_INSERT_ID()']}','{$original_pid}','{$contents}');";
            $this->db->databasequery($sql);
           
        endforeach;

    }

    function generatePDF(){
        #マスタ形成
        $m_category = $this->commonconst->m_category;
        $m_bank_classification = $this->commonconst->m_bank_classification;
        $m_prefecture = $this->commonconst->m_prefecture;

        $m_futan = $this->getFutanCode();
        $m_syubetsu = $this->getSyubetsuCode();
        $m_service = $this->getServiceCode();
        

        $newpage_offset = 18;
        $newpage_offset2 = 56;
        $newpage_offset3 = 20;

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf=new \Mpdf\Mpdf([
            'mode' => 'ja+aCJK',
            'format' => array(257,364),
            'margin_left' => 10, // 左の余白
            'margin_right' => 10, // 右の余白
            'margin_top' => 6, //上の余白
            'margin_bottom' => 0, //下の余白
            'margin_header' => 0, //ヘッダーの余白
            'margin_footer' => 0, //フッターの余白
            'dpi' => 150,
            'img_dpi' => 150,
            'debug'=> true,
            'debugfonts'=> true,
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts/',
            ]),
            'fontdata' => $fontData + [ // lowercase letters only in font key
                'ipamjm' => [
                    'R' => 'ipamjm.ttf',
                    'I' => 'ipamjm.ttf',
                ]
            ],
            'default_font' => 'ipamjm',
        ]);
        /*$mpdf->fontdata = [
            "trebuchetms" => [
                'R' => "trebuc.ttf",
                'B' => "trebucbd.ttf",
                'I' => "trebucit.ttf",
                'BI' => "trebucbi.ttf",
            ],
        ];*/
        #$mpdf->dpi = 150;
        #$mpdf->img_dpi = 150;
        #$mpdf->debug = true;
        #$mpdf->debugfonts = true;

        
        // $mpdf->WriteHTML($html);
        // $mpdf->Output();exit;
        $data = $this->getPaymentData();
        #print_r($data);exit;
        
        
        #個人毎PDFデータ生成
        $cnt = 1;
        #print_r($m_category);
        #print_r($data);exit;
        foreach ($data as $original_pid => $patient_data) {
            // if(isset($patient_data['srm'])):
            //     echo $original_pid."---介護データあり";
            // else:
            //     echo $original_pid."---介護データなし";
            // endif;
            
            /*$html = "<html>
            <head>
            <title></title>
            </head>
            <body>";*/
            $html = "";
            
            $name_flag = false;
            if(isset($patient_data['data']['shipto_name']) && $patient_data['data']['shipto_name'] != ""){
              $name_flag = true;
            }elseif( isset($patient_data['data']['name']) && $patient_data['data']['name'] != ""){
              $name_flag = true;
            }else{
              #echo "---shipto_name:".$patient_data['data']['shipto_name']."---name:".$patient_data['data']['name'];exit;
            }

            #請求番号
            $inv_id = $patient_data['data']['irkkcode'] . "-" . $srm . "-" . sprintf('%07d', strval($original_pid));

            ### ---------- 封筒窓 ---------- ###

            ## 封筒表紙（左窓）##
            $html .= "<div class=\"wrap\"></div>";
            if($this->format == "seikyu"){
                $html .= "<p class=\"header-left\">請求書｜訪問診療</p>";
            }else if($this->format == "ryosyu"){
                $html .= "<p class=\"header-left\">領収書｜訪問診療</p>";
            }

            #顧客情報
            $html .= "<p class=\"patient-address\">〒".$patient_data['data']['postal_code']."-".$patient_data['data']['postal_code2']."<br>".$m_prefecture[$patient_data['data']['prefecture']]."<br>".$patient_data['data']['address1']."<br>".$patient_data['data']['address2']."</p>";

            if($patient_data['data']['shipto_name']){
                $html .= "<p class=\"patient-name\">".$patient_data['data']['shipto_name']." 様<br><span class=\"patient-name-sub\">（".$patient_data['data']['name']." 様分）</span></p>";
            } else {
                $html .= "<p class=\"patient-name\">".$patient_data['data']['name']." 様</p>";
            }

            $html .= "<p class=\"patient-id\"><span>No.$inv_id</span></p>";


            #注意書き
            if($this->format == "seikyu"){
                $html .= "<p class=\"notes\">※診療費(自己負担金）を、ご請求申し上げます。<br>
                        ※保険証の変更等ございましたらご連絡いただきますよう宜しくお願い申し上げます。</p>";
            } else if($this->format == "ryosyu"){
                $html .= "<p class=\"notes\">※印紙税法、第5条　第1項により非課税。<br>
                        ※医療費控除を受けるために必要です再発行はできませんので、大切に保管して下さい。</p>";
            }

            ## 封筒表紙（右窓）##
            $html .= "<p class=\"header-right\">医療機関名 <span class=\"header-right-sub\">※お問い合わせはこちらへ</span></p>";
            $html .= "<p class=\"irkk-name\">".$patient_data['data']['irkkname']."</p>";
            $html .= "<p class=\"irkk-address\">〒".$patient_data['data']['irkk_postal_code']."<br>".$patient_data['data']['irkk_prefecture']."<br>".$patient_data['data']['irkk_address1']."<br>".$patient_data['data']['irkk_address2']."<br>".$patient_data['data']['irkk_tel']."</p><br>";

            if($this->format == "seikyu"){
                $html .= "<p class=\"irkk-account\">".$patient_data['data']['irkk_bank_name']." ".$patient_data['data']['irkk_bank_branch']." ".$m_bank_classification[$patient_data['data']['irkk_bank_clasification']]." ".$patient_data['data']['irkk_bank_no']."</p><p class=\"irkk-account2\">（口座振替ご利用の方は、振り込みは不要です）</p>";
            }


            ## 折位置表示 ##
            $html .= "<p class=\"fold-point\">▶</p>";
            #echo $html;exit;
            #請求額
            if($this->manageperiod_flag == 1):
                $seikyu_month = (isset($this->targetym) && $this->targetym != "") ? date('Y年m月',strtotime($this->targetym."01")) : date('Y年m月',strtotime($patient_data['data']['srm']."01"));
            else:
                $seikyu_month = date('Y年m月',strtotime($this->srd_start . " + 1month")) ;
            endif;
            
            $html .= "<p id=\"shinryo-month\">".$seikyu_month."分</p>";
            if($this->format == "seikyu"){
                $html .= "<p id=\"total-copayment\">ご請求額　".number_format($patient_data['total_copayment'])." 円</p>";
            } else if($this->format == "ryosyu"){
                $html .= "<p id=\"total-copayment\">領収額　".number_format($patient_data['total_copayment'])." 円</p>";
                #$html .= "<p id=\"ryosyu-date\">領収日<br>2019/07/07</p>";
                #領収日自由記入追加21-12-04
                if($this->ryosyu_date !== ""){
                    $html .= "<p id=\"ryosyu-date\">領収日<br>".date("Y/m/d",strtotime($this->ryosyu_date))."</p>";
                }else{
                    $html .= "<p id=\"ryosyu-date\">領収日<br>".date("Y/m/d",strtotime($this->targetym."10  +1month"))."</p>";
                }
            }
            #echo $original_pid."---".$total_copayment."---<br>";

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
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['A'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['B'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['C'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['D'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['E'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['F'])."点</td></tr>";
            $html .= "<tr>
                    <th class=\"color333 border_rb\">".$m_category['G']['title']."</th>
                    <th class=\"color333 font18 border_rb\">".$m_category['H']['title']."</th>
                    <th class=\"color333 border_rb\">".$m_category['I']['title']."</th>
                    <th class=\"color333 border_rb\">".$m_category['J']['title']."</th>
                    <th class=\"color333 border_rb\">".$m_category['K']['title']."</th>
                    <th class=\"color333 border_rb\">".$m_category['L']['title']."</th></tr>";
            $html .= "<tr>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['G'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['H'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['I'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['J'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['K'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['L'])."点</td></tr>";
            $html .= "<tr>
                    <th class=\"color333 font16 border_rb\">".$m_category['M']['title']."</th>
                    <th class=\"color333 border_rb\">".$m_category['N']['title']."</th>
                    <th class=\"color333 border_rb\">".$m_category['O']['title']."</th>
                    <th class=\"color333 border-border-top border_b\">合計</th>
                    <th class=\"color333 border-border-top font14 border_b\">居宅療養管理指導(介護保険)</th>
                    <th class=\"color333 border-border-top border_b\">一部負担金</th></tr>";

            #介護保険は1円まで金額出す。一部負担金の四捨五入を解除
            $html .= "<tr>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['M'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['N'])."点</td>
                    <td class=\"tensu-row border_rb\">".number_format($patient_data['category']['O'])."点</td>
                    <td class=\"tensu-row border-border-bottom\">".number_format($patient_data['total_tensu'])."点</td>
                    <td class=\"tensu-row border-border-bottom\">".number_format($patient_data['srm']['total_service_unit'])."単位</td>
                    <td class=\"tensu-row border-border-bottom\">".number_format($patient_data['ichibufutankin'])."円</td></tr>";
            $html .= "</table><br/>\n";

            #保険外負担
            $html .= "<div id=\"hokengai-table\"><table class='disp_table'>";
            $html .= "<tr><th rowspan=\"4\" class=\"side-header border_rb\">保険外負担</th></tr>";
            $html .= "<th class=\"hokengai-col border_rb\">自由診療</th>
                    <th class=\"hokengai-col border_rb\">販売品</th>
                    <th class=\"hokengai-col border_rb\">その他</th></tr>";
            $html .= "<tr><td class='border_r'>".number_format($patient_data['app_cat']['1'])."円</td>
                    <td class='border_r'>".number_format($patient_data['app_cat']['2'])."円</td>
                    <td class='border_r'>".number_format($patient_data['app_cat']['3'])."円</td></tr>";

            if(isset($patient_data['app_item']['1']) && $patient_data['app_item']['1'] != "") $app_item1 = $patient_data['app_item']['1']; else $app_item1 = "<br>";
            if(isset($patient_data['app_item']['2']) && $patient_data['app_item']['2'] != "") $app_item2 = $patient_data['app_item']['2']; else $app_item2 = "<br>";
            if(isset($patient_data['app_item']['3']) && $patient_data['app_item']['3'] != "") $app_item3 = str_replace(")","）",str_replace("(","（",$patient_data['app_item']['3'])); else $app_item3 = "<br>";
            /*$html .= "<tr><td class=\"uchiwake border_rb\">".$patient_data['app_item']['1']."\n</td>
                    <td class=\"uchiwake border_rb\">".$patient_data['app_item']['2']."\n</td>
                    <td class='border_rb'>".$patient_data['app_item']['3']."\n</td></tr>";*/
            $html .= "<tr><td class=\"uchiwake border_rb\">".$app_item1."\n</td>
                    <td class=\"uchiwake border_rb\">".$app_item2."\n</td>
                    <td class='border_rb' style='font-size:14px;'>".$app_item3."\n</td></tr>";
            $html .= "</table></div>";

            #未収金／過剰金
            #介護保険は1円まで金額出す。
            $html .= "<div id=\"misyu-kajo-table\"><table class='disp_table'>";
            $html .= "<tr><th class=\"color333 border_rb\">前回未収金</th>
                        <th class=\"color333 border_rb\">前回過剰金</th>
                        <th class=\"color333 border_rb\">今回ご請求額</th></tr>";
            $html .= "<tr><td class='border_r'>0円</td><td class='border_r'>0円</td><td class='border_r'>".number_format($patient_data['total_copayment'])."円</td></tr>";
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

    #診療月でソート月を跨ぐ対策
    ksort($patient_data['srd']);
    foreach($patient_data['srd'] as $kk => $vv){
        
        #診療日順にソート
        ksort($vv);
        #print_r($vv);
        foreach($vv as $k => $v){
            #print_r($v);

            if($row_count == 0 && $global_count > 0){

                $html .= "{$tmp}<div id=\"iryo-meisai-table\"><table class='disp_table'><tr><th colspan=5 class='border_rb'>医療保険</th></tr><tr>
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
                #echo "ここでglobalcount繰り上がり1";
                if($global_count%2 == 0){
                    $tmp = "<div class='clearfix'>&nbsp;</div>";
                }else{
                    $tmp = "";
                }

                $html .= "<tr><td colspan=5 class='border_rb'></td></tr></table></div>{$tmp}<div id=\"iryo-meisai-table\"><table class='disp_table'><tr><th colspan=5 class='border_rb'>医療保険</th></tr><tr>
                <th class=\"category-col border_b\">部</th>
                <th class=\"border_b\">項目</th>
                <th class=\"tensu-col border_b\">点数</th>
                <th class=\"x-col border_b\"></th>
                <th class=\"kaisu-col border_rb\">回数</th></tr>";
            }

            #ルーティン②：診療行の処理
            #アルファベット順にソート
            /*
            $sort_keys = array();
            foreach($v['sid'] as $key => $value){
                $sort_keys[$key] = $value['category'];
            }
            array_multisort($sort_keys, SORT_ASC, $v['sid']);
            */
            
            foreach($v['sid'] as $meisai){
                $tmp_shinryo = explode("（",$meisai['shinryo_name']);

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
                    #echo "ここでglobalcount繰り上がり2";
                    if($global_count%2 == 0){
                        #echo "通過A";
                        $tmp = "<div class='clearfix'>&nbsp;</div><div style='margin-top:100px;'>&nbsp;</div>";
                    }else{
                        #echo "通過B";
                        $tmp = "";
                    }

                    $html .= "<tr><td colspan=5 class='border_rb'></td></tr></table></div>{$tmp}<div id=\"iryo-meisai-table\"><table class='disp_table'><tr><th colspan=5 class='border_rb'>医療保険</th></tr><tr>
                    <th class=\"category-col border_b\">部</th>
                    <th class=\"border_b\">項目</th>
                    <th class=\"tensu-col border_b\">点数</th>
                    <th class=\"x-col border_b\"></th>
                    <th class=\"kaisu-col border_rb\">回数</th></tr>";
                }
                #echo "通過C";
            }

            #ルーティン③：小計行の処理：この行で全診療レコードが完了する可能性があるため完了時の処理
            $html .= "<tr><td class=\"sum-row border_r\" colspan=\"5\"><p>小計:".number_format($v['tensu'])."点 　 ".number_format($v['copayment'])."円 　 負担:".$v['ratio']."%</p></td></tr>";

            $row_count++;

            if($global_count < 2){
                $check_offset = $newpage_offset;
            }else{
                $check_offset = $newpage_offset2;
            }
            #echo $row_count ."---".$check_offset."<br>\n";
            if($row_count == $check_offset){
                $row_count = 0;
                $global_count++;
                #echo "ここでglobalcount繰り上がり3";
                if($global_count%2 == 0){
                    $tmp = "<div class='clearfix'>&nbsp;</div>";
                }else{
                    $tmp = "";
                }

                $html .= "<tr><td colspan=5 class='border_rb'></td></tr></table></div>";
                
                /*
                $html .= "{$tmp}<div id=\"iryo-meisai-table\"><table class='disp_table'><tr><th colspan=5 class='border_rb'>医療保険</th></tr><tr>
                <th class=\"category-col non-border\">部</th>
                <th class=\"non-border\">項目</th>
                <th class=\"tensu-col non-border\">点数</th>
                <th class=\"x-col non-border\"></th>
                <th class=\"kaisu-col border_r\">回数</th></tr>";
                */
            }
        }#foreach内側
    }#foreach外側

        $html .= "<tr><td colspan=5 class='border_rb'></td></tr></table>";

        #echo "現在地：".$global_count."\n";continue;

        ### 220428 介護データがある場合は、回り込みdivを閉じない / 医療保険が左列だけだったら介護の開始divが必要
        #介護がある場合
        #echo "この時点で".$row_count."---";
        if( isset($patient_data['srm']) && count($patient_data['srm']) > 0 ):
            #現在地が左側（global_ccoung=0）
            if($global_count == 0):
                #$html .= "global={$global_count}-rowcount-{$row_count}-ここ1";
                $html .= "</div><div id=\"iryo-meisai-table\">";
            elseif($global_count == 1):

                #中途半端な場所ならページ変える
                if($row_count > 12):
                    #$html .= "global={$global_count}-rowcount-{$row_count}-ここ2";
                   # echo $row_count."---";
                    $html .= "</div><div class='clearfix'>&nbsp;</div><div id=\"iryo-meisai-table\">";
                    $row_count = 0;
                else:
                    #$html .= "global={$global_count}-rowcount-{$row_count}-ここ3";

                    #row_countが18でglobalcountが1増加した直後、閉じdiv</div>が入るからその考慮をする
                    if($row_count == 0):
                        $html .= "<div id=\"iryo-meisai-table\">";
                    else:
                        $html .= "<br>";
                    endif;
                    #$html .= "</div><div class='clearfix'>&nbsp;</div><div id=\"iryo-meisai-table\">";
                    #$html .= "<br></div><div>";
                endif;
            endif;
        #介護がない場合
        else:
            #$html .= "介護なし";
            $html .= "</div>";
        endif;

        #介護保険（右列）
        #foreach($patient_data['srm'] as $kaigo_key => $kaigo_value){
        if(isset($patient_data['srm'])){
            foreach($patient_data['srm'] as $kaigo_key => $kaigo_loop){
                // if($original_pid == 151){
                //     print_r($kaigo_value);
                // }
                if($kaigo_key == "data"):
                    $row_count++;$row_count++;$row_count++;
                    $html .= "<table class='disp_table'><tr><th colspan=5 class='border_rb'>介護保険</th></tr><tr>
                    <th class=\"border_b meisai-title-row\">項目</th>
                    <th class=\"tensu-col border_b meisai-title-row\">単位</th>
                    <th class=\"x-col border_b meisai-title-row\"></th>
                    <th class=\"kaisu-col border_b meisai-title-row\">回数</th>
                    <th class=\"border_rb meisai-title-row\">算定日</th></tr>";
                    foreach($kaigo_loop as $srm_value => $kaigo_value){
                        $srm_ym = date('Y年m月',strtotime($srm_value."01"));

                        $html .= "<tr><td colspan=\"5\" class=\"date-row border_r\">●".$srm_ym."</td></tr>";
                        $row_count++;

                        if(isset($kaigo_value['sid'])){
                            foreach ($kaigo_value['sid'] as $meisai){
                                $tmp_tensu = isset($patient_data['srm']['data']['tensu']) ? $patient_data['srm']['data']['tensu'] : 0;
                                $tmp_rate = isset($patient_data['srm']['data']['rate']) ? $patient_data['srm']['data']['rate'] : 0;
                                $copeyment = 10 * $tmp_tensu * $tmp_rate /100;
                                $html .= "<tr><td class=\"item-col non-border\">".$meisai['service_name']."</td><td class=\"tensu-col non-border\">".$meisai['service_unit']."</td>
                                <td class=\"x-col non-border\">×</td>
                                <td class=\"kaisu-col non-border\">".$meisai['kaisu']."</td>
                                <td class=\"date-col border_r\" align=center>".$meisai['tekiyo']."</td></tr>";



                                $row_count++;

                                if($global_count < 2){
                                    $check_offset = $newpage_offset;
                                }else{
                                    $check_offset = $newpage_offset2;
                                }
                                /*
                                if($row_count > 12):
                                    $html .= "<tr><td colspan=5 class='border_rb'></td></tr></table></div><div id=\"iryo-meisai-table\">";
                                    $html .= "<table class='disp_table'><tr><th colspan=5 class='border_rb'>介護保険</th></tr><tr>
                                    <th class=\"border_b meisai-title-row\">項目</th>
                                    <th class=\"tensu-col border_b meisai-title-row\">単位</th>
                                    <th class=\"x-col border_b meisai-title-row\"></th>
                                    <th class=\"kaisu-col border_b meisai-title-row\">回数</th>
                                    <th class=\"border_rb meisai-title-row\">算定日</th></tr>";
                                else:
                                    $html .= "<tr><td colspan=5 class='border_rb'></td></tr></table></div><div id=\"\">";
                                    $html .= "<table class='disp_table'><tr><th colspan=5 class='border_rb'>介護保険</th></tr><tr>
                                    <th class=\"border_b meisai-title-row\">項目</th>
                                    <th class=\"tensu-col border_b meisai-title-row\">単位</th>
                                    <th class=\"x-col border_b meisai-title-row\"></th>
                                    <th class=\"kaisu-col border_b meisai-title-row\">回数</th>
                                    <th class=\"border_rb meisai-title-row\">算定日</th></tr>";
                                endif;
                                */

                                if($row_count == $check_offset){
                                    $row_count = 0;
                                    $global_count++;
                                    $html .= "ここでglobalcount繰り上がり4";
                                    if($global_count%2 == 0){
                                        $tmp = "<div class='clearfix'>&nbsp;</div>";
                                    }else{
                                        $tmp = "";
                                    }

                                    #rowcountが13以上だったら次のブロックいく？

                                /*
                                    if($row_count > 12):
                                        $html .= "<tr><td colspan=5 class='border_rb'></td></tr></table></div><div id=\"iryo-meisai-table\">";
                                    else:
                                        $html .= "<tr><td colspan=5 class='border_rb'></td></tr></table></div><div id=\"\">";
                                    endif;*/
                                /*
                                    $html .= "<table class='disp_table'><tr><th colspan=5 class='border_rb'>介護保険</th></tr><tr>
                                    <th class=\"border_b meisai-title-row\">項目</th>
                                    <th class=\"tensu-col border_b meisai-title-row\">単位</th>
                                    <th class=\"x-col border_b meisai-title-row\"></th>
                                    <th class=\"kaisu-col border_b meisai-title-row\">回数</th>
                                    <th class=\"border_rb meisai-title-row\">算定日</th></tr>";
                                    */
                                }



                            }#下のforeach
                        }
                        $html .= "<tr><td class=\"sum-row border_r\" colspan=\"5\"><p>小計:".number_format($kaigo_value['tensu'])."点 　 ".number_format($kaigo_value['copayment'])."円 　 負担:".$kaigo_value['rate']."%</p></td></tr>";
                    }#上のforeach
                    #$html .= "<tr><td class=\"sum-row-kaigo border_rb\" colspan=\"5\"><p>小計:".number_format($patient_data['srm']['data']['tensu'])."単位　　 ".number_format($copeyment)."円 　負担:".$patient_data['srm']['data']['rate']."%</p></td></tr></table></div>";
                    $html .= "<tr><td colspan=5 class='border_rb'></td></tr></table></div>";
                endif;
            }
        }

        #clearfix
        $html .= "<div class=\"clearfix\"></div>";
        $total_tensu = $total_copayment = $total_service_unit = "0";


        #CSS
        #$html .= "<style>
$css = <<<EOD
<style>
body {
font-family: sans;
}
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
top:624px;
left:990px;
font-size:18px;
font-weight:bold;
}
.irkk-account2{
position:absolute;
top:646px;
left:990px;
font-size:14px;
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

padding:0;
margin:0;
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
word-wrap: break-word;
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
margin: 0px auto 30px auto;
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
</style>
EOD;

                #$html .= "</body></html>";
            
            #$mpdf->WriteHTML($html);
            $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

            if($cnt < count($data)){
                $mpdf->AddPage();
            }
            $cnt++;

            #if($cnt > 3) break;
        }
        #echo $html;exit;
        if($this->pdf_path != ""):
            #$mpdf->Output( dirname(dirname(__FILE__)) . "/downloadpdf/202203_seikyu.pdf","F");
            $mpdf->Output( $this->pdf_path , "F");
        else:
            $mpdf->Output();
        endif;
       
    }

    function getKaisyuData(){
        #新バージョン
        $data = array();
        if(isset($this->targetym) && $this->targetym != ""):
            #$sql = "SELECT a.*, b.patient_birth, b.patient_name FROM acc_result as a , patient_info as b WHERE a.original_pid = b.original_pid and  a.targetym = '{$_GET['targetym']}';";
            $sql = "SELECT a.*, b.patient_birth, b.patient_name, c.original_irkkcode  
                    FROM acc_result as a 
                    , patient_info as b 
                    , accountpatient_relation as c 
                    WHERE a.original_pid = b.original_pid 
                    and a.original_pid = c.original_pid 
                    and  a.targetym = '{$this->targetym}' order by a.rid asc";

            $stmt = $this->db->databasequery($sql);
            $tmp_acc_data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        
            foreach($tmp_acc_data as $v){
                $data['acc_data_total'][$v['original_irkkcode']][] = $v;

                #合計金額の計算：未回収は含めない
                if($v['rp_errorflag'] == 9):
                    if( isset($data['monthly_copayment'][$v['original_irkkcode']]) ){
                        $data['monthly_copayment'][$v['original_irkkcode']] += $v['ta'];
                    }else{
                        $data['monthly_copayment'][$v['original_irkkcode']] = $v['ta'];
                    }
                endif;
            }
        endif;

        return $data;
        
    }

    function getAccountInfo(){
        $sql = "SELECT *
            FROM account_info
            WHERE original_irkkcode ";
        $stmt = $this->db->databasequery($sql);
        $tmp = $stmt->fetchALL(PDO::FETCH_ASSOC);
        $data = array();
        foreach($tmp as $v){
            $data[$v['original_irkkcode']] = $v;
        }
        return $data;
    }

    function generateKaisyuPDF(){

        $data = $this->getKaisyuData();
        $irkk_data = $this->getAccountInfo();

        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf=new \Mpdf\Mpdf([
            'mode' => 'ja+aCJK',
            'format' => array(257,364),
            'margin_left' => 10, // 左の余白
            'margin_right' => 10, // 右の余白
            'margin_top' => 6, //上の余白
            'margin_bottom' => 0, //下の余白
            'margin_header' => 0, //ヘッダーの余白
            'margin_footer' => 0, //フッターの余白
            'dpi' => 150,
            'img_dpi' => 150,
            'debug'=> true,
            'debugfonts'=> true,
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/fonts/',
            ]),
            'fontdata' => $fontData + [ // lowercase letters only in font key
                'ipamjm' => [
                    'R' => 'ipamjm.ttf',
                    'I' => 'ipamjm.ttf',
                ]
            ],
            'default_font' => 'ipamjm',
        ]);
        /*
        $mpdf=new mPDF('ja+aCJK',array(257,364),
        0,
        '',
        10,
        10,
        6,
        0,
        0,
        ''
        );
        $mpdf->dpi = 150;
        $mpdf->img_dpi = 150;
        $mpdf->debug = true;
        $mpdf->debugfonts = true;
        */

          #医療機関毎のPDFデータ生成

        $cnt = 0;
        #システム上の管理番号
        #$inv_id = $patient_data['data']['irkkcode'] . "-" . $patient_data['data']['srm'] . "-" . sprintf('%07d', strval($original_pid));
        foreach($data['acc_data_total'] as $irkkcode => $acc_data):

            #echo $irkkcode;
            #print_r($acc_data);
            #exit;
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
            $html .= "<p class=\"irkk-name\">Clinic Payment 株式会社</p>";
            $html .= "<p class=\"irkk-address\">〒193-0824<br>東京都八王子市長房町484-3<br>ベルドミール長房302<br>請求エクスプレス事業担当</p><br>";

            ## 折位置表示 ##
            $html .= "<p class=\"fold-point\">▶</p>";

            #print_r($data);

            ### ---------- 回収表 ---------- ###


            $html .= "<p id=\"total-copayment\">回収明細一覧</p>";

            #回収明細まとめテーブル
            $html .= "<table id=\"hoken-table\">
                        <tr><th>歯科医院名</th><td>".$irkk_data[$irkkcode]['irkkname']." 様</td></tr>
                        <tr><th>処理年月</th><td>".date('Y年m月',strtotime($this->targetym."01"))."分</td></tr>
                        <tr><th>合計件数</th><td>".count($acc_data)."件</td></tr>
                        <tr><th>合計金額</th><td>¥".number_format($data['monthly_copayment'][$irkkcode])."</td></tr>
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
                }elseif($v['rp_disableflag'] == 2){
                    $status = "回収済";
                    $date = date('Y年m月',strtotime($v['date']));
                } else {
                    $status = "未回収";
                    $date = "---";
                }

                $html .= "<tr>
                            <td>".$cnt."</td>
                            <td>".$status."</td>
                            <td>"."[".$v['original_pid']."]".$v['patient_name']."</td>
                            <td>".date('Y年m月d日',strtotime($v['patient_birth']))."</td>
                            <!--<td>¥".number_format($v['ta'])."</td>-->
                            <td>¥".number_format($v['am'])."</td>
                            <td>".$kaisyu_method."</td>
                            <td>".$date."</td>
                        </tr>";

                if($cnt < count($acc_data)) $cnt++;

                /*if(!isset($_REQUEST['testview'])){
                    if($cnt > 100) break;
                }*/

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

        $mpdf->Output();


    }

    function getTargetymFromManageperiod(){
        #医療保険マスター
        #$sql = "SELECT * FROM manageperiod where status = 0;";
        ###$sql = "SELECT * FROM manageperiod where status = 1;";
        #$sql = "SELECT * FROM manageperiod where status = 2;";

        $sql = "SELECT * FROM manageperiod where status <> 9;";
        $stmt = $this->db->databasequery($sql);
        $manageperiod = $stmt->fetchALL(PDO::FETCH_ASSOC);
        if(count($manageperiod) == 0){
            echo "なし";
            exit;
        }else{
            #print_r($manageperiod);
        }
        #$status = $manageperiod[0]['status'];
        $targetym = $manageperiod[0]['targetym'];
        return $targetym;

    }

    function pickupTargetymRecords(){

        /*
        $sql = "SELECT re_patient.original_pid as repatient_original_pid,re_shinryo.original_pid as shinryo_original_pid, patient_info.original_pid as patient_original_pid FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid INNER JOIN patient_info ON re_shinryo.original_pid = patient_info.original_pid INNER JOIN account_info on re_shinryo.original_irkkcode = account_info.original_irkkcode 
        WHERE re_shinryo.manageperiod_targetym = '{$this->targetym}' 
        AND patient_info.disp = 0 order by re_shinryo.srd,re_shinryo.category";
        #echo $sql;
        $stmt = $this->db->databasequery($sql);
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        foreach($data as $v):
            if($v['patient_original_pid'] == $v['repatient_original_pid'] && $v['patient_original_pid'] == $v['shinryo_original_pid']):
            else:
                echo "患者DB\t".$v['patient_original_pid']."\tre_患者\t".$v['repatient_original_pid']."\tre_診療\t".$v['shinryo_original_pid']."\n";
            endif;
        endforeach;
        */

        #医療保険
        $sql = "SELECT patient_info.original_pid,re_patient.pid, re_shinryo.sid,re_shinryo.srd,re_shinryo.manageperiod_targetym FROM re_shinryo INNER JOIN re_patient ON re_shinryo.pid = re_patient.pid INNER JOIN patient_info ON re_shinryo.original_pid = patient_info.original_pid INNER JOIN account_info on re_shinryo.original_irkkcode = account_info.original_irkkcode 
        WHERE re_shinryo.manageperiod_targetym = '{$this->targetym}' 
        AND patient_info.disp = 0 order by re_shinryo.srd,re_shinryo.category";
        #echo $sql;
        $stmt = $this->db->databasequery($sql);
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        foreach($data as $v):
            $sql = "INSERT INTO re_manageperiod (original_pid,pid,sid,srd,status,targetym,delete_flag,regist_date) VALUES ('{$v['original_pid']}','{$v['pid']}','{$v['sid']}','{$v['srd']}',0,'{$v['manageperiod_targetym']}',0,now());";
            #echo $sql."\n";
            $this->db->databasequery($sql);
        endforeach;

        #介護保険
        $sql = "SELECT rek_service.*, rek_patient.jigyosya,rek_patient.kaigo_hoban,rek_patient.kaigo_hihoban,rek_patient.futansya,rek_patient.jukyusya,rek_patient.birth,rek_patient.sex,rek_patient.hoken_rate,rek_patient.kouhi_rate,rek_patient.totalcopayment 
                FROM rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid
                WHERE 1 = 1 ";
        $sql .= "AND rek_service.original_pid <> 0 AND rek_patient.original_pid <> 0 ";
        $sql .= "AND rek_service.manageperiod_targetym = '{$this->targetym}' ";
        #echo $sql;
        $stmt = $this->db->databasequery($sql);
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        #print_r($data);
        foreach($data as $v):
            $sql = "INSERT INTO rek_manageperiod (original_pid,pid,sid,srm,status,targetym,delete_flag,regist_date) VALUES ('{$v['original_pid']}','{$v['pid']}','{$v['sid']}','{$v['srm']}',0,'{$v['manageperiod_targetym']}',0,now());";
            #echo $sql."\n";
            $this->db->databasequery($sql);
        endforeach;

        #自由診療
        $sql = "SELECT * FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid ";
        $sql .= "WHERE appendix.manageperiod_targetym = '{$this->targetym}' ";
        $sql .= "and patient_info.disp = 0 and appendix.disp = 0 order by app_date";
        $stmt = $this->db->databasequery($sql);
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        #print_r($data);
        foreach($data as $v):
            $sql = "INSERT INTO appendix_manageperiod (original_pid,app_id,app_date,status,targetym,delete_flag,regist_date) VALUES ('{$v['original_pid']}','{$v['app_id']}','{$v['app_date']}',0,'{$v['manageperiod_targetym']}',0,now());";
            #echo $sql."\n";
            $this->db->databasequery($sql);
        endforeach;

    }

    function carryForward(){
        $sql = "SELECT * FROM acc_result WHERE targetym = '{$this->targetym}' AND rp_errorflag = 1";
        $stmt = $this->db->databasequery($sql);
        #echo $sql."\n";
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);

        foreach($data as $v):
            $sql = "SELECT * FROM re_shinryo WHERE manageperiod_targetym = '{$this->targetym}' AND original_pid = '{$v['original_pid']}' ;";
            $stmt = $this->db->databasequery($sql);
            $data2 = $stmt->fetchALL(PDO::FETCH_ASSOC);
            #print_r($data2);
            foreach($data2 as $vv):
                $sql = "UPDATE re_shinryo SET manageperiod_status = 5 WHERE sid = {$vv['sid']};";
                #echo $sql ."\n";
                $this->db->databasequery($sql);
            endforeach;

            $sql = "SELECT * FROM rek_service WHERE manageperiod_targetym = '{$this->targetym}' AND original_pid = '{$v['original_pid']}' ;";
            $stmt = $this->db->databasequery($sql);
            $data2 = $stmt->fetchALL(PDO::FETCH_ASSOC);
            #print_r($data2);

            foreach($data2 as $vv):
                $sql = "update rek_service set manageperiod_status = 5 where sid = '{$vv['sid']}';";
                #echo $sql."\n";
                $this->db->databasequery($sql);
            endforeach;

            $sql = "SELECT * FROM appendix WHERE manageperiod_targetym = '{$this->targetym}' AND original_pid = '{$v['original_pid']}' ;";
            $stmt = $this->db->databasequery($sql);
            $data2 = $stmt->fetchALL(PDO::FETCH_ASSOC);
            #print_r($data2);

            foreach($data2 as $vv):
                $sql = "update appendix set manageperiod_status = 5 where app_id = '{$vv['app_id']}';";
                #echo $sql."\n";
                $this->db->databasequery($sql);
            endforeach;

        endforeach;
    }

    function carryForward2(){
        $sql = "SELECT max(targetym) FROM manageperiod WHERE status = 9";
        $stmt = $this->db->databasequery($sql);
        #echo $sql."\n";
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->targetym = $data['max(targetym)'];
        #echo $this->targetym;exit;

        #reqidが存在してamが0のものはロボペイから返ってこないからrp_errorflagを強制9（正常）処理して繰り越させない
        #$sql = "SELECT * FROM acc_result WHERE targetym <= '{$this->targetym}' AND reqid is not null AND reqid != '' AND rp_errorflag = 0 AND am = 0";
        $sql = "SELECT * FROM acc_result WHERE targetym <= '{$this->targetym}' AND rp_errorflag = 0 AND am = 0";
        $stmt = $this->db->databasequery($sql);
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        print_r($data);
        if($stmt->rowCount() > 0):
            foreach($data as $v):
                $sql = "UPDATE acc_result SET rp_errorflag = 9 WHERE rid = '{$v['rid']}'; ";
                echo $sql." を実行します... ";
                $stmt = $this->db->databasequery($sql);
                echo "完了\n";
            endforeach;
        else:
            echo "reqidが存在してamが0のものは対象なし\n";
        endif;

        echo "次の行程：{$this->targetym} rp_errorflag != 9 carryforward_flag = 0 の検索\n";
        #$sql = "SELECT * FROM acc_result WHERE targetym <= '{$this->targetym}' AND rp_errorflag != 9 AND carryforward_flag = 0";   #targetymより以前が対象
        #$sql = "SELECT * FROM acc_result WHERE targetym = '{$this->targetym}' AND rp_errorflag != 9 AND carryforward_flag = 0";     #targetymが対象
        $sql = "SELECT a.* FROM acc_result as a , patient_info as b WHERE a.original_pid = b.original_pid AND a.targetym = '{$this->targetym}' AND a.rp_errorflag != 9 AND a.carryforward_flag = 0 AND b.disp = 0";     #targetymが対象・disp=0対象

        #$sql = "SELECT * FROM acc_result WHERE original_pid = 183";
        echo $sql."\n";
        $stmt = $this->db->databasequery($sql);
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        print_r($data);
        #exit;

        foreach($data as $v):
            #医療保険
            /*$sql = "SELECT * FROM re_shinryo WHERE (manageperiod_targetym <= '{$this->targetym}' AND manageperiod_targetym != 0) 
                     AND (manageperiod_status != 5 AND manageperiod_status != 4 AND manageperiod_status != 9) AND original_pid = '{$v['original_pid']}' ;";*/
            $sql = "SELECT * FROM re_shinryo WHERE (manageperiod_targetym = '{$this->targetym}' AND manageperiod_targetym != 0) 
                    AND (manageperiod_status != 5 AND manageperiod_status != 4 AND manageperiod_status != 9) AND original_pid = '{$v['original_pid']}' ;";
            $stmt = $this->db->databasequery($sql);
            $data2 = $stmt->fetchALL(PDO::FETCH_ASSOC);
            echo $sql."\n";
            print_r($data2);

            foreach($data2 as $vv):
                $sql = "UPDATE re_shinryo SET manageperiod_status = 5 WHERE sid = {$vv['sid']};";
                echo $sql ."\n";
                $this->db->databasequery($sql);
            endforeach;

            #介護保険
            /*$sql = "SELECT * FROM rek_service WHERE (manageperiod_targetym <= '{$this->targetym}' AND manageperiod_targetym != 0) 
                     AND (manageperiod_status != 5 AND manageperiod_status != 4 AND manageperiod_status != 9) AND original_pid = '{$v['original_pid']}' ;";*/
            $sql = "SELECT * FROM rek_service WHERE (manageperiod_targetym = '{$this->targetym}' AND manageperiod_targetym != 0) 
                     AND (manageperiod_status != 5 AND manageperiod_status != 4 AND manageperiod_status != 9) AND original_pid = '{$v['original_pid']}' ;";
            $stmt = $this->db->databasequery($sql);
            $data2 = $stmt->fetchALL(PDO::FETCH_ASSOC);
            echo $sql."\n";
            print_r($data2);

            foreach($data2 as $vv):
                $sql = "update rek_service set manageperiod_status = 5 where sid = '{$vv['sid']}';";
                echo $sql."\n";
                $this->db->databasequery($sql);
            endforeach;

            #自由診療
            /*$sql = "SELECT * FROM appendix WHERE (manageperiod_targetym <= '{$this->targetym}' AND manageperiod_targetym != 0) 
                     AND (manageperiod_status != 5 AND manageperiod_status != 4 AND manageperiod_status != 9) AND original_pid = '{$v['original_pid']}' ;";*/
            $sql = "SELECT * FROM appendix WHERE (manageperiod_targetym = '{$this->targetym}' AND manageperiod_targetym != 0) 
                     AND (manageperiod_status != 5 AND manageperiod_status != 4 AND manageperiod_status != 9) AND original_pid = '{$v['original_pid']}' ;";
            $stmt = $this->db->databasequery($sql);
            $data2 = $stmt->fetchALL(PDO::FETCH_ASSOC);
            echo $sql."\n";
            print_r($data2);

            foreach($data2 as $vv):
                $sql = "update appendix set manageperiod_status = 5 where app_id = '{$vv['app_id']}';";
                echo $sql."\n";
                $this->db->databasequery($sql);
            endforeach;

            #acc_resultに繰り越しフラグを立てる

            $sql = "UPDATE acc_result SET rp_errorflag = 1 ,carryforward_flag = 1 WHERE rid = '{$v['rid']}' ;";
            echo $sql."\n";
            $this->db->databasequery($sql);

        endforeach;

        ###ここからはイレギュラー処理
        /*
        $sql = "select max(targetym) from manageperiod where status != 9 ";
        $stmt = $this->db->databasequery($sql);
        echo $sql."\n";
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->targetym = $data['max(targetym)'];
        echo $this->targetym;

        #医療保険
        $sql = "select * from re_shinryo where manageperiod_status = 0 or manageperiod_status = 5;";
        $stmt = $this->db->databasequery($sql);
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        #print_r($data);
        foreach($data as $v):
            $sql = "UPDATE re_shinryo SET manageperiod_status = 1 , manageperiod_targetym = '{$this->targetym}' WHERE sid = {$v['sid']};";
            echo $sql ."\n";
            $this->db->databasequery($sql);
        endforeach;

        #介護保険
        $sql = "select * from rek_service where manageperiod_status = 0 or manageperiod_status = 5;";
        $stmt = $this->db->databasequery($sql);
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        #print_r($data);
        foreach($data as $v):
            $sql = "UPDATE rek_service SET manageperiod_status = 1 , manageperiod_targetym = '{$this->targetym}' WHERE sid = {$v['sid']};";
            echo $sql ."\n";
            $this->db->databasequery($sql);
        endforeach;

        #自由診療
        $sql = "select * from appendix where manageperiod_status = 0 or manageperiod_status = 5;";
        $stmt = $this->db->databasequery($sql);
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        #print_r($data);
        foreach($data as $v):
            $sql = "UPDATE appendix SET manageperiod_status = 1 , manageperiod_targetym = '{$this->targetym}' WHERE app_id = {$v['app_id']};";
            echo $sql ."\n";
            $this->db->databasequery($sql);
        endforeach;
        */

    }

    function irregularAdjust($original_pids){

        foreach($original_pids as $original_pid){
            echo $original_pid."<br>\n";

            $this->manageperiod_flag = 1;
            $this->targetym = $this->targetym;
            $this->readFromAccDetail = false;
            $this->manageperiod_debug_flag = true;
            $this->original_pid = $original_pid;
            $data = $this->getPaymentData();
            #print_r($data[$original_pid]);
            #qprint_r($data);exit;
            $tmp = serialize($data[$original_pid]);

            $sql = "SELECT rid FROM acc_result WHERE targetym = {$this->targetym} AND original_pid = {$original_pid}; ";
            $stmt = $this->db->databasequery($sql);

            if($stmt->rowCount() == 1):
                echo "1件HIT\n";
                $data2 = $stmt->fetch(PDO::FETCH_ASSOC);
                #print_r($data);
                $rid = $data2['rid'];
                $sql = "UPDATE acc_result SET am = '{$data[$original_pid]['total_copayment']}' WHERE rid = {$rid}";
                #echo $sql."\n";
                $this->db->databasequery($sql);
                $sql = "UPDATE acc_detail SET contents = '{$tmp}' WHERE rid = {$rid}";
                #echo $sql."\n";exit;
                $this->db->databasequery($sql);
            elseif($stmt->rowCount() == 0):
                echo "対象なし";

                foreach($data as $original_pid => $patient_data):
                    $tmp_rand = uniqid();
                    $inv_id = $patient_data['data']['irkkcode'] . "-" . sprintf('%07d', strval($original_pid)) . "-" . $this->targetym ."-".$tmp_rand;
                    $sql = "INSERT INTO acc_result (gid,rst,ap,ec,god,cod,am,tx,sf,ta,em,nm,original_pid,srm,targetym,reqid,rp_disableflag,rp_errorflag,rp_errormsg,carryforward_flag)
                            VALUES (0,0,0,0,0,'$inv_id','{$patient_data['total_copayment']}',0,0,0,'','','$original_pid',0,'{$this->targetym}',null,'{$patient_data['data']['direct_debit']}',0,'',0);";
                    echo $sql."\n";
                    $this->db->databasequery($sql);
                    echo $original_pid."\t".$patient_data['data']['name']."\t".$patient_data['total_copayment']."\n";
        
                    #220327
                    #idを取得
                    $sql = "SELECT LAST_INSERT_ID();";
                    $stmt = $this->db->databasequery($sql);
                    $result = $stmt->fetch();
        
                    $contents = serialize($patient_data);
                    $sql = "INSERT INTO acc_detail (rid,original_pid,contents) VALUES ('{$result['LAST_INSERT_ID()']}','{$original_pid}','{$contents}');";
                    $this->db->databasequery($sql);
                   
                endforeach;


            else:
                echo "2件以上。エラー";
            endif;
           




            
        }
        echo "完了";
        #黒川
        #$original_pid = 180;
        #$rid = 1459;
        #畑野
        #$original_pid = 181;
        #$rid = 1460;

        #①
        /*
        $this->manageperiod_flag = 1;
        $this->targetym = 202204;
        $this->manageperiod_debug_flag = true;
        $this->original_pid = $original_pid;
        $data = $this->getPaymentData();
        #print_r($data);exit;
        $tmp = serialize($data[$original_pid]);
        */

        #強制更新
        #②
        /*
        $sql = "UPDATE acc_detail SET contents = '{$tmp}' WHERE rid = {$rid}";
        $this->db->databasequery($sql);
        exit;
        */

        #調査
        #③
        
        /*
        $sql = "SELECT * FROM acc_detail WHERE rid = '{$rid}'";
        $stmt = $this->db->databasequery($sql);
        #echo $sql."\n";
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $contents = unserialize($data['contents']);
        print_r($contents);
        */

        /*
        $sql = "SELECT * FROM acc_result as a , acc_detail as b WHERE a.rid = b.rid AND a.targetym = '202204'";
        $stmt = $this->db->databasequery($sql);
        $acc_detail_count = $stmt->rowCount();
        $acc_detail_data = array();
        if($acc_detail_count > 0):
            $tmp = $stmt->fetchALL(PDO::FETCH_ASSOC);
            print_r($tmp);
            foreach($tmp as $v):
                $acc_detail_data[$v['original_pid']] = unserialize($v['contents']);
                print_r($acc_detail_data[$v['original_pid']]);
            endforeach;
        endif;
        */

    }

    function dummy(){
        /*
        $sql = "SELECT * FROM acc_detail ";
        $stmt = $this->db->databasequery($sql);
        $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
        foreach($data as $v){
            $patient_data = unserialize($v['contents']);
            print_r($patient_data);
        }
        */
        echo dirname(dirname(__FILE__)) . "/downloadpdf/";
    }
}

?>