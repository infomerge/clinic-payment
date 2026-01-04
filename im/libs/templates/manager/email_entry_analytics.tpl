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

<h2 class="title_name">キャンペーン解析</h2>

<a href="email_entry_master_list.php" class="btn_back" style="width:250px">導入元キャンペーン管理</a>
<br /><br />

「導入元キャンペーン」を選択し「検索」ボタンをクリックすると一覧が表示されます。
		<div class="searcharea">
        <form method="get">
        <table class="searcharea_tbl">
        <tr>
        <td><b>導入元キャンペーン</b>　
        <select name="account_name">
        
        {foreach from=$m_account_name item=item key=key}
        <option value="{$key}"{if isset($smarty.get.account_name) and $smarty.get.account_name eq $key} selected="selected"{/if}>{$item}</option>
        {/foreach}
        </select>
        </td>
        <td>　　　</td>
        {*
        <td><b>対象期間</b>　<input type="text" name="from" value="{$from}" placeholder="2016-05-10" style="width:120px;" />&nbsp;〜&nbsp;<input type="text" name="to" value="{$to}" placeholder="2016-05-30" style="width:120px;" />
        <td>　　　</td>
        *}
        <td>
        <input type="submit" value="検索" class="btn_search" />
        </td>
        <td>　　　　</td>
        <td>
        {*
        <a href="{$current_url}" class="btn_showall">全件表示</a>
        *}
        </td>
        </tr>
        </table>
        </form>
        </div>


{*
<a href="email_entry.php?account_name={$account_name}&from={$from}&to={$to}&csv=1">CSVダウンロード</a><br /><br />
*}

<a href="email_entry_analytics.php?account_name={$smarty.get.account_name}">アクセス数／CV数</a>｜<a href="email_entry_analytics.php?account_name={$smarty.get.account_name}&type=step">ステップ視聴数</a><br /><br />


{if !isset($smarty.get.type)}
<table class="list_body" style="width:500px;">
        <tr>
        <th width="150">総アクセス数</th>
        
        <th width="">総CV数</th>
        <th></th>
        </tr>
        <tr>
<td>{$an.total}</td><td>{$an.cv}</td><td>{$an.cv/$an.total*100}%</td>
</tr>
</table>
<br />
<br />

日毎

<table class="list_body" style="width:500px;">
        <tr>
        <th width="150">日付</th>
        
        <th width="200">PV数</th>
        <th width="120">CV数</th>
        </tr>
{foreach from=$an.daily item=item key=key}
<tr>
<td>
{$key}
</td><td>{$item.pv}</td><td>{$item.cv}</td>
</tr>
{/foreach}
</table>
<br /><br /><br />

アフィリエイター毎

<table class="list_body" style="width:500px;">
        <tr>
        <th width="150">アフィリエイターID</th>
        
        <th width="200">PV数</th>
        <th width="120">CV数</th>
        </tr>
{foreach from=$an.detail item=item key=key}
<tr>
<td>
{if $key ne 9999999}
{if isset($m_drm[$key])}{$m_drm[$key]['sei']}{$m_drm[$key]['mei']}{else}{$item.affiliate_id}{/if}
{else}
（直流入）
{/if}
</td><td>{$item.pv}</td><td>{$item.cv}</td>
</tr>
{/foreach}
</table>
<br /><br /><br />

{*
{foreach from=$data item=item key=key}
{print_r($item)}
{/foreach}
*}

{*print_r($data)*}

<table class="list_body" style="width:500px;">
        <tr>
        <th width="150">アフィリエイターID</th>
        
        <th width="">登録メアド</th>
        </tr>
{foreach from=$data2 item=item key=key}
<tr style="border-bottom:1px solid #333;">
<td valign="top">
{if isset($m_drm[$key])}{$m_drm[$key]['sei']}{$m_drm[$key]['mei']}{else}{$key}{/if}
</td>
<td>
{foreach from=$item item=email}
{$email}<br />
{/foreach}
</td>
</tr>
{/foreach}
</table>
{/if}

<br /><br /><br />

{if isset($smarty.get.type) and $smarty.get.type eq 'step'}
ステップ視聴解析<br />
<table class="list_body">
        <tr>
        <th width="120">ストーリーNO</th>
        <th width="80">視聴数</th>
        <th width="">URL</th>
        <th>アフィリエイトID｜閲覧メアド</th>
        </tr>
{foreach from=$data3 item=item key=key}
<tr style="border-bottom:1px solid #333;">
<td align="center">{$key}</td>
<td align="center">{$item.email|@count}</td>
<td>{$item.filename}</td>
<td>
{foreach from=$item.email item=email}
{if isset($m_drm[$email.affiliate_id])}{$m_drm[$email.affiliate_id]['sei']}{$m_drm[$email.affiliate_id]['mei']}{else}{$email.affiliate_id}{/if}｜{$email.email}<br />
{/foreach}
</td>
</tr>
{/foreach}
</table>
{/if}

{*
{foreach from=$data item=item key=key}
アフィリエイトID：{$item.affiliate_id}　{$item.detail.email}<br />
{/foreach}
*}

<br /><br /><br /><br /><br />

{*
        <table class="list_body">
        <tr>
        <th width="150">導入元キャンペーン</th>
        
        <th width="200">email</th>
        <th width="120">申込日時</th>
        </tr>
        {foreach from=$data item=item}
        <tr>
        <td width="150">{$m_account_name[$item.account_name]}</a></td>
        
        <td width="200">
        <div>{$item.email}</div>
        </td>
        
    <td>{$item.regist_date|date_format:"%Y/%m/%d"}</td>
        </tr>
        {/foreach}
        </table>
*}

    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
