<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>医療機関情報一覧</title>
{include file="common/head_inc.tpl"}
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">


    
    <div class="content">
    	{*<a href="./syukei.php">RESTY集計</a>*}


        <div id="breadcrumb">
        <a href="./">トップページ</a>&nbsp;&gt;&nbsp;医療機関情報一覧
       
        </div>

<h2 class="title_name">医療機関情報一覧</h2>

<a href="account_info.php" class="btn">新規登録</a>

<div class="mb20"></div>

<!--
 / 
<a href="contract_info.php">編集</a>
-->
<br /><br />


        <table class="list_body">
            
        <tr>
        <th width="20"></th>
        <th width="50" align="center">アカウントID</th>
        <th width="200">医療機関名</th>
        <th width="50">ログインID</th>
        <th width="50">パスワード</th>
        <th width="50">郵便番号</th>
        <th width="100">住所</th>
        <th width="80">電話番号</th>
        <th width="50">その他</th>
        </tr>
            
        {foreach from=$data item=item}
        <tr style="border-bottom:1px solid #999;">
        <td align="center"><a href="account_info.php?original_irkkcode={$item.original_irkkcode}">編集</a></td>
        <td width="50">{$item.original_irkkcode}</td>
        <td width="200">{$item.irkkname}</td>
        <td width="50">{$item.login_id}</td>
        <td width="50">{$item.password}</td>
        <td width="50">{$item.postal_code}</td>
        <td width="100">{$item.address}</td>
        <td width="80">{$item.tel}</td>
        <td width="50">{$item.others}</td>
       
        </tr>
        {/foreach}
        </table>


    </div><!-- content -->
    
   

{*include file="common/sidebar.tpl"*}
    
</div>
</body>
</html>
