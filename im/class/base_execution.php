<?php
	#include_once '../common/database.php';
	include_once dirname(dirname(__FILE__)).'/common/database.php';

	class BASE_EXECUTION {
		var $database;
		var $id_name;
		var $id_value;
		var $table_name;
		var $db_columns = array();
		var $exception_columns = array();	#$db_columnsから除外するカラム
		var $conditions = array();	#$db_columnsから除外するカラム
		
								
		function __construct() {
			// MySQLと接続する
			$this->database = new DATABASE;
			$this->database->connectdb();
		}
		function __destruct(){
			// MySQLの接続を解除する
			$this->database->disconnect();
        }
		function set_properties(){
			if(isset($this->table_name) && $this->table_name != ""){
				$column_sql = "show columns from `{$this->table_name}`";
				$result = $this->database->databasequery($column_sql);
				while($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)){
					if(!in_array($row['field'],$this->exception_columns)){
						$this->db_columns[$row['field']] = $row['type'];
						if($row['extra'] == "auto_increment"){
							$this->db_columns[$row['field']]."|auto_increment";
						}
					}
				}
			}
		}
		function select() {
			#if(isset($this->id_name) && $this->id_name != ""){
			$res_data = array();
				
				$sql = "SELECT * from {$this->table_name}";
				
				if(isset($this->id_name) && $this->id_name != "" && isset($this->id_value) && $this->id_value != ""){
					$sql .= " where {$this->id_name} = '{$this->id_value}'";
				}else{
					$sql .= " where 1";	
				}
				
				if(count($this->conditions) > 0){
					foreach($this->conditions as $column => $expression){
						$sql .= " and {$column} {$expression}";
					}
				}
				if(isset($this->orderby) && $this->orderby != ""){
					$sql .= " ".$this->orderby;
				}
				echo "<!-- ".$sql."<br> -->";
				$res = $this->database->databasequery($sql);
				
				while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
					$res_data[] = $row;
				}
				return $res_data;
			#}
		}
		function createFromSession() {
			$res_data = array();
			
			foreach($this->db_columns as $column => $type){
				$res_data[0][$column] = $_SESSION[$column];
			}

			return $res_data;
		}
		function createNullData() {
			
			foreach($this->db_columns as $column => $type){
				$value = "";
				/*
				if($type == "date"){
					$value = "0000-00-00";
				}elseif($type == "datetime"){
					$value = "0000-00-00 00:00:00";
				}elseif(strpos($type,"int") !== false){
					$value = 0;
				}
				*/
				$res_data[0][$column] = $value;
			}
			return $res_data;
		}
		function insert() {
			if(isset($this->id_name) && $this->id_name != ""){
				#idがセットされていればアップデート
				if(isset($this->id_value) && $this->id_value != "" && $this->id_value != 0 && strpos($this->db_columns[$this->id_name],"auto_increment") !== false){
					self::update();
				}else{
					$column_name = array();
					$column_value = array();
					foreach($this->db_columns as $column => $type){
						$column_name[] = "`".$column."`";
						$column_value[] = isset($this->$column) ? "'".$this->$column."'" : "''";
					}
					$column_name_str = implode(",",$column_name);
					$column_value_str = implode(",",$column_value);
					
					$sql = "INSERT INTO `{$this->table_name}` (".$column_name_str.") VALUES (".$column_value_str.");";
					#echo $sql."\n";
					$res = $this->database->databasequery($sql);
				}
			}
		}
		function insert4hearingsheet(){
			if(isset($this->id_name) && $this->id_name != ""){
				#idがセットされていればアップデート
				if(isset($this->id_value) && $this->id_value != "" && $this->id_value != 0 && strpos($this->db_columns[$this->id_name],"auto_increment") !== false){
					self::update();
				}else{
					$column_name = array();
					$column_value = array();
					foreach($this->db_columns as $column => $type){
						$column_name[] = "`".$column."`";
						$column_value[] = isset($this->$column) ? "'".$this->$column."'" : "''";
					}
					$column_name_str = implode(",",$column_name);
					$column_value_str = implode(",",$column_value);
					
					$sql = "INSERT INTO `{$this->table_name}` (".$column_name_str.") VALUES (".$column_value_str.");";
					#echo $sql;
					$res = $this->database->databasequery($sql);
					
					$sql = "select max( client_id ) from `{$this->table_name}`";
					$result = $this->database->databasequery($sql);
					$row = $result->fetchRow();
					$client_id = $row[0];
					$tmp = 1000000000 + intval($client_id);
					$salon_id = 's'.substr($tmp,1,9);
					
					$sql = "update `{$this->table_name}` set salon_id = '{$salon_id}' where client_id = {$client_id};";
					$res = $this->database->databasequery($sql);
				}
			}
		}
		function setSalon_id2hearingsheet() {
			$sql = "select max( client_id ) from hearingsheet";
			$result = $this->database->databasequery($sql);
			$row = $result->fetchRow();
			$client_id = $row[0];
			$tmp = 1000000000 + intval($client_id);
			$salon_id = 's'.substr($tmp,1,9);
			
			$sql = "update hearingsheet set salon_id = '{$salon_id}' where client_id = {$client_id};";
			$res = $this->database->databasequery($sql);
		}
		function setGeometry(){
			if(isset($this->sln_latitude) && isset($this->sln_longitude)){
				
				$sql = "select count(*) from `{$this->table_name}` where {$this->id_name} = '{$this->id_value}';";
				$res = $this->database->databasequery($sql);
				$row = $res->fetchRow();
				if($row[0] == 0){
					$sql = "INSERT INTO `{$this->table_name}` ({$this->id_name}, latlng) values('{$this->id_value}', GeomFromText('POINT({$this->sln_longitude} {$this->sln_latitude})'));";
					$res = $this->database->databasequery($sql);
				}else{
					$sql = "UPDATE `{$this->table_name}` SET  latlng = GeomFromText('POINT({$this->sln_longitude} {$this->sln_latitude})') WHERE {$this->id_name} = '{$this->id_value}';";
					$res = $this->database->databasequery($sql);
				}
				#echo $sql;
			}
		}
		function update() {
			if(isset($this->id_name) && $this->id_name != ""){
				if(isset($this->id_value) && $this->id_value != "" && $this->id_value != 0){
					#レコードがなければインサート
					$sql = "select count(*) from `{$this->table_name}` where {$this->id_name} = '{$this->id_value}';";
					$res = $this->database->databasequery($sql);
					$row = $res->fetchRow();
					if($row[0] == 0){
						self::insert();
					}else{
						$sql = "UPDATE `{$this->table_name}` SET";
					
						//更新データのみUpdate対象とする(データセットがされた場合NULL値以外になる)
						$temp_sql = array();
						foreach($this as $key => $value){
							if(array_key_exists($key,$this->db_columns)){
								if($value !== null){
									$temp_sql[] = "$key = '$value'";
								}
							}
						}
							
						//更新対象がある場合だけ実行する
						if(!$temp_sql){
							return false;
						}else{
							$sql .= "\n".implode(",\n",$temp_sql)."\n";
							$sql .= "WHERE {$this->id_name} = '{$this->id_value}';";
						}
						#echo $sql."\n";
						$res = $this->database->databasequery($sql);
					}
				}#endif
			}
		}
		function update_multiCond() {
			
			$sql = "UPDATE `{$this->table_name}` SET";
		
			//更新データのみUpdate対象とする(データセットがされた場合NULL値以外になる)
			$temp_sql = array();
			foreach($this as $key => $value){
				if(array_key_exists($key,$this->db_columns)){
					if($value !== null){
						$temp_sql[] = "$key = '$value'";
					}
				}
			}
				
			//更新対象がある場合だけ実行する
			if(!$temp_sql){
				return false;
			}else{
				$sql .= "\n".implode(",\n",$temp_sql)."\n";
				#$sql .= "WHERE {$this->id_name} = '{$this->id_value}';";
				
				$sql .= "WHERE 1";
				
				if(count($this->conditions) > 0){
					foreach($this->conditions as $column => $expression){
						$sql .= " and {$column} {$expression}";
					}
				}
				if(isset($this->id_name) && $this->id_name != "" && isset($this->id_value) && $this->id_value != ""){
					$sql .= " and {$this->id_name} = '{$this->id_value}'";
				}
			}
			#echo "<!-- ".$sql." -->";
			#echo $sql;
			$res = $this->database->databasequery($sql);
				
		}
		function isRegist(){
			$sql = "select count(*) from `{$this->table_name}` where {$this->id_name} = '{$this->id_value}';";
			$res = $this->database->databasequery($sql);
			$row = $res->fetchRow();
					
			return $row[0];
		}
		function getClient_idFromSalon_id(){
			$client_id = "";
			
			if(isset($this->salon_id) && $this->salon_id != ""){
				$sql = "select client_id from salon where salon_id = '{$this->salon_id}';";
				#echo $sql;
				$res = $this->database->databasequery($sql);
				$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
				$client_id = $row['client_id'];
			}
			
			return $client_id;
		}
		function getSalon_idFromClient_id(){
			$salon_id = "";
			
			if(isset($this->client_id) && $this->client_id != ""){
				$sql = "select salon_id from salon where client_id = '{$this->client_id}';";
				#echo $sql;
				$res = $this->database->databasequery($sql);
				$row = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
				$salon_id = $row['salon_id'];
			}
			
			return $salon_id;
		}
	}
?>