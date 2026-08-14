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
        
        店舗名：{$shop.name}<br /><br />
        
        <table>
        {foreach from=$resty_data item=item}
        <tr>
        {foreach from=$item item=item2 key=key}
        <td>{$item2}</td>
        {/foreach}
        <td>
        
        </td>
        </tr>
        {/foreach}
        </table>

    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
