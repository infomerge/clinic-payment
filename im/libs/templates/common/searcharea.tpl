		<div class="searcharea">
        <form method="get">
        <table class="searcharea_tbl">
        <tr>
        <td><b>パートナー</b>　
        <select name="account_id">
        <option value=""{if !isset($smarty.get.account_id) or $smarty.get.account_id eq ''} selected="selected"{/if}></option>
        {foreach from=$account item=item}
        <option value="{$item.account_id}"{if isset($smarty.get.account_id) and $smarty.get.account_id eq $item.account_id} selected="selected"{/if}>{$item.account_name}</option>
        {/foreach}
        </select>
        </td>
        <td>　　　</td>
        <td>
        <b>店舗コード</b>　<input type="text" name="shop_id" value="{$shop_id}" class="frmtxt_search" />
        </td>
        <td>　　　</td>
        <td>
        <input type="submit" value="検索" class="btn_search" />
        </td>
        <td>　　　　</td>
        <td>
        <a href="{$current_url}" class="btn_showall">全件表示</a>
        </td>
        </tr>
        </table>
        </form>
        </div>