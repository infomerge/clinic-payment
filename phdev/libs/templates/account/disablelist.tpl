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


            <div class="searchshort">
            <form action="disablelist.php" method="POST">
            
            <div id="search_left">
            
                  
            <div class="sbar"><p>アカウントの検索</p></div>
            
            
             <table border="0" cellspacing="0" cellpadding="0" class="stbl">
              <tr>
                <td class="stblleft">アカウント名</td>
                <td class="stblright"><input name="account_name" type="text" size="40" maxlength="100" class="sb02-01" /></td>
              </tr>
            </table>
            
            <div class="marb10"></div>
              <div align="center"><input type="submit" name="button" class="btn_cancel" value="検索" /></div>
            </div>
            <div class="clearfloat"></div>
            
            </form>
            </div>
            
            <br /><br />
            
            <div class="asdata">
              <table class="list_top">
                <tr class="tablehead">
                  <th>アカウント名</th>
                  <th width="150">&nbsp;</th>
                  <th width="120">登録日</th>
                  </tr>
                </table>
                
                <table class="list_body">
                {foreach from=$data item="item"}
                <tr>
                  <td>{$item.account_name}</td>
                  <td>
                  <div align="center">
                  <form action="enableaccount.php" method="post"><input type="hidden" name="account_id" value="{$item.account_id}" /><input type="submit" name="button3" id="button3" class="btn_save" value="利用中に変更" onclick="return confirm('利用中に変更します。よろしいですか？');" /></form>
                  </div>
                  </td>
                  <td>{$item.created_at|date_format:"%Y/%m/%d"}</td>
                  </tr>
                {/foreach}
              </table>
              <br /><br />
              
            <div class="paging">
            {if $page > 1 }
            <a href="/account/disablelist.php?page={$page - 1}&account_name={$account_name}">前へ</a>
            {/if}
            {section name=cnt loop=$totalpage}
            <a href="/account/disablelist.php?page={$smarty.section.cnt.iteration}&account_name={$account_name}">{$smarty.section.cnt.iteration}</a> 
            {/section}
            {if $totalpage > $page }
            <a href="/account/disablelist.php?page={$page + 1}&account_name={$account_name}">次へ</a>
            {/if}
            </div>
            </div>

	</div><!-- content -->
  
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
