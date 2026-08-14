<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>患者情報登録</title>

{include file="common/head_inc.tpl"}
<script src="/js/ajaxzip3.js" charset="UTF-8"></script>
<script>
{literal}
function zen2Han(){
  var mae = $("#holder_name").val();
  let zen = new Array(
     'ア','イ','ウ','エ','オ','カ','キ','ク','ケ','コ'
    ,'サ','シ','ス','セ','ソ','タ','チ','ツ','テ','ト'
    ,'ナ','ニ','ヌ','ネ','ノ','ハ','ヒ','フ','ヘ','ホ'
    ,'マ','ミ','ム','メ','モ','ヤ','ヰ','ユ','ヱ','ヨ'
    ,'ラ','リ','ル','レ','ロ','ワ','ヲ','ン'
    ,'ガ','ギ','グ','ゲ','ゴ','ザ','ジ','ズ','ゼ','ゾ'
    ,'ダ','ヂ','ヅ','デ','ド','バ','ビ','ブ','ベ','ボ'
    ,'パ','ピ','プ','ペ','ポ'
    ,'ァ','ィ','ゥ','ェ','ォ','ャ','ュ','ョ','ッ'
    ,'゛','°','、','。','「','」','ー','・'
  );

  let han = new Array(
     'ｱ','ｲ','ｳ','ｴ','ｵ','ｶ','ｷ','ｸ','ｹ','ｺ'
    ,'ｻ','ｼ','ｽ','ｾ','ｿ','ﾀ','ﾁ','ﾂ','ﾃ','ﾄ'
    ,'ﾅ','ﾆ','ﾇ','ﾈ','ﾉ','ﾊ','ﾋ','ﾌ','ﾍ','ﾎ'
    ,'ﾏ','ﾐ','ﾑ','ﾒ','ﾓ','ﾔ','ｲ','ﾕ','ｴ','ﾖ'
    ,'ﾗ','ﾘ','ﾙ','ﾚ','ﾛ','ﾜ','ｦ','ﾝ'
    ,'ｶﾞ','ｷﾞ','ｸﾞ','ｹﾞ','ｺﾞ','ｻﾞ','ｼﾞ','ｽﾞ','ｾﾞ','ｿﾞ'
    ,'ﾀﾞ','ﾁﾞ','ﾂﾞ','ﾃﾞ','ﾄﾞ','ﾊﾞ','ﾋﾞ','ﾌﾞ','ﾍﾞ','ﾎﾞ'
    ,'ﾊﾟ','ﾋﾟ','ﾌﾟ','ﾍﾟ','ﾎﾟ'
    ,'ｧ','ｨ','ｩ','ｪ','ｫ','ｬ','ｭ','ｮ','ｯ'
    ,'ﾞ','ﾟ','､','｡','｢','｣','ｰ','･'
  );

  let ato = "";

  for (let i=0;i<mae.length;i++){
    let maechar = mae.charAt(i);
    let zenindex = zen.indexOf(maechar);
    if(zenindex >= 0){
      maechar = han[zenindex];
    }
    ato += maechar;
  }
  $("#holder_name").val(ato);
  //return ato;
}
function setbank(id,bank_name){
  $("#bac").val(id);
  $("#bank_name").text(bank_name);

  $("#brc").val("");
  $("#branch_name").text("");
  $("#branch_data").text("");
  var $loading = $(".loading");
  $.ajax({
    url: 'bank2branch.php?bank_id='+id,
    type: 'GET',
    dataType: 'json',
    // フォーム要素の内容をハッシュ形式に変換

    timeout: 5000,
    beforeSend:function(){
			$loading.removeClass("is-hide");
		},
  })
  .done(function(data) {
    /*
    $.each(data, function(i, item){
      $("#branch_data").prepend(item.branch_id+"---"+item.name + '<br>');
    });
    */
    $loading.addClass("is-hide");
    var len = data.length;
    var branch_html = "";
    for(var i=0; i<len; i++){
      branch_html += "<a href=\"\" onclick=\"setbranch('"+data[i].branch_id+"','"+data[i].name+"');return false;\">"+data[i].name+"</a><br>";
      $("#branch_data").prepend(branch_html);
    }

  })
  .fail(function() {
      // 通信失敗時の処理を記述
  });

}
function setbranch(id,branch_name){
  $("#brc").val(id);
  $("#branch_name").text(branch_name);
}
{/literal}
</script>
<style>
{literal}
.is-hide{display:none;}
{/literal}
</style>
</head>

<body>
{include file="common/header.tpl"}

<div id="wrap">
    <div class="content">
        <div id="breadcrumb">
            <a href="./">トップページ</a>&nbsp;&gt;&nbsp;<a href="./patient_info_list.php">患者情報一覧</a>&nbsp;&gt;&nbsp;患者情報登録
        </div>

    <h2 class="title_name">患者情報登録</h2>

        <form method="post">
            <input type="hidden" name="original_pid" value="{$data.original_pid}" />

            <div class="tbldetail_wrap" align="center">
            <table class="tbldetail">

            <tr><th>患者番号</th>
            <td class="bg_odd"><input type="text" name="original_pid" value="{$data.original_pid}" class="frmtxt" style="width:150px;" /></td></tr>

            <tr><th>患者名</th>
            <td class="bg_odd"><input type="text" name="patient_name" value="{$data.patient_name}" class="frmtxt" style="width:200px;" /></td></tr>

            <tr><th>生年月日</th>
            <td class="bg_odd"><input type="text" name="patient_birth" value="{$data.patient_birth}" class="frmtxt" style="width:200px;" /></td></tr>

            <tr><th>被保険者番号（医療）</th>
            <td class="bg_odd"><input type="text" name="patient_hihoban" value="{$data.patient_hihoban}" class="frmtxt" style="width:200px;" /></td></tr>

            <tr><th>被保険者番号（介護）</th>
            <td class="bg_odd"><input type="text" name="patient_kaigo_hihoban" value="{$data.patient_kaigo_hihoban}" class="frmtxt" style="width:200px;" /></td></tr>

            <tr><th>メールアドレス</th>
            <td class="bg_odd"><input type="text" name="email" value="{$data.email}" class="frmtxt" style="width:200px;" /></td></tr>

            <!---住所情報--->

            <tr><th rowspan="5">メイン住所<br><br><a href="change-address.php?original_pid={$data.original_pid}" class="change_button">メイン⇔サブ住所切替</a></th>

            <td class="bg_odd">送付先氏名：<input type="text" name="shipto_name" value="{$data.shipto_name}" class="frmtxt" style="width:200px;" /></td></tr>

            <td class="bg_odd">郵便番号：<input type="text" name="postal_code" value="{$data.postal_code}" class="frmtxt" style="width:70px;" onchange="AjaxZip3.zip2addr(this,'postal_code2','prefecture','address1','address2');" /> - <input type="text" name="postal_code2" value="{$data.postal_code2}" class="frmtxt" style="width:100px;" onchange="AjaxZip3.zip2addr('postal_code',this,'prefecture','address1','address2');" /></td></tr>

            <tr>
            <td class="bg_odd">都道府県：
              <select id="prefecture" name="prefecture">
              <option value=""{if $data.prefecture eq ""} selected{/if}>都道府県</option>
              <option value="1"{if $data.prefecture eq 1} selected{/if}>北海道</option>
              <option value="2"{if $data.prefecture eq 2} selected{/if}>青森県</option>
              <option value="3"{if $data.prefecture eq 3} selected{/if}>岩手県</option>
              <option value="4"{if $data.prefecture eq 4} selected{/if}>宮城県</option>
              <option value="5"{if $data.prefecture eq 5} selected{/if}>秋田県</option>
              <option value="6"{if $data.prefecture eq 6} selected{/if}>山形県</option>
              <option value="7"{if $data.prefecture eq 7} selected{/if}>福島県</option>
              <option value="8"{if $data.prefecture eq 8} selected{/if}>茨城県</option>
              <option value="9"{if $data.prefecture eq 9} selected{/if}>栃木県</option>
              <option value="10"{if $data.prefecture eq 10} selected{/if}>群馬県</option>
              <option value="11"{if $data.prefecture eq 11} selected{/if}>埼玉県</option>
              <option value="12"{if $data.prefecture eq 12} selected{/if}>千葉県</option>
              <option value="13"{if $data.prefecture eq 13} selected{/if}>東京都</option>
              <option value="14"{if $data.prefecture eq 14} selected{/if}>神奈川県</option>
              <option value="15"{if $data.prefecture eq 15} selected{/if}>新潟県</option>
              <option value="16"{if $data.prefecture eq 16} selected{/if}>富山県</option>
              <option value="17"{if $data.prefecture eq 17} selected{/if}>石川県</option>
              <option value="18"{if $data.prefecture eq 18} selected{/if}>福井県</option>
              <option value="19"{if $data.prefecture eq 19} selected{/if}>山梨県</option>
              <option value="20"{if $data.prefecture eq 20} selected{/if}>長野県</option>
              <option value="21"{if $data.prefecture eq 21} selected{/if}>岐阜県</option>
              <option value="22"{if $data.prefecture eq 22} selected{/if}>静岡県</option>
              <option value="23"{if $data.prefecture eq 23} selected{/if}>愛知県</option>
              <option value="24"{if $data.prefecture eq 24} selected{/if}>三重県</option>
              <option value="25"{if $data.prefecture eq 25} selected{/if}>滋賀県</option>
              <option value="26"{if $data.prefecture eq 26} selected{/if}>京都府</option>
              <option value="27"{if $data.prefecture eq 27} selected{/if}>大阪府</option>
              <option value="28"{if $data.prefecture eq 28} selected{/if}>兵庫県</option>
              <option value="29"{if $data.prefecture eq 29} selected{/if}>奈良県</option>
              <option value="30"{if $data.prefecture eq 30} selected{/if}>和歌山県</option>
              <option value="31"{if $data.prefecture eq 31} selected{/if}>鳥取県</option>
              <option value="32"{if $data.prefecture eq 32} selected{/if}>島根県</option>
              <option value="33"{if $data.prefecture eq 33} selected{/if}>岡山県</option>
              <option value="34"{if $data.prefecture eq 34} selected{/if}>広島県</option>
              <option value="35"{if $data.prefecture eq 35} selected{/if}>山口県</option>
              <option value="36"{if $data.prefecture eq 36} selected{/if}>徳島県</option>
              <option value="37"{if $data.prefecture eq 37} selected{/if}>香川県</option>
              <option value="38"{if $data.prefecture eq 38} selected{/if}>愛媛県</option>
              <option value="39"{if $data.prefecture eq 39} selected{/if}>高知県</option>
              <option value="40"{if $data.prefecture eq 40} selected{/if}>福岡県</option>
              <option value="41"{if $data.prefecture eq 41} selected{/if}>佐賀県</option>
              <option value="42"{if $data.prefecture eq 42} selected{/if}>長崎県</option>
              <option value="43"{if $data.prefecture eq 43} selected{/if}>熊本県</option>
              <option value="44"{if $data.prefecture eq 44} selected{/if}>大分県</option>
              <option value="45"{if $data.prefecture eq 45} selected{/if}>宮崎県</option>
              <option value="46"{if $data.prefecture eq 46} selected{/if}>鹿児島県</option>
              <option value="47"{if $data.prefecture eq 47} selected{/if}>沖縄県</option>
              </select>
              {*<input type="text" name="prefecture" value="{$data.prefecture}" class="frmtxt" style="width:50px;" />*}</td></tr>

            <tr>
            <td class="bg_odd">住所1：<input type="text" name="address1" value="{$data.address1}" class="frmtxt" style="width:300px;" /></td></tr>

            <tr>
            <td class="bg_odd">住所2：<input type="text" name="address2" value="{$data.address2}" class="frmtxt" style="width:300px;" /></td></tr>

            <tr><th rowspan="5">サブ住所<br><br><a href="change-address.php?original_pid={$data.original_pid}" class="change_button">メイン⇔サブ住所切替</a></th>

            <td class="bg_odd">送付先氏名：<input type="text" name="shipto_name_sub" value="{$data.shipto_name_sub}" class="frmtxt" style="width:200px;" /></td></tr>

            <td class="bg_odd">郵便番号：<input type="text" name="postal_code_sub" value="{$data.postal_code_sub}" class="frmtxt" style="width:70px;" onchange="AjaxZip3.zip2addr(this,'postal_code_sub2','prefecture_sub','address1_sub','address2_sub');" /> - <input type="text" name="postal_code_sub2" value="{$data.postal_code_sub2}" class="frmtxt" style="width:70px;" onchange="AjaxZip3.zip2addr('postal_code_sub',this,'prefecture_sub','address1_sub','address2_sub');" /></td></tr>

            <tr>
            <td class="bg_odd">都道府県：
              <select id="prefecture_sub" name="prefecture_sub">
              <option value=""{if $data.prefecture_sub eq ""} selected{/if}>都道府県</option>
              <option value="1"{if $data.prefecture_sub eq 1} selected{/if}>北海道</option>
              <option value="2"{if $data.prefecture_sub eq 2} selected{/if}>青森県</option>
              <option value="3"{if $data.prefecture_sub eq 3} selected{/if}>岩手県</option>
              <option value="4"{if $data.prefecture_sub eq 4} selected{/if}>宮城県</option>
              <option value="5"{if $data.prefecture_sub eq 5} selected{/if}>秋田県</option>
              <option value="6"{if $data.prefecture_sub eq 6} selected{/if}>山形県</option>
              <option value="7"{if $data.prefecture_sub eq 7} selected{/if}>福島県</option>
              <option value="8"{if $data.prefecture_sub eq 8} selected{/if}>茨城県</option>
              <option value="9"{if $data.prefecture_sub eq 9} selected{/if}>栃木県</option>
              <option value="10"{if $data.prefecture_sub eq 10} selected{/if}>群馬県</option>
              <option value="11"{if $data.prefecture_sub eq 11} selected{/if}>埼玉県</option>
              <option value="12"{if $data.prefecture_sub eq 12} selected{/if}>千葉県</option>
              <option value="13"{if $data.prefecture_sub eq 13} selected{/if}>東京都</option>
              <option value="14"{if $data.prefecture_sub eq 14} selected{/if}>神奈川県</option>
              <option value="15"{if $data.prefecture_sub eq 15} selected{/if}>新潟県</option>
              <option value="16"{if $data.prefecture_sub eq 16} selected{/if}>富山県</option>
              <option value="17"{if $data.prefecture_sub eq 17} selected{/if}>石川県</option>
              <option value="18"{if $data.prefecture_sub eq 18} selected{/if}>福井県</option>
              <option value="19"{if $data.prefecture_sub eq 19} selected{/if}>山梨県</option>
              <option value="20"{if $data.prefecture_sub eq 20} selected{/if}>長野県</option>
              <option value="21"{if $data.prefecture_sub eq 21} selected{/if}>岐阜県</option>
              <option value="22"{if $data.prefecture_sub eq 22} selected{/if}>静岡県</option>
              <option value="23"{if $data.prefecture_sub eq 23} selected{/if}>愛知県</option>
              <option value="24"{if $data.prefecture_sub eq 24} selected{/if}>三重県</option>
              <option value="25"{if $data.prefecture_sub eq 25} selected{/if}>滋賀県</option>
              <option value="26"{if $data.prefecture_sub eq 26} selected{/if}>京都府</option>
              <option value="27"{if $data.prefecture_sub eq 27} selected{/if}>大阪府</option>
              <option value="28"{if $data.prefecture_sub eq 28} selected{/if}>兵庫県</option>
              <option value="29"{if $data.prefecture_sub eq 29} selected{/if}>奈良県</option>
              <option value="30"{if $data.prefecture_sub eq 30} selected{/if}>和歌山県</option>
              <option value="31"{if $data.prefecture_sub eq 31} selected{/if}>鳥取県</option>
              <option value="32"{if $data.prefecture_sub eq 32} selected{/if}>島根県</option>
              <option value="33"{if $data.prefecture_sub eq 33} selected{/if}>岡山県</option>
              <option value="34"{if $data.prefecture_sub eq 34} selected{/if}>広島県</option>
              <option value="35"{if $data.prefecture_sub eq 35} selected{/if}>山口県</option>
              <option value="36"{if $data.prefecture_sub eq 36} selected{/if}>徳島県</option>
              <option value="37"{if $data.prefecture_sub eq 37} selected{/if}>香川県</option>
              <option value="38"{if $data.prefecture_sub eq 38} selected{/if}>愛媛県</option>
              <option value="39"{if $data.prefecture_sub eq 39} selected{/if}>高知県</option>
              <option value="40"{if $data.prefecture_sub eq 40} selected{/if}>福岡県</option>
              <option value="41"{if $data.prefecture_sub eq 41} selected{/if}>佐賀県</option>
              <option value="42"{if $data.prefecture_sub eq 42} selected{/if}>長崎県</option>
              <option value="43"{if $data.prefecture_sub eq 43} selected{/if}>熊本県</option>
              <option value="44"{if $data.prefecture_sub eq 44} selected{/if}>大分県</option>
              <option value="45"{if $data.prefecture_sub eq 45} selected{/if}>宮崎県</option>
              <option value="46"{if $data.prefecture_sub eq 46} selected{/if}>鹿児島県</option>
              <option value="47"{if $data.prefecture_sub eq 47} selected{/if}>沖縄県</option>
              </select>

              {*<input type="text" name="prefecture_sub" value="{$data.prefecture_sub}" class="frmtxt" style="width:50px;" />*}</td></tr>

            <tr>
            <td class="bg_odd">住所1：<input type="text" name="address1_sub" value="{$data.address1_sub}" class="frmtxt" style="width:300px;" /></td></tr>

            <tr>
            <td class="bg_odd">住所2：<input type="text" name="address2_sub" value="{$data.address2_sub}" class="frmtxt" style="width:300px;" /></td></tr>



            <!---口座情報--->

            <tr><th>金融機関コード</th>
            <td class="bg_odd">
              <table>
                <tr>
                  <td>
                    <input type="text" id="bac" name="bac" value="{$data.bac}" class="frmtxt" style="width:80px;" />&nbsp;<span id="bank_name">{$data.bank_name}</span>
                  </td>
                  <td>
                    <div id="display_bank" style="height:200px;overflow:scroll;border:1px solid #ccc;padding:5px;">
{foreach from = $bank_master item=item}
<a href="" onclick="setbank('{$item.bank_id}','{$item.name}');return false;">{$item.name}</a><br>
{/foreach}
                    </div>
                  </td>
                </tr>
              </table>
            </td></tr>

            <tr><th>支店コード</th>
            <td class="bg_odd">
              <table>
                <tr>
                  <td>
                    <input type="text" id="brc" name="brc" value="{$data.brc}" class="frmtxt" style="width:50px;" />&nbsp;<span id="branch_name">{$data.branch_name}</span>
                  </td>
                  <td>
                    <img src="../images/loading.gif" width="200" class="loading is-hide">
                    <div id="branch_data" style="height:200px;overflow:scroll;border:1px solid #ccc;padding:5px;"></div>
                  </td>
                </tr>
              </table>
              ※銀行は3桁　ゆうちょは5桁
            </td></tr>

            <tr><th>種別</th>
            <td class="bg_odd"><input type="radio" name="classification" value="1"{if $data.classification eq 1} checked{/if} />&nbsp;普通　<input type="radio" name="classification" value="2"{if $data.classification eq 2} checked{/if} />&nbsp;当座</td></tr>

            <tr><th>口座番号</th>
            <td class="bg_odd"><input type="text" name="account_number" value="{$data.account_number}" class="frmtxt" style="width:200px;" />

              <div style="margin-top:5px;">※ゆうちょは末尾の1を抜いて7桁<br>
              ※7桁未満は先頭に0を付けて7桁にする</div>
            </td></tr>

            <tr><th>口座名義</th>
            <td class="bg_odd"><input type="text" id="holder_name" name="holder_name" value="{$data.holder_name}" class="frmtxt" style="width:300px;" onchange="zen2Han();" /></td></tr>


            <tr><th>その他1</th>
            <td class="bg_odd"><input type="text" name="others" value="{$data.others}" class="frmtxt" style="width:200px;" /></td></tr>
            <tr><th>その他2</th>
            <td class="bg_odd"><input type="text" name="others2" value="{$data.others2}" class="frmtxt" style="width:200px;" /></td></tr>
            <tr><th>その他3</th>
            <td class="bg_odd"><input type="text" name="others3" value="{$data.others3}" class="frmtxt" style="width:200px;" /></td></tr>

            <tr><th>口座振替</th>
            <td class="bg_odd">
                <input type="radio" name="direct_debit" value="0" {if $data.direct_debit == 0} checked = "checked"{/if}>する
                <input type="radio" name="direct_debit" value="1" {if $data.direct_debit == 1} checked = "checked"{/if}>しない
            </td></tr>
            <tr><th>請求書発行</th>
            <td class="bg_odd">
                <input type="radio" name="invoice_output" value="0" {if $data.invoice_output == 0} checked = "checked"{/if}>する
                <input type="radio" name="invoice_output" value="1" {if $data.invoice_output == 1} checked = "checked"{/if}>しない
            </td></tr>
            <tr><th>領収書発行</th>
            <td class="bg_odd">
                <input type="radio" name="receipt_output" value="0" {if $data.receipt_output == 0} checked = "checked"{/if}>する
                <input type="radio" name="receipt_output" value="1" {if $data.receipt_output == 1} checked = "checked"{/if}>しない
            </td></tr>
            <tr><th>手続終了（逝去など）</th>
            <td class="bg_odd">
                <input type="radio" name="disp" value="0" {if $data.disp == 0} checked = "checked"{/if}>しない　
                <input type="radio" name="disp" value="2" {if $data.disp == 2} checked = "checked"{/if}>手続終了（回収管理から除く）
            </td></tr>


            <!---施設情報--->

            <tr><th>施設名</th>
            <td class="bg_odd"><input type="text" name="irkk_name" value="{$data.irkk_name}" class="frmtxt" style="width:200px;" /></td></tr>

            <tr><th>施設住所</th>
            <td class="bg_odd"><input type="text" name="irkk_address" value="{$data.irkk_address}" class="frmtxt" style="width:400px;" /></td></tr>



            </table>
            </div>

            <br /><br />

            {if $data.original_pid == ""}
            <div align="center">
            <input type="submit" name="db_only" class="btn_submit" value="新規登録" />
            </div>
            <br />

                {if $data.rp_cid == 0}
                <div align="center">
                <input type="submit" name="rp_new" class="btn_submit" value="新規登録＋RP新規登録" />
                </div>
                <br />
                {/if}

            {else}

            <div align="center">
            <input type="submit" name="db_only" class="btn_submit" value="情報更新" />
            </div>
            <br />

                {if $data.rp_cid == 0}
                    <div align="center">
                    <input type="submit" name="rp_new" class="btn_submit" value="更新＋RP新規登録" />
                    </div>
                    <br />
                {else}
                    <div align="center">
                    <input type="submit" name="rp_update" class="btn_submit" value="更新＋RP情報更新" />
                    </div>
                    <br />
                {/if}

            {/if}

            <div align="center">
            <br><a href="patient_info_list.php">患者情報一覧に戻る</a><br>
            </div>

        </form>

    </div><!-- content -->


{*include file="common/sidebar.tpl"*}

</div>
</body>
</html>
