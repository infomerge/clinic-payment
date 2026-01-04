<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Charisma マイページ</title>
{include file="common/head_inc.tpl"}
<style>
{literal}
.tbldetail2 td{padding:3px;}
{/literal}
</style>
</head>

<body style="background:#EEE;">
{include file="common/header.tpl"}

<div id="wrap">

    <div id="main">
    
    <div class="content">
    	


        <div id="breadcrumb">
<!--        TOP&nbsp;&gt;&nbsp;店舗情報 -->
        </div>
<form method="post">

<h2 class="title_name">
<div style="float:right">
<input type="submit" name="refund_regist" value="保存する" class="btn_new" style="width:100px" />
</div>
{$data.sei}{if isset($data.biz_name)}（ビジネスネーム：{$data.biz_name}）{/if}さんの自己PR</h2>

<div class="tbldetail_wrap">
<table style="width:100%">
<tr>
<td width="590" valign="top">

		<table class="tbldetail">
        <tr>
        <td>ユーザーID</td>
        <td>{$data.client_id}</td>
        <tr>
        <td width="140">ビジネスネーム</td>
<td width="">{$data.biz_name}</td>
</tr>
<tr>
        <td>電話番号</td>
<td width="">{$data.phone}</td>
</tr>
<tr>
<td>LINE ID</td>
<td>{$data.lineid}</td>
</tr>
</table>
<br />


<table class="tbldetail">
<tr>
<td>Facebook URL<br />
{$data.facebook_url}</td>
</tr>
<tr>
<td>Twitter URL<br />
{$data.twitter_url}</td>
</tr>
<tr>
<td>Instagram URL<br />
{$data.instagram_url}</td>
</tr>
<tr>
<td>ブログ・HP URL<br />
{$data.blog_url}</td>
</tr>
<tr>
<td>その他自己PR資料1 URL<br />
{$data.etc1}</td>
</tr>
<tr>
<td>その他自己PR資料2 URL<br />
{$data.etc2}</td>
</tr>
<tr>
<td>その他自己PR資料3 URL<br />
{$data.etc3}</td>
</tr>
</table>

<table class="tbldetail">


</table>

</td>
<td valign="top">

<table class="tbldetail">
<tr>
<td><strong>自己PR</strong></td>
</tr>
<tr>
<td>

{$data.summary|nl2br}
</td>
</tr>
</table>


</td>
</tr>
</table>


</div>
</form>

    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
