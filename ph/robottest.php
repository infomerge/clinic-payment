<?php

/*
#顧客番号発番
$data = array(
	"aid" => 115141,
	"cmd" => 0,
	"tday" => 2,
	"cod" => time(),
);
$url = "https://credit.j-payment.co.jp/gateway/at_gateway.aspx";
*/

##########
#
#　顧客登録
#
##########
/*
$data = array(
	"aid" => 115141,
	"cmd" => 1,
	"tday" => 2,
	"nm" => "竹垣孝啓",
	"em" => "no@noname.com",
	"bac" => "0009",
	"brc" => "169",
	"atype" => 1,
	"anum" => "1698438",
	"anm" => "ﾀｹｶﾞｷﾀｶﾋﾛ",
	"amo" => 100,
	"date" => "2018/07/26",
	"type" => 1,
	"stat" => 0,
	"po" => "3520001",
	"pre" => 11,
	"ad1" => "新座市東北2-27-10",
	"ad2" => "ボルテックス志木アイオン304",
	"co" => "株式会社インフォマージ",
	"cod" => "00000001",
);
*/

##########
#
#　請求追加
#
##########
$data = array(
	"aid" => 115141,	#店舗ID
	"cmd" => 2,	#処理タイプ
	"tday" => 2, #振替日
	"cid" => "56822157000000357584",	#顧客番号	
	"amo" => 1000,	#振替金額
	"date" => "2018/09/26",	#口座引落日
	"type" => 1,	#動作タイプ(1:単発 2:連続 3:従量)
	"stat" => 1,	#課金状態（0:停止中　1:稼働中）
);




$url = "https://credit.j-payment.co.jp/gateway/at_gateway.aspx";

$data = http_build_query($data, "", "&");

//header
$header = array(
	"Content-Type: application/x-www-form-urlencoded",
	"Content-Length: ".strlen($data)
);

$context = array(
	"http" => array(
		"method"  => "POST",
		'header'=> "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36\r\n",
		"header"  => implode("\r\n", $header),
		"content" => $data
	)
);



$res = file_get_contents($url, false, stream_context_create($context));

print_r($res);

$sample=explode(',',$res);

print_r($sample);

