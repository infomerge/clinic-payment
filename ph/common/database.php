<?php

class DATABASE {
    var $db;
    
    function connectdb() {
        if (!defined('DBNAME')) {
            include_once dirname(__FILE__) . '/../class/config.php';
        }

        $server = DBHOST;
        $dbname = DBNAME;
        $user = DBUSER;
        $pass = DBPASS;

        $this->db = new PDO("mysql:dbname={$dbname};host={$server};charset=utf8",$user,$pass);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }

    function disconnect() {
        $this->db = null;
    }

    // データベースへSQLの実行を依頼し、resultへ戻す
    function databasequery($sql) {
        $result = $this->db->query($sql);

        return $result;
    }
}