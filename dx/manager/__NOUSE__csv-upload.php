<?php
ini_set("display_errors",1);
include_once "../common/smarty_settings.php";
include_once "../class/config.php";
include_once "../class/functions.php";


//DB Connect
$dbh = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dx','wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Common Part
$file = $_FILES["upfile"]["tmp_name"];
$handle = fopen ( $file, "r" );


while ( ( $dt = fgetcsv ( $handle, 200) ) !== FALSE ) {
    #print_r($dt);
    $res = array();

    $ec = "";
    $gid = "";
    $cod = "";
    $ta = 0;
    $tx = 0;
    $sf = 0;
    $manageperiod_status = "";
    $rp_errorflag = 0;
    $rst = 0;
    $ap = "";
    foreach($dt as $key => $val){
        $tmp = mb_convert_encoding($val, "UTF-8", "SJIS");
        $res[$key] = $tmp;
        
        switch($key){
            case 0:
                $gid = $tmp;
                break;
            case 2:
                if( strcmp($tmp,"決済成功") === 0 ){
                    $rp_errorflag = 9;
                    $manageperiod_status = 4;
                    $rst = 1;
                    $ap = 'ACC';
                }else{
                    $rp_errorflag = 1;
                    $manageperiod_status = 5;
                    $rst = 2;
                    if( strcmp($tmp,"決済待ち") === 0 ){
                        $ec = "J005";
                    }
                    $ap = 'ACC';
                }
                break;
            case 3:
                $cod = $tmp;
                break;
            case 7:
                $ta = $tmp;
                break;
            case 8:
                $ta = $tx;
                break;
            case 9:
                $sf = $tmp;
                break;
        }

        #echo $tmp.",";
    }
    
    if(strcmp($gid,"決済番号") === 0)   continue;

    #対象のoriginal_pidを抽出
    #rst = 1の場合、関連するレコードのstatusを4
    #rst = 2の場合、関連するレコードのstatusを5
    $sql = "SELECT original_pid,targetym FROM acc_result WHERE cod = '$cod';";
    $stmt = $dbh->query($sql);
    $data = $stmt->fetchALL(PDO::FETCH_ASSOC);
    echo $cod."<br>\n";
    print_r($data);
    echo "<br>\n";
    if(count($data)){
        $original_pid = $data[0]['original_pid'];
        $manageperiod_targetym = $data[0]['targetym'];
        

        $sql = "UPDATE re_shinryo SET manageperiod_status = '$manageperiod_status' WHERE original_pid = '$original_pid' and manageperiod_targetym = '$manageperiod_targetym' and manageperiod_status = 3 ;";
        $message .= $sql."\n";
        #$dbh->query($sql);
        echo $sql."<br>\n";

        $sql = "UPDATE rek_service SET manageperiod_status = '$manageperiod_status' WHERE original_pid = '$original_pid' and manageperiod_targetym = '$manageperiod_targetym' and manageperiod_status = 3 ;";
        $message .= $sql."\n";
        #$dbh->query($sql);
        echo $sql."<br>\n";

        $sql = "UPDATE appendix SET manageperiod_status = '$manageperiod_status' WHERE original_pid = '$original_pid' and manageperiod_targetym = '$manageperiod_targetym' and manageperiod_status = 3 ;";
        $message .= $sql."\n";
        #$dbh->query($sql);
        echo $sql."<br>\n";


        $sql = "UPDATE acc_result SET gid='$gid',rst='$rst',ap='$ap',ec='$ec',tx='$tx',sf='$sf',ta='$ta',rp_errorflag = '$rp_errorflag' where cod = '$cod'";
        $message .= $sql."\n";
        #$dbh->query($sql);
        echo $sql."<br>\n";
    }
    
    #echo "<br>\n";
    #print_r($res);
}

exit;
?>