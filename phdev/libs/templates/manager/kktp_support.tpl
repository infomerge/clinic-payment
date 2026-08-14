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

<h2 class="title_name">KKTP直接サポート申し込み状況</h2>

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
        {*
        <a href="{$current_url}" class="btn_showall">全件表示</a>
        *}
        </td>
        </tr>
        </table>
        </form>
        </div>


{if $account_name ne ''}
<a href="kktp_support.php?account_name={$account_name}&from={$from}&to={$to}&csv=1">CSVダウンロード</a><br /><br />

        <table class="list_body">
        <tr>
        <th width="150">導入元キャンペーン</th>
        
       
        <th width="200">氏名</th>
        <th width="200">email</th>
        <th width="100">携帯番号</th>
        <th width="120">都道府県</th>
        <th width="">意気込み/回答</th>
       <th width="120">LINE ID</th>
        <th width="120">申込日時</th>
        </tr>
        {foreach from=$data item=item}
        <tr>
        <td width="150">{$item.account_name}</a></td>
        
        
        <td width="100">{$item.name}</td>
        <td width="200">
        <div>{$item.email}</div>
        
       
        
        </td>
        <td width="">{$item.phone}</td>
        <td width="">{$item.pref}</td>
        <td width="">{$item.msg}</td>
  		<td width="">{$item.lineid}</td>
    <td>{$item.regist_date|date_format:"%Y/%m/%d"}</td>
        </tr>
        {/foreach}
        </table>

{/if}

    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
