<?php
	#require_once '../libs/MDB2.php';
	require_once dirname(dirname(__FILE__)).'/libs/MDB2.php';

	class DATABASE {
		var $db;
		var $db_name;
/*		
		function connectdb() {

			$server = "localhost";
			$user = 'root';
			$pass = "elwo5kR7sosPb=di";
			$host = 'localhost';
			$dbname = 'aggdb';
		
			$this->db = MDB2::connect("mysql://$user:$pass@$server/$dbname?charset=utf8");

			if (MDB2::isError( $this->db )) {
				echo "エラー";
			}
		}
*/
		function connectdb($dbname = 'ns_crossline') {

			$server = "localhost";
			$user = "root";
			$pass = "WaV3m4DWr";
			
			#$dbname = (isset($this->db_name) && $this->db_name != "") ? $this->db_name : "krtruang";
			
			$this->db = MDB2::connect("mysql://$user:$pass@$server/$dbname?charset=utf8");

			if (MDB2::isError( $this->db )) {
//				header("HTTP/1.1 301 Moved Permanently");
//				header("Location: /index.php?error=dberror");
			}
		}
		function disconnect() {
			$this->db->disconnect();
		}

		// データベースへSQLの実行を依頼し、resultへ戻す
		function databasequery($sql) {
			#$this->db->query("set names UTF-8");
			$result = $this->db->query($sql);

			if (MDB2::isError( $result )) {
//				header("HTTP/1.1 301 Moved Permanently");
//				header("Location: /index.php?error=unknownerror");
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
