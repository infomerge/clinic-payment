<?php
	#include_once '../common/database.php';
	include_once dirname(dirname(__FILE__)).'/class/base_epksalon.php';

	class BASE_EPKSALON_CUSTOM extends BASE_EPKSALON {
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
						$sql .= " and {$column} = '{$expression}'";
					}
				}
				if(isset($this->orderby) && $this->orderby != ""){
					$sql .= " order by ".$this->orderby;
				}

				if(isset($this->limit) && $this->limit != ""){
					$sql .= " limit '{$this->limit}'";
				}

				if(isset($this->offset) && $this->offset != ""){
					$sql .= " offset '{$this->offset}'";
				}

				#echo "<!-- ".$sql."<br> -->";
				$res = $this->database->databasequery($sql);
				
				while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
					$res_data[] = $row;
				}
				return $res_data;
			#}
		}
	}
?>