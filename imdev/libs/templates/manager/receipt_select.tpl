<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>レセプトデータの取り込み</title>
{include file="common/head_inc.tpl"}
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">


    <div class="content">
    	{*<a href="./syukei.php">RESTY集計</a>*}


        <div id="breadcrumb">
        <a href="./">トップページ</a>&nbsp;&gt;&nbsp;レセプトデータ取り込み

        </div>

<div style="border:2px solid #336699;padding:14px;margin:12px 0;background:#f7fbff;">
  <h2 class="title_name" style="margin-top:0;">レセプト取込前バックアップ</h2>
  <p style="font-size:13px;">取込前の状態に戻せるよう、アップロードの<strong>前に</strong>バックアップを取得してください。</p>
  {if $latest_receipt_bk}
    <p style="color:#006600;font-weight:bold;">直近バックアップ: {$latest_receipt_bk.mtime|escape}（{$latest_receipt_bk.filename|escape}）</p>
  {else}
    <p style="color:#cc0000;font-weight:bold;">取込前バックアップがまだありません</p>
  {/if}
  <form method="post" action="backup.php" style="display:inline;">
    <input type="hidden" name="action" value="create" />
    <input type="hidden" name="reason" value="before_receipt" />
    <input type="hidden" name="return" value="receipt" />
    <input type="submit" value="レセプト取込前バックアップを取得" style="padding:8px 16px;font-size:14px;" />
  </form>
  <a href="backup.php" style="margin-left:12px;">バックアップ一覧・リストア</a>
</div>

{if isset($smarty.get.backup) && $smarty.get.backup == 'ok'}
<p style="color:#006600;font-weight:bold;">バックアップを作成しました。</p>
{/if}
{if isset($smarty.get.backup) && $smarty.get.backup == 'ng'}
<p style="color:#cc0000;font-weight:bold;">バックアップ作成に失敗しました。<a href="backup.php">詳細</a></p>
{/if}


<h2 class="title_name">【内科】レセプトデータの取り込み</h2>



    <form method="post" action="recept-upload-im.php" enctype="multipart/form-data">

<div align="center">
        <br />

        <input type="file" name="upfile" size="30" />

        <br /><br />

        <input type="submit" value="アップロード" />
</div>

    </form>

<br /><br />

    <h2 class="title_name">【内科】介護保険レセプトデータの取り込み</h2>

    <form method="post" action="kaigo-recept-upload-003.php" enctype="multipart/form-data">

<div align="center">
        <br />

        <input type="file" name="upfile" size="30" />
        <br />

        <br />
        <input type="submit" value="アップロード" />
</div>
    </form>


{if isset($smarty.get.testview)}
<br><br>
<h2 class="title_name">【ロボペイの結果をロボペイフォーマットCSVファイルから取り込み処理】</h2>

<form method="post" action="csv-upload.php" enctype="multipart/form-data">

<div align="center">
    <br />

    <input type="file" name="upfile" size="30" />

    <br /><br />

    <input type="submit" value="アップロード" />
</div>

</form>
{/if}

    </div><!-- content -->


{* include file="common/sidebar.tpl" *}

</div>
</body>
</html>
