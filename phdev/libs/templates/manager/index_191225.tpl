<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>{$smarty.const.SERVICETITLE}</title>
{include file="common/head_inc.tpl"}
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">

   
    
    <div class="content">
    	

        <div id="breadcrumb"></div>

		<h2 class="title_name">メニュー</h2>  
        
    
    <ul id="globalmenu">
    <li><a href="/manager/account_info_list.php">医療機関アカウント登録/編集</a></li>
    <li><a href="/manager/patient_info_list.php">患者・口座　登録/編集</a></li>
    <!-- <li><a href="/manager/product_master_list.php" class="navi01_a">レセプトッチェック</a></li>  -->
    <li><a href="/manager/receipt_select.php">レセプトデータ取込</a></li>
    <li><a href="/manager/appendix.php">自由診療／物販／金額調整</a></li>
    <li><a href="/manager/generate.php">請求書・領収書</a></li>
	</ul>

    </div><!-- content -->
    

{*include file="common/sidebar.tpl"*}
    
</div>
</body>
</html>
