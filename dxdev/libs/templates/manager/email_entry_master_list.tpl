<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>クルテルワン管理ツール</title>
{include file="common/head_inc.tpl"}
<style>
{literal}
td{vertical-align:top;}
{/literal}
</style>
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">

    <div id="main">
    
    <div class="content">
    	{*<a href="./syukei.php">RESTY集計</a>*}


        <div id="breadcrumb">
<!--        TOP&nbsp;&gt;&nbsp;店舗情報 -->
        </div>

<h2 class="title_name">導入元キャンペーン一覧</h2>

<a href="email_entry_master.php" class="btn_back" style="width:250px">新規登録</a>
<br />
【注意】下記フォームコードを埋め込むファイルの最上段に、必ず次のコードを挿入してください。
<pre style="font-size:12px;">
&lt;?php
$session_id = md5( uniqid( rand(), true ) );
?&gt;
</pre>


<table class="list_body">
        <tr>
        <th width="120">キャンペーンID</th>
        
        <th width="200">キャンペーン名</th>
        <th width="120">DRM商品ID</th>
        <th width="100">エキスパフォームCD</th>
        <th width="">LP登録完了後リダイレクトURL</th>
        <th width="400">フォームコード</th>
        </tr>
{foreach from=$data item=item key=key}
<tr style="border-bottom:1px solid #333;">
<td>
<a href="email_entry_master.php?id={$item.id}">{$item.account_name}</a>
</td><td>{$item.title}</td><td>{$item.pid}</td><td>{$item.formcd}</td>
<td>
{$item.redirect}
{if $item.redirect2}
{$item.redirect2}
{/if}
</td>
<td>
<pre style="font-size:12px;">
{if $item.redirect2}
&lt;?php
$arr = array("{$item.redirect}","{$item.redirect2}");
shuffle($arr);
$redirect = $arr[0];
?&gt;
{/if}
&lt;form method="post" action="http://krtruang.com/contract/entry_email.php" class="clearfix"&gt;
&lt;input type="hidden" name="f" value="1" /&gt;
&lt;input type="hidden" name="account_name" value="{$item.account_name}" /&gt;
{if $item.redirect2}
&lt;input type="hidden" name="redirect" value="&lt;?php echo $redirect; ?&gt;" /&gt;
{else}
&lt;input type="hidden" name="redirect" value="{$item.redirect}" /&gt;
{/if}
&lt;input type="hidden" name="Publisher_Id" value="{$item.publisher_id}" /&gt;
&lt;input type="hidden" name="Form_Cd" value="{$item.formcd}" /&gt;
&lt;input type="hidden" name="charcode" value="auto" /&gt;
&lt;input type="hidden" name="pid" value="{$item.pid}" /&gt;
&lt;input type="hidden" id="session_id" name="session_id" value="&lt;?php echo $session_id; ?&gt;" /&gt;
&lt;input type="text" name="email" value="" placeholder="メールアドレスを入力してください。" &gt;
&lt;button type="submit" class="fr"&gt;
&lt;div&gt;全て無料で手に入れる!&lt;/div&gt;
&lt;/button&gt;
&lt;/form&gt;
</pre>
</td>
</tr>
{/foreach}
</table>
<br /><br /><br />



    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
