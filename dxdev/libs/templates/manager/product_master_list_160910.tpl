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

<h2 class="title_name">商品マスタ一覧</h2>

<a href="product_master.php">新規登録</a><br /><br />

{*
「導入元キャンペーン」を選択し「検索」ボタンをクリックすると一覧が表示されます。
		<div class="searcharea">
        <form method="get">
        <table class="searcharea_tbl">
        <tr>
        <td><b>導入元キャンペーン</b>　
        <select name="account_name">
        
        {foreach from=$master_account_name item=item}
        <option value="{$item}"{if isset($smarty.get.account_name) and $smarty.get.account_name eq $item} selected="selected"{/if}>{$item}</option>
        {/foreach}
        </select>
        </td>
        <td>　　　</td>
        <td><b>対象期間</b>　<input type="text" name="from" value="{$from}" placeholder="2016-05-10" style="width:120px;" />&nbsp;〜&nbsp;<input type="text" name="to" value="{$to}" placeholder="2016-05-30" style="width:120px;" />
        <td>　　　</td>
        <td>
        <input type="submit" value="検索" class="btn_search" />
        </td>
        <td>　　　　</td>
        <td>
        
        </td>
        </tr>
        </table>
        </form>
        </div>
*}


        <table class="list_body">
        <tr>
        <th width="300">商品名</th>
        
       
        <th width="200">対象パートナー</th>
        <th width="100">提供期間</th>
        <th width="500">一括払い</th>
        <th width="">月々払い</th>
        
        </tr>
        {foreach from=$data item=item}
        <tr>
        <td width="150"><a href="product_master.php?product_id={$item.product_id}">{$item.product_name}</a></td>
        
        
        <td width="100">{$m_partner[$item.client_id]}</td>
        <td>{$item.term}</td>
        <td width="200">
        {if $item.bulk_flag eq 1}[一括払い有効]{/if}　
        {if $item.bulk_credit_flag eq 1}[クレカ有効]{/if}　
        {if $item.bulk_bank_flag eq 1}[銀振有効]{/if}
        <div>金額：{$item.bulk_price}円</div>
        <div>クレカ決済URL：{$item.bulk_credit_url}</div>
       <div>銀振決済URL：{$item.bulk_bank_url}</div>
        
        </td>
        <td width="">
        {if $item.monthly_flag eq 1}[月々払い有効]{/if}
        <div>銀振決済URL：{$item.bulk_bank_url}</div>
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
