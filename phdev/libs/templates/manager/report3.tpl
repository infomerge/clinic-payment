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
            <a href="./syukei.php">RESTY集計</a>
            <br /><br />
            
            <form method="get">
            <input type="hidden" name="id" value="{$smarty.get.id}" />
            指定年度；
            <select name="dt">
            <option value="">選択</option>
            {foreach from=$term item=item key=key}
            <option value="{$key}"{if $key eq $dt} selected="selected"{/if}>{$item}</option>
            {/foreach}
            </select>
            <input type="submit" value="検索" />
            </form>
            
            <h2 class="title_name">{$shop.name}</h2>
            
            <table class="list_top">
            <tr>
            <th width="80">予約ID</th>
            <th width="100">予約日</th>
            <th width="70">予約時間</th>
            <th width="120">予約ステータス</th>
            <th width="80">予約人数</th>
            <th width="120">予約人数(子供)</th>
            <th width="100">店舗ID</th>
            <th>店舗名</th>
            <th width="70">媒体社名</th>
            </tr>
            </table>
            
           <table class="list_body">
            {foreach from=$resty_data item=item}
            <tr>
            <td width="80">{$item.rr_id}</td>
            <td width="100">{$item.rr_date}</td>
            <td width="70">{$item.rr_time}</td>
            <td width="120">{$item.rr_status}</td>
            <td width="80">{$item.rr_number_people}</td>
            <td width="120">{$item.rr_number_children}</td>
            <td width="100">{$item.shop_id}</td>
            <td>{$shop.name}</td>
            <td width="70">{$shop.account_name}</td>
            </tr>
            {/foreach}
            </table>
            <br /><br /><br />
            
            <div align="right">
            <a href="report2.php?id={$smarty.get.id}&dt={$smarty.get.dt}" class="btn_back">戻る</a>
            </div>
            
            <br /><br />
            
    	</div><!-- content -->

    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
