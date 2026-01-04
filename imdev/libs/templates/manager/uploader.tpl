<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>クルテルワン管理ツール</title>
{include file="common/head_inc.tpl"}
<script type="text/javascript">
{literal}
$(function(){
	$("[name=file_type]").change(function(){
		$("[name=form]").submit();
	});
});
{/literal}
</script>
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">

<div id="main">
    <div class="content">

        <h2 class="title_name">CSVアップローダー</h2>

        {if isset($message) and "" ne $message}
        <div id="errormsg">{$message}</div>
        {/if}

        <div class="searcharea">
            <form action="/manager/uploader.php" method="POST" name="form" enctype="multipart/form-data">
                <table class="searcharea_tbl" width="960">
                    <tr>
                        <td width="150">
                            <select name="file_type">
                            {foreach from=$file_type_list key=value item=label}
                            <option value="{$value}"{if isset($file_type) and $file_type eq $value} selected="selected"{/if}>{$label}</option>
                            {/foreach}
                            </select>
                        </td>
                        <td>
                            <input type="file" name="ul_file" />
                        </td>
                        <td width="120">
                            <input type="submit" value="アップロード" class="btn_search" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>

		<div class="list_area" style="overflow-x: scroll">
			<table class="list_body">
			    <tr>
			        {foreach from=$header_list item=header_label}
			        <th nowrap="nowrap">{$header_label}</th>
			        {/foreach}
			    </tr>
			    {foreach from=$data_list item=data_row}
			    <tr>
			        {foreach from=$data_row item=data_value}
			        <td>{$data_value}</td>
			        {/foreach}
			    </tr>
			    {/foreach}
			</table>
		</div>

    </div><!-- .content -->
</div><!-- #main -->

{include file="common/sidebar.tpl"}

</div>
</body>
</html>
