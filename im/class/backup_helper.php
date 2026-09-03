<?php
/**
 * DB data-only backup / restore helper for manager UI.
 */
class BackupHelper
{
    const REASON_BEFORE_RECEIPT = 'before_receipt';
    const REASON_BEFORE_CLOSING = 'before_closing';
    const REASON_BEFORE_RESTORE = 'before_restore';
    const MAX_KEEP = 30;

    private $dbName;
    private $dbUser;
    private $dbPass;
    private $dbHost;
    private $backupDir;
    private $dbh;

    public static $reasonLabels = array(
        self::REASON_BEFORE_RECEIPT => 'レセプト取込前',
        self::REASON_BEFORE_CLOSING => '締め前',
        self::REASON_BEFORE_RESTORE => 'リストア前',
    );

    public function __construct($dbh = null)
    {
        $this->dbName = defined('DBNAME') ? DBNAME : '';
        $this->dbUser = 'xs547384_dx';
        $this->dbPass = 'wwxlkl7m';
        $this->dbHost = 'localhost';
        $this->backupDir = dirname(__DIR__) . '/backup';
        $this->dbh = $dbh;
        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0755, true);
        }
    }

    public function getBackupDir()
    {
        return $this->backupDir;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function ensureLogTable()
    {
        if (!$this->dbh) {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS backup_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(32) NOT NULL,
            reason VARCHAR(64) DEFAULT NULL,
            filename VARCHAR(255) DEFAULT NULL,
            operator VARCHAR(128) DEFAULT NULL,
            message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $this->dbh->exec($sql);
    }

    public function writeLog($action, $reason, $filename, $operator, $message)
    {
        $this->ensureLogTable();
        if (!$this->dbh) {
            $line = date('Y-m-d H:i:s') . "\t{$action}\t{$reason}\t{$filename}\t{$operator}\t{$message}\n";
            @file_put_contents($this->backupDir . '/backup_log.txt', $line, FILE_APPEND);
            return;
        }
        $stmt = $this->dbh->prepare(
            "INSERT INTO backup_log (action, reason, filename, operator, message) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute(array($action, $reason, $filename, $operator, $message));
    }

    public function create($reason, $operator = '')
    {
        if (!isset(self::$reasonLabels[$reason])) {
            return array('ok' => false, 'error' => '不正な理由です');
        }
        if ($this->dbName === '') {
            return array('ok' => false, 'error' => 'DBNAME が未定義です');
        }

        $stamp = date('Ymd_His');
        $filename = $reason . '_' . $stamp . '.sql.gz';
        $path = $this->backupDir . '/' . $filename;

        // -pPASSWORD (no space). Ignore operational log tables so restore keeps audit trail.
        $ignore = ' --ignore-table=' . escapeshellarg($this->dbName . '.backup_log')
            . ' --ignore-table=' . escapeshellarg($this->dbName . '.closing_log');
        $cmd = 'mysqldump -h ' . escapeshellarg($this->dbHost)
            . ' -u ' . escapeshellarg($this->dbUser)
            . ' -p' . $this->dbPass
            . ' --single-transaction --skip-triggers --no-create-info --default-character-set=utf8'
            . $ignore . ' '
            . escapeshellarg($this->dbName)
            . ' 2>/tmp/clinic_mysqldump_err.txt | gzip > ' . escapeshellarg($path);

        exec($cmd, $output, $exitCode);
        $err = @file_get_contents('/tmp/clinic_mysqldump_err.txt');
        @unlink('/tmp/clinic_mysqldump_err.txt');

        clearstatcache();
        if (!file_exists($path) || filesize($path) < 50) {
            $this->writeLog('create_fail', $reason, $filename, $operator, trim($err . ' ' . implode("\n", $output)));
            return array(
                'ok' => false,
                'error' => 'バックアップ作成に失敗しました: ' . trim($err),
            );
        }

        $this->pruneOld();
        $this->writeLog('create', $reason, $filename, $operator, 'ok size=' . filesize($path));

        return array(
            'ok' => true,
            'filename' => $filename,
            'path' => $path,
            'size' => filesize($path),
            'reason' => $reason,
            'label' => self::$reasonLabels[$reason],
        );
    }

    public function listBackups($limit = 30)
    {
        $files = glob($this->backupDir . '/*.sql.gz');
        if (!$files) {
            return array();
        }
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $list = array();
        foreach (array_slice($files, 0, $limit) as $path) {
            $name = basename($path);
            $reason = $this->parseReason($name);
            $list[] = array(
                'filename' => $name,
                'reason' => $reason,
                'label' => isset(self::$reasonLabels[$reason]) ? self::$reasonLabels[$reason] : $reason,
                'mtime' => date('Y-m-d H:i:s', filemtime($path)),
                'size' => filesize($path),
                'size_h' => $this->formatBytes(filesize($path)),
            );
        }
        return $list;
    }

    public function latestByReason($reason)
    {
        foreach ($this->listBackups(100) as $item) {
            if ($item['reason'] === $reason) {
                return $item;
            }
        }
        return null;
    }

    public function resolvePath($filename)
    {
        $filename = basename($filename);
        if (!preg_match('/^[a-z0-9_]+\.sql\.gz$/i', $filename)) {
            return null;
        }
        $path = $this->backupDir . '/' . $filename;
        if (!is_file($path)) {
            return null;
        }
        return $path;
    }

    public function restore($filename, $confirmCode, $sessionCode, $operator = '')
    {
        if ($confirmCode === '' || $sessionCode === '' || $confirmCode !== $sessionCode) {
            return array('ok' => false, 'error' => '確認コードが一致しません');
        }
        $path = $this->resolvePath($filename);
        if (!$path) {
            return array('ok' => false, 'error' => 'バックアップファイルが見つかりません');
        }

        // Safety dump before restore
        $safety = $this->create(self::REASON_BEFORE_RESTORE, $operator);
        if (!$safety['ok']) {
            return array('ok' => false, 'error' => 'リストア前バックアップに失敗したため中止: ' . $safety['error']);
        }

        if (!$this->dbh) {
            return array('ok' => false, 'error' => 'DB接続がありません');
        }

        try {
            $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0');
            $tables = $this->dbh->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM);
            foreach ($tables as $row) {
                $table = $row[0];
                if ($table === 'backup_log' || $table === 'closing_log') {
                    continue; // keep operational logs
                }
                $this->dbh->exec('TRUNCATE TABLE `' . str_replace('`', '``', $table) . '`');
            }

            $cmd = 'gunzip -c ' . escapeshellarg($path)
                . ' | mysql -h ' . escapeshellarg($this->dbHost)
                . ' -u ' . escapeshellarg($this->dbUser)
                . ' -p' . $this->dbPass
                . ' --default-character-set=utf8 '
                . escapeshellarg($this->dbName)
                . ' 2>/tmp/clinic_mysql_restore_err.txt';
            exec($cmd, $output, $exitCode);
            $err = @file_get_contents('/tmp/clinic_mysql_restore_err.txt');
            @unlink('/tmp/clinic_mysql_restore_err.txt');
            $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1');

            if ($exitCode !== 0) {
                $this->writeLog('restore_fail', '', $filename, $operator, trim($err));
                return array(
                    'ok' => false,
                    'error' => 'リストア実行に失敗しました: ' . trim($err),
                    'safety' => $safety['filename'],
                );
            }

            $this->writeLog('restore', '', $filename, $operator, 'ok safety=' . $safety['filename']);
            return array(
                'ok' => true,
                'filename' => $filename,
                'safety' => $safety['filename'],
            );
        } catch (Exception $e) {
            try {
                $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1');
            } catch (Exception $e2) {
            }
            $this->writeLog('restore_fail', '', $filename, $operator, $e->getMessage());
            return array('ok' => false, 'error' => $e->getMessage(), 'safety' => $safety['filename']);
        }
    }

    private function parseReason($filename)
    {
        if (preg_match('/^(before_receipt|before_closing|before_restore)_/', $filename, $m)) {
            return $m[1];
        }
        return '';
    }

    private function pruneOld()
    {
        $files = glob($this->backupDir . '/*.sql.gz');
        if (!$files || count($files) <= self::MAX_KEEP) {
            return;
        }
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        foreach (array_slice($files, self::MAX_KEEP) as $old) {
            @unlink($old);
        }
    }

    public function formatBytes($bytes)
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1048576, 1) . ' MB';
    }
}
