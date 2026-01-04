<?php 
$html =<<<EOF
<div class="wrap"></div><p class="header-left">請求書｜訪問歯科診療</p>
<p class="patient-address">〒000-0000<br>都道府県<br>市区<br>町村　〇〇番地<br>建物名　〇〇〇〇号</p><p class="patient-name">郵送先　氏名 様<br><span class="patient-name-sub">（者　氏名　様分）</span></p><p class="patient-id"><span>No.0000000-0000000</span></p><p class="notes">※診療費(自己負担金）を、ご請求申し上げます。<br>
                        ※保険証の変更等ございましたらご連絡いただきますよう宜しくお願い申し上げます。</p><p class="header-right">医療機関名 <span class="header-right-sub">※お問い合わせはこちらへ</span></p>
                        <p class="irkk-name">医療機関名</p><p class="irkk-address">〒000-0000<br>都道府県<br>市区<br>町村　〇〇番地<br>000-000-0000</p><br><p class="irkk-account">   </p><p class="irkk-account2">（口座振替ご利用の方は、振り込みは不要です）</p><p class="fold-point">▶</p><p id="shinryo-month">2022年02月分</p><p id="total-copayment">ご請求額　2,654 円</p><table class='disp_table' id="hoken-table"><tr>
            <th rowspan="6" class="side-header border_rb">保険</th><th class="color333 hoken-col border_rb">初・再診料</th>
            <th class="color333 hoken-col border_rb">医学管理等</th>
            <th class="color333 hoken-col border_rb">在宅医療</th>
            <th class="color333 hoken-col border_rb">検査</th>
            <th class="color333 hoken-col border_rb">画像診断</th>
            <th class="color333 hoken-col border_rb">投薬</th></tr><tr>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border_rb">2,780点</td>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border_rb">0点</td></tr><tr>
            <th class="color333 border_rb">注射</th>
            <th class="color333 font18 border_rb">リハビリテーション</th>
            <th class="color333 border_rb">処置</th>
            <th class="color333 border_rb">手術</th>
            <th class="color333 border_rb">麻酔</th>
            <th class="color333 border_rb">放射線治療</th></tr><tr>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border_rb">0点</td></tr><tr>
            <th class="color333 font16 border_rb"></th>
            <th class="color333 border_rb"></th>
            <th class="color333 border_rb"></th>
            <th class="color333 border-border-top border_b">合計</th>
            <th class="color333 border-border-top font14 border_b">居宅療養管理指導(介護保険)</th>
            <th class="color333 border-border-top border_b">一部負担金</th></tr><tr>
            <td class="tensu-row border_rb">625点</td>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border_rb">0点</td>
            <td class="tensu-row border-border-bottom">3,405点</td>
            <td class="tensu-row border-border-bottom">1,754単位</td>
            <td class="tensu-row border-border-bottom">2,254円</td></tr></table><br/>
<div id="hokengai-table"><table class='disp_table'><tr><th rowspan="4" class="side-header border_rb">保険外負担</th></tr><th class="hokengai-col border_rb">自由診療</th>
            <th class="hokengai-col border_rb">販売品</th>
            <th class="hokengai-col border_rb">その他</th></tr><tr><td class='border_r'>0円</td>
            <td class='border_r'>0円</td>
            <td class='border_r'>400円</td></tr><tr><td class="uchiwake border_rb"><br>
</td>
            <td class="uchiwake border_rb"><br>
</td>
            <td class='border_rb'>交通費12/8（200）12/24（200）
</td></tr></table></div><div id="misyu-kajo-table"><table class='disp_table'><tr><th class="color333 border_rb">前回未収金</th>
                <th class="color333 border_rb">前回過剰金</th>
                <th class="color333 border_rb">今回ご請求額</th></tr><tr><td class='border_r'>0円</td><td class='border_r'>0円</td><td class='border_r'>2,654円</td></tr><tr><td class='border_rb'>&nbsp;</td><td class='border_rb'>&nbsp;</td><td class='border_rb'>&nbsp;</td></tr></table></div><div class="clearfix"></div><br/>
<p id="shinryo-meisai">診療明細書</p><div id="iryo-meisai-table">
            <table class='disp_table'><tr><th colspan="5" class='border_rb'>医療保険</th></tr><tr>
            <th class="category-col border_b">部</th>
            <th class="border_b">項目</th>
            <th class="tensu-col border_b">点数</th>
            <th class="x-col border_b"></th>
            <th class="kaisu-col border_rb">回数</th></tr><tr><td colspan="5" class="date-row border_r">●2021年12月08日</td></tr><tr>
                <td class="category-col non-border">C</td>
                <td class="item-col non-border">訪問診療１（診療所）</td>
                <td class="tensu-col non-border">1,390</td>
                <td class="x-col non-border">×</td>
                <td class="kaisu-col border_r">1</td></tr><tr>
                <td class="category-col non-border">M</td>
                <td class="item-col non-border">咬合（有床義歯（少数歯欠損））</td>
                <td class="tensu-col non-border">97</td>
                <td class="x-col non-border">×</td>
                <td class="kaisu-col border_r">1</td></tr><tr>
                <td class="category-col non-border">M</td>
                <td class="item-col non-border">咬合（有床義歯（多数歯欠損））</td>
                <td class="tensu-col non-border">318</td>
                <td class="x-col non-border">×</td>
                <td class="kaisu-col border_r">1</td></tr><tr><td class="sum-row border_r" colspan="5"><p>小計:1,805点 　 0円 　 負担:0%</p></td></tr><tr><td colspan="5" class="date-row border_r">●2021年12月24日</td></tr><tr>
                <td class="category-col non-border">C</td>
                <td class="item-col non-border">訪問診療１（診療所）</td>
                <td class="tensu-col non-border">1,390</td>
                <td class="x-col non-border">×</td>
                <td class="kaisu-col border_r">1</td></tr><tr>
                <td class="category-col non-border">M</td>
                <td class="item-col non-border">試適（少数歯欠損）</td>
                <td class="tensu-col non-border">60</td>
                <td class="x-col non-border">×</td>
                <td class="kaisu-col border_r">1</td></tr><tr>
                <td class="category-col non-border">M</td>
                <td class="item-col non-border">試適（多数歯欠損）</td>
                <td class="tensu-col non-border">150</td>
                <td class="x-col non-border">×</td>
                <td class="kaisu-col border_r">1</td></tr><tr><td class="sum-row border_r" colspan="5"><p>小計:1,600点 　 0円 　 負担:0%</p></td></tr><tr><td colspan=5 class='border_rb'></td></tr></table></div><div id="iryo-meisai-table">
                <table class='disp_table'><tr><th colspan=5 class='border_rb'>介護保険</th></tr><tr>
                <th class="border_b meisai-title-row">項目</th>
                <th class="tensu-col border_b meisai-title-row">単位</th>
                <th class="x-col border_b meisai-title-row"></th>
                <th class="kaisu-col border_b meisai-title-row">回数</th>
                <th class="border_rb meisai-title-row">算定日</th></tr><tr><td class="item-col non-border">歯科衛生士等居宅療養Ⅰ</td><td class="tensu-col non-border">361</td>
                    <td class="x-col non-border">×</td>
                    <td class="kaisu-col non-border">2</td>
                    <td class="date-col border_r" align=center>8,24</td></tr><tr><td class="item-col non-border">歯科医師居宅療養管理指導Ⅰ</td><td class="tensu-col non-border">516</td>
                    <td class="x-col non-border">×</td>
                    <td class="kaisu-col non-border">2</td>
                    <td class="date-col border_r" align=center>8,24</td></tr><tr><td class="sum-row-kaigo border_rb" colspan="5"><p>小計:1,754単位　　 1,754円 　負担:10%</p></td></tr></table></div><div class="clearfix"></div><style>
                .wrap{
                position:relative;
                width: 1400px;
                height: 665px;
                }
                .header-left{
                position:absolute;
                top: 100px;
                left: 86.74px;
                width: 433.71px;
                background-color: #555;
                border-radius: 5px;
                font-size:24px;
                letter-spacing:0.25em;
                padding:5px;
                color: #fff;
                text-align:center;
                }
                .patient-address{
                position:absolute;
                top:202.4px;
                left:195.17px;
                font-size:20px;
                font-weight:bold;
                isplay: inline-block;
                vertical-align:top;
                }
                .patient-name{
                position:absolute;
                top:346.97px;
                left:195.17px;
                font-size:32px;
                font-weight:bold;
                }
                .patient-id{
                position:absolute;
                top:448.17px;
                left:195.17px;
                font-size:20px;
                font-weight:bold;
                }
                .notes{
                position:absolute;
                top:549.37px;
                left:104.57px;
                font-size:18px;
                }

                .header-right{
                position:absolute;
                top: 390.34px;
                right: 93.97px;
                width: 433.71px;
                background-color: #555;
                border-radius: 5px;
                font-size:24px;
                padding:5px;
                text-align:center;
                color: #fff;
                }
                .irkk-name{
                position:absolute;
                top: 448.17px;
                left: 990px;
                border-radius: 5px;
                font-size:18px;
                font-weight:bold;
                width:425px;
                letter-spacing:-1px;
                }
                .irkk-address{
                position:absolute;
                top:484.31px;
                left:990px;
                font-size:16px;
                font-weight:bold;
                width:425px;
                }
                .irkk-account{
                position:absolute;
                top:624px;
                left:990px;
                font-size:18px;
                font-weight:bold;
                }
                .irkk-account2{
                position:absolute;
                top:646px;
                left:990px;
                font-size:14px;
                font-weight:bold;
                }
                .fold-point{
                position:absolute;
                top:680px;
                left: 30px;
                font-size:32px;
                font-weight:bold;
                }

                .patient-name-sub{
                font-size: 20px;
                font-weight:bold;
                }
                .header-right-sub{
                font-size: 20px;
                }


                #shinryo-month{
                    position:absolute;
                    top:730px;
                    left:120px;
                    font-size:36px;
                    text-align:center;
                    width:400px;
                }
                #total-copayment{
                    margin: 30px auto;
                    font-size:36px;
                    text-align:center;
                    width:400px;
                    border-bottom:1px solid #262626;
                }
                #ryosyu-date{
                    position:absolute;
                    top:730px;
                    left: 1080px;
                    font-size: 20px;
                    text-align:center;
                }

                .disp_table{
                border-top:1px solid #262626;
                border-left:1px solid #262626;
                border-spacing:0;
                border-collapse:none;
                text-align:center;
                font-size:20px;
                }
                .disp_th,.disp_td{
                /*border-right:1px solid #262626;
                border-bottom:1px solid #262626;*/
                padding:5px;
                font-size:20px;
                }
                .border_rb{
                border-right:1px solid #262626;
                border-bottom:1px solid #262626;
                word-wrap: break-word;
                }
                .border_r{
                border-right:1px solid #262626;
                }
                .border_b{
                border-bottom:1px solid #262626;
                }
                th{
                background-color: #EEEDED;
                }
                th,td{padding:5px;}

                #hoken-table{
                table-layout: fixed;
                width: 1400px;
                }
                .hoken-col{
                width:15%;
                }
                .tensu-row{
                    height:60px;
                    font-size: 24px;
                }

                #hokengai-table{
                width:60%;
                float:left;
                padding-right:50px;
                }
                #hokengai-table table{
                table-layout: fixed;
                width:100%;
                }
                .hokengai-col{
                width:30%;
                }

                #misyu-kajo-table{
                width:auto;
                float:left;
                }
                #misyu-kajo-table table{
                width:100%;
                }

                .border-border-top{
                border-top:3px solid #262626;
                }
                .border-border-bottom{
                border-bottom:3px solid #262626;
                }
                .border-border-top,
                .border-border-bottom{
                border-left:3px solid #262626;
                border-right:3px solid #262626;
                }

                .side-header{
                width:3.8em;
                }
                .uchiwake{
                text-align:center;
                }

                #shinryo-meisai{
                font-size:36px;
                width:500px;
                margin: 50px auto 30px auto;
                text-align:center;
                border-bottom:3px solid #262626;
                }

                #iryo-meisai-table{
                width:665px;
                float:left;
                padding-right:30px;
                }
                #iryo-meisai-table table{
                width:100%;
                }
                .non-border{
                border:none;
                }
                .meisai-title-row{
                border-bottom:1px solid #262626;
                }
                .date-row{
                text-align:left;
                border-bottom:none;
                padding-bottom:0;
                }
                .category-col{width:80px}
                .item-col{text-align:left}
                .tensu-col{width:80px}
                .x-col{width:50px}
                .kaisu-col{width:80px}
                .sum-row{
                text-align:right;
                border-top:none;
                }
                .sum-row-kaigo{
                text-align:right;
                border-top:double;
                }
                .item-col{
                font-size:16px;
                }
                .sum-row p,
                .sum-row-kaigo p{
                padding-right:50px;
                border-bottom:1px solid #262626;
                }


                #kaigo-meisai-table{
                wiedth:auto;
                float:left;
                }
                #kaigo-meisai-table table{
                width:100%;
                }
                .date-col{
                width:150px;
                text-align:left;
                }
                .font14{
                font-size:14px;
                }
                .font16{
                font-size:16px;
                }
                .font18{
                font-size:18px;
                }
                .color333{
                    color:#333;
                }

                </style>
EOF;

include_once "../class/config.php";
include("../pdf/mpdf/mpdf.php");

$newpage_offset = 18;
$newpage_offset2 = 56;
$newpage_offset3 = 20;

$mpdf=new mPDF('ja+aCJK',array(257,364),
0,//フォントサイズ default 0
'',//フォントファミリー
10,//左マージン
10,//右マージン
6,//トップマージン
0,//ボトムマージン
0,//ヘッダーマージン
''
);
$mpdf->dpi = 150;
$mpdf->img_dpi = 150;
$mpdf->debug = true;
$mpdf->debugfonts = true;

$mpdf->WriteHTML($html);
$mpdf->Output();
#echo $html;
?>