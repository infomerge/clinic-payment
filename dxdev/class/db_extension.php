<?php
//include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'database.php';
#include_once( dirname(dirname(__FILE__))."/common/database.php");
	include_once dirname(dirname(__FILE__)).'/class/config.php';

class DbEx
{

    protected static $connection = null;

    /**
     * 接続
     */
    public static function connect($dbname = 'aggdb')
    {
      $database = new PDO('mysql:dbname='.DBNAME.';host=localhost;charset=utf8','xs547384_dxdev','wwxlkl7m');
      $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      self::$connection = $database;

        #$database = new DATABASE();
        #$database->connectdb($dbname);
        #self::$connection = $database->db;
    }

	public static function destruct(){
		// MySQLの接続を解除する
		#self::$connection->disconnect();
		self::$connection = null;
	}

    /**
     * 参照系クエリ
     * @param  string $sql
     * @return mixed
     */
    public static function query($sql)
    {
        $mdb2 = self::$connection->query($sql);
        if(PEAR::isError($mdb2)){
            self::outputError($mdb2);
            return null;
        }else{
            if($mdb2->rowCount() > 0){
                $result = $mdb2->fetchAll(MDB2_FETCHMODE_ASSOC);
                return $result;
            }else{
                return null;
            }
        }
    }

    /**
     * 実行系クエリ
     * @param  string $sql
     * @return mixed
     */
/*
    public static function exec($sql)
    {
        $exec = self::$connection->exec($sql);
        if(PEAR::isError($exec)){
            self::outputError($exec);
            return null;
        }else{
            return $exec;
        }
    }
*/
    /**
     * 全件SELECT
     * @param  string $table
     * @param  string $columns
     * @param  string $postfix
     * @return mixed
     */
    public static function select($dbname,$table, $columns = '*', $postfix = '')
    {
        if(self::$connection === null){ self::connect($dbname);}
        $sql  = "SELECT {$columns} FROM {$table} {$postfix}";#echo "<!-- ".$sql." -->\n";
		#echo $sql."<br>\n";

        $mdb2 = self::$connection->query($sql);
        #print_r($mdb2); echo "---".$mdb2->rowCount()."---";exit;
        #if($mdb2->rowCount() > 0){
            $result = $mdb2->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        #}else{
        #    return null;
        #}

    }

    /**
     * 1行SELECT
     * @param  string $table
     * @param  string $columns
     * @param  string $postfix
     * @return mixed
     */
    public static function selectRow($dbname, $table, $columns = '*', $postfix = '')
    {
        if(self::$connection === null){ self::connect($dbname);}
        $sql  = "SELECT {$columns} FROM {$table} {$postfix}";
		#echo "<!-- ".$sql." -->";
		#echo $sql."\n";exit;
        $mdb2 = self::$connection->query($sql);
        if($mdb2->rowCount() > 0){
            $result = $mdb2->fetch(PDO::FETCH_ASSOC);
            return $result;
        }else{
            return null;
        }
    }

    /**
     * COUNTする
     * @param  string $table
     * @return mixed
     */
    public static function count($table)
    {
        #$result = self::selectRow($table, 'COUNT(*) AS count');
        $result = self::select($table, 'COUNT(*) AS count');
        return $result['count'];
    }

    /**
     * UPDATEする
     * @param  string $table
     * @param  array  $data
     * @param  string $postfix
     * @return mixed
     */
    public static function update($dbname, $table, $data, $postfix = '')
    {
        if(self::$connection === null){ self::connect($dbname);}
        $set_array = array();
        foreach($data as $column => $val){
            if($val === null){
                array_push($set_array, "{$column}=NULL");
            }else if($val === 'NOW()'){
                array_push($set_array, "{$column}=NOW()");
            }else{
                if(is_bool($val)){
                    $val = ($val === true) ? 1 : 0;
                }
                array_push($set_array, "{$column}='{$val}'");
            }
        }
        $set_val = implode(',', $set_array);
        $sql     = "UPDATE {$table} SET {$set_val} {$postfix}";
		#echo $sql;exit;
        return self::$connection->exec($sql);
    }

    /**
     * INSERTする
     * @param  string $table
     * @param  array $data
     * @return mixed
     */
    public static function insert($dbname, $table, $data)
    {
        if(self::$connection === null){ self::connect($dbname);}
        $columns_array = array();
        $values_array  = array();
        foreach($data as $column => $val){
            array_push($columns_array, $column);
            if($val === null){
                array_push($values_array, 'NULL');
            }else if($val === 'NOW()'){
                array_push($values_array, 'NOW()');
            }else{
                if(is_bool($val)){
                    $val = ($val === true) ? 1 : 0;
                }
                array_push($values_array, "'{$val}'");
            }
        }
        $columns = implode(',', $columns_array);
        $values  = implode(',', $values_array);
        $sql     = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
		echo $sql;
        return self::$connection->exec($sql);
		#return $sql;
    }

    /**
     * INSERTする(複数行)
     * @param  string $table
     * @param  array $data
     * @return mixed
     */
    public static function insertMultiLine($dbname, $table, $data)
    {
        if(self::$connection === null){ self::connect($dbname);}
        $columns_array = array();
        $values_array  = array();
        foreach($data as $row_index => $row_data){

            $row_values = array();
            foreach($row_data as $column => $val){

                //最初の行だけカラムを取得する
                if((int)$row_index === 0){
                    array_push($columns_array, $column);
                }

                if($val === null){
                    array_push($row_values, 'NULL');
                }else if($val === 'NOW()'){
                    array_push($row_values, 'NOW()');
                }else{
                    if(is_bool($val)){
                        $val = ($val === true) ? 1 : 0;
                    }
                    array_push($row_values, "'{$val}'");
                }

            }

            array_push($values_array, '(' . implode(',', $row_values) . ')');

        }
        $columns = implode(',', $columns_array);
        $values  = implode(',', $values_array);
        $sql     = "INSERT INTO {$table} ({$columns}) VALUES {$values}";
        return self::$connection->exec($sql);
    }

    /**
     * エラー出力
     * @param $mdb2
     */
    protected static function outputError($mdb2)
    {
        echo '<pre style="border:1px solid #bdc3c7; border-radius:2px; background:#c0392b; color:#fff; padding:4px; clear:both; margin-top:2px; margin-bottom:2px; width:100%; overflow:auto;">';
        echo '<span style="font-weight:bold; font-size:large;">Database error!</span>' . PHP_EOL;
        echo 'Message     : ' . $mdb2->getMessage() . PHP_EOL;
        echo 'Information : ' . $mdb2->getUserinfo() . PHP_EOL;
        echo '</pre>';
    }

}
