<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>締めダッシュボード</title>
{include file="common/head_inc.tpl"}
<style type="text/css">
.cl-box { border: 1px solid #336699; padding: 14px; margin: 14px 0; background: #f9fcff; }
.cl-box h3 { margin-top: 0; color: #234; }
.cl-ok { color: #006600; font-weight: bold; }
.cl-err { color: #cc0000; font-weight: bold; }
.cl-warn { background: #fff8e6; border-color: #cc9900; }
.cl-btn { padding: 8px 14px; margin: 4px 4px 4px 0; font-size: 13px; cursor: pointer; }
.cl-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.cl-btn-primary { background: #336699; color: #fff; border: none; }
.cl-btn-danger { background: #aa3333; color: #fff; border: none; }
.cl-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.cl-table th, .cl-table td { border: 1px solid #ccc; padding: 5px 8px; }
.cl-table th { background: #eef3f8; }
.cl-pre { background: #111; color: #d4d4d4; padding: 10px; overflow: auto; max-height: 320px; font-size: 12px; white-space: pre-wrap; }
.cl-status { display: inline-block; padding: 2px 8px; background: #dde8f5; border-radius: 3px; }
</style>
</head>
<body>
{include file="common/header.tpl"}
<div id="wrap">
<div class="content">
<div id="breadcrumb">
  <a href="./">トップページ</a>&nbsp;&gt;&nbsp;締めダッシュボード
</div>

<h2 class="title_name">締めダッシュボード</h2>
<p>DB: <strong>{$dbname|escape}</strong>
  ｜ <a href="backup.php">バックアップ管理</a>
  ｜ <a href="generate.php">請求書・領収書</a>
</p>

{if $flash_ok}<p class="cl-ok">{$flash_ok|escape}</p>{/if}
{if $flash_err}<p class="cl-err">{$flash_err|escape}</p>{/if}

<div class="cl-box{if !$latest_closing_bk} cl-warn{/if}">
  <h3>締め前バックアップ</h3>
  {if $latest_closing_bk}
    <p class="cl-ok">直近: {$latest_closing_bk.mtime|escape} （{$latest_closing_bk.filename|escape}）</p>
  {else}
    <p class="cl-err">締め前バックアップがありません。当月締めの前に必ず取得してください。</p>
  {/if}
  <form method="post" action="backup.php">
    <input type="hidden" name="action" value="create" />
    <input type="hidden" name="reason" value="before_closing" />
    <input type="hidden" name="return" value="closing" />
    <input type="submit" class="cl-btn cl-btn-primary" value="締め前バックアップを取得" />
  </form>
</div>

<div class="cl-box">
  <h3>現在の manageperiod</h3>
  {if $active}
    <p>
      対象年月: <strong>{$active.targetym|escape}</strong>
      ／ status: <span class="cl-status">{$active.status|escape}{if $active_label}（{$active_label|escape}）{/if}</span>
    </p>
  {else}
    <p>進行中の締めはありません（すべて status=9 または未登録）。</p>
  {/if}
  <table class="cl-table">
    <tr><th>ID</th><th>対象年月</th><th>status</th><th>意味</th><th>登録日時</th></tr>
    {foreach from=$periods item=p}
    <tr>
      <td>{$p.id|escape}</td>
      <td>{$p.targetym|escape}</td>
      <td>{$p.status|escape}</td>
      <td>{$p.status_label|escape}</td>
      <td>{$p.regist_date_display|escape}</td>
    </tr>
    {/foreach}
  </table>
</div>

<div class="cl-box">
  <h3>Phase A — 前月残処理（ロボペイ結果後）</h3>
  <p style="font-size:12px;color:#555;">順序: A1 → A2 → A3 → A4。step6 は廃止（UI非表示）。</p>
  <form method="post" style="display:inline;">
    <input type="hidden" name="action" value="a1_step4" />
    <input type="submit" class="cl-btn" value="A1 結果待ちへ（step4）" {if !$enabled['a1_step4']}disabled="disabled"{/if} />
  </form>
  <form method="post" style="display:inline;">
    <input type="hidden" name="action" value="a2_step5" />
    <input type="submit" class="cl-btn" value="A2 口振以外を完了（step5）" {if !$enabled['a2_step5']}disabled="disabled"{/if} />
  </form>
  <form method="post" style="display:inline;">
    <input type="hidden" name="action" value="a3_step7" />
    <input type="submit" class="cl-btn" value="A3 月次クローズ（step7）" {if !$enabled['a3_step7']}disabled="disabled"{/if}
      onclick="return confirm('月次クローズ（status→9）を実行します。よろしいですか？');" />
  </form>
  <form method="post" style="display:inline;">
    <input type="hidden" name="action" value="a4_step8" />
    <input type="submit" class="cl-btn" value="A4 振替失敗の繰越（step8）" {if !$enabled['a4_step8']}disabled="disabled"{/if}
      onclick="return confirm('繰越処理を実行します。よろしいですか？');" />
  </form>
</div>

<div class="cl-box">
  <h3>Phase B — 当月締め</h3>
  <p style="font-size:12px;color:#555;">順序: バックアップ → B0 → B1 → B2確認→確定 → B3確認→送信。step3 と step4 は分離（送信後に A1 で結果待ちへ）。</p>
  <form method="post" style="display:inline;">
    <input type="hidden" name="action" value="b0_setperiod" />
    <input type="submit" class="cl-btn cl-btn-primary" value="B0 締め開始（setperiod）" {if !$enabled['b0_setperiod']}disabled="disabled"{/if}
      onclick="return confirm('当月の締めを開始します。よろしいですか？');" />
  </form>
  <form method="post" style="display:inline;">
    <input type="hidden" name="action" value="b1_step1" />
    <input type="submit" class="cl-btn" value="B1 対象割当（step1）" {if !$enabled['b1_step1']}disabled="disabled"{/if} />
  </form>
  <form method="post" style="display:inline;">
    <input type="hidden" name="action" value="b2_preview" />
    <input type="submit" class="cl-btn" value="B2 請求プレビュー" {if !$enabled['b2_preview']}disabled="disabled"{/if} />
  </form>
  <form method="post" style="display:inline;">
    <input type="hidden" name="action" value="b2_commit" />
    <input type="submit" class="cl-btn cl-btn-primary" value="B2 請求データ確定（step2）" {if !$enabled['b2_commit']}disabled="disabled"{/if}
      onclick="return confirm('acc_result を作成します。プレビュー内容を確認済みですか？');" />
  </form>
  <form method="post" style="display:inline;">
    <input type="hidden" name="action" value="b3_preview" />
    <input type="submit" class="cl-btn" value="B3 送信対象確認" {if !$enabled['b3_preview']}disabled="disabled"{/if} />
  </form>
  <form method="post" style="display:inline;">
    <input type="hidden" name="action" value="b3_send" />
    <input type="submit" class="cl-btn cl-btn-danger" value="B3 ロボペイ送信（step3）" {if !$enabled['b3_send']}disabled="disabled"{/if}
      onclick="return confirm('ロボペイへ請求を送信します。件数・金額を確認済みですか？この操作は外部APIを呼び出します。');" />
  </form>
</div>

{if $step2_preview && $step2_preview.ok}
<div class="cl-box">
  <h3>B2 プレビュー — {$step2_preview.targetym|escape}（{$step2_preview.count}件 / 合計 {$step2_preview.total|number_format} 円）</h3>
  <table class="cl-table">
    <tr><th>患者ID</th><th>氏名</th><th>金額</th><th>口振以外</th></tr>
    {foreach from=$step2_preview.list item=r}
    <tr>
      <td>{$r.original_pid|escape}</td>
      <td>{$r.name|escape}</td>
      <td style="text-align:right;">{$r.amount|number_format}</td>
      <td>{$r.direct_debit|escape}</td>
    </tr>
    {/foreach}
  </table>
</div>
{/if}

{if $step3_preview && $step3_preview.ok}
<div class="cl-box">
  <h3>B3 送信対象 — {$step3_preview.count}件 / 合計 {$step3_preview.total|number_format} 円 ／ 振替日 {$step3_preview.transfer_date|escape}</h3>
  <table class="cl-table">
    <tr><th>rid</th><th>患者ID</th><th>氏名</th><th>金額</th><th>cod</th><th>targetym</th></tr>
    {foreach from=$step3_preview.list item=r}
    <tr>
      <td>{$r.rid|escape}</td>
      <td>{$r.original_pid|escape}</td>
      <td>{$r.patient_name|escape}</td>
      <td style="text-align:right;">{$r.am|number_format}</td>
      <td>{$r.cod|escape}</td>
      <td>{$r.targetym|escape}</td>
    </tr>
    {/foreach}
  </table>
</div>
{/if}

{if $result_detail}
<div class="cl-box">
  <h3>実行ログ</h3>
  <pre class="cl-pre">{$result_detail|escape}</pre>
</div>
{/if}

<div class="cl-box">
  <h3>最近の closing_log</h3>
  <table class="cl-table">
    <tr><th>日時</th><th>action</th><th>年月</th><th>操作者</th><th>メッセージ</th></tr>
    {foreach from=$logs item=l}
    <tr>
      <td>{$l.created_at|escape}</td>
      <td>{$l.action|escape}</td>
      <td>{$l.targetym|escape}</td>
      <td>{$l.operator|escape}</td>
      <td>{$l.message|escape}</td>
    </tr>
    {foreachelse}
    <tr><td colspan="5">ログなし</td></tr>
    {/foreach}
  </table>
</div>

</div>
</div>
</body>
</html>
