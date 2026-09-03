<?php
/**
 * Closing (manageperiod) step runner and status helper.
 */
class ClosingHelper
{
    private $dbh;
    private $operator;

    public static $statusLabels = array(
        0 => '締め待ち（割当前）',
        1 => '対象割当済',
        2 => '請求データ生成済（送信前／結果待ち前）',
        3 => 'ロボペイ結果待ち',
        9 => '月次クローズ完了',
    );

    public function __construct($dbh, $operator = '')
    {
        $this->dbh = $dbh;
        $this->operator = $operator;
    }

    public function ensureLogTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS closing_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(64) NOT NULL,
            targetym VARCHAR(8) DEFAULT NULL,
            operator VARCHAR(128) DEFAULT NULL,
            message TEXT,
            detail MEDIUMTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $this->dbh->exec($sql);
    }

    public function writeLog($action, $targetym, $message, $detail = '')
    {
        $this->ensureLogTable();
        $stmt = $this->dbh->prepare(
            "INSERT INTO closing_log (action, targetym, operator, message, detail) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute(array($action, $targetym, $this->operator, $message, $detail));
    }

    public function getRecentLogs($limit = 20)
    {
        $this->ensureLogTable();
        $limit = (int)$limit;
        $stmt = $this->dbh->query("SELECT * FROM closing_log ORDER BY id DESC LIMIT {$limit}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getManageperiods()
    {
        $stmt = $this->dbh->query("SELECT * FROM manageperiod ORDER BY targetym DESC, id DESC LIMIT 12");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveManageperiod()
    {
        $stmt = $this->dbh->query("SELECT * FROM manageperiod WHERE status <> 9 ORDER BY targetym DESC, id DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }

    public function getByStatus($status)
    {
        $stmt = $this->dbh->prepare("SELECT * FROM manageperiod WHERE status = ? ORDER BY targetym DESC LIMIT 5");
        $stmt->execute(array($status));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Which action buttons should be enabled.
     */
    public function getEnabledActions()
    {
        $enabled = array(
            'a1_step4' => false,
            'a2_step5' => false,
            'a3_step7' => false,
            'a4_step8' => false,
            'b0_setperiod' => false,
            'b1_step1' => false,
            'b2_preview' => false,
            'b2_commit' => false,
            'b3_preview' => false,
            'b3_send' => false,
        );

        $s2 = $this->getByStatus(2);
        $s3 = $this->getByStatus(3);
        $s0 = $this->getByStatus(0);
        $s1 = $this->getByStatus(1);
        $s9 = $this->getByStatus(9);

        $pendingSend = 0;
        if (count($s2) >= 1) {
            $stmt = $this->dbh->query(
                "SELECT COUNT(*) AS c FROM acc_result AS a
                 INNER JOIN patient_info AS b ON a.original_pid = b.original_pid
                 WHERE a.reqid IS NULL AND b.rp_cid IS NOT NULL AND b.direct_debit = 0 AND a.rp_disableflag = 0"
            );
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $pendingSend = (int)$row['c'];
        }

        if (count($s2) === 1) {
            if ($pendingSend > 0) {
                $enabled['b3_preview'] = true;
                $enabled['b3_send'] = true;
            } else {
                // 送信済み → 結果待ちへ（step4）
                $enabled['a1_step4'] = true;
            }
        }
        if (count($s3) === 1) {
            $enabled['a2_step5'] = true;
            $enabled['a3_step7'] = true;
        }
        if (count($s9) > 0 && count($s2) === 0 && count($s3) === 0 && count($s0) === 0 && count($s1) === 0) {
            $enabled['a4_step8'] = true;
        }
        if (count($s0) === 0 && count($s1) === 0 && count($s2) === 0 && count($s3) === 0) {
            $enabled['b0_setperiod'] = true;
        }
        if (count($s0) === 1) {
            $enabled['b1_step1'] = true;
        }
        if (count($s1) === 1) {
            $enabled['b2_preview'] = true;
            $enabled['b2_commit'] = true;
        }

        return $enabled;
    }

    public function runSetperiod()
    {
        $tejimai_date = date('Y-m-d H:i:s');
        $year = date('Y', strtotime($tejimai_date));
        $month = date('m', strtotime($tejimai_date));

        $sql = "SELECT * FROM rp_schedule WHERE YEAR(deadline_datetime) = '{$year}' AND MONTH(deadline_datetime) = '{$month}' ORDER BY deadline_datetime ASC LIMIT 1";
        $stmt = $this->dbh->query($sql);
        $tmp = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tmp) {
            return array('ok' => false, 'error' => 'rp_schedule に当月の締切がありません');
        }

        if (strtotime($tmp['deadline_datetime']) > strtotime($tejimai_date)) {
            $targetym = date('Ym', strtotime($tejimai_date));
        } else {
            $targetym = date('Ym', strtotime($tejimai_date . '+1 month'));
        }

        $sql = "SELECT * FROM manageperiod WHERE targetym = {$targetym}";
        $stmt = $this->dbh->query($sql);
        if ($stmt->rowCount() > 0) {
            return array('ok' => false, 'error' => "targetym={$targetym} は既に登録済みです");
        }

        $sql = "INSERT INTO manageperiod VALUES (NULL, '{$targetym}', 0, CURRENT_TIMESTAMP)";
        $this->dbh->query($sql);
        $this->writeLog('setperiod', $targetym, '締め開始（status=0）');
        return array('ok' => true, 'targetym' => $targetym, 'message' => "締め開始: {$targetym}");
    }

    public function runStep1()
    {
        $rows = $this->getByStatus(0);
        if (count($rows) === 0) {
            return array('ok' => false, 'error' => 'status=0 の manageperiod がありません');
        }
        $messages = array();
        foreach ($rows as $v) {
            $targetym = $v['targetym'];
            $this->dbh->query("UPDATE re_shinryo SET manageperiod_status = 1, manageperiod_targetym = '{$targetym}' WHERE manageperiod_status = 0 OR manageperiod_status = 5");
            $this->dbh->query("UPDATE rek_service SET manageperiod_status = 1, manageperiod_targetym = '{$targetym}' WHERE manageperiod_status = 0 OR manageperiod_status = 5");
            $this->dbh->query("UPDATE appendix SET manageperiod_status = 1, manageperiod_targetym = '{$targetym}' WHERE manageperiod_status = 0 OR manageperiod_status = 5");
            $this->dbh->query("UPDATE manageperiod SET status = 1 WHERE targetym = '{$targetym}' AND status = 0");
            $messages[] = "割当完了 targetym={$targetym}";
            $this->writeLog('step1', $targetym, '対象割当');
        }
        return array('ok' => true, 'message' => implode("\n", $messages));
    }

    public function previewStep2()
    {
        include_once dirname(__FILE__) . '/clsystem.php';
        $rows = $this->getByStatus(1);
        if (count($rows) !== 1) {
            return array('ok' => false, 'error' => 'status=1 の manageperiod が1件必要です');
        }
        $targetym = $rows[0]['targetym'];
        $cl = new CLSYSTEM();
        $cl->manageperiod_flag = 1;
        $cl->manageperiod_debug_flag = false;
        $cl->format = 'seikyu';
        $cl->targetym = $targetym;
        $data = $cl->getPaymentData();

        $list = array();
        $total = 0;
        $count = 0;
        foreach ($data as $original_pid => $patient_data) {
            if (!isset($patient_data['data']) || !is_array($patient_data['data'])) {
                continue;
            }
            if (!isset($patient_data['total_copayment']) || $patient_data['total_copayment'] == 0) {
                continue;
            }
            $name = isset($patient_data['data']['name']) ? $patient_data['data']['name'] : '';
            if (method_exists($cl, 'resolveRegisteredPatientName')) {
                $name = $cl->resolveRegisteredPatientName($patient_data);
            }
            $am = (float)$patient_data['total_copayment'];
            $dd = isset($patient_data['data']['direct_debit']) ? $patient_data['data']['direct_debit'] : '';
            $list[] = array(
                'original_pid' => $original_pid,
                'name' => $name,
                'amount' => $am,
                'direct_debit' => $dd,
            );
            $total += $am;
            $count++;
        }
        return array(
            'ok' => true,
            'targetym' => $targetym,
            'list' => $list,
            'count' => $count,
            'total' => $total,
        );
    }

    public function runStep2()
    {
        include_once dirname(__FILE__) . '/clsystem.php';
        set_time_limit(0);
        ini_set('memory_limit', '5120M');
        $rows = $this->getByStatus(1);
        if (count($rows) !== 1) {
            return array('ok' => false, 'error' => 'status=1 の manageperiod が1件必要です');
        }
        $targetym = $rows[0]['targetym'];
        $cl = new CLSYSTEM();
        $cl->manageperiod_flag = 1;
        $cl->manageperiod_debug_flag = false;
        $cl->format = 'seikyu';
        $cl->targetym = $targetym;

        ob_start();
        $cl->generateRPdata();
        $cl->pickupTargetymRecords();
        $out = ob_get_clean();

        $this->writeLog('step2', $targetym, 'acc_result 生成', $out);
        return array('ok' => true, 'targetym' => $targetym, 'message' => '請求データ生成完了', 'detail' => $out);
    }

    public function previewStep3()
    {
        $finish_date = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM rp_schedule WHERE deadline_datetime > '{$finish_date}' ORDER BY deadline_datetime ASC LIMIT 1";
        $stmt = $this->dbh->query($sql);
        $transfer = $stmt->fetch(PDO::FETCH_ASSOC);
        $transfer_date = $transfer ? $transfer['transfer_date'] : '';

        $sql = "SELECT a.rid, a.original_pid, a.am, a.cod, a.targetym, b.patient_name, b.rp_cid
                FROM acc_result AS a
                INNER JOIN patient_info AS b ON a.original_pid = b.original_pid
                WHERE a.reqid IS NULL AND b.rp_cid IS NOT NULL AND b.direct_debit = 0 AND a.rp_disableflag = 0";
        $stmt = $this->dbh->query($sql);
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = 0;
        foreach ($list as $v) {
            $total += (float)$v['am'];
        }
        return array(
            'ok' => true,
            'transfer_date' => $transfer_date,
            'list' => $list,
            'count' => count($list),
            'total' => $total,
        );
    }

    public function runStep3()
    {
        set_time_limit(0);
        ini_set('memory_limit', '5120M');

        $finish_date = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM rp_schedule WHERE deadline_datetime > '{$finish_date}' ORDER BY deadline_datetime ASC LIMIT 1";
        $stmt = $this->dbh->query($sql);
        $transfer_date_array = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$transfer_date_array) {
            return array('ok' => false, 'error' => '次回振替日が rp_schedule にありません');
        }
        $transfer_date = $transfer_date_array['transfer_date'];

        $sql = "SELECT * FROM acc_result AS a
                INNER JOIN patient_info AS b ON a.original_pid = b.original_pid
                WHERE a.reqid IS NULL AND b.rp_cid IS NOT NULL AND b.direct_debit = 0 AND a.rp_disableflag = 0";
        $stmt = $this->dbh->query($sql);
        $acc_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $url = 'https://credit.j-payment.co.jp/gateway/at_gateway.aspx';
        $okCount = 0;
        $errCount = 0;
        $lines = array();

        foreach ($acc_result as $v) {
            $data = array(
                'aid' => AID,
                'cmd' => 2,
                'tday' => TDAY,
                'cid' => $v['rp_cid'],
                'amo' => $v['am'],
                'date' => $transfer_date,
                'type' => 1,
                'stat' => BILLINGSTATUS,
                'cod' => $v['cod'],
            );
            $body = http_build_query($data, '', '&');
            $header = array(
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Length: ' . strlen($body),
            );
            $context = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => $body,
                ),
            );
            $res = @file_get_contents($url, false, stream_context_create($context));
            $res = mb_convert_encoding($res, 'utf-8', 'sjis,EUC-JP');
            $resArr = explode(',', $res);
            $disc = mb_substr($resArr[0], 0, 2);

            if ($disc == 'ER') {
                $errMsg = $resArr[0];
                $this->dbh->query("UPDATE acc_result SET reqid = 0, rp_errorflag = 1, rp_errormsg = '{$errMsg}' WHERE rid = '{$v['rid']}'");
                $errCount++;
                $lines[] = "ERR rid={$v['rid']} pid={$v['original_pid']} {$errMsg}";
            } else {
                $reqid = $resArr[0];
                $this->dbh->query("UPDATE re_shinryo SET rp_reqid = '{$reqid}' WHERE original_pid = '{$v['original_pid']}' AND manageperiod_status = 2 AND manageperiod_targetym = '{$v['targetym']}'");
                $this->dbh->query("UPDATE rek_service INNER JOIN rek_patient ON rek_service.pid = rek_patient.pid SET rp_reqid = '{$reqid}' WHERE rek_patient.original_pid = '{$v['original_pid']}' AND manageperiod_targetym = '{$v['targetym']}'");
                $this->dbh->query("UPDATE appendix SET rp_reqid = '{$reqid}' WHERE original_pid = '{$v['original_pid']}' AND manageperiod_targetym = '{$v['targetym']}'");
                $this->dbh->query("UPDATE acc_result SET reqid = '{$reqid}' WHERE rid = '{$v['rid']}'");
                $okCount++;
                $lines[] = "OK rid={$v['rid']} pid={$v['original_pid']} reqid={$reqid} am={$v['am']}";
            }
        }

        $detail = implode("\n", $lines);
        $msg = "送信完了 成功={$okCount} 失敗={$errCount} 振替日={$transfer_date}";
        $targetym = count($acc_result) ? $acc_result[0]['targetym'] : '';
        $this->writeLog('step3', $targetym, $msg, $detail);
        return array('ok' => true, 'message' => $msg, 'detail' => $detail, 'ok_count' => $okCount, 'err_count' => $errCount);
    }

    public function runStep4()
    {
        $data = $this->getByStatus(2);
        if (count($data) !== 1) {
            return array('ok' => false, 'error' => 'status=2 の manageperiod が1件である必要があります（現在 ' . count($data) . ' 件）');
        }
        $targetym = $data[0]['targetym'];
        $this->dbh->query("UPDATE manageperiod SET status = 3 WHERE status = 2 AND targetym = '{$targetym}'");
        $this->dbh->query("UPDATE re_shinryo SET manageperiod_status = 3 WHERE manageperiod_status = 2 AND manageperiod_targetym = '{$targetym}'");
        $this->dbh->query("UPDATE rek_service SET manageperiod_status = 3 WHERE manageperiod_status = 2 AND manageperiod_targetym = '{$targetym}'");
        $this->dbh->query("UPDATE appendix SET manageperiod_status = 3 WHERE manageperiod_status = 2 AND manageperiod_targetym = '{$targetym}'");
        $this->writeLog('step4', $targetym, '結果待ちへ（status 2→3）');
        return array('ok' => true, 'targetym' => $targetym, 'message' => "結果待ちへ移行: {$targetym}");
    }

    public function runStep5()
    {
        $data = $this->getByStatus(3);
        if (count($data) !== 1) {
            return array('ok' => false, 'error' => 'status=3 の manageperiod が1件必要です');
        }
        $targetym = $data[0]['targetym'];
        $n = 0;

        $sql = "SELECT re_shinryo.sid FROM re_shinryo INNER JOIN patient_info ON re_shinryo.original_pid = patient_info.original_pid
                WHERE patient_info.direct_debit = 1 AND re_shinryo.manageperiod_targetym = '{$targetym}'";
        foreach ($this->dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $v) {
            $this->dbh->query("UPDATE re_shinryo SET manageperiod_status = 4 WHERE sid = '{$v['sid']}'");
            $n++;
        }
        $sql = "SELECT rek_service.sid FROM rek_service INNER JOIN patient_info ON rek_service.original_pid = patient_info.original_pid
                WHERE patient_info.direct_debit = 1 AND rek_service.manageperiod_targetym = '{$targetym}'";
        foreach ($this->dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $v) {
            $this->dbh->query("UPDATE rek_service SET manageperiod_status = 4 WHERE sid = '{$v['sid']}'");
            $n++;
        }
        $sql = "SELECT appendix.app_id FROM appendix INNER JOIN patient_info ON appendix.original_pid = patient_info.original_pid
                WHERE patient_info.direct_debit = 1 AND appendix.manageperiod_targetym = '{$targetym}'";
        foreach ($this->dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $v) {
            $this->dbh->query("UPDATE appendix SET manageperiod_status = 4 WHERE app_id = '{$v['app_id']}'");
            $n++;
        }

        $this->writeLog('step5', $targetym, "口振以外を完了扱い count={$n}");
        return array('ok' => true, 'targetym' => $targetym, 'message' => "口振以外を完了扱い: {$n} 件");
    }

    public function runStep7()
    {
        $data = $this->getByStatus(3);
        if (count($data) !== 1) {
            return array('ok' => false, 'error' => 'status=3 の manageperiod が1件必要です');
        }
        $id = $data[0]['id'];
        $targetym = $data[0]['targetym'];
        $this->dbh->query("UPDATE manageperiod SET status = 9 WHERE id = '{$id}'");
        $this->writeLog('step7', $targetym, '月次クローズ（status→9）');
        return array('ok' => true, 'targetym' => $targetym, 'message' => "月次クローズ完了: {$targetym}");
    }

    public function runStep8()
    {
        include_once dirname(__FILE__) . '/clsystem.php';
        set_time_limit(0);
        $cl = new CLSYSTEM();
        ob_start();
        $cl->carryForward2();
        $out = ob_get_clean();
        $targetym = isset($cl->targetym) ? $cl->targetym : '';
        $this->writeLog('step8_renew', $targetym, '振替失敗の繰越', $out);
        return array('ok' => true, 'targetym' => $targetym, 'message' => '繰越処理完了', 'detail' => $out);
    }
}
