<?php
session_start();
ini_set('display_errors', 1);
set_time_limit(0);

include_once '../common/smarty_settings.php';
include_once '../class/common.php';
include_once '../class/config.php';
include_once '../class/backup_helper.php';

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

$helper = new BackupHelper($dbh);
$helper->ensureLogTable();

$flash_ok = '';
$flash_err = '';
$confirm_view = null;
$view = isset($_GET['view']) ? $_GET['view'] : 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'create') {
        $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
        $res = $helper->create($reason, $login_name);
        if ($res['ok']) {
            $flash_ok = 'バックアップを作成しました: ' . $res['filename'] . ' (' . $helper->formatBytes($res['size']) . ')';
        } else {
            $flash_err = $res['error'];
        }
        $return = isset($_POST['return']) ? $_POST['return'] : '';
        if ($return === 'receipt') {
            header('Location: receipt_select.php?backup=' . ($res['ok'] ? 'ok' : 'ng'));
            exit;
        }
        if ($return === 'closing') {
            header('Location: closing.php?backup=' . ($res['ok'] ? 'ok' : 'ng'));
            exit;
        }
    }

    if ($action === 'download') {
        $path = $helper->resolvePath(isset($_POST['filename']) ? $_POST['filename'] : '');
        if ($path) {
            header('Content-Type: application/gzip');
            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }
        $flash_err = 'ファイルが見つかりません';
    }

    if ($action === 'restore_confirm') {
        $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
        $path = $helper->resolvePath($filename);
        if (!$path) {
            $flash_err = 'ファイルが見つかりません';
        } else {
            $code = sprintf('%06d', mt_rand(0, 999999));
            $_SESSION['backup_restore_code'] = $code;
            $_SESSION['backup_restore_file'] = basename($filename);
            $confirm_view = array(
                'filename' => basename($filename),
                'code' => $code,
                'dbname' => $helper->getDbName(),
                'mtime' => date('Y-m-d H:i:s', filemtime($path)),
                'size' => filesize($path),
            );
            $view = 'confirm';
        }
    }

    if ($action === 'restore_execute') {
        $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
        $code = isset($_POST['confirm_code']) ? trim($_POST['confirm_code']) : '';
        $sessionCode = isset($_SESSION['backup_restore_code']) ? $_SESSION['backup_restore_code'] : '';
        $sessionFile = isset($_SESSION['backup_restore_file']) ? $_SESSION['backup_restore_file'] : '';

        if ($filename !== $sessionFile) {
            $flash_err = '確認セッションが不正です。最初からやり直してください。';
        } else {
            $res = $helper->restore($filename, $code, $sessionCode, $login_name);
            unset($_SESSION['backup_restore_code'], $_SESSION['backup_restore_file']);
            if ($res['ok']) {
                $flash_ok = 'リストア完了: ' . $filename . '（直前バックアップ: ' . $res['safety'] . '）';
            } else {
                $flash_err = $res['error'];
            }
        }
    }
}

// expose formatBytes via wrapper if private — add public method call through list
$backups = $helper->listBackups(30);
$latest_receipt = $helper->latestByReason(BackupHelper::REASON_BEFORE_RECEIPT);
$latest_closing = $helper->latestByReason(BackupHelper::REASON_BEFORE_CLOSING);

$smarty->assign('account_name', $login_name);
$smarty->assign('flash_ok', $flash_ok);
$smarty->assign('flash_err', $flash_err);
$smarty->assign('backups', $backups);
$smarty->assign('latest_receipt', $latest_receipt);
$smarty->assign('latest_closing', $latest_closing);
$smarty->assign('dbname', $helper->getDbName());
$smarty->assign('confirm_view', $confirm_view);
$smarty->assign('view', $view);
$smarty->assign('reason_labels', BackupHelper::$reasonLabels);
$smarty->display('manager/backup.tpl');
