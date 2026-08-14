<?php 
$html =<<<EOF

<div class="wrap"></div><p class="header-left">回収明細票</p><p class="patient-address">〒000-0000<br>都道府県<br>町村　〇〇番地</p><p class="patient-name">医療機関名 様</p>
<p class="header-right">発送元</p><p class="irkk-name">株式会社クロスライン</p>
<p class="irkk-address">〒105-0013<br>東京都港区浜松町２丁目２番１５号<br>浜松町ダイヤビル２Ｆ<br>請求エクスプレス事業担当</p><br><p class="fold-point">▶</p><p id="total-copayment">回収明細一覧</p><table id="hoken-table">
                    <tr><th>歯科医院名</th><td>医療機関名 様</td></tr>
                    <tr><th>処理年月</th><td>2021年12月分</td></tr>
                    <tr><th>合計件数</th><td>1件</td></tr>
                    <tr><th>合計金額</th><td>¥0,000</td></tr>
                </table><br/>
<table id="hoken-table">
                    <tr>
                        <th>No.</th>
                        <th>ステータス</th>
                        <th>患者名</th>
                        <th>生年月日</th>
                        <th>金額</th>
                        <th>回収方法</th>
                        <th>回収日</th>
                    </tr><tr>
                    <td>1</td>
                    <td>回収済</td>
                    <td>郵送先　氏名</td>
                    <td>0000年00月00日</td>
                    <td>¥0,000</td>
                    <td>口座振替</td>
                    <td>2022年01月10日</td>
                </tr></table><br/>
<div class="clearfix"></div><style>
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
          left:144.57px;
          font-size:20px;
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
          left: 1020px;
          border-radius: 5px;
          font-size:26px;
          font-weight:bold;
        }
        .irkk-address{
          position:absolute;
          top:484.31px;
          left:1020px;
          font-size:16px;
          font-weight:bold;
        }
        .irkk-account{
          position:absolute;
          top:600px;
          left:1020px;
          font-size:18px;
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

        table{
          border:1px solid #262626;
          border-spacing:0;
          border-collapse:none;
          text-align:center;
          font-size:20px;
        }
        th,td{
          border:1px solid #262626;
          padding:5px;
        }
        th{
          background-color: #EEEDED;
        }

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
          text-align:left;
        }

        #shinryo-meisai{
          font-size:36px;
          width:500px;
          margin: 50px auto 30px auto;
          text-align:center;
          border-bottom:3px solid #262626;
        }

        #iryo-meisai-table{
          width:675px;
          float:left;
          padding-right:50px;
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