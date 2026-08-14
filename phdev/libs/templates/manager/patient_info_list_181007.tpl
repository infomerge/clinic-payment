<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>患者情報一覧</title>
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

<h2 class="title_name">患者情報一覧</h2>

<a href="patient_info.php">新規登録</a>
<!--
 / 
<a href="contract_info.php">編集</a>
-->
<br /><br />


        <table class="list_body">
            
        <tr>
        <th width="50" align="center">患者番号</th>
        <th width="50">患者名（性）</th>
        <th width="50">患者名（名）</th>
        <th width="50">郵便番号</th>
        <th width="100">住所</th>
        <th width="80">電話番号</th>
        <th width="100">口座名義</th>
        <th width="80">金融機関名</th>
        <th width="80">支店名</th>
        <th width="20">種別</th>
        <th width="80">口座番号</th>
        <th width="100">その他</th>
        </tr>
            
        {foreach from=$data item=item}
        <tr style="border-bottom:1px solid #999;">
        <td width="50">{$item.patient_id}</td>
        <td width="50">{$item.patient_last_name}</td>
        <td width="50">{$item.patient_first_name}</td>
        <td width="50">{$item.postal_code}</td>
        <td width="100">{$item.address}</td>
        <td width="80">{$item.tel}</td>
        <td width="100">{$item.holder_name}</td>
        <td width="80">{$item.bank_name}</td>
        <td width="80">{$item.branch_name}</td>
        <td width="20">{$item.classification}</td>
        <td width="80">{$item.account_number}</td>
        <td width="50">{$item.others}</td>
       
        </tr>
        {/foreach}
        </table>


    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
