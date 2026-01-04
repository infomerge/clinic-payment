<?php
	include_once '../common/database.php';
	include_once '../class/base_execution.php';

	class COVERAGESHEET extends BASE_EXECUTION {
		
		function selectCoverageList() {
			$res_data = array();
			$this->selectStart = ($this->page - 1) * $this->selectLimit;
			
			// 件数の取得
			$sql = "SELECT count(*) FROM pharmacy_shop where 1 = 1 ";
			if(isset($this->status) && $this->status != "") {
				$sql .= " and status = $this->status";
			}
			
			if(isset($this->shop_id) && $this->shop_id != ""){
				$sql .= " and coveragesheet.shop_id = '$this->shop_id'";	
			}
			
			if(isset($this->shop_name) && $this->shop_name != "") {
				$sql .= " and shop_name like '%$this->shop_name%'";
			}
			if(isset($this->shop_name_kana) && $this->shop_name_kana != "") {
				$sql .= " and shop_name_kana like '%$this->shop_name_kana%'";
			}
			if(isset($this->tel) && $this->tel != "") {
				$sql .= " and tel like '%$this->tel%'";
			}
			
			$sql .= " ORDER BY shop_id";
			
			if(isset($this->testview)){
				echo $sql."<BR>";
			}
			
		/*	
		list($msec, $sec) = explode(" ", microtime());
		$start = (float)$msec + (float)$sec;	
		*/
		
			$res = $this->database->databasequery($sql);
			while($row = $res->fetchRow()){
				$this->totalpage = ceil($row['0'] / $this->selectLimit);
				$this->datacount = $row['0'];
			}
			
			#echo $this->totalpage."---".$this->datacount."<BR>";
			
						
			// データの取得
			$sql = "SELECT * FROM pharmacy_shop where 1 = 1 ";		 
			 
			 
			if(isset($this->status) && $this->status != "") {
				$sql .= " and status = $this->status";
			}
			
			
			if(isset($this->shop_id) && $this->shop_id != ""){
				$sql .= " and coveragesheet.shop_id = '$this->shop_id'";	
			}
			
			if(isset($this->shop_name) && $this->shop_name != "") {
				$sql .= " and shop_name like '%$this->shop_name%'";
			}
			if(isset($this->shop_name_kana) && $this->shop_name_kana != "") {
				$sql .= " and shop_name_kana like '%$this->shop_name_kana%'";
			}
			if(isset($this->tel) && $this->tel != "") {
				$sql .= " and tel like '%$this->tel%'";
			}
			
			
			if(isset($this->sorttxt) && $this->sorttxt != "") {
				$sql .= " ORDER BY {$this->sorttxt} ";
			}else{
				$sql .= " ORDER BY shop_id ";
				#$sql .= " ORDER BY auction_id ";
			}
			
			
			
				#$sql .= " ORDER BY shop_id LIMIT $this->selectStart, $this->selectLimit;";
				$sql .= " LIMIT $this->selectStart, $this->selectLimit;";
				
			if(isset($this->testview)){
				echo $sql."<BR>";
			}
			
			$res = $this->database->databasequery($sql);
			
			while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
				$res_data[] = $row;
			}
			
			
			
			return $res_data;
		}
		
		
	}
?>