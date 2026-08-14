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

<h2 class="title_name">出店者別登録ユーザー</h2>


{foreach from=$data key=key item=item2}

{if $key ne 'krtruang'}

<h2 class="title_name">{if $partner[$key]['account_type'] eq 1}<span class="icon_tc">TC</span>{elseif $partner[$key]['account_type'] eq 2}<span class="icon_ns">NS</span>{/if}　{$partner[$key]['client_name']}　無料体験：{$partner[$key]['g1']}　正会員：{$partner[$key]['g2']}</h2>

        <table class="list_body">
        <tr>
       
       
        <th width="200">氏名</th>
        <th width="300">Email</th>
        <th width="">ステータス</th>
        <th width="">登録日時</th>
        
        </tr>
        {foreach from=$item2 item=item}
        <tr>
        
      
       <td>
      {$item.firstname} {$item.lastname}
      </td>
      <td>
      {$item.email}
       </td>
       <td>
       {if $item.group_id eq '1'}無料体験入学{elseif $item.group_id eq '2'}正会員{elseif $item.group_id eq '3'}生放送{/if}
       </td>
       <td>
      {$item.created}
       </td>
      
        </tr>
        {/foreach}
        </table>
<br /><br />

{/if}

{/foreach}

<br /><br /><br />
    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
