<?php
/**
 * 湘南信用金庫用決済ログテーブル
 *
 */
class BANK_LOG extends MODEL {
	use CSV_LOG {
		convertData as convertDataCommon;
		convertValue as convertValueCommon;
	}
	
	// CSVからアップロードするときの変換テーブル
	protected static $csv_format = [
		'データ種別' => 'data_type',
		'日付' => 'process_date',
		'取引内容' => 'transaction',
		'出金額' => 'paid_amount',
		'入金額' => 'received_amount',
		'残高' => 'balance',
	];

	/**
	 * 重複確認用カラム
	 * @var array
	 */
	protected static $check_columns = [
		'process_date',
		'md5',
	];

	/**
	 * CSVデータ以外に登録するカラム
	 * @var array
	 */
	protected static $add_columns = [
		'md5' => "?",
		'regist_date' => 'now()',
	];

	/**
	 * 推奨される並び順
	 * @var array
	 */
	protected $recommend_order = [
		'process_date',
		'id',
	];
	
	/**
	 * DB名
	 * @var string
	 */
	protected $db_name = 'charisma';

	/**
	 * CSVヘッダの内容を確認する
	 * 
	 * @param array $header CSVヘッダデータ
	 * @return boolean
	 */
	public function checkHeader( $header ) {
		// ヘッダチェック不要
		return true;
	}

	/**
	 * テーブルに登録する形式に変換する
	 *
	 * @param string $column_name カラム名
	 * @param string $value CSVの値
	 * @return string 変換済みデータ
	 */
	protected function convertValue ( $column_name, $value ) {
		$value = $this->convertValueCommon( $column_name, $value );

		// 日付が日本語なので変換する
		if ( 'process_date' == $column_name ) {
			$value = mb_ereg_replace( '(年|月|日)', '-', $value );
		}
		return $value;
	}

	/**
	 * テーブルに登録する形式に変換する
	 *
	 * @param string $csv_header CSVのヘッダ文言
	 * @param string $csv_value CSVの値
	 * @param string $column_name カラム名
	 * @return string 変換済みデータ
	 */
	public function convertData ( $header, $data ) {
		static $dummy_header = null;
		if ( is_null( $dummy_header ) ) {
			// 変換テーブルの取得
			$csv_format = $this->getCsvFormat();
			if ( empty( $csv_format ) ) {
				return false;
			}

			$dummy_header = array_keys( $csv_format );
		}
		
		// ヘッダを置き換えてから読み込みなおす
		$convert_data = $this->convertDataCommon( $dummy_header, $data );
		if ( false === $convert_data ) {
			return false;
		}

		// md5ハッシュ値を追加する
		$base_text = implode( "", $data );
		$convert_data['md5'] = md5( $base_text );
		
		return $convert_data;
	}
}