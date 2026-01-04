<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>レセプトデータの取り込み</title>
{include file="common/head_inc.tpl"}
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">


    <div class="content">
    	{*<a href="./syukei.php">RESTY集計</a>*}


        <div id="breadcrumb">
        <a href="./">トップページ</a>&nbsp;&gt;&nbsp;レセプトデータ取り込み

        </div>





<h2 class="title_name">【内科】レセプトデータの取り込み</h2>



    <form method="post" action="recept-upload-im.php" enctype="multipart/form-data">

<div align="center">
        <br />

        <input type="file" name="upfile" size="30" />

        <br /><br />

        <input type="submit" value="アップロード" />
</div>

    </form>

<br /><br />

    <h2 class="title_name">【内科】介護保険レセプトデータの取り込み</h2>

    <form method="post" action="kaigo-recept-upload-003.php" enctype="multipart/form-data">

<div align="center">
        <br />

        <input type="file" name="upfile" size="30" />
        <br />

        <br />
        <input type="submit" value="アップロード" />
</div>
    </form>


{if isset($smarty.get.testview)}
<br><br>
<h2 class="title_name">【ロボペイの結果をロボペイフォーマットCSVファイルから取り込み処理】</h2>

<form method="post" action="csv-upload.php" enctype="multipart/form-data">

<div align="center">
    <br />

    <input type="file" name="upfile" size="30" />

    <br /><br />

    <input type="submit" value="アップロード" />
</div>

</form>
{/if}

    </div><!-- content -->


{* include file="common/sidebar.tpl" *}

</div>
</body>
</html>
