<?php
/**
 * DB接続のラッパークラス
 */

class DB_CONNECTION extends DATABASE {
	use LOGGING;

	/**
	 * SQLを設定しステートメントを返す
	 *
	 * @param string $sql SQL文
	 * @return DB_STATEMENT
	 */
	public function prepare( $sql ) {
		$statement = $this->db->prepare( $sql );
		if ( PEAR::isError( $statement ) ) {
			return false;
		}

		// DBステートメントのラッパクラスを作成
		$db_statement = new DB_STATEMENT( $this->db, $statement );
		return $db_statement;
	}

	/**
	 * コミット
	 */
	public function commit() {
		$result = $this->db->commit();
		if ( PEAR::isError( $result ) ) {
			return false;
		}
		return $result;
	}

	/**
	 * ロールバック
	 */
	public function rollback() {
		$result = $this->db->rollback();
		if ( PEAR::isError( $result ) ) {
			return false;
		}
		return $result;
	}
}