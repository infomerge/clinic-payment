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

<h2 class="title_name">リスト不正チェック用</h2>

<a href="listcheck.php?csv=1">CSVダウンロード</a>
<br /><br />
        <table class="list_body">
        <tr>
        <th width="150">コード</th>
        <th width="150">IPアドレス</th>
        
        <th width="200">Email</th>
       
        <th width="">登録日時</th>
       
       
        </tr>
        {foreach from=$data item=item}
      
        <tr>
        <td width="">{$item.form_code}</a></td>
        <td width="">{$item.ip_address}</td>
        <td width="">{$item.email}</td>
        <td>{$item.regist_date}</td>  
        </tr>
      
        {/foreach}
        </table>
     


    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
