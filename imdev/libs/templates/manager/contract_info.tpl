<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>契約情報登録</title>
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

<h2 class="title_name">契約情報登録</h2>

        <a href="account_info.php">アカウント情報新規登録／編集</a><br /><br />

        <select name="inst_name" style="font-size:16px;">
            <option value=""{if $data.inst_name eq ''} selected="selected"{/if}>選択</option>
            {foreach from=$account_info item=item key=key}
            <option value="{$key}"{if $data.inst_name eq $key} selected="selected"{/if}>{$item}</option>
            {/foreach}
        </select>
        
        <br /><br />
        
    <form method="post">
        <input type="hidden" name="account_id" value="{$data.account_id}" />
        
        <div class="tbldetail_wrap">
        <table class="tbldetail">
            
        <tr>
        <th>郵便番号</th>
        <td class="bg_odd">
        
        <input type="text" name="postal_code" value="{$data.postal_code}" class="frmtxt" style="width:300px;" />
        </td>
        </tr>
        <tr>
        <th>住所</th>
        <td class="bg_odd">
        
        <input type="text" name="address" value="{$data.address}" class="frmtxt" style="width:300px;" />

        </td>
        </tr>
        <tr>
        <th>電話番号</th>
        <td class="bg_odd">
        
        <input type="text" name="tel" value="{$data.tel}" class="frmtxt" style="width:300px;" />
        
        </td>
        </tr>
        <tr>
        <th>その他</th>
        <td class="bg_odd">
        
        <input type="text" name="others" value="{$data.others}" class="frmtxt" style="width:300px;" />

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
