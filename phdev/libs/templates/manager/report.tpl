<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>成果報酬集計管理ツール</title>
{include file="common/head_inc.tpl"}
<style>
{literal}
td{border:1px solid #CCC;padding:7px;}
{/literal}
</style>
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">
    <div id="main">
		<div class="content">
        	{* <a href="./syukei.php">RESTY集計</a>*}
            <div id="breadcrumb">
            TOP&nbsp;&gt;&nbsp;<a href="resty_list.php">個別集計（パートナー）</a>&nbsp;&gt;&nbsp;月別
            </div>
            
            
            <h2 class="title_name">{$shop.name}</h2>
            
            {if $next eq 3}
            <a href="/pdf/?id={$shop.id}&dt={$dt}">支払通知書</a>
            {/if}
            
            <table class="list_top">
            <tr>
            <th width="20%">年月</th><th width="20%">総予約数</th><th width="20%">有効予約数</th><th>Resty料金</th>
            </tr>
            </table>
            
            <table class="list_body">
            {foreach from=$data item=item key=key}
            <tr>
            <td width="20%"><a href="report{$next}.php?id={$shop.id}&dt={$key}">{$key}</a></td>
            <td width="20%">{$item.all}</td>
            <td width="20%">{if isset($item.valid)}{$item.valid}{else}0{/if}</td>
            <td>{if isset($item.price)}{$item.price}{else}0{/if}</td>
            </tr>
            {/foreach}
            </table>
    
            <br /><br /><br />
            
            <div align="right">
            
            {if $next eq 3}
            <a href="report.php?id={$smarty.get.id}&dt={$smarty.get.dt}" class="btn_back">戻る</a>
            {else}
            <a href="resty_list.php" class="btn_back">戻る</a>
            {/if}
            </div>
            
            <br /><br />
            
            </div><!-- content -->

    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
