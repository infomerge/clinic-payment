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
        <div id="breadcrumb">
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="./patient_info_list.php">患者情報一覧</a>&nbsp;&gt;&nbsp;患者情報登録
        </div>

    <h2 class="title_name">患者情報登録</h2>

        <form method="post">
            <input type="hidden" name="original_pid" value="{$data.original_pid}" />

            <div class="tbldetail_wrap" align="center">
            <table class="tbldetail">

            <tr><th>患者番号</th>
            <td class="bg_odd"><input type="text" name="original_pid" value="{$data.original_pid}" class="frmtxt" style="width:150px;" /></td></tr>

            <tr><th>患者名</th>
            <td class="bg_odd"><input type="text" name="patient_name" value="{$data.patient_name}" class="frmtxt" style="width:200px;" /></td></tr>
                
            <tr><th>生年月日</th>
            <td class="bg_odd"><input type="text" name="patient_birth" value="{$data.patient_birth}" class="frmtxt" style="width:200px;" /></td></tr>
            
            <tr><th>被保険者番号</th>
            <td class="bg_odd"><input type="text" name="patient_hihoban" value="{$data.patient_hihoban}" class="frmtxt" style="width:200px;" /></td></tr>
                
            <tr><th>メールアドレス</th>
            <td class="bg_odd"><input type="text" name="email" value="{$data.email}" class="frmtxt" style="width:200px;" /></td></tr>

            <!---住所情報--->

            <tr><th rowspan="5">メイン住所<br><br><a href="change-address.php?original_pid={$data.original_pid}" class="change_button">メイン⇔サブ住所切替</a></th>
                
            <td class="bg_odd">送付先氏名：<input type="text" name="shipto_name" value="{$data.shipto_name}" class="frmtxt" style="width:200px;" /></td></tr>
                
            <td class="bg_odd">郵便番号：<input type="text" name="postal_code" value="{$data.postal_code}" class="frmtxt" style="width:100px;" /></td></tr>

            <tr>
            <td class="bg_odd">都道府県コード：<input type="text" name="prefecture" value="{$data.prefecture}" class="frmtxt" style="width:50px;" /></td></tr>

            <tr>
            <td class="bg_odd">住所1：<input type="text" name="address1" value="{$data.address1}" class="frmtxt" style="width:300px;" /></td></tr>

            <tr>
            <td class="bg_odd">住所2：<input type="text" name="address2" value="{$data.address2}" class="frmtxt" style="width:300px;" /></td></tr>
                
            <tr><th rowspan="5">サブ住所<br><br><a href="change-address.php?original_pid={$data.original_pid}" class="change_button">メイン⇔サブ住所切替</a></th>
                
            <td class="bg_odd">送付先氏名：<input type="text" name="shipto_name_sub" value="{$data.shipto_name_sub}" class="frmtxt" style="width:200px;" /></td></tr>
                
            <td class="bg_odd">郵便番号：<input type="text" name="postal_code_sub" value="{$data.postal_code_sub}" class="frmtxt" style="width:100px;" /></td></tr>

            <tr>
            <td class="bg_odd">都道府県コード：<input type="text" name="prefecture_sub" value="{$data.prefecture_sub}" class="frmtxt" style="width:50px;" /></td></tr>

            <tr>
            <td class="bg_odd">住所1：<input type="text" name="address1_sub" value="{$data.address1_sub}" class="frmtxt" style="width:300px;" /></td></tr>

            <tr>
            <td class="bg_odd">住所2：<input type="text" name="address2_sub" value="{$data.address2_sub}" class="frmtxt" style="width:300px;" /></td></tr>
                
            

            <!---口座情報--->

            <tr><th>金融機関コード</th>
            <td class="bg_odd"><input type="text" name="bac" value="{$data.bac}" class="frmtxt" style="width:80px;" /></td></tr>

            <tr><th>支店コード</th>
            <td class="bg_odd"><input type="text" name="brc" value="{$data.brc}" class="frmtxt" style="width:50px;" /></td></tr>

            <tr><th>種別</th>
            <td class="bg_odd"><input type="text" name="classification" value="{$data.classification}" class="frmtxt" style="width:20px;" /></td></tr>

            <tr><th>口座番号</th>
            <td class="bg_odd"><input type="text" name="account_number" value="{$data.account_number}" class="frmtxt" style="width:200px;" /></td></tr>

            <tr><th>口座名義</th>
            <td class="bg_odd"><input type="text" name="holder_name" value="{$data.holder_name}" class="frmtxt" style="width:300px;" /></td></tr>

            
            <tr><th>その他1</th>
            <td class="bg_odd"><input type="text" name="others" value="{$data.others}" class="frmtxt" style="width:200px;" /></td></tr>
            <tr><th>その他2</th>
            <td class="bg_odd"><input type="text" name="others2" value="{$data.others2}" class="frmtxt" style="width:200px;" /></td></tr>
            <tr><th>その他3</th>
            <td class="bg_odd"><input type="text" name="others3" value="{$data.others3}" class="frmtxt" style="width:200px;" /></td></tr>
            
            <tr><th>口座振替</th>
            <td class="bg_odd">
                <input type="radio" name="direct_debit" value="0" {if $data.direct_debit == 0} checked = "checked"{/if}>する
                <input type="radio" name="direct_debit" value="1" {if $data.direct_debit == 1} checked = "checked"{/if}>しない
            </td></tr>
            <tr><th>請求書発行</th>
            <td class="bg_odd">
                <input type="radio" name="invoice_output" value="0" {if $data.invoice_output == 0} checked = "checked"{/if}>する
                <input type="radio" name="invoice_output" value="1" {if $data.invoice_output == 1} checked = "checked"{/if}>しない    
            </td></tr>
            <tr><th>領収書発行</th>
            <td class="bg_odd">
                <input type="radio" name="receipt_output" value="0" {if $data.receipt_output == 0} checked = "checked"{/if}>する
                <input type="radio" name="receipt_output" value="1" {if $data.receipt_output == 1} checked = "checked"{/if}>しない    
            </td></tr>
            
            
            
            </table>
            </div>

            <br /><br />
            
            {if $data.original_pid == ""}
            <div align="center">
            <input type="submit" name="db_only" class="btn_submit" value="新規登録" />
            </div>
            <br />
            
                {if $data.rp_cid == 0}
                <div align="center">
                <input type="submit" name="rp_new" class="btn_submit" value="新規登録＋RP新規登録" />
                </div>
                <br />
                {/if}
            
            {else}
            
            <div align="center">
            <input type="submit" name="db_only" class="btn_submit" value="情報更新" />
            </div>
            <br />
            
                {if $data.rp_cid == 0}
                    <div align="center">
                    <input type="submit" name="rp_new" class="btn_submit" value="更新＋RP新規登録" />
                    </div>
                    <br />
                {else}
                    <div align="center">
                    <input type="submit" name="rp_update" class="btn_submit" value="更新＋RP情報更新" />
                    </div>
                    <br />
                {/if}
            
            {/if}
            
            <div align="center">
            <br><a href="patient_info_list.php">患者情報一覧に戻る</a><br>
            </div>
		
        </form>
        
    </div><!-- content -->
    

{*include file="common/sidebar.tpl"*}

</div>
</body>
</html>
