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

<h2 class="title_name">エクランKGI</h2>

  <table class="list_body">
        <tr>
       
       
        <th width="200">パートナー名</th>
        <th width="300">正会員ポイント</th>
        <th width="">バックエンドポイント</th>
        <th>パートナー別ポイント計</th>
        
        </tr>
{foreach from=$data key=key item=item}




      
        <tr>
        
      
       <td>
      {$item.client_name}
      </td>
      <td>
      {$item.payuser}
       </td>
       <td>
      {$item.backend}
       </td>
      <td>{$item.payuser + $item.backend}</td>
        </tr>
       
        



{/foreach}

<tr>
<td><strong>合計</strong></td><td><b>{$sum_payuser}</b></td><td><b>{$sum_backend}</b></td><td><b>{$sum}</b></td>
</tr>
</table>
<br /><br />

<br /><br /><br />
    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
