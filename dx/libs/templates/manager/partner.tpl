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

<h2 class="title_name">出店者・各種URL</h2>
{*        
        {include file="common/searcharea.tpl"}
*}
      
        <table class="list_body">
        <tr>
        <th width="150">出店者名</th>
        <th width="50">TC/NS</th>
       <th width="">cyfonsアカウント</th>
       {*
        <th width="">WPパスワード</th>
        <th width="">サイフォンスパスワード</th>
        <th width="">サイフォンス設置状況</th>
        *}
        <th width="">各種URL</th>
        
        </tr>
        {foreach from=$data item=item}
        <tr>
        <td width="150">{$item.client_name}</a></td>
        <td width="">{if $item.account_type eq 1}<span class="icon_tc">TC</span>{elseif $item.account_type eq 2}<span class="icon_ns">NS</span>{/if}</td>
       <td width="">{$item.account_name}</td>
       {*
        <td width="">{$item.wp_password}</td>
        <td width="">{$item.cyfons_password}</td>
        <td width="">{if $item.cyfons_flag eq 1}設置済{/if}</td>
        *}
       <td>
      <div> 生放送申込フォーム：
      <a href="http://{if $item.account_type eq 1}teachers{else}netstars{/if}.vision/contract/applicationform.php?an={$item.account_name}" target="_blank">http://{if $item.account_type eq 1}teachers{else}netstars{/if}.vision/contract/applicationform.php?an={$item.account_name}</a></div>
       <div>無料体験入学 会員登録フォーム：
       {if $item.account_name eq 'maro-ka' or $item.account_name eq 'bando-megumi-3moon' or $item.account_name eq 'sakuragi-media' or $item.account_name eq 'base'}
       <a href="http://{$item.account_name}.teachers.vision/formadd/?group_id=1" target="_blank">http://{$item.account_name}.teachers.vision/formadd/?group_id=1</a>
       {else}
       <a href="http://member.{if $item.account_type eq 1}teachers{else}netstars{/if}.vision/{$item.account_name}/formadd/?group_id=1" target="_blank">http://member.{if $item.account_type eq 1}teachers{else}netstars{/if}.vision/{$item.account_name}/formadd/?group_id=1</a>
       {/if}</div>
       <div>正会員登録フォーム：
       {if $item.account_name eq 'maro-ka' or $item.account_name eq 'bando-megumi-3moon' or $item.account_name eq 'sakuragi-media' or $item.account_name eq 'base'}
       <a href="http://{$item.account_name}.teachers.vision/formadd/?group_id=2" target="_blank">http://{$item.account_name}.teachers.vision/formadd/?group_id=2</a>
       {else}
       <a href="http://member.{if $item.account_type eq 1}teachers{else}netstars{/if}.vision/{$item.account_name}/formadd/?group_id=2" target="_blank">http://member.{if $item.account_type eq 1}teachers{else}netstars{/if}.vision/{$item.account_name}/formadd/?group_id=2</a>
       {/if}</div>
       <div>
       無料体験LPショートコード：
       {if $item.account_name eq 'maro-ka' or $item.account_name eq 'bando-megumi-3moon' or $item.account_name eq 'sakuragi-media' or $item.account_name eq 'base'}
       [setLPform action="http://{$item.account_name}.teachers.vision/formadd/?group_id=1" label="{$item.account_name}"]
       {else}
       [setLPform action="http://member.teachers.vision/{$item.account_name}/formadd/?group_id=1" label="{$item.account_name}"]
       {/if}
       </div>
       
       </td>
        </tr>
        {/foreach}
        </table>


    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
