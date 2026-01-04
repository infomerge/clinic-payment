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

<h2 class="title_name">プロジェクト一覧</h2>

<a href="c_projects_detail.php">新規登録</a><br /><br />




        <table class="list_body">
        <tr>
        <th width="150" align="center">プロジェクトID</th>
        <th width="200">プロジェクト名</th>
        
       
        <th width="500">商品</th>
        
       
        
        <th></th>
      
        
        </tr>
        {foreach from=$data item=item}
        <tr style="border-bottom:1px solid #999;">
        <td align="center">{$item.project_id}</td>
        <td width="150">
<a href="c_projects_detail.php?project_id={$item.project_id}">{$item.project_name}</a></td>
        
        
        <td width="500">
        {foreach from=$item.products key=key item=item2}
        <a href="c_pruducts_detail.php?project_id={$item.project_id}&product_id={$item2.product_id}">{$item2.product_name}</a>　{$item2.price}円（{if $item2.tax_flag eq 1}消費税別{elseif $item2.tax_flag eq 2}消費税込{/if}）{if $item2.monthly_flag eq 1}　月額課金商品{/if}
        {/foreach}
        </td>
      <td><a href="c_products_detail.php?project_id={$item.project_id}">商品を追加する</a></td>
        
        
        
        
        </tr>
        {/foreach}
        </table>



    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
