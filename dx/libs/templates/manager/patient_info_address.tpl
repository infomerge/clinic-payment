<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>患者情報登録</title>
{include file="common/head_inc.tpl"}
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">


    
    <div class="content">
    	{*<a href="./syukei.php">RESTY集計</a>*}


        <div id="breadcrumb">
<!--        TOP&nbsp;&gt;&nbsp;店舗情報 -->
        </div>

<h2 class="title_name">患者情報住所</h2>

    <!-- <a href="bank_info.php">口座情報登録</a><br /><br /> -->
        
        
    <form method="post">
        <input type="hidden" name="patient_id" value="{$data.patient_id}" />
        
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

           
        </table>
</div>
<br /><br />
        <div align="center">
        <input type="button" class="btn_submit" value="戻る" />　　<input type="submit" class="btn_submit" value="登録" />
        </div>
		
        </form>

<br /><br /><br />

        
        <br /><br />
        

        
        
        
    </div><!-- content -->
    


{*include file="common/sidebar.tpl"*}
    
</div>
</body>
</html>
