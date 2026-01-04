<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>クルテルワン管理ツール</title>
{include file="common/head_inc.tpl"}

<script>
function checkSubmit(){
	var errTxt = "";
	if( $("#account_name").val() == ""){	errTxt += "アカウント名 は必須項目です\n";}
	
	if($("input[name='authority_id']:checked").val() != 1 && $("input[name='authority_id']:checked").val() != 2 && $("input[name='authority_id']:checked").val() != 3)	errTxt += "権限選択は必須項目です\n";
	
	
	if( $("#login_id").val() == ""){	errTxt += "ログインID は必須項目です\n";}
	if( $("#password").val() == ""){	errTxt += "パスワード は必須項目です\n";}
	//if( $("#submit_name").val() == ""){	errTxt += "メール送信用名称 は必須項目です\n";}
	//if( $("#submit_email").val() == ""){	errTxt += "メール送信用メールアドレス は必須項目です\n";}
	//if( $("#partner_code").val() == ""){	errTxt += "パートナーコード は必須項目です\n";}
	
	if(errTxt.length > 0){	
		alert(errTxt); 
		return false;	
	}else{
		return true;
	}

}
</script>
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">

    <div id="main">
    
	<div class="content">
        
        <form action="entryaccount.php" method="post">
          <input type="hidden" name="account_id" value="{$data.account_id}" />
          <input type="hidden" name="expiration_date_from" value="{$data.expiration_date_from}" />
          <input type="hidden" name="expiration_date_to" value="{$data.expiration_date_to}" />
          <input type="hidden" name="suspension_flag" value="{$data.suspension_flag}" />
        
        <div class="tbldetail_wrap">
        <table class="tbldetail">
          <tr>
            <td width="140"><input type="submit" name="entry" id="button" class="btn_130px" value="アカウントを登録" onclick="return checkSubmit();" /></td>
            <td><input type="submit" name="notentry"  class="btn_cancel" value="登録せずにアカウント利用中トレイに戻る" /></td>
            </tr>
            </table>
          </div>
          <div>
          <div class="marb20"></div>
            <div class="bar"><p>基本情報</p></div>
            
              
           <table class="tbldetail">
              <tr>
                <th>アカウント名 <span class="rd">[必須]</span><br />
                  （50文字まで）</th>
                <td class="bg_odd">
                  <input type="text" name="account_name" id="account_name" class="frmtxt" value="{$data.account_name}" /></td>
                </tr>
              <tr>
                <th>権限 <span class="rd">[必須]</span></th>
                <td class="bg_even">
                    <label><input name="authority_id" value="1"{if $data.authority_id eq '1'} checked="checked"{/if} type="radio">&nbsp;管理者　</label>　
                    <label><input name="authority_id" value="2"{if $data.authority_id eq '2' or $data.authority_id eq ''} checked="checked"{/if} type="radio">&nbsp;一般　</label>　
                    
                  </td>
                </tr>
            
              </table>
            <p>&nbsp;</p>
              <div class="bar"><p>ログイン情報</p></div>
              
            <table class="tbldetail">
              <tr>
                <th>ログインID <span class="rd">[必須]</span><br />
                  （10文字まで）</th>
                <td class="bg_odd"><input type="text" name="login_id" id="login_id" class="frmtxt" value="{$data.login_id}" /></td>
                </tr>
              <tr>
                <th>パスワード <span class="rd">[必須]</span><br />
                  （10文字まで）</th>
                <td class="bg_even"><input type="text" name="password" class="frmtxt" value="{$data.password}" /></td>
                </tr>
               
              </table>
            <p>&nbsp; </p>
          </div>
        </form>
        </div>
          <!-- end .content -->
  
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
