<?php
/**
 * 決済結果アップロードおよび表示画面コントローラ
 */

class UPLOADER_CONTROLLER extends CONTROLLER {
	/**
	 * @var array
	 */
	protected $file_type_list = [
		'paypal' => 'PayPal',
		'ips' => 'IPS',
 		'bank' => '湘南信用金庫',
	];
	
	
	/**
	 * アップロードファイルのハンドラ
	 * @var resource
	 */
	protected $file_handler = null;
	
	/**
	 * メイン処理
	 */
	public function main() {
		// DB接続
		$conn = new DB_CONNECTION ();
		$conn->connectdb( 'charisma' );

		// ファイルタイプ確認
		$file_type = $this->getParam( 'file_type', 'paypal', 'POST' );
		
		// モデルを取得する
		$model = $this->getModel( $file_type, $conn );
		if ( ! is_null ( $model ) ) {
			// アップロードされたファイルがあれば取り込みを実施
			$this->upload( $model );

			// 取り込まれている一覧を取得
			$data_list = $this->getList( $model );
			if ( is_array( $data_list ) && 0 < count( $data_list ) ) {
				$first_data = reset( $data_list );
				$header_list = array_keys( $first_data );
			}
			else {
				$header_list = [];
			}
		}
		else {
			$header_list = [];
			$data_list = [];
		}
		
		// 画面表示
		$this->smarty->assign( 'navi_type', $this->navi_type );
		$this->smarty->assign( 'file_type', $file_type );
		$this->smarty->assign( 'file_type_list', $this->file_type_list );
		$this->smarty->assign( 'header_list', $header_list );
		$this->smarty->assign( 'data_list', $data_list );
		$error_message = $this->getErrorMessage();
		if ( 0 < count( $error_message ) ) {
			$message = implode( '<br />', $error_message );
			$this->smarty->assign( 'message', $message );
		}
		$this->smarty->display( 'manager/uploader.tpl' );
	}

	/**
	 * 一覧を取得する
	 * 
	 * @param MODEL $model 
	 */
	protected function getList( $model ) {
		$csv_format = $model->getCsvFormat();

		$data_list = $model->find( null, null, $csv_format );
		if ( false === $data_list ) {
			$data_list = [];
		}
		return $data_list;
	}
	
	/**
	 * アップロードされたファイルをDBに取り込む
	 * 
	 * @param MODEL $model
	 */
	protected function upload( $model ) {
		// アップロードされたファイルを開く
		$ulfile = $this->openUploadFile( 'ul_file' );
		// アップロードされたファイルがなければ処理を中断
		if ( false === $ulfile ) {
			return false;
		}

		// 変換テーブルの取得
		$csv_format = $model->getCsvFormat();

		// ヘッダ行を読み込む
		$header = $this->getOneLine();

		// ヘッダ行を確認する
		$header_check = $model->checkHeader( $header );
		// ヘッダ行に不一致があれば取り込みを実施しない
		if ( ! $header_check ) {
			$this->addErrorMessage( "ファイルのフォーマットが違います。ファイルの種別を確認してください。" );
			return false;
		}

		// ファイルの終端まで読み込む
		$line_cnt = 1;
		$data_check = true;
		while( $data = $this->getOneLine() ) {
			$insert_data = $model->convertData( $header, $data );
			if ( false === $insert_data ) {
				$this->addErrorMessage( $line_cnt . "行目のデータのフォーマットが違います。ファイルの内容を確認してください。" );
				$data_check = false;
				$line_cnt++;
				continue;
			}

			// 重複チェック
			$is_duplicate = $model->isDuplicate( $insert_data );
			if ( $is_duplicate ) {
				$this->addErrorMessage( $line_cnt . "行目のデータは既に取り込み済みです。" );
				$line_cnt++;
				continue;
			}

			$result = $model->uploadCsv( $insert_data );
			if ( ! $result ) {
				$error_message = $model->getErrorMessage();
				if ( "" != $error_message ) {
					$this->addErrorMessage( $error_message );
				}
				$model->rollback();
				$data_check = false;
				break;
			}
			// 1行ずつコミットする
			$model->commit();
			$line_cnt++;
		}
		
		if ( ! $data_check ) {
			return false;
		}
		return true;
	}

	/**
	 * ファイルタイプに対応したテーブルモデルを取得する
	 * @parma string $file_type ファイルタイプ 
	 * @param DB_CONNECTION $conn DB接続
	 */
	protected function getModel ( $file_type, $conn = null ) {
		$model = null;
		switch( $file_type ) {
			case 'paypal':
				$model = new PAYPAL_LOG( $conn );
				break;
			case 'ips':
				$model = new IPS_LOG( $conn );
				break;
			case 'bank':
				$model = new BANK_LOG( $conn );
				break;
		}
		return $model;
	}
	
	/**
	 * アップロードしたファイルハンドラを取得する
	 * 
	 * @return resource/boolean ファイルハンドラ
	 */
	protected function openUploadFile( $key ) {
		if ( ! isset( $_FILES[$key] ) || ! is_uploaded_file( $_FILES[$key]['tmp_name'] ) ) {
			return false;
		}
		
		// ファイルの内容をUTF-8に変換する
		$file_data = file_get_contents( $_FILES[$key]['tmp_name'] );
		$enc_data = mb_convert_encoding( $file_data, 'UTF-8', 'sjis-win, UTF-8' );

		// BOMを取り除く
		if ( 0xEF == ord( $enc_data[0] ) && 0xBB == ord( $enc_data[1] ) && 0xBF == ord( $enc_data[2] ) ) {
			$enc_data = substr( $enc_data, 3 );
		}

		// ファイルを残す必要がないので一時ファイルで処理する
		$temp_file = tmpfile();
		fwrite( $temp_file, $enc_data );
		rewind( $temp_file );
		
		return $this->file_handler = $temp_file;
	}

	/**
	 * アップロードされたファイルから1行取得する
	 */
	protected function getOneLine() {
		return fgetcsv( $this->file_handler );
	}
	
	/**
	 * アップロードを閉じる
	 *
	 * @return 
	 */
	protected function closeUploadFile() {
		if ( ! is_null( $this->file_handler ) ) {
			fclose( $this->file_handler );
			$this->file_handler = null;
		}
	}

	/**
	 * デストラクタ
	 */
	function __destruct() {
		$this->closeUploadFile();
	}
}
