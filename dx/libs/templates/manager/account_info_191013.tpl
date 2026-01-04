<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>アカウント情報登録</title>
{include file="common/head_inc.tpl"}
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">

    
    <div class="content">
    	{*<a href="./syukei.php">RESTY集計</a>*}


        <div id="breadcrumb">
        <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="./account_info_list.php">医療機関情報一覧</a>&nbsp;&gt;&nbsp;医療機関アカウント情報登録・編集
        </div>

	<h2 class="title_name">医療機関アカウント情報登録</h2>


    <form method="post">
        <input type="hidden" name="original_irkkcode" value="{$data.original_irkkcode}" />
        
        <div class="tbldetail_wrap" align="center">
        <table class="tbldetail">
            
        <tr>
        <th>医療機関名</th>
        <td class="bg_odd">
        
        <input type="text" name="irkkname" value="{$data.irkkname}" class="frmtxt" style="width:300px;" />
        </td>
        </tr>
            
        <tr>
        <th>ログインID</th>
        <td class="bg_odd">
        
        <input type="text" name="login_id" value="{$data.login_id}" class="frmtxt" style="width:300px;" />

        </td>
        </tr>
            
        <tr>
        <th>パスワード</th>
        <td class="bg_odd">
        
        <input type="text" name="password" value="{$data.password}" class="frmtxt" style="width:300px;" />

        </td>
        </tr>
            
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
        <input type="submit" class="btn_submit" value="{if $data.original_irkkcode eq ''}登録{else}編集{/if}" />
        </div>
		
        </form>
        
    </div><!-- content -->
    

{*include file="common/sidebar.tpl"*}

</div>
</body>
</html>
