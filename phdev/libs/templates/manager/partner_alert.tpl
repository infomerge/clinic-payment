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

<h2 class="title_name">アラート出店者</h2>
{*        
        {include file="common/searcharea.tpl"}
*}
      
        <table class="list_body">
        <tr>
        <th width="150">出店者名</th>
        <th width="50">TC/NS</th>
       <th width="">cyfonsアカウント</th>
       
        <th width="">送信者名</th>
        <th width="">サイト名</th>
        <th width="">作成STEP</th>
        <th width="">稼働STEP</th>
        <th width="">グループ数</th>
        
        </tr>
        {foreach from=$data item=item}
        <tr>
        <td width="150">{$item.client_name}</a></td>
        <td width="">{if $item.account_type eq 1}<span class="icon_tc">TC</span>{elseif $item.account_type eq 2}<span class="icon_ns">NS</span>{/if}</td>
       <td width="">{$item.account_name}</td>
      
       <td>
      {if $check[$item.cyfons_dbname]['firstname'] eq 'システム管理者'}<span style="color:red">{$check[$item.cyfons_dbname]['firstname']}</span>{else}{$check[$item.cyfons_dbname]['firstname']}{/if}
      </td>
      <td>
       {if $check[$item.cyfons_dbname]['site_name'] eq 'サイト名'}<span style="color:red">{$check[$item.cyfons_dbname]['site_name']}</span>{else}{$check[$item.cyfons_dbname]['site_name']}{/if}
       </td>
       <td>
       {if $check[$item.cyfons_dbname]['stepmail_num'] eq 0}
       <span style="color:red">{$check[$item.cyfons_dbname]['stepmail_num']}</span>
       {elseif $check[$item.cyfons_dbname]['stepmail_num'] < 30}
       <span style="color:blue">{$check[$item.cyfons_dbname]['stepmail_num']}</span>
       {else}
       {$check[$item.cyfons_dbname]['stepmail_num']}
       {/if}
       </td>
       <td>
       {if $check[$item.cyfons_dbname]['stepmail_num'] ne $check[$item.cyfons_dbname]['stepmail_oknum'] or $check[$item.cyfons_dbname]['stepmail_oknum'] eq 0}
       <span style="color:red">{$check[$item.cyfons_dbname]['stepmail_oknum']}</span>
       {else}
       {$check[$item.cyfons_dbname]['stepmail_oknum']}
       {/if}
       </td>
       <td>
       {if $check[$item.cyfons_dbname]['groupnum'] ne 3}<span style="color:red">{$check[$item.cyfons_dbname]['groupnum']}</span>{else}{$check[$item.cyfons_dbname]['groupnum']}{/if}
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
