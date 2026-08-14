<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1.0">
<meta name="Description" content="" />
<meta name="Keywords" content="" />
<title>患者情報一覧</title>
{include file="common/head_inc.tpl"}
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">


    <div class="content">
    	{*<a href="./syukei.php">RESTY集計</a>*}


        <div id="breadcrumb">
        <a href="./">トップページ</a>&nbsp;&gt;&nbsp;患者情報一覧
        </div>

<h2 class="title_name">患者情報一覧</h2>
<div style="margin-bottom:30px;">
<form action="./patient_info_list.php" method="get">
  <table>
    <tr>
<td>患者氏名：</td><td><input type="text" name="patient_name" value="{$params.patient_name}"></td></tr>
<tr>
<td>医療保険被保険者番号：</td><td><input type="text" name="patient_hihoban" value="{$params.patient_hihoban}"></td></tr>
<tr>
<td>介護保険被保険者番号：</td><td><input type="text" name="patient_kaigo_hihoban" value="{$params.patient_kaigo_hihoban}"></td></tr>
</table>
<br>
<input type="submit" value="検索">
</div>

</form>

<a href="patient_info.php" class="btn">新規登録</a>

<br /><br />

{$data.count}件中 {$data.count_from}〜{$data.count_to}件を表示<br>

<div class="marb30"></div>
<div align="center">
{$pagination}
</div>
<div class="marb20"></div>
        <table class="list_body">

        <tr>
        <th width="20"></th>
        <th width="30" align="center">患者番号</th>
        <th width="50">患者氏名</th>
        <th width="50">医療保険被保険者番号</th>
        <th width="50">介護保険被保険者番号</th>
        <th width="50">口座情報</th>
        <th width="100">住所情報</th>
        <th width="">請求情報</th>
        <th>自由診療／物販</th>
        <th width="">RP顧客番号</th>
        </tr>

        {foreach from=$data.result item=item}
        <tr style="border-bottom:1px solid #999;">
        <td align="center">
          <div class="marb10"><a href="patient_info.php?original_pid={$item.original_pid}" target="_blank">編集</a></div>

          <div><a href="delete_patient.php?original_pid={$item.original_pid}" onclick="return confirm('削除します。本当によろしいですか？');">削除</a></div>
        </td>
        <td>{$item.original_pid}</td>
        <td><a href="patient_info.php?original_pid={$item.original_pid}" target="_blank">{$item.patient_name}</a></td>
        <td><a href="nayose_iryo.php?original_pid={$item.original_pid}" target="_blank">{$item.patient_hihoban}</td>
        <td><a href="nayose_kaigo.php?original_pid={$item.original_pid}" target="_blank">{$item.patient_kaigo_hihoban}</td>
        <td>{$item.bac} {$item.brc}<br>{$item.classification}<br>{$item.account_number}<br>{$item.holder_name}</td>
        <td>{$item.postal_code}<br>{$item.prefecture}<br>{$item.address1}<br>{$item.address2}</td>
        <td><a href="seikyu_info_list.php?original_pid={$item.original_pid}" target="_blank">月別一覧</a></td>
        <td><a href="./appendix-list.php?original_pid={$item.original_pid}" target="_blank">自由診療／物販</a></td>
        {if $item.rp_cid == 0}
            <td><!--<a href="payment-test-exe.php?original_pid={$item.original_pid}&req_type=1&nm={$item.patient_name}&bac={$item.bac}&brc={$item.brc}&atype={$item.classification}&anum={$item.account_number}&anm={$item.holder_name}&po={$item.postal_code}&pre={$item.prefecture}&ad1={$item.address1}&ad2={$item.address2}">未登録</a>-->未登録</td>
        {else}
            <td>{$item.rp_cid}<!--<br><a href="payment-test-exe.php?original_pid={$item.original_pid}&req_type=4&cid={$item.rp_cid}&nm={$item.patient_name}&bac={$item.bac}&brc={$item.brc}&atype={$item.classification}&anum={$item.account_number}&anm={$item.holder_name}&po={$item.postal_code}&pre={$item.prefecture}&ad1={$item.address1}&ad2={$item.address2}">口座情報更新</a>--></td>
        {/if}
        </tr>
        {/foreach}
        </table>

        <div class="marb50"></div>
        <div align="center">
        {$pagination}
        </div>


    </div><!-- content -->


{*include file="common/sidebar.tpl"*}

</div>
</body>
</html>
