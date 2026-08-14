    <div id="side">
    	<ul id="navi">
            
{if $category eq 'users'}
	<li><a href="/manager/" class="navi01{if $navi_type eq 1}_a{/if}">トップ</a></li>
{else}
	<li><a href="/manager/" class="navi01{if $navi_type eq 1}_a{/if}">メニューを閉じる</a></li>
{/if}
            
{if $category eq 'aggregate'}
    <li><a href="/manager/account_info_list.php" class="navi01_a">医療機関アカウント登録/編集</a></li>
    <li><a href="/manager/patient_info_list.php" class="navi01_a">患者・口座　登録/編集</a></li>
    <!-- <li><a href="/manager/product_master_list.php" class="navi01_a">レセプトッチェック</a></li>  -->
    <li><a href="/manager/receipt_select.php" class="navi01_a">レセプトデータ取込</a></li>
    <!-- <li><a href="/manager/product_master_list.php" class="navi01_a">取引</a></li>
    <li><a href="/manager/product_master_list.php" class="navi01_a">GMOデータ作成</a></li>
    <li><a href="/manager/product_master_list.php" class="navi01_a">GMOデータ取込</a></li>
    <li><a href="/manager/product_master_list.php" class="navi01_a">請求書/領収書発行</a></li> -->
{else}
    <li><a href="/manager/account_info_list.php" class="navi01">管理メニュー</a></li>
{/if}
        </ul>
    
    </div>

	<div class="clear"></div>