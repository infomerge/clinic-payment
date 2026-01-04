<?php
define('DBNAME','xs547384_dxdev');
define('SERVICETITLE','DX クリニックペイメント');
define('LISTOFFSET',20);

define('DUMMYEMAIL',"info@crossline-exp.com");
#define('BILLINGSTATUS',0);
define('BILLINGSTATUS',1);

define('AID',"115106");#店舗ID
define('TDAY',"1");#振替日「1」は10日のこと
#define('BILLINGSTATUS',1);
define('DOMAIN','cldeploy.netstars.vision');
define('SQLINSERT_OFFSET',1000);

#カテゴリマスター
$m_category['A'] = array('kigo'=>'A', 'title'=>'初・再診料', 'tensu'=>0);
$m_category['B'] = array('kigo'=>'B', 'title'=>'医学管理等', 'tensu'=>0);
$m_category['C'] = array('kigo'=>'C', 'title'=>'在宅医療', 'tensu'=>0);
$m_category['D'] = array('kigo'=>'D', 'title'=>'検査', 'tensu'=>0);
$m_category['E'] = array('kigo'=>'E', 'title'=>'画像診断', 'tensu'=>0);
$m_category['F'] = array('kigo'=>'F', 'title'=>'投薬', 'tensu'=>0);
$m_category['G'] = array('kigo'=>'G', 'title'=>'注射', 'tensu'=>0);
$m_category['H'] = array('kigo'=>'H', 'title'=>'リハビリテーション', 'tensu'=>0);
$m_category['I'] = array('kigo'=>'I', 'title'=>'処置', 'tensu'=>0);
$m_category['J'] = array('kigo'=>'J', 'title'=>'手術', 'tensu'=>0);
$m_category['K'] = array('kigo'=>'K', 'title'=>'麻酔', 'tensu'=>0);
$m_category['L'] = array('kigo'=>'L', 'title'=>'放射線治療', 'tensu'=>0);
$m_category['M'] = array('kigo'=>'M', 'title'=>'歯冠修復及び欠損補綴', 'tensu'=>0);
$m_category['N'] = array('kigo'=>'N', 'title'=>'歯科矯正', 'tensu'=>0);
$m_category['O'] = array('kigo'=>'O', 'title'=>'病理診断', 'tensu'=>0);

#未定義カテゴリを暫定で用意
$m_category['P'] = array('kigo'=>'P', 'title'=>'不明１', 'tensu'=>0);
$m_category['Q'] = array('kigo'=>'Q', 'title'=>'不明２', 'tensu'=>0);
$m_category['R'] = array('kigo'=>'R', 'title'=>'不明３', 'tensu'=>0);
$m_category['S'] = array('kigo'=>'S', 'title'=>'不明４', 'tensu'=>0);
$m_category['T'] = array('kigo'=>'T', 'title'=>'不明５', 'tensu'=>0);
$m_category['U'] = array('kigo'=>'U', 'title'=>'不明６', 'tensu'=>0);
$m_category['V'] = array('kigo'=>'V', 'title'=>'不明７', 'tensu'=>0);
$m_category['W'] = array('kigo'=>'W', 'title'=>'不明８', 'tensu'=>0);
$m_category['X'] = array('kigo'=>'X', 'title'=>'不明９', 'tensu'=>0);
$m_category['Y'] = array('kigo'=>'Y', 'title'=>'不明１０', 'tensu'=>0);
$m_category['Z'] = array('kigo'=>'Z', 'title'=>'不明１１', 'tensu'=>0);

$m_category['-'] = array('kigo'=>'-', 'title'=>'その他', 'tensu'=>0);

$m_error['ER000'] = array('決済システム内部エラー','再度決済処理を行ってください。');
$m_error['ER001'] = array('リクエストエラー','送信パラメータに不備がございます。今一度ご確認ください。');
$m_error['ER003'] = array('送信元IPエラー','管理画面「システム設定」より、「決済データ送信元IP」をご設定ください。');
$m_error['ER004'] = array('店舗設定エラー','恐れ入りますが、ROBOT PAYMENTサポートセンターまでご連絡下さい。');
$m_error['ER050'] = array('店舗IDエラー','店舗ID(aid)の値に不備がございます。');
$m_error['ER054'] = array('店舗オーダー番号エラー','店舗オーダー番号(cod)の値に不備がございます。');
$m_error['ER059'] = array('メールアドレスエラー','メールアドレス(em)の値に不備がございます。');
$m_error['ER069'] = array('処理コードエラー','処理コード(cmd)の値に不備がございます。');
$m_error['ER073'] = array('郵便番号エラー','郵便番号(po)の値に不備がございます。');
$m_error['ER130'] = array('振替日エラー','振替日(tday)の値に不備がございます。');
$m_error['ER131'] = array('氏名エラー','名前(nm)の値に不備がございます。');
$m_error['ER132'] = array('銀行コードエラー','銀行コード(bac)の値に不備がございます。');
$m_error['ER133'] = array('支店コードエラー','支店コード(brc)の値に不備がございます。');
$m_error['ER134'] = array('口座番号エラー','口座番号(anum)の値に不備がございます。');
$m_error['ER135'] = array('口座種目エラー','口座種目(atype)の値に不備がございます。');
$m_error['ER136'] = array('口座名義エラー','口座名義(anm)の値に不備がございます。');
$m_error['ER137'] = array('都道府県エラー','都道府県(pre)の値に不備がございます。');
$m_error['ER138'] = array('市町村エラー','市町村(ad1)の値に不備がございます。');
$m_error['ER139'] = array('ビル・マンションエラー','ビル・マンション(ad2)の値に不備がございます。');
$m_error['ER140'] = array('会社名エラー','会社名(co)の値に不備がございます。');
$m_error['ER141'] = array('動作タイプエラー','動作タイプ(type)の値に不備がございます。');
$m_error['ER142'] = array('引落金額エラー','引落金額(amo)の値に不備がございます。');
$m_error['ER143'] = array('次回振替日エラー','次回振替日(date)の値に不備がございます。');
$m_error['ER144'] = array('課金状態エラー','課金状態(stat)の値に不備がございます。');
$m_error['ER145'] = array('顧客番号エラー','顧客番号(cid)の値に不備がございます。');
$m_error['ER146'] = array('請求IDエラー','請求ID(reqid)の値に不備がございます。');
$m_error['ER999'] = array('メンテナンス中','メンテナンス中です。しばらくお待ちください。');

$m_prefecture = array( 0 => '選択して下さい',
          1 => '北海道',
          2 => '青森県',
          3 => '岩手県',
          4 => '宮城県',
          5 => '秋田県',
          6 => '山形県',
          7 => '福島県',
          8 => '茨城県',
          9 => '栃木県',
          10 => '群馬県',
          11 => '埼玉県',
          12 => '千葉県',
          13 => '東京都',
          14 => '神奈川県',
          15 => '新潟県',
          16 => '富山県',
          17 => '石川県',
          18 => '福井県',
          19 => '山梨県',
          20 => '長野県',
          21 => '岐阜県',
          22 => '静岡県',
          23 => '愛知県',
          24 => '三重県',
          25 => '滋賀県',
          26 => '京都府',
          27 => '大阪府',
          28 => '兵庫県',
          29 => '奈良県',
          30 => '和歌山県',
          31 => '鳥取県',
          32 => '島根県',
          33 => '岡山県',
          34 => '広島県',
          35 => '山口県',
          36 => '徳島県',
          37 => '香川県',
          38 => '愛媛県',
          39 => '高知県',
          40 => '福岡県',
          41 => '佐賀県',
          42 => '長崎県',
          43 => '熊本県',
          44 => '大分県',
          45 => '宮崎県',
          46 => '鹿児島県',
          47 => '沖縄県');

$m_bank_clasification = array("","普通","当座");
?>
