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

        <div class="contentshead mart10">
          <div class="hspace">利用中アカウント一覧</div>
          
          <form action="accountdetail.php">
            <input type="submit" name="button" class="btn_submit" value="アカウント作成 " />
            </form>
        </div>
        <div class="searchshort">
        <form action="enablelist.php" method="POST">
        
        <div id="search_left">
        
              
        <div class="sbar"><p>アカウントの検索</p></div>
        
        
         <table border="0" cellspacing="0" cellpadding="0" class="stbl">
          <tr>
            <td class="stblleft">アカウント名</td>
            <td class="stblright"><input name="account_name" type="text" size="40" maxlength="100" class="sb02-01" /></td>
          </tr>
        </table>
        
        <div class="marb10"></div>
          <div align="center"><input type="submit" name="button"  class="btn_cancel" value="検索" /></div>
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
              <td><a href="accountdetail.php?account_id={$item.account_id}">{$item.account_name}</a></td>
              <td width="150">
              <div align="center">
              <form action="disableaccount.php" method="post"><input type="hidden" name="account_id" value="{$item.account_id}" /><input type="submit" name="button3" class="btn_delete" value="利用停止" onclick="return confirm('利用停止します。よろしいですか？');" /></form>
              </div>
              </td>
              
              <td width="120">{$item.created_at|date_format:"%Y/%m/%d"}</td>
              </tr>
            {/foreach}
          </table>
        
        <div class="paging">
        {if $page > 1 }
        <a href="/account/enablelist.php?page={$page - 1}&account_name={$account_name}">前へ</a>
        {/if}
        {section name=cnt loop=$totalpage}
        <a href="/account/enablelist.php?page={$smarty.section.cnt.iteration}&account_name={$account_name}">{$smarty.section.cnt.iteration}</a> 
        {/section}
        {if $totalpage > $page }
        <a href="/account/enablelist.php?page={$page + 1}&account_name={$account_name}">次へ</a>
        {/if}
        </div>

	</div><!-- content -->

</div>
  <!-- end .content -->
  
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
