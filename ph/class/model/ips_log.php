<?php
/**
 * IPS用決済ログテーブル
 *
 */
class IPS_LOG extends MODEL {
	use CSV_LOG;
	
	// CSVからアップロードするときの変換テーブル
	protected static $csv_format = [
		'決済番号' => 'payment_no',
		'サービス種別' => 'service',
		'決済ジョブ' => 'payment_job',
		'結果' => 'payment_result',
		'店舗オーダー番号' => 'order_no',
		'カードブランド' => 'card_brand',
		'名前' => 'name',
		'電話番号' => 'phone',
		'メールアドレス' => 'email',
		'合計金額' => 'sum',
		'決済日' => 'payment_date',
		'実売日' => 'sale_date',
		'取消日' => 'cancel_date',
		'発行ID' => 'issued_id',
		'発行パスワード' => 'issued_password',
		'カード有効期限' => 'expiry_date',
		'名前(日本語)' => 'name_jpn',
		'名前(フリガナ)' => 'name_kana',
		'住所' => 'address',
		'住所(フリガナ)' => 'addr_kana',
		'携帯電話番号' => 'mobile',
		'ＦＡＸ番号' => 'fax',
		'生年月日' => 'birthday',
		'質問' => 'message',
		'郵送先名前' => 'shipping_name',
		'郵送先名前(フリガナ)' => 'shipping_name_kana',
		'郵送先住所' => 'shipping_address',
		'郵送先住所(フリガナ)' => 'shipping_address_kana',
		'郵送電話番号' => 'shipping_phone',
		'郵送先携帯電話番号' => 'shipping_mobile',
		'郵送先ＦＡＸ番号' => 'shipping_fax',
		'郵送先メールアドレス' => 'shipping_email',
		'商品コード' => 'product_id',
		'商品名' => 'product_name',
		'支払方法' => 'payment_mode',
		'支払回数' => 'payment_times',
		'その他パラメータ' => 'other_parameter',
	];

	/**
	 * 重複確認用カラム
	 * @var array
	 */
	protected static $check_columns = [
		'payment_no',
	];

	/**
	 * 推奨される並び順
	 * @var array
	 */
	protected $recommend_order = [
		'payment_date',
		'id',
	];
	
	/**
	 * DB名
	 * @var string
	 */
	protected $db_name = 'charisma';

}