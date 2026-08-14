<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>成果報酬集計管理ツール</title>
{include file="common/head_inc.tpl"}
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">

    <div id="main">
    	
        <div class="content">
        {*<a href="./syukei.php">RESTY集計</a>*}
    
    	<div id="breadcrumb">
        TOP&nbsp;&gt;&nbsp;個別集計（パートナー）
        </div>
        
        {include file="common/searcharea.tpl"}
    
        <table class="list_top">
        <tr>
        <th>店舗コード</th>
        <th width="100">パスコード</th>
        <th>店舗名</th>
        <th width="100">PPC番号</th>
        <th width="100">パートナー名</th>
        </tr>
        </table>
        
        <table class="list_body">
        {foreach from=$shop item=item}
        <tr>
        <td><a href="report.php?id={$item.id}">{$item.lp_shop_id}</a></td>
        <td width="100">{$item.password}</td>
        <td>{$item.name}</td>
        <td width="100">{$item.ppc_number}</td>
        <td width="100">{$item.account_name}</td>
        </tr>
        {/foreach}
        </table>
    
    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
