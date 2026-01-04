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

<h2 class="title_name">プロジェクト登録</h2>


    <form method="post">
        <input type="hidden" name="product_id" value="{$data.product_id}" />
        
        <div class="tbldetail_wrap">
        <table class="tbldetail">

        <tr>
        <th>プロジェクト名</th>
        <td class="bg_odd">
        
        <input type="text" name="product_name" value="{$data.product_name}" class="frmtxt" style="width:600px;" />

        </td>
      	</tr>
        <tr>
        <th>担当事業部</th>
        <td class="bg_odd">
        <select name="division_id">
        <option value=""{if !isset($data.division_id) and $data.division_id eq ""} selected="selected"{/if}>選択</option>
        {foreach from=$m_division item=item key=key}
        <option value="{$key}"{if $data.division_id eq $key} selected="selected"{/if}>{$item}</option>
		{/foreach}

        </td>
      	</tr>
        </table>
        </div>
        
        <br /><br />
        
        <div align="center">
        <input type="submit" class="btn_submit" value="内容確定" />
        </div>
		
        </form>
        
        
       
    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
