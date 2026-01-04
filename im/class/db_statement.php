<?php
/**
 * DBステートメントのラッパクラス
 *
 */

class DB_STATEMENT {
	use LOGGING;

	/**
	 * DB接続
	 * @var MDB2_Driver_mysql
	 */
	protected $conn = null;

	/**
	 * DBステートメント
	 * @var MDB2_Statement_mysql
	 */
	protected $statement = null;

	/**
	 * コンストラクタ
	 * 
	 * @param MDB2_Driver_mysql $conn
	 * @param MDB2_Statement_mysql $statement
	 */
	public function __construct( $conn, $statement ) {
		$this->conn = $conn;
		$this->statement = $statement;
	}
	
	/**
	 * パラメータを設定する
	 * 
	 * @param int $index
	 * 
	 */
	public function bindValue( $index, $value, $type = null ) {
		$result = $this->statement->bindValue( $index, $value, $type );
		if ( PEAR::isError( $result ) ) {
			$this->addErrorMessage( $result->getMessage() );
			return false;
		}
		return $result;
	}

	/**
	 * SQLを実行する
	 * 
	 * @return MDB2_Result
	 */
	public function execute() {
		$result = $this->statement->execute();
		if ( PEAR::isError( $result ) ) {
			$this->addErrorMessage( $result->getMessage() );
			return false;
		}
		return $result;
	}

	/**
	 * ステートメントを開放する
	 */
	public function close() {
		$this->statement->free();
	}
}