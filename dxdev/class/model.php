<?php
require_once ( dirname( dirname( __FILE__ ) ) . '/common/config.php' ); // 設定ファイル


/**
 * DBテーブルモデル抽象クラス
 *
 */
abstract class MODEL {
	use LOGGING;
	
	/**
	 * DB接続
	 * @var DB_CONNECTION
	 */
	protected $conn;

	/**
	 * DB名
	 * @var string
	 */
	protected $db_name = 'krtruang';

	/**
	 * テーブル名
	 * @var string
	 */
	protected $table_name;

	/**
	 * カラム一覧
	 * @var array
	 */
	protected $columns = [];
	
	/**
	 * 推奨される並び順
	 * @var array
	 */
	protected $recommend_order = [];

	/**
	 * コンストラクタ
	 * @param DB_CONNECTION $conn DB接続
	 */
	public function __construct( $conn = null ) {
		// 対象テーブル
		if ( ! isset( $this->table_name ) ) {
			$this->table_name = strtolower( get_class( $this ) );
		}

		// MySQLと接続する
		if ( ! isset( $conn ) ) {
			$conn = new DB_CONNECTION ();
			
		}
		$this->conn = $conn;
		$this->conn->connectdb( $this->db_name );

		// カラム一覧の取得
		$this->setColumns();
	}
	
	/**
	 * デストラクタ
	 */
	function __destruct() {
	}

	/**
	 * テーブル名を返す
	 */
	public function getTableName () {
		return $this->table_name;
	}

	/**
	 * カラムの一覧を取得する
	 */
	protected function setColumns () {
		// SQL
		$sql = "show full columns from `" . $this->table_name . "`";

		// SQL実行
		$result = $this->conn->databasequery( $sql );
		if ( PEAR::isError( $result ) ) {
			return false;
		}
		
		// カラム情報取得
		$this->columns = [];
		while( $row = $result->fetchRow( MDB2_FETCHMODE_ASSOC ) ) {
			$column_name = $row['field'];
			
			$this->columns[$column_name] = $row;
		}
	}

	/**
	 * 参照用SQL文の準備をする
	 * 
	 * @param array $select 取得するカラム
	 * @param array $where 取得条件
	 * @parma array $order 取得順序
	 * @return DB_STATEMENT ステートメント
	 */
	public function prepareFind ( $select = null, $where = null, $order = null ) {
		$sql = '';
		
		// 取得するカラムの指定
		$sql .= 'SELECT ';
		if ( isset( $select ) && is_array( $select ) && 0 < count( $select ) ) {
			$first = true;
			foreach ( $select as $key => $value ) {
				if ( ! $first ) {
					$sql .= ', ';
				}
				// キーが数字ならカラムの直接指定
				if ( is_numeric( $key ) ) {
					$sql .= $value . ' ';
				}
				// キーが文字列ならキーはエイリアスとして指定する
				else {
					$sql .= '(' . $value . ') AS \'' . $key . '\' ';
				}
				$first = false;
			}
		}
		else{
			$sql .= '* ';
		}

		// テーブルの指定
		$sql .= 'FROM ' . $this->getTableName() . ' ';

		// 取得条件の指定
		$sql .= 'WHERE ';
		if ( isset( $where ) && is_array( $where ) && 0 < count( $where ) ) {
			$first = true;
			foreach ( $where as $key => $value ) {
				if ( ! $first ) {
					$sql .= 'AND ';
				}
				// キーが数字なら検索条件の直接指定
				if ( is_numeric( $key ) ) {
					$sql .= '(' . $value . ') ';
				}
				// キーが文字列ならキーをカラムとして条件式を作成
				else {
					if ( is_array( $value ) ) {
						$sql .= $key . ' in (' . implode( ', ', $value ) . ') ';
					}
					else {
						$sql .= '(' . $key . ' = ' . $value . ') ';
					}
				}
				$first = false;
			}
		}
		else{
			$sql .= '1 = 1 ';
		}

		// 取得順序の指定
		if ( ! isset( $order ) ) {
			$order = $this->recommend_order;
		}
		if ( isset( $order ) && is_array( $order ) && 0 < count( $order ) ) {
			$sql .= 'ORDER BY ' . implode( ', ', $order );
		}
		
		// SQL実行準備
		$statement = $this->conn->prepare( $sql );
		if ( false === $statement ) {
			$error_message = $statement->getErrorMessage();
			if ( "" != $error_message ) {
				$this->addErrorMessage( $error_message );
			}
			return false;
		}
		
		return $statement;
	}

	/**
	 * データを参照する
	 *
	 * @param DB_STATEMENT $statement
	 * @param array $bind パラメータ
	 * @param array $select 取得するカラム
	 * @param array $where 取得条件
	 * @parma array $order 取得順序
	 * @return array 取得結果
	 */
	public function find( $statement = null, $bind = null, $select = null, $where = null, $order = null ) {

		// SQL実行準備
		$temp_flag = false;
		if ( ! isset( $statement ) ) {
			$statement = $this->prepareFind( $select, $where, $order );
			if ( false === $statement ) {
				return false;
			}
			$temp_flag = true;
		}

		// パラメータの指定
		if ( isset( $bind ) && is_array( $bind ) && 0 < count( $bind ) ) {
			$i = 0;
			foreach ( $bind as $value ) {
				$statement->bindValue( $i++, $value );
			}
		}
		
		// SQL実行
		$result = $statement->execute();
		if ( false == $result ) {
			$error_message = $statement->getErrorMessage();
			if ( "" != $error_message ) {
				$this->addErrorMessage( $error_message );
			}
		}

		if ( false != $result ) {
			// 一覧取得
			$data_list = [];
			while( $row = $result->fetchRow( MDB2_FETCHMODE_ASSOC ) ) {
				$data_list[] = $row;
			}
		}

		// この関数内で作成されたステートメントの場合、解放する
		if ( $temp_flag ) {
			$statement->close();
		}
		
		if ( false == $result ) {
			return false;
		}
		return $data_list;
		
	}

	/**
	 * レコードを登録する準備
	 * 
	 * @param array $insert 登録するカラム
	 * @param array $data 登録データ
	 */
	public function prepareInsert ( $insert = null, $data = null ) {
		$sql = '';

		// 登録するカラムを指定
		if ( ! isset( $insert ) ) {
			$insert = array_keys ( $this->columns );
		}

		$sql .= 'INSERT INTO ';
		$sql .= $this->getTableName() . ' ( ';
		$sql .= implode( ', ', $insert );
		$sql .= ') ';
		$sql .= 'VALUES (  ';

		$first = true;
		foreach( $insert as $value ) {
			if ( ! $first ) {
				$sql .= ', ';
			}
			if ( isset( $data[$value] ) ) {
				$sql .= $data[$value] . ' ';
			}
			else {
				$sql .= 'null ';
			}
			$first = false;
		}
		$sql .= ') ';

		// SQL実行準備
		$statement = $this->conn->prepare( $sql );
		if ( false === $statement ) {
			$error_message = $statement->getErrorMessage();
			if ( "" != $error_message ) {
				$this->addErrorMessage( $error_message );
			}
			return false;
		}
		
		return $statement;
	}

	/**
	 * レコードを登録する
	 *
	 * @param DB_STATEMENT $statement
	 * @param array $bind パラメータ
	 * @param array $insert 登録するカラム
	 * @param array $data 登録データ
	 * @return boolean 登録結果
	 */
	public function insert( $statement = null, $bind = null, $insert = null, $data = null ) {

		// SQL実行準備
		$temp_flag = false;
		if ( ! isset( $statement ) ) {
			$statement = $this->prepareInsert( $insert, $data );
			if ( false === $statement ) {
				return false;
			}
			$temp_flag = true;
		}
	
		// パラメータの指定
		if ( isset( $bind ) && is_array( $bind ) && 0 < count( $bind ) ) {
			$i = 0;
			foreach ( $bind as $value ) {
				$statement->bindValue( $i++, $value );
			}
		}
	
		// SQL実行
		$result = $statement->execute();
		if ( false == $result ) {
			$error_message = $statement->getErrorMessage();
			if ( "" != $error_message ) {
				$this->addErrorMessage( $error_message );
			}
		}

		// この関数内で作成されたステートメントの場合、解放する
		if ( $temp_flag ) {
			$statement->close();
		}
	
		if ( false == $result ) {
			return false;
		}
		return true;
	}
	

	/**
	 * コミット
	 */
	public function commit() {
		return $this->conn->commit();
	}
	
	/**
	 * ロールバック
	 */
	public function rollback() {
		return $this->conn->rollback();
	}
}
?>