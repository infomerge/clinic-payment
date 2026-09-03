<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>データベースバックアップ</title>
{include file="common/head_inc.tpl"}
<style type="text/css">
.bk-box { border: 2px solid #336699; padding: 16px; margin: 12px 0; background: #f7fbff; }
.bk-warn { border-color: #cc6600; background: #fff8f0; color: #663300; }
.bk-ok { color: #006600; font-weight: bold; }
.bk-err { color: #cc0000; font-weight: bold; }
.bk-btn { padding: 8px 16px; font-size: 14px; cursor: pointer; margin: 4px; }
.bk-btn-danger { background: #cc0000; color: #fff; border: none; padding: 10px 20px; font-size: 14px; cursor: pointer; }
.bk-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
.bk-table th, .bk-table td { border: 1px solid #ccc; padding: 6px 8px; font-size: 13px; }
.bk-code { font-size: 28px; letter-spacing: 6px; font-weight: bold; padding: 12px; background: #eee; display: inline-block; }
</style>
</head>
<body>
{include file="common/header.tpl"}
<div id="wrap">
<div class="content">
<div id="breadcrumb">
  <a href="./">トップページ</a>&nbsp;&gt;&nbsp;データベースバックアップ
</div>

<h2 class="title_name">データベースバックアップ / リストア</h2>
<p>対象DB: <strong>{$dbname|escape}</strong>（構造を除く全データ）</p>

{if $flash_ok}<p class="bk-ok">{$flash_ok|escape}</p>{/if}
{if $flash_err}<p class="bk-err">{$flash_err|escape}</p>{/if}

{if $view == 'confirm' && $confirm_view}
<div class="bk-box bk-warn">
  <h3>リストア確認（取り消しできません）</h3>
  <p>以下のバックアップで DB の全テーブルデータを置き換えます。</p>
  <ul>
    <li>ファイル: <strong>{$confirm_view.filename|escape}</strong></li>
    <li>作成日時: {$confirm_view.mtime|escape}</li>
    <li>DB: {$confirm_view.dbname|escape}</li>
  </ul>
  <p>実行前に「リストア前」バックアップを自動取得します。</p>
  <p>下の確認コードを入力してください:</p>
  <p class="bk-code">{$confirm_view.code|escape}</p>
  <form method="post" action="backup.php" onsubmit="return confirm('本当にリストアしますか？現在のデータは上書きされます。');">
    <input type="hidden" name="action" value="restore_execute" />
    <input type="hidden" name="filename" value="{$confirm_view.filename|escape}" />
    確認コード: <input type="text" name="confirm_code" size="10" autocomplete="off" />
    <br /><br />
    <input type="submit" class="bk-btn-danger" value="リストアを実行する" />
    <a href="backup.php">キャンセル</a>
  </form>
</div>
{else}

<div class="bk-box">
  <h3>バックアップ作成</h3>
  <form method="post" action="backup.php" style="display:inline;">
    <input type="hidden" name="action" value="create" />
    <input type="hidden" name="reason" value="before_receipt" />
    <input type="submit" class="bk-btn" value="レセプト取込前バックアップ" />
  </form>
  <form method="post" action="backup.php" style="display:inline;">
    <input type="hidden" name="action" value="create" />
    <input type="hidden" name="reason" value="before_closing" />
    <input type="submit" class="bk-btn" value="締め前バックアップ" />
  </form>
  <p style="margin-top:10px;font-size:13px;">
    直近・取込前:
    {if $latest_receipt}<span class="bk-ok">{$latest_receipt.mtime|escape}</span> ({$latest_receipt.filename|escape})
    {else}<span class="bk-err">未取得</span>{/if}
    ／ 直近・締め前:
    {if $latest_closing}<span class="bk-ok">{$latest_closing.mtime|escape}</span> ({$latest_closing.filename|escape})
    {else}<span class="bk-err">未取得</span>{/if}
  </p>
</div>

<div class="bk-box">
  <h3>バックアップ一覧</h3>
  <table class="bk-table">
    <tr><th>日時</th><th>理由</th><th>ファイル</th><th>サイズ</th><th>操作</th></tr>
    {foreach from=$backups item=b}
    <tr>
      <td>{$b.mtime|escape}</td>
      <td>{$b.label|escape}</td>
      <td>{$b.filename|escape}</td>
      <td>{$b.size_h|escape}</td>
      <td>
        <form method="post" action="backup.php" style="display:inline;">
          <input type="hidden" name="action" value="download" />
          <input type="hidden" name="filename" value="{$b.filename|escape}" />
          <input type="submit" value="DL" />
        </form>
        <form method="post" action="backup.php" style="display:inline;">
          <input type="hidden" name="action" value="restore_confirm" />
          <input type="hidden" name="filename" value="{$b.filename|escape}" />
          <input type="submit" value="リストア…" />
        </form>
      </td>
    </tr>
    {foreachelse}
    <tr><td colspan="5">バックアップはまだありません</td></tr>
    {/foreach}
  </table>
</div>
{/if}

<p><a href="closing.php">締めダッシュボードへ</a> ｜ <a href="receipt_select.php">レセプト取込へ</a></p>

</div>
</div>
</body>
</html>
