<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>クルテルワン管理ツール</title>
{include file="common/head_inc.tpl"}
<link href="/css/email_entry_master.css" rel="stylesheet" type="text/css" />
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

<h2 class="title_name">導入元キャンペーン登録</h2>


    <form method="post">
        <input type="hidden" name="id" value="{$data.id}" />
        <input type="hidden" name="account_name" value="{$data.account_name}" />
        
        <div class="tbldetail_wrap">
        <table class="tbldetail">
        <tr>
        <th>導入元キャンペーン名</th>
        <td class="bg_odd">
        
        <input type="text" name="title" value="{$data.title}" class="frmtxt" style="width:600px;" />

        </td>
        </tr>
        <tr>
        <th>DRM商品ID</th>
        <td class="bg_even">
        <input type="text" name="pid" value="{$data.pid}" class="frmtxt"  />
        
        </td>
        </tr>
        <tr>
        <th>エキスパアカウント</th>
        <td class="bg_odd">
        <label class="frmselect">
        <select name="publisher_id">
        {foreach from=$expa_list key=k item=v}
        <option value="{$k}"{if $k == $data.publisher_id} selected="selected"{/if}>{$v}</option>
        {/foreach}
        </select>
        </label>
        </td>
        </tr>
        <tr>
        <th>エキスパフォームCD</th>
        <td class="bg_odd"><input type="text" name="formcd" value="{$data.formcd}" class="frmtxt" /></td>
        </tr>
        <tr>
        <th>LP完了画面リダイレクトURL</th>
        <td class="bg_even">
        <input type="text" name="redirect" value="{$data.redirect}" class="frmtxt" style="width:600px;" />
        </td>
        </tr>
        <tr>
        <th>LP完了画面リダイレクトURL2（ABテスト用）</th>
        <td class="bg_even">
        <input type="text" name="redirect2" value="{$data.redirect2}" class="frmtxt" style="width:600px;" />
        </td>
        </tr>
             
        </table>
        </div>
        
        <br /><br />
        
        <div align="center">
        <input type="submit" class="btn_submit" value="内容確定" />
        </div>
		
        </form>
        
        
        

{if $account_name ne ''}
<a href="kktp.php?account_name={$account_name}&from={$from}&to={$to}&csv=1">CSVダウンロード</a><br /><br />

        <table class="list_body">
        <tr>
        <th width="150">導入元キャンペーン</th>
        
       
        <th width="200">氏名</th>
        <th width="200">email</th>
        <th width="100">携帯番号</th>
        <th width="120">都道府県</th>
        <th width="">意気込み</th>
       <th width="240">希望連絡時間帯</th>
        <th width="120">申込日時</th>
        </tr>
        {foreach from=$data item=item}
        <tr>
        <td width="150">{$item.account_name}</a></td>
        
        
        <td width="100">{$item.name}</td>
        <td width="200">
        <div>{$item.email}</div>
        
       
        
        </td>
        <td width="">{$item.phone}</td>
        <td width="">{$item.pref}</td>
        <td width="">{$item.msg}</td>
    <td width="">{$item.time_zone}</td>
    <td>{$item.regist_date|date_format:"%Y/%m/%d"}</td>
        </tr>
        {/foreach}
        </table>

{/if}

    </div><!-- content -->
    
    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
