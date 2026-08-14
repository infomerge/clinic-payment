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

<h2 class="title_name">バックエンド商品購入者</h2>


{foreach from=$data key=key item=item2}


<h2 class="title_name">{$partner[$key]}</h2>

        <table class="list_body">
        <tr>
       
       <th width="200">購入商品</th>
        <th width="200">氏名</th>
        <th width="300">Email</th>
        <th width="">決済方法</th>
        <th width="">購入日時</th>
        
        </tr>
        {foreach from=$item2 item=item}
        <tr>
        
      <td>{$item.product_cd}</td>
       <td>
      {$item.firstname} {$item.lastname}
      </td>
      <td>
      {$item.email}
       </td>
       <td>
        {$item.pay_method}
       </td>
       <td>
      {$item.kessai_date}
       </td>
      
        </tr>
        {/foreach}
        </table>
<br /><br />



{/foreach}

<br /><br /><br />
    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
