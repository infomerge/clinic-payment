<?php
trait CSV_LOG {
	/**
	 * CSVからアップロードするときの変換テーブルを返す
	 * 
	 * @return array
	 */
	public function getCsvFormat () {
	if ( ! property_exists( $this, 'csv_format' ) ) {
			return [];
		}
		return static::$csv_format;
	}

	/**
	 * CSVからアップロードするときの変換テーブルを返す
	 * 
	 * @return array
	 */
	public function getCheckColumns () {
		if ( ! property_exists( $this, 'check_columns' ) ) {
			return [];
		}
		return static::$check_columns;
	}

	/**
	 * CSVデータ以外に登録するカラムを返す
	 * 
	 * @return array
	 */
	public function getAddColumns () {
		// デフォルト設定
		static $default_add_columns = [
			'regist_date' => 'now()',
		];
		if ( ! property_exists( $this, 'add_columns' ) ) {
			return $default_add_columns;
		}
		return static::$add_columns;
	}

	/**
	 * テーブルに登録する形式に変換する
	 * 
	 * @param string $column_name カラム名
	 * @param string $value CSVの値
	 * @return string 変換済みデータ
	 */
	protected function convertValue ( $column_name, $value ) {
		// カラムのタイプを取得
		$column_type = $this->columns[$column_name]['type'];

		// タイプによって変換する
		switch ( true ) {
			// intを含む場合
			case ( false !== strpos( $column_type, 'int' ) ):
			// 浮動少数
			case ( 'float' == $column_type ):
			case ( 'double' == $column_type ):
				$value = str_replace( ['\\', ','], '', trim( $value ) );
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
		// 変換テーブルの取得
		$csv_format = $this->getCsvFormat();
		if ( empty( $csv_format ) ) {
			return false;
		}

		$insert_data = [];
		$data_cnt = 0;
		foreach ( $data as $csv_value ) {
			// ヘッダよりもデータの方が多い場合、フォーマット異常としてfalseを返す
			if ( ! isset( $header[$data_cnt] ) ) {
				return false;
			}
			// 該当するヘッダ文言を取得
			$csv_header = $header[$data_cnt];

			// カラム名の確認
			$column_name = isset( $csv_format[$csv_header] ) ? $csv_format[$csv_header] : '';
			if ( '' != $column_name && ! isset(  $this->columns[$column_name] ) ) {
				$column_name = '';
			}
			if ( '' == $column_name ) {
				$data_cnt++;
				continue;
			}

			$insert_data[$column_name] = $this->convertValue( $column_name, $csv_value );
			$data_cnt++;
		}
		return $insert_data;
	}
	
	/**
	 * 重複するデータがあるかどうか確認する
	 * 
	 * @param array $data 登録用データ
	 * @return boolean
	 */
	public function isDuplicate( $data = null ) {
		/** @var DB_STATEMENT $statement */
		static $statement = null;
		
		if ( ! isset( $data ) && ! is_null( $statement ) ) {
			$statement->close();
			$statement = null;
			return false;
		}

		$check_columns = $this->getCheckColumns();
		if ( empty( $check_columns ) ) {
			return false;
		}

		// SQL準備
		if ( is_null( $statement ) ) {
			$select = [
				'count' => 'COUNT(*)',
			];
			$where = [];
			foreach ( $check_columns as $column_name ) {
				$where[] = $column_name . ' = ? ';
			}

			$statement = $this->prepareFind( $select, $where, [] );
			if ( false == $statement ) {
				$statement = null;
				return false;
			}
		}

		// SQL実行
		$bind = [];
		foreach ( $check_columns as $column_name ) { 
			$bind[] = isset( $data[$column_name] ) ? $data[$column_name] : null;
		}
		$result = $this->find( $statement, $bind );
		if ( isset( $result[0]['count'] ) && 0 < $result[0]['count'] ) {
			return true;
		}

		return false;
	}

	/**
	 * CSVヘッダの内容を確認する
	 * 
	 * @param array $header CSVヘッダデータ
	 * @return boolean
	 */
	public function checkHeader( $header ) {
		// 変換テーブルの取得
		$csv_format = $this->getCsvFormat();
		if ( empty( $csv_format ) ) {
			return false;
		}

		$header_cnt = 0;
		foreach ( $csv_format as $csv_header => $column_name ) {
			// ヘッダの方が件数が少ない場合
			if ( ! isset( $header[$header_cnt] ) ) {
				return false;
			}
			// ヘッダの該当する項目の名称が異なる場合
			if ( $csv_header != $header[$header_cnt] ) {
				return false;
			}
			$header_cnt++;
		}
		return true;
	}

	/**
	 * CSVデータをDBに登録する
	 * 
	 * @param array $data 登録用データ
	 */
	public function uploadCsv ( $data = null ) {
		/** @var DB_STATEMENT $statement */
		static $statement = null;
		
		if ( ! isset( $data ) && ! is_null( $statement ) ) {
			$statement->close();
			$statement = null;
			return false;
		}
		
		// 変換テーブルの取得
		$csv_format = $this->getCsvFormat();
		if ( empty( $csv_format ) ) {
			return false;
		}
		
		// SQL準備
		if ( is_null( $statement ) ) {
			// ヘッダ行を確認する
			$insert_columns = [];
			$insert_data = [];
			foreach ( $csv_format as $csv_header => $column_name ) {
				if ( '' != $column_name && ! isset(  $this->columns[$column_name] ) ) {
					$column_name = '';
				}
				if ( '' != $column_name ) {
					$insert_columns[] = $column_name;
					$insert_data[$column_name] = '?';
				}
			}

			// CSVデータ以外に登録するカラムの設定
			$add_columns = $this->getAddColumns();
			foreach( $add_columns as $column_name => $add_value ) {
				$insert_columns[] = $column_name;
				$insert_data[$column_name] = $add_value;
			}

			$statement = $this->prepareInsert( $insert_columns, $insert_data );
			if ( false == $statement ) {
				$statement = null;
				return false;
			}
		}
		
		return $this->insert( $statement, $data );
	}
}