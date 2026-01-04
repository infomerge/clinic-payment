<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>成果報酬集計管理ツール</title>
{include file="common/head_inc.tpl"}
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">

    <div id="main">
    
    <div class="content">
    
    		<div id="breadcrumb">
            TOP&nbsp;&gt;&nbsp;<a href="/aggregate/">店舗情報</a>&nbsp;&gt;&nbsp;店舗詳細
            </div>
            
    	<h2 class="title_name">{$shop.name}</h2>
        
        {*
        <a href="index.php">一覧に戻る</a>
        <br /><br />
        店舗ID：{$shop.lp_shop_id}<br />
        店舗名：{$shop.name}<br />
        <br />
        *}
        
        <form method="post">
        <input type="hidden" name="id" value="{$shop.id}" />
        
        <div class="tbldetail_wrap">
        <table class="tbldetail">
        <tr>
        <th>金額／割合</th>
        <td class="bg_odd"><input type="radio" name="revenue_type" value="1"{if $shop.revenue_type eq 1} checked="checked"{/if} />&nbsp;金額　　<input type="radio" name="revenue_type" value="2"{if $shop.revenue_type eq 2} checked="checked"{/if} />&nbsp;割合</td>
        </tr>
        <tr>
        <th>予約：金額</th>
        <td class="bg_even"><input type="text" name="revenue_price" value="{$shop.revenue_price}" class="frmtxt" />&nbsp;円</td>
        </tr>
        <tr>
        <th>PPC：金額</th>
        <td class="bg_odd"><input type="text" name="ppc_price" value="{$shop.ppc_price}" class="frmtxt" />&nbsp;円</td>
        </tr>
        <tr>
        <th>予約：割合</th>
        <td class="bg_even"><input type="text" name="revenue_rate" value="{$shop.revenue_rate}" class="frmtxt" />&nbsp;%</td>
        </tr>
        <tr>
        <th>PPC：割合</th>
        <td class="bg_odd"><input type="text" name="ppc_rate" value="{$shop.ppc_rate}" class="frmtxt" />&nbsp;円</td>
        </tr>
        
        <tr>
        <th>パスワード</th>
        <td class="bg_even"><input type="text" name="password" value="{$shop.password}" class="frmtxt" /></td>
        </tr>
        
        <tr>
        <th>レクチャー有無</th>
        <td class="bg_odd"><input type="radio" name="lecture_flag" value="0"{if $shop.lecture_flag eq 0} checked="checked"{/if} />&nbsp;未レクチャー　　<input type="radio" name="lecture_flag" value="1"{if $shop.lecture_flag eq 1} checked="checked"{/if} />&nbsp;レクチャー済み
        <div class="marb20"></div>
        <div>レクチャー実施日</div>
        <input type="text" name="lecture_date" value="{if $shop.lecture_date ne '0000-00-00'}{$shop.lecture_date}{/if}" class="frmtxt" />（入力例：2016-04-01）
        </td>
        </tr>
        
        </table>
        </div>
        
        <br /><br />
        
        <div align="center">
        <input type="submit" class="btn_submit" value="内容確定" />
        </div>
		
        </form>
{*        
        <div style="width:80%;">
        <table align="center">
        <tr>
        <td>
        <input type="submit" class="btn_submit" value="内容確定" />
        </td>
        <td>
        </form>
        　　　　　
        </td>
        <td>
        <input type="submit" class="btn_cancel" value="キャンセル" />
        </td>
        </tr>
        </table>
        </div>
*}
        
        	<div align="right">
            <a href="index.php" class="btn_back">戻る</a>
            </div>
            
        </div>


    </div>

{include file="common/sidebar.tpl"}
    
</div>
</body>
</html>
