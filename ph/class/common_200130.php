<?php
	#include_once '../common/database.php';
	include_once dirname(dirname(__FILE__)).'/common/database.php';

	class COMMON {
		var $database;
		var $id;
		var $password;
		
		function __construct() {
			// MySQLと接続する
			$this->database = new DATABASE;
			$this->database->connectdb();
		}
		function __destruct(){
			// MySQLの接続を解除する
			$this->database->disconnect();
		}
	
		// ログインユーザIDとログインパスワードの検証
		function checkid() {
			$sql = "select count(*),account_name,authority_id,account_id from _cms_account where login_id = '" . $this->id . "' and password = '" . $this->password . "'";

			$result = $this->database->databasequery($sql);
			#$sql = "select count(*),account_name from account where login_id = ? and password = ?";
			#$params = array($this->id, $this->password);
			#$result = $this->database->prepExec($sql, $params);
	
			if (MDB2::isError( $result )) {
			#	header("HTTP/1.1 301 Moved Permanently");
			#	header("Location: /index.php?error=unknownerror");
			}
			return $result;
		}
		
		function checkid_hospital() {
			$sql = "select count(*),inst_name as account_name,account_id from account_info where login_id = '" . $this->id . "' and password = '" . $this->password . "'";

			$result = $this->database->databasequery($sql);
			#$sql = "select count(*),account_name from account where login_id = ? and password = ?";
			#$params = array($this->id, $this->password);
			#$result = $this->database->prepExec($sql, $params);
	
			if (MDB2::isError( $result )) {
			#	header("HTTP/1.1 301 Moved Permanently");
			#	header("Location: /index.php?error=unknownerror");
			}
			return $result;
		}
		
		function getaccountid() {
			$sql = "select account_id from account where login_id = '" . $this->id . "' and password = '" . $this->password . "'";
			$result = $this->database->databasequery($sql);
	
			if (MDB2::isError( $result )) {
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: /index.php?error=unknownerror");
			}
			$row = $result->fetchRow();
			return $row[0];
		}
		function isEditable($client_id,$account_id){
			$tmp = array();
			
			/*
			if($account_id != 0 && $account_id != ""){
				$sql = "select account_id from account_relationship where allowed_account_id = '{$account_id}';";
				$res = $this->database->databasequery($sql);
				while($row = $res->fetchRow()){
					$tmp[] = $row['0'];
				}
				$allowed_account_ids = implode(",",$tmp);
			}
			*/
			$sql = "select count(*) from account,coveragesheet where account.account_id = coveragesheet.registration_account_id and coveragesheet.client_id = '{$client_id}'";
			#$sql .= " and coveragesheet.registration_account_id in ({$allowed_account_ids})";
			#echo $sql;
			$res = $this->database->databasequery($sql);
			$row = $res->fetchRow();
			
			if($row[0] == 1)	return true;
			else	return false;
		}
		function mail_sender($to,$bcc, $subject, $body)
		{
			require_once 'Mail.php';  //[Mail.phpのパス]
	
			$mail_from = 'admin@eparkhair.jp';  //メールのFromアドレス
			#$from_name = 'EPARKヘアサロン';
			$from_name = 'EPARKサロンマネージ';
			$SMTP = 'mail.eparkhair.jp';  #SMTPサーバーのホスト
	
			$org = mb_internal_encoding();
			mb_internal_encoding("ISO-2022-JP");
			$subject = mb_encode_mimeheader(mb_convert_encoding($subject, "ISO-2022-JP", "UTF-8"),"ISO-2022-JP","B","");
			mb_internal_encoding($org);
	
			$body = mb_convert_encoding($body, "ISO-2022-JP", "UTF-8");
	
			$params = array(
				"host" => $SMTP,
				"port" => "587",
				"auth" => true,
				"username" => "admin@eparkhair.jp",
				"password" => "eparkhairinfo",
				"sendmail_args" => "f"
			);
	
			$mailObject = Mail::factory("smtp", $params);
	
			$headers = array(
				"To" => $to,
				"Bcc" => $bcc,
				"From" => mb_encode_mimeheader($from_name) ."<" .$mail_from.">",
				"Subject" => $subject,
				"Return-Path" => $mail_from
			);
	
			$recipients = array($to,$bcc);
	
			$mailObject->send($recipients, $headers, $body);
		}
		function login_rsvmgr($passcode,$account_id){
			$sql = "select count(*) from account where account_id = '{$account_id}' and rsvmgr_passcode = '{$passcode}';";
			$res = $this->database->databasequery($sql);
			$row = $res->fetchRow();
			
			if($row[0] == 1)	return true;
			else	return false;
		}

	}
?>
