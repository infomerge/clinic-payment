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

    <div id="main">
    
    <div class="content">
    	{*<a href="./syukei.php">RESTY集計</a>*}


        <div id="breadcrumb">
<!--        TOP&nbsp;&gt;&nbsp;店舗情報 -->
        </div>

<h2 class="title_name">患者情報登録</h2>

    <!-- <a href="bank_info.php">口座情報登録</a><br /><br /> -->
        
        
    <form method="post">
        <input type="hidden" name="patient_id" value="{$data.patient_id}" />
        
        <div class="tbldetail_wrap">
        <table class="tbldetail">
            
        <tr>
        <th>患者番号</th>
        <td class="bg_odd">
        
        <input type="text" name="patient_code" value="{$data.patient_code}" class="frmtxt" style="width:300px;" />
        </td>
        </tr>
            
        <tr>
        <th>患者名</th>
        <td class="bg_odd">
        
        姓
        <input type="text" name="patient_last_name" value="{$data.patient_last_name}" class="frmtxt" style="width:200px;" />
        <br />
        名
        <input type="text" name="patient_first_name" value="{$data.patient_first_name}" class="frmtxt" style="width:200px;" />

        </td>
        </tr>
{*            
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
*}
        <tr>
        <th>電話番号</th>
        <td class="bg_odd">
        
        <input type="text" name="tel" value="{$data.tel}" class="frmtxt" style="width:300px;" />
            

        </td>
        </tr>
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

<br /><br /><br />
<h2 class="title_name">患者情報登録</h2>

        <div class="tbldetail_wrap">
        <table class="tbldetail">
        {foreach from=$data2 item=item}
        <tr>
        <th>郵便番号</th>
        <td class="bg_odd">
        
        {$item.postal_code}
        
        </td>
        </tr>
        <tr>
        <th>住所</th>
        <td class="bg_odd">
        
        {$item.address}

            
        </td>
        </tr>
        {/foreach}
        </table>
        
        <br /><br />
        <a href="patient_info_address.php?patient_id={$data.patient_id}" style="font-size:16px !important;">住所を追加する</a>
        
        </div>
        
        <br /><br />
        

        
        
        
    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
