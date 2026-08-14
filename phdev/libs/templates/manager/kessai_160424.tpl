<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>クルテルワン管理ツール</title>
{include file="common/head_inc.tpl"}
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

<h2 class="title_name">IPS決済状況（クレカ・銀振）</h2>

{*        
        {include file="common/searcharea.tpl"}
*}
      
        <table class="list_body">
        <tr>
        <th width="150">出店者名</th>
        <th width="">氏名</th>
        {*<th width="200">email</th>*}
        {*<th>ec</th>*}
        <th width="200">決済方法</th>
        <th width="100">決済job</th>
        <th width="150">決済日時</th>
        <th width="100">決済番号</th>
       
        </tr>
        {foreach from=$data item=item}
        {if ($item.job eq 'CAPTURE' and $item.ec eq 'ER000000000') or $item.job eq 'EBTRANSFER'}
        <tr>
        <td width="">{$item.client_name}</a></td>
        <td width="">{$item.firstname}　{$item.lastname}</td>
        {*<td width="">{$item.email}</td>*}
        {*<td>{$item.ec}</td>*}
        <td width="">{if $item.pay_method eq 'credit_12'}クレジット12ヶ月一括{elseif $item.pay_method eq 'credit'}クレジット月払{elseif $item.pay_method eq 'credit_11'}クレジット11ヶ月一括{elseif $item.pay_method eq 'bank'}銀振{else}{$item.pay_method}{/if} </td>
        <td width="">{if $item.job eq 'EBTRANSFER'}銀振入金完了{elseif $item.job eq 'CAPTURE' and $item.ec eq 'ER000000000'}決済完了{/if}</td>
        <td width="">{$item.regist_date}</td>
        <td width="">{$item.pid}</td>
      
        </tr>
        {/if}
        {/foreach}
        </table>


    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
