<?php
ini_set( 'display_errors', 1 );
include_once '../class/common.php';
require_once('../class/db_extension.php');
include_once "../class/config.php";


if(isset($_GET['bank_id']) && $_GET['bank_id'] != ""){
  $bank_id = htmlspecialchars($_GET['bank_id']);
  $dbname = DBNAME;
  $table = "bank_master";
  $columns = "branch_id,name";
  $postfix = " where bank_id = '{$bank_id}' and class= 2";
  $branch_master = DbEx::select($dbname, $table, $columns, $postfix);
  echo json_encode($branch_master);
  #print_r($branch_master);
}
