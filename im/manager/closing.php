<?php
session_start();
ini_set('display_errors', 1);
set_time_limit(0);
ini_set('memory_limit', '5120M');

include_once '../common/smarty_settings.php';
include_once '../class/common.php';
include_once '../class/config.php';
include_once '../class/backup_helper.php';
include_once '../class/closing_helper.php';

$common = new COMMON;
$common->id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
$common->password = isset($_SESSION['password']) ? $_SESSION['password'] : '';
$result = $common->checkid();
$row = $result->fetch();
if (!$row || $row[0] == 0) {
    header('Location: /index.php?error=error');
    exit;
}
$login_name = $row[1];

$dbh = new PDO('mysql:dbname=' . DBNAME . ';host=localhost;charset=utf8', 'xs547384_dx', 'wwxlkl7m');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$backup = new BackupHelper($dbh);
$closing = new ClosingHelper($dbh, $login_name);
$closing->ensureLogTable();

$flash_ok = '';
$flash_err = '';
$result_detail = '';
$step2_preview = null;
$step3_preview = null;

if (isset($_GET['backup'])) {
    if ($_GET['backup'] === 'ok') {
        $flash_ok = 'バックアップを作成しました';
    } elseif ($_GET['backup'] === 'ng') {
        $flash_err = 'バックアップ作成に失敗しました。backup.php で詳細を確認してください';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $enabled = $closing->getEnabledActions();

    try {
        switch ($action) {
            case 'a1_step4':
                if (!$enabled['a1_step4']) {
                    throw new Exception('現在 step4 は実行できません');
                }
                $res = $closing->runStep4();
                break;
            case 'a2_step5':
                if (!$enabled['a2_step5']) {
                    throw new Exception('現在 step5 は実行できません');
                }
                $res = $closing->runStep5();
                break;
            case 'a3_step7':
                if (!$enabled['a3_step7']) {
                    throw new Exception('現在 step7 は実行できません');
                }
                $res = $closing->runStep7();
                break;
            case 'a4_step8':
                if (!$enabled['a4_step8']) {
                    throw new Exception('現在 step8 は実行できません');
                }
                $res = $closing->runStep8();
                break;
            case 'b0_setperiod':
                if (!$enabled['b0_setperiod']) {
                    throw new Exception('現在 締め開始 は実行できません（未完了の期間があります）');
                }
                $latest = $backup->latestByReason(BackupHelper::REASON_BEFORE_CLOSING);
                if (!$latest) {
                    throw new Exception('締め前バックアップがありません。先にバックアップを取得してください');
                }
                $res = $closing->runSetperiod();
                break;
            case 'b1_step1':
                if (!$enabled['b1_step1']) {
                    throw new Exception('現在 step1 は実行できません');
                }
                $res = $closing->runStep1();
                break;
            case 'b2_preview':
                if (!$enabled['b2_preview']) {
                    throw new Exception('現在 step2 プレビューは実行できません');
                }
                $step2_preview = $closing->previewStep2();
                if (!$step2_preview['ok']) {
                    $res = $step2_preview;
                } else {
                    $res = array('ok' => true, 'message' => 'プレビューを表示しました（DB未変更）');
                }
                break;
            case 'b2_commit':
                if (!$enabled['b2_commit']) {
                    throw new Exception('現在 step2 は実行できません');
                }
                $res = $closing->runStep2();
                break;
            case 'b3_preview':
                if (!$enabled['b3_preview']) {
                    throw new Exception('現在 step3 プレビューは実行できません');
                }
                $step3_preview = $closing->previewStep3();
                if (!$step3_preview['ok']) {
                    $res = $step3_preview;
                } else {
                    $res = array('ok' => true, 'message' => '送信対象プレビュー（未送信） 件数=' . $step3_preview['count'] . ' 合計=' . number_format($step3_preview['total']) . '円');
                }
                break;
            case 'b3_send':
                if (!$enabled['b3_send']) {
                    throw new Exception('現在 step3 は実行できません');
                }
                $res = $closing->runStep3();
                break;
            default:
                throw new Exception('不明なアクションです');
        }

        if (!$res['ok']) {
            $flash_err = isset($res['error']) ? $res['error'] : '処理に失敗しました';
        } else {
            $flash_ok = isset($res['message']) ? $res['message'] : '完了';
            if (!empty($res['detail'])) {
                $result_detail = $res['detail'];
            }
        }
    } catch (Exception $e) {
        $flash_err = $e->getMessage();
    }
}

$enabled = $closing->getEnabledActions();
$periods = $closing->getManageperiods();
foreach ($periods as &$p) {
    $st = (int)$p['status'];
    $p['status_label'] = isset(ClosingHelper::$statusLabels[$st]) ? ClosingHelper::$statusLabels[$st] : '';
    if (isset($p['regist_date'])) {
        $p['regist_date_display'] = $p['regist_date'];
    } elseif (isset($p['created_at'])) {
        $p['regist_date_display'] = $p['created_at'];
    } else {
        $vals = array_values($p);
        $p['regist_date_display'] = isset($vals[3]) ? $vals[3] : '';
    }
}
unset($p);
$active = $closing->getActiveManageperiod();
$active_label = '';
if ($active) {
    $st = (int)$active['status'];
    $active_label = isset(ClosingHelper::$statusLabels[$st]) ? ClosingHelper::$statusLabels[$st] : '';
}
$logs = $closing->getRecentLogs(15);
$latest_closing_bk = $backup->latestByReason(BackupHelper::REASON_BEFORE_CLOSING);
$latest_receipt_bk = $backup->latestByReason(BackupHelper::REASON_BEFORE_RECEIPT);

$smarty->assign('account_name', $login_name);
$smarty->assign('flash_ok', $flash_ok);
$smarty->assign('flash_err', $flash_err);
$smarty->assign('result_detail', $result_detail);
$smarty->assign('enabled', $enabled);
$smarty->assign('periods', $periods);
$smarty->assign('active', $active);
$smarty->assign('active_label', $active_label);
$smarty->assign('status_labels', ClosingHelper::$statusLabels);
$smarty->assign('logs', $logs);
$smarty->assign('latest_closing_bk', $latest_closing_bk);
$smarty->assign('latest_receipt_bk', $latest_receipt_bk);
$smarty->assign('step2_preview', $step2_preview);
$smarty->assign('step3_preview', $step3_preview);
$smarty->assign('dbname', DBNAME);
$smarty->display('manager/closing.tpl');
