<?php
	#require_once '../libs/MDB2.php';
	require_once dirname(dirname(__FILE__)).'/libs/MDB2.php';

	class DATABASE {
		var $db;
		
		function connectdb() {

			$server = "localhost";
			$dbname = "aggdb";
			$user = "root";
			$pass = "elwo5kR7sosPb=di";
			
			
			
			
			$dsn = array(
				'phptype'  => 'mysql',
				'username' => $user,
			#    'phptype'  => 'mysql',
			#    'username' => 'root',
				'password' => $pass,
				'hostspec' => $server,
				'database' => $dbname,
				'charset' => 'utf8',
			);
			#$dsn = 'sqlite:///:memory:';
			
			// create MDB2 instance
			$this->db = MDB2::factory($dsn);
			if (MDB2::isError($this->db)) {
				die($this->db->getMessage());
			}
			
			// set the default fetchmode
			$this->db->setFetchMode(MDB2_FETCHMODE_ASSOC);
			
			#$sql = "set names UTF-8;";
			#$this->db->query($sql);
			$sql = "select * from account";
			$res = $this->db->query($sql);
			$row = $res->fetchRow();
			print_r($row);
			
			
			#$this->db = MDB2::connect("mysql://$user:$pass@$server/$dbname?charset=utf8");
/*
			if (MDB2::isError( $this->db )) {
				echo "エラー";
			}
			*/
		}
		function disconnect() {
			$this->db->disconnect();
		}

		// データベースへSQLの実行を依頼し、resultへ戻す
		function databasequery($sql) {
			echo $sql;
			
			$result = $this->db->query($sql);

			if (MDB2::isError( $result )) {
				echo "あかん";
//				header("HTTP/1.1 301 Moved Permanently");
//				header("Location: /index.php?error=unknownerror");
			}
			return $result;
		}
		
		// プリペアドステートメント準備ラッパ
		/*
		function prepare($sql,$args) {
	
			$result = $this->db->prepare($sql,$args);
	
			if (PEAR::isError($result)){ 
				self::dberror($result);
			}
	
			return $result;
		}
	*/
		// プリペアドステートメント実行ラッパ
/*		function execute($state, $params) {
	
			$result = $state->execute($params);
	
			#if (PEAR::isError($result)) self::dberror($result);
	
			return $result;
		}
	*/
		/*
		 * 準備して実行
		 * @var $sql SQLステートメント（プレースホルダ可）
		 * @var $params	プレースホルダに置換する値の配列
		 * @return $result クエリ実行結果配列
		 */
		function prepExec($sql,$type, $params) {
	#echo $sql;
			$state = $this->db->prepare($sql, $type, MDB2_PREPARE_MANIP);
			$result = $state->execute($params);
			print_r($result);
			if(PEAR::isError($result)){
die($result->getMessage());
}
			return $result;
		}
		
		// DBエラー
		function dberror($obj) {
	
			if (BOOL_DEBUGMODE) {
	
				die(
					$obj->getMessage(). "\n"
					. $obj->getUserInfo(). "\n"
				);
			}
			else {
	
				die();
			}
		}
		
	}
?>
