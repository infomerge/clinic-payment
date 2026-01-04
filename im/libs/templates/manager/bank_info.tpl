<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>口座情報登録</title>
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

<h2 class="title_name">口座情報登録</h2>

<a href="patient_info.php">患者情報登録</a><br /><br />

    <form method="post">
        <input type="hidden" name="holder_id" value="{$data.holder_id}" />
        
        <div class="tbldetail_wrap">
        <table class="tbldetail">
            
        <tr>
        <th>口座名義</th>
        <td class="bg_odd">
        
        <input type="text" name="holder_name" value="{$data.holder_name}" class="frmtxt" style="width:300px;" />
        </td>
        </tr>
        <tr>
        <th>金融機関名</th>
        <td class="bg_odd">
        
        <input type="text" name="bank_name" value="{$data.bank_name}" class="frmtxt" style="width:300px;" />

        </td>
        </tr>
        <tr>
        <th>支店名</th>
        <td class="bg_odd">
        
        <input type="text" name="branch_name" value="{$data.branch_name}" class="frmtxt" style="width:300px;" />
        
        </td>
        </tr>
        <tr>
        <th>種別</th>
        <td class="bg_odd">
        
        <input type="text" name="classification" value="{$data.classification}" class="frmtxt" style="width:300px;" />

            
        </td>
        </tr>
        <tr>
        <th>口座番号</th>
        <td class="bg_odd">
        
        <input type="text" name="account_number" value="{$data.account_number}" class="frmtxt" style="width:300px;" />
            

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
        <input type="submit" class="btn_submit" value="登録" />
        </div>
		
        </form>
        

    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
