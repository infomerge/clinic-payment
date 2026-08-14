<?php
	#include_once '../common/database.php';
	include_once dirname(dirname(__FILE__)).'/common/database.php';

	class ACCOUNT {
		var $database;
		var $page;
		var $totalpage;
		var $selectStart = 0;
		var $selectLimit = 20;
		var $table_name;
		#var $db_columns = array();
		var $exception_columns = array("account_id");	#$db_columnsから除外するカラム
		
		// 項目
		/*
		var $account_id;
		var $account_name;
		var $authority_id;
		var $type;
		var $area;
		var $login_id;
		var $password;
		var $submit_name;
		var $submit_email;
		var $expiration_date_from;
		var $expiration_date_to;
		var $suspension_flag;
		var $the_registration_date_and_time;
		var $registration_account_id;
		var $the_update_date_and_time;
		var $update_account_id;
		var $partner_code;
		*/
		
			
		function __construct() {
			// MySQLと接続する
			$this->database = new DATABASE;
			$this->database->connectdb();
			
			#対象テーブル
			$this->table_name = strtolower(get_class($this));
			
			#対象テーブルのカラム取得：テーブル改変対策
			$column_sql = "show columns from `{$this->table_name}`";
			$result = $this->database->databasequery($column_sql);
			while($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC)){
				if(!in_array($row['field'],$this->exception_columns))	$this->db_columns[$row['field']] = $row['type'];
			}
			
		}
		function __destruct(){
			// MySQLの接続を解除する
			$this->database->disconnect();
        }
		function select() {
			$res_data = array();
			$this->selectStart = ($this->page - 1) * $this->selectLimit;
			
			// 最大件数の取得
			$sql = "SELECT count(*) FROM {$this->table_name} WHERE suspension_flag = 0";
			
			#if(isset($this->account_id) && $this->account_id != "") {
			#	$sql .= " and account_id = {$this->account_id}";
			#}
			if(isset($this->account_name) && $this->account_name != "") {
				$sql .= " and account_name like '%{$this->account_name}%'";
			}
			$res = $this->database->databasequery($sql);
			while($row = $res->fetchRow()){
				$this->totalpage = ceil($row['0'] / $this->selectLimit);
			}
			
			// データの取得
			$sql = "SELECT * FROM {$this->table_name} WHERE suspension_flag = 0";
			
			#if(isset($this->account_id) && $this->account_id != "") {
			#	$sql .= " and account_id = {$this->account_id}";
			#}
			if(isset($this->account_name) && $this->account_name != "") {
				$sql .= " and account_name like '%{$this->account_name}%'";
			}
			if(!isset($this->account_id) || $this->account_id == "") {
				$sql .= " ORDER BY account_id LIMIT {$this->selectStart}, {$this->selectLimit};";
			}
			$res = $this->database->databasequery($sql);
			#echo $sql;
			while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){				
				$res_data[] = $row;
			}
			return $res_data;
		}
		
		function selectDetail() {
			$res_data = array();
			
			// データの取得
			$sql = "SELECT * FROM {$this->table_name} WHERE suspension_flag = 0";
			
			if(isset($this->account_id) && $this->account_id !== "") {
				$sql .= " and account_id = {$this->account_id}";
			}
			if(isset($this->account_name) && $this->account_name != "") {
				$sql .= " and account_name like '%{$this->account_name}%'";
			}
			if(isset($this->login_id) && $this->login_id != "") {
				$sql .= " and login_id = '{$this->login_id}'";
			}
			#echo $sql;
			$res = $this->database->databasequery($sql);
			
			while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
				
				$res_data[] = $row;
			}
			return $res_data;
		}
		function selectDisable() {
			$res_data = array();
			$this->selectStart = ($this->page - 1) * $this->selectLimit;
			
			// 最大件数の取得
			$sql = "SELECT count(*) FROM {$this->table_name} WHERE suspension_flag = 1";
			
			if(isset($this->account_id) && $this->account_id != "") {
				$sql .= " and account_id = {$this->account_id}";
			}
			if(isset($this->account_name) && $this->account_name != "") {
				$sql .= " and account_name like '%{$this->account_name}%'";
			}
			$res = $this->database->databasequery($sql);
			while($row = $res->fetchRow()){
				$this->totalpage = ceil($row['0'] / $this->selectLimit);
			}

			$sql = "SELECT * FROM {$this->table_name} WHERE suspension_flag = 1";
			
			if(isset($this->account_id) && $this->account_id != "") {
				$sql .= " and account_id = $this->account_id";
			}
			if(isset($this->account_name) && $this->account_name != "") {
				$sql .= " and account_name like '%{$this->account_name}%'";
			}
			
			if(!isset($this->account_id) || $this->account_id == "") {
				$sql .= " ORDER BY account_id LIMIT {$this->selectStart}, {$this->selectLimit};";
			}
			$res = $this->database->databasequery($sql);
			
			while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
				$res_data[] = $row;
			}
			return $res_data;
		}
		function createNullData() {
			
			foreach($this->db_columns as $column => $type){
				$value = "";
				if($type == "date"){
					$value = "0000-00-00";
				}elseif($type == "datetime"){
					$value = "0000-00-00 00:00:00";
				}elseif(strpos($type,"int") !== false){
					$value = 0;
				}
				$res_data[0][$column] = $value;
			}
			return $res_data;
		}
		function createNullAccount() {
			$res_data[] = array(
					"account_id" => "",
					"account_name" => "",
					"authority_id" => "",
					"type" => "",
					"area" => "",
					"login_id" => "",
					"password" => "",
					
					"submit_name" => "",
					"submit_email" => "",
					"partner_code" => "",
					
					"expiration_date_from" => "",
					"expiration_date_to" => "",
					"suspension_flag" => "",
					"the_registration_date_and_time" => "",
					"registration_account_id" => "",
					"the_update_date_and_time" => "",
					"update_account_id" => "",
					"parent_account_id" => ""
			);
			return $res_data;
		}
		function insert() {			
			$column_name = array();
			$column_value = array();
			foreach($this->db_columns as $column => $type){
				$column_name[] = "`".$column."`";
				$column_value[] = "'".$this->$column."'";
			}
			$column_name_str = implode(",",$column_name);
			$column_value_str = implode(",",$column_value);
			
			$sql = "INSERT INTO `{$this->table_name}` (".$column_name_str.") VALUES (".$column_value_str.");";
			
			$res = $this->database->databasequery($sql);
		}
		function UPDATE() {
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
				$sql .= "WHERE account_id = '{$this->account_id}';";
			}
			#echo $sql;
			if(isset($this->account_id) && $this->account_id != "" && $this->account_id != 0){
				$res = $this->database->databasequery($sql);
			}
		}
		function getPartnerList(){
			$sql = "SELECT account_id,account_name from {$this->table_name} where partner_code <> '' and suspension_flag = 0 ";		
			$res = $this->database->databasequery($sql);
			
			$data = array();
			$cnt = 0;
			while($row = $res->fetchRow()){
				$data[$cnt]['account_id'] = $row["0"];
				$data[$cnt]['account_name'] = $row["1"];
				$cnt++;
			}
			return $data;
		}
		function getAccountIdFromPartnerCode($partner_code){
			$sql = "SELECT account_id from {$this->table_name} where partner_code = '{$partner_code}' and suspension_flag = 0 ";		
			$res = $this->database->databasequery($sql);
			
			$row = $res->fetchRow();
			
			return $row[0];
		}
	}
?>