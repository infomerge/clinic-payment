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
    	


        <div id="breadcrumb">
<!--        TOP&nbsp;&gt;&nbsp;店舗情報 -->
        </div>

<h2 class="title_name">商品登録</h2>


    <form method="post">
    	<input type="hidden" name="projectt_id" value="{$project_id}" />
        <input type="hidden" name="product_id" value="{$data.product_id}" />
        
        <div class="tbldetail_wrap">
        <table class="tbldetail">

        <tr>
        <th>商品名</th>
        <td class="bg_odd">
        
        <input type="text" name="product_name" value="{$data.product_name}" class="frmtxt" style="width:600px;" />

        </td>
      	</tr>
        <tr>
        <th>商品コード</th>
        <td class="bg_odd">
        
        <input type="text" name="product_code" value="{$data.product_code}" class="frmtxt" style="width:600px;" />

        </td>
      	</tr>
        <tr>
        <th>金額</th>
        <td class="bg_odd">
        
        <input type="text" name="price" value="{$data.price}" class="frmtxt" style="width:600px;" />
		　<input type="radio" name="tax_flag" value="1"{if $data.tax_flag eq 1} checked="checked"{/if} />消費税別　<input type="radio" name="tax_flag" value="2"{if $data.tax_flag eq 2} checked="checked"{/if} />消費税込<br />
        <input type="checkbox" value="1"{if $data.monthly_flag eq 1} checked="checked"{/if} />&nbsp;月額課金
        </td>
      	</tr>
         <tr>
        <th>規約</th>
        <td class="bg_odd">
        
        <textarea name="terms" style="width:100%;" rows="20">{$data.terms}</textarea>

        </td>
      	</tr>
         <tr>
        <th>申込時送信メールアドレス<br />
		（複数ある場合は半角カンマ区切り）</th>
        <td class="bg_odd">
        
        <input type="text" name="alert_emails" value="{$data.alert_emails}" class="frmtxt" style="width:600px;" />

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
