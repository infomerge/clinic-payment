<?php
/**
 * PayPal用決済ログテーブル
 *
 */
class PAYPAL_LOG extends MODEL {
	use CSV_LOG;
	
	// CSVからアップロードするときの変換テーブル
	protected static $csv_format = [
		'日付' => 'process_date',
		'時間' => 'process_time',
		'タイムゾーン' => 'timezone',
		'名前' => 'name',
		'タイプ' => 'type',
		'ステータス' => 'status',
		'通貨' => 'currency',
		'合計' => 'sum',
		'手数料' => 'commission',
		'正味' => 'net',
		'送信者メールアドレス' => 'sender_email',
		'受信者メールアドレス' => 'receive_email',
		'取引ID' => 'transaction_id',
		'配送先住所' => 'shipping_address',
		'住所ステータス' => 'address_status',
		'商品タイトル' => 'product_title',
		'商品ID' => 'product_id',
		'配送および手数料の額' => 'shipping_fee',
		'保険金額' => 'insurance_amount',
		'消費税' => 'tax',
		'オプション1: 名前' => 'option1_name',
		'オプション1: 金額' => 'option1_price',
		'オプション2: 名前' => 'option2_name',
		'オプション2: 金額' => 'option2_price',
		'リファレンス トランザクションID' => 'ref_transaction_id',
		'請求書番号' => 'invoice_number',
		'カスタム番号' => 'custom_number',
		'数量' => 'num',
		'領収書ID' => 'receipt_id',
		'残高' => 'balance',
		'住所1行目' => 'address1',
		'住所2行目/地区/地域' => 'address2',
		'市区町村' => 'city',
		'都道府県' => 'pref',
		'郵便番号' => 'zip',
		'国および地域' => 'country',
		'連絡先の電話番号' => 'tel',
		'件名' => 'product_title2',
		'備考' => 'note',
		'Country Code' => 'country_code',
		'Balance Impact' => 'balance_impact',
	];

	/**
	 * 重複確認用カラム
	 * @var array
	 */
	protected static $check_columns = [
		'transaction_id',
	];

	/**
	 * 推奨される並び順
	 * @var array
	 */
	protected $recommend_order = [
		'process_date',
		'process_time',
		'id',
	];
	
	/**
	 * DB名
	 * @var string
	 */
	protected $db_name = 'charisma';

}