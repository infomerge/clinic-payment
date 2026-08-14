<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<meta name="robots" content="noindex,nofollow,noarchive" />
<title>{$smarty.const.SERVICETITLE}</title>
{include file="common/head_inc.tpl"}
<script>
<!--
function checkAgree(obj){
		$("#openlogin").slideToggle("fast");
}
-->
</script>
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap_login">

<div class="padb50"></div>

<div class="container">
 
  <div class="marb50"></div>
  
  <div align="center"><img src="/images/logo.png" width="50%" class="mart20" /></div>
  
  <div class="marb40"></div>
  	{if $message ne ""}
  	<div id="errormsg">{$message}</div>
  	{/if}
    
    
    
    <div class="loginpanel">
    	
        <form action="manager/verifylogin.php" method="post">
        <div class="formdiv">
        
          <h2 align="center">{$smarty.const.SERVICETITLE}</h2>
          
          <div class="marb40"></div>
          
          <h3>ユーザー名</h3>
          <input name="id" id="userid" type="text"  />
			
            <div class="marb20"></div>
            
          <h3>パスワード</h3>
          <input type="password" name="password" id="password"  />
          
          <div class="marb30"></div>
          
		<input type="hidden" name="autologin" value="" />
      
          
        
        </div>
        
        
        
        <div align="center" id="openlogin">
        <input type="submit" name="button" id="button" value="ログイン" />
        </div>
        
        <div class="marb20"></div>
        
        </form>
    </div>
    
    <div class="padb30"></div>
    
<!-- end .container --></div>

<div class="padb50"></div>


</div>
</body>
</html>