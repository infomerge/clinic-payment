<?php
/**
 * Robot Payment / サブスクペイ サーバー証明書変更向け 疎通確認
 *
 * 公式テスト環境へ HTTP GET のみ行い、SSL ハンドシェイク可否を確認する。
 * 決済 API（at_gateway）への POST・店舗ID・顧客番号・金額等は一切送信しない。
 *
 * 対象 URL: https://test.dev.j-payment.co.jp/
 * 成功目安: HTTP 200 系が返り、SSL エラーが出ないこと
 */

header('Content-Type: text/html; charset=UTF-8');

$testUrl = 'https://test.dev.j-payment.co.jp/';

/**
 * cURL で HEAD 相当（公式手順に近い）
 */
function rp_test_via_curl($url)
{
    $result = array(
        'method'  => 'cURL',
        'ok'      => false,
        'http'    => null,
        'error'   => null,
        'detail'  => '',
    );

    if (!function_exists('curl_init')) {
        $result['error'] = 'cURL 拡張が利用できません';
        return $result;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_NOBODY         => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_USERAGENT      => 'clinic-payment-rp-ssl-test/1.0',
    ));

    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    $result['http'] = isset($info['http_code']) ? (int)$info['http_code'] : null;
    $result['detail'] = sprintf(
        "ssl_verify_result=%s\nprimary_ip=%s\ntotal_time=%s",
        isset($info['ssl_verify_result']) ? $info['ssl_verify_result'] : 'n/a',
        isset($info['primary_ip']) ? $info['primary_ip'] : 'n/a',
        isset($info['total_time']) ? $info['total_time'] : 'n/a'
    );

    if ($errno !== 0) {
        $result['error'] = sprintf('curl errno=%d: %s', $errno, $error);
        return $result;
    }

    $result['ok'] = ($result['http'] >= 200 && $result['http'] < 400);
    if (!$result['ok']) {
        $result['error'] = '想定外の HTTP ステータス';
    }
    if (is_string($body) && $body !== '') {
        $result['detail'] .= "\n--- response headers ---\n" . trim($body);
    }

    return $result;
}

/**
 * file_get_contents（本番 step3 / payment-test-exe と同じ通信経路）
 * GET のみ。ボディ・クエリなし。
 */
function rp_test_via_file_get_contents($url)
{
    $result = array(
        'method'  => 'file_get_contents (PHP OpenSSL)',
        'ok'      => false,
        'http'    => null,
        'error'   => null,
        'detail'  => '',
    );

    $context = stream_context_create(array(
        'http' => array(
            'method'        => 'GET',
            'timeout'       => 30,
            'ignore_errors' => true,
            'header'        => "User-Agent: clinic-payment-rp-ssl-test/1.0\r\n",
        ),
        'ssl' => array(
            'verify_peer'      => true,
            'verify_peer_name' => true,
        ),
    ));

    $http_response_header = null;
    $body = @file_get_contents($url, false, $context);

    if ($body === false && empty($http_response_header)) {
        $last = error_get_last();
        $result['error'] = $last ? $last['message'] : 'file_get_contents 失敗（SSL または接続エラーの可能性）';
        return $result;
    }

    if (!empty($http_response_header) && isset($http_response_header[0])) {
        $result['detail'] = implode("\n", $http_response_header);
        if (preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $result['http'] = (int)$m[1];
        }
    }

    $result['ok'] = ($result['http'] !== null && $result['http'] >= 200 && $result['http'] < 400);
    if (!$result['ok'] && $result['error'] === null) {
        $result['error'] = '想定外の HTTP ステータス、またはステータス未取得';
    }

    return $result;
}

$results = array(
    rp_test_via_curl($testUrl),
    rp_test_via_file_get_contents($testUrl),
);

$allOk = true;
foreach ($results as $r) {
    if (!$r['ok']) {
        $allOk = false;
        break;
    }
}

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>Robot Payment SSL 疎通テスト</title>
<style>
body { font-family: sans-serif; margin: 2rem; line-height: 1.5; }
.ok { color: #0a7a2f; font-weight: bold; }
.ng { color: #b00020; font-weight: bold; }
.box { border: 1px solid #ccc; padding: 1rem; margin: 1rem 0; background: #fafafa; }
pre { white-space: pre-wrap; word-break: break-all; background: #f0f0f0; padding: .75rem; }
.note { color: #555; font-size: .95rem; }
</style>
</head>
<body>
<h1>Robot Payment SSL 疎通テスト</h1>
<p class="note">
  サブスクペイサーバー証明書変更（2026年11月20日切替）向けの確認用です。<br>
  <strong>決済データは一切送信しません。</strong>テスト用エンドポイントへの GET / HEAD のみです。
</p>

<p>
  接続先: <code><?php echo h($testUrl); ?></code><br>
  実行時刻: <?php echo h(date('Y-m-d H:i:s')); ?><br>
  PHP: <?php echo h(PHP_VERSION); ?>
</p>

<p>
  総合判定:
  <?php if ($allOk): ?>
    <span class="ok">成功（SSL ハンドシェイク完了・新証明書への影響なしと判断可）</span>
  <?php else: ?>
    <span class="ng">失敗（ルート証明書の更新等が必要な可能性）</span>
  <?php endif; ?>
</p>

<?php foreach ($results as $r): ?>
<div class="box">
  <h2><?php echo h($r['method']); ?></h2>
  <p>
    結果:
    <?php if ($r['ok']): ?>
      <span class="ok">OK</span>
    <?php else: ?>
      <span class="ng">NG</span>
    <?php endif; ?>
  </p>
  <p>HTTP: <?php echo $r['http'] !== null ? h($r['http']) : '（なし）'; ?></p>
  <?php if ($r['error']): ?>
    <p class="ng">エラー: <?php echo h($r['error']); ?></p>
  <?php endif; ?>
  <?php if ($r['detail'] !== ''): ?>
    <pre><?php echo h($r['detail']); ?></pre>
  <?php endif; ?>
</div>
<?php endforeach; ?>

<p class="note">
  失敗時の典型例: <code>SSL certificate problem: unable to get local issuer certificate</code><br>
  → サーバー側の ca-certificates / PHP OpenSSL CA ストアの更新を検討してください。<br>
  本番の請求送信（<code>_batch_manageperiod_step3.php</code>）は <code>file_get_contents</code> 経路のため、そちらの結果を特に確認してください。
</p>
</body>
</html>
