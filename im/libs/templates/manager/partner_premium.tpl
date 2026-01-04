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

<h2 class="title_name">{if $partner[$key]['account_type'] eq 1}<span class="icon_tc">TC</span>{elseif $partner[$key]['account_type'] eq 2}<span class="icon_ns">NS</span>{/if}　{$partner[$key]['client_name']}　正会員：{$partner[$key]['g2']}</h2>

        <table class="list_body">
        <tr>
       
       
        <th width="150">氏名</th>
        <th width="300">Email</th>
        <th width="120">ステータス</th>
        <th width="200">支払い方法</th>
        <th>月々払い決済ログ</th>
        <th width="">申込日時</th>
        <th width="">決済日</th>
        <th width="200">メモ</th>
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
       <td>{$payment_master[$user_master[$key][$item.id]['pay_method']]}</td>
       <td>
       {if $user_master[$key][$item.id]['pay_method'] eq 'credit'}
       {foreach from = $item.payment_log item=item3 name=log}
       <div>{$smarty.foreach.log.iteration}回目：決済承認番号 {$item3.ap}　決済日時 {$item3.regist_date}</div>
       {/foreach}
       
       {*print_r($item.payment_log)*}
       {/if}
       
       
       </td>
       <td>
      {$item.created}
       </td>
      <td>
      {if $user_master[$key][$item.id]['pay_method'] eq 'credit'}
      <div style="font-size:16px;">{$item.created|date_format:"%d"}日</div>
      {/if}
      </td>
      <td>
      {if $user_master[$key][$item.id]['pay_method'] eq 'credit'}
     
      <form method="post">
      <input type="hidden" name="pay_id" value="{$item.pay_id}" />
      <textarea name="memo" style="font-size:12px;width:200px;">{$item.memo}</textarea>
      <div style="margin-bottom:5px"></div>
      <input type="submit" value="送信" />
      </form>
      {/if}
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
