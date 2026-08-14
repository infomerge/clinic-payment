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
        
        <form>
        指定年度；
        <select name="dt">
        <option value="">選択</option>
        {foreach from=$term item=item key=key}
        <option value="{$key}"{if $key eq $dt} selected="selected"{/if}>{$item}</option>
        {/foreach}
        </select>
        <input type="submit" value="検索" />
        </form>
        
        <table>
        {foreach from=$shop item=item}
        <tr>
        <td><a href="syukei2.php?id={$item.id}&dt={$dt}">{$item.lp_shop_id}</a></td>
        <td>{$item.name}</td>
        </tr>
        {/foreach}
        </table>

    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
