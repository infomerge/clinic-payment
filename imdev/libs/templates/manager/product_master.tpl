<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>アカウント情報登録</title>
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

<h2 class="title_name">アカウント情報登録</h2>


    <form method="post">
        <input type="hidden" name="product_id" value="{$data.product_id}" />
        
        <div class="tbldetail_wrap">
        <table class="tbldetail">
            
        <tr>
        <th>医療機関名</th>
        <td class="bg_odd">
        
        <input type="text" name="product_code" value="{$data.product_code}" class="frmtxt" style="width:300px;" />
        </td>
        </tr>
        <tr>
        <th>ログインID</th>
        <td class="bg_odd">
        
        <input type="text" name="product_name" value="{$data.product_name}" class="frmtxt" style="width:300px;" />

        </td>
        </tr>
        <tr>
        <th>パスワード</th>
        <td class="bg_odd">
        
        <input type="text" name="partner_name" value="{$data.partner_name}" class="frmtxt" style="width:300px;" />

        </td>
        </tr>
           
        </table>
        </div>
        
        <br /><br />
        
        <div align="center">
        <input type="submit" class="btn_submit" value="内容確定" />
        </div>
		
        </form>
        
        
        

{if $account_name ne ''}
<a href="kktp.php?account_name={$account_name}&from={$from}&to={$to}&csv=1">CSVダウンロード</a><br /><br />

        <table class="list_body">
        <tr>
        <th width="150">導入元キャンペーン</th>
        
       
        <th width="200">氏名</th>
        <th width="200">email</th>
        <th width="100">携帯番号</th>
        <th width="120">都道府県</th>
        <th width="">意気込み</th>
       <th width="240">希望連絡時間帯</th>
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
    <td width="">{$item.time_zone}</td>
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
