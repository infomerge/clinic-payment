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

<h2 class="title_name">契約状況</h2>

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


{*
<a href="kktp.php?account_name={$account_name}&from={$from}&to={$to}&csv=1">CSVダウンロード</a><br /><br />
*}
        <table class="list_body">
        <tr>
        <th width="90">契約金額</th>
        <th width="150">担当者</th>
        
       
        <th width="200">氏名</th>
        <th>住所</th>
        <th width="100">電話番号</th>
        <th width="120">誕生日</th>
        <th width="200">email</th>
        
        
        <th width="140">規約に同意</th>
        <th width="100">同意日</th>
        </tr>
        {foreach from=$data item=item}
        <tr>
        <td>{$item.type}</td>
        <td width="">{$item.account_name}</a></td>
        
        
        <td width="">{$item.sei} {$item.mei}</td>
        <td>{$item.zip} {$item.pref}{$item.city}{$item.addr}</td>
        <td width="">{$item.phone}</td>
        <td width="">{$item.birthday}</td>
        <td width="">
        <div>{$item.email}</div>
       </td>
        <td width="">{$item.consent_check}</td>
        <td width="">{$item.consent_date}</td>
    
        </tr>
        {/foreach}
        </table>



    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
