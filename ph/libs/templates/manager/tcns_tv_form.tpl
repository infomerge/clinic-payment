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

<h2 class="title_name">ティーチャーズ／ネットスター 生放送フォーム申し込み状況</h2>

<a href="./?csv=1">CSVダウンロード</a><br /><br />


【絞り込み】{if $type eq ''}全件{else}<a href="./">全件</a>{/if}｜{if $type eq 'tcns'}TC / NS{else}<a href="./?type=tcns">TC / NS</a>{/if}｜{if $type eq 'krtruang'}Krtruang本部{else}<a href="./?type=krtruang">Krtruang本部</a>{/if}<br /><br />
    
        <table class="list_body">
        <tr>
        <th width="150">アカウント名</th>
        <th width="180">出店者名</th>
        <th width="60">TC/NS</th>
        <th width="100">氏名</th>
        <th width="200">email / 決済URL</th>
        <th width="100">電話番号</th>
        <th width="150">決済方法</th>
        <th width="100">カード番号</th>
        <th width="150">セキュリティ番号</th>
        <th width="80">有効期限</th>
        <th width="">名義</th>
        <th width="100">申込日時</th>
        </tr>
        {foreach from=$data item=item}
        <tr>
        <td width="150">{$item.account_name}</a></td>
        <td width="">{if isset($partner[$item.account_name])}{$partner[$item.account_name]}{/if}</td>
        <td>{if $item.account_type eq 1}<span class="icon_tc">TC</span>{elseif $item.account_type eq 2}<span class="icon_ns">NS</span>{/if}</td>
        <td width="100">{$item.sei}　{$item.mei}</td>
        <td width="200">
        <div>{$item.email}</div>
        
        {if $account_name eq 'maro-ka' or $account_name eq 'sakuragi-media' or $account_name eq 'bando-megumi-3moon' or $account_name eq 'base'}
        
        {else}
        
            {if $item.account_name ne ''}
            
            	{if substr($item.account_name, 0,8) eq 'krtruang'}
                <a href="http://teachers.vision/payment/reg.php?cd={$item.cd}&email={$item.email}" target="_blank">決済URL</a>
                {else}
            	<a href="http://{if $item.account_type eq 1}teachers{elseif $item.account_type eq 2}netstars{/if}.vision/payment/reg.php?email={$item.email}&url=http://member.teachers.vision/{$item.account_name}&type={if $item.pay_method eq 'クレジット一括'}cr12{elseif $item.pay_method eq 'クレジット12分割'}credit{elseif $item.pay_method eq '銀行振込一括'}bank{/if}" target="_blank">決済URL</a>
            	{/if}
            
         	{/if}
        
        
        {/if}
        
        </td>
        <td width="">{$item.phone}</td>
        <td width="">{$item.pay_method}</td>
        <td width="">{$item.cardno}</td>
        <td width="">{$item.security}</td>
        <td width="">{$item.expire}</td>
        <td width="">{$item.meigi}</td>
        <td width="">{$item.regist_date}</td>
        </tr>
        {/foreach}
        </table>


    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
