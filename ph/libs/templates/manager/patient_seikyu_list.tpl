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


        <div id="breadcrumb">
        <a href="./">トップページ</a>&nbsp;&gt;&nbsp;患者請求情報一覧
        </div>

<h2 class="title_name">{$item.patient_name}様 請求情報一覧</h2>

<a href="patient_info.php" class="btn">新規登録</a>

<br /><br />

{$data.count}件中 {$data.count_from}〜{$data.count_to}件を表示<br>
        <table class="list_body">
            
        <tr>
        <th width="30" align="center">患者番号</th>
        <th width="50">患者氏名</th>
        <th width="50">診療年月</th>
        <th width="50">請求金額</th>
        <th width="20">金融機関コード</th>
        <th width="15">支店コード</th>
        <th width="20">種別</th>
        <th width="80">口座番号</th>
        <th width="50">口座名義</th>
        <th width="">RP顧客番号</th>
        </tr>
            
        {foreach from=$data.result item=item}
        <tr style="border-bottom:1px solid #999;">
        <td>{$item.original_pid}</td>
        <td>{$item.name}</td>
        <td>{$item.srd}</td>
        <td>{$item.test}</td>
        <td>{$item.bac}</td>
        <td>{$item.brc}</td>
        <td>{$item.classification}</td>
        <td>{$item.account_number}</td>
        <td>{$item.holder_name}</td>
        {if $item.rp_cid == 0}
            <td><a href="payment-test-exe.php?original_pid={$item.original_pid}&
                req_type=2&nm={$item.name}&bac={$item.bac}&brc={$item.brc}&
                atype={$item.classification}&anum={$item.account_number}&
                anm={$item.holder_name}&
                po={$item.postal_code}&pre={$item.prefecture}&
                ad1={$item.address1}&ad2={$item.address2}">未登録</a>
            </td>
        {else}
            <td>{$item.rp_cid}</td>
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
