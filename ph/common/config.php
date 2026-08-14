<?php 
/**
 * 必ず読み込む設定ファイル
 */

// セッション開始
session_start();

// エラーメッセージ表示モード
ini_set( 'display_errors', 1 );
// error_reporting(E_ALL);

// 基本ディレクトリ
if ( ! defined( 'BASE_DIR' ) ) {
	define( 'BASE_DIR', dirname( dirname( __FILE__ ) ) );
}

ini_set('include_path', BASE_DIR . '/libs' . PATH_SEPARATOR . ini_get('include_path'));


// 共通で使うクラスを読み込んでおく
require_once ( BASE_DIR . '/common/database.php' ); // DB接続
require_once ( BASE_DIR . '/common/smarty_settings.php' ); // テンプレートエンジン設定

// 簡易オートローダー
spl_autoload_register( function ( $class_name ) {
	static $dir_list = [
		BASE_DIR . '/class/',
		BASE_DIR . '/class/model/',
		BASE_DIR . '/class/controller/',
	];
	
	foreach ( $dir_list as $dir_name ) {
		$class_path = $dir_name . strtolower( $class_name ) . ".php";
		if ( is_file( $class_path ) ) {
			require $class_path;
			return true;
		}
	}
} );


?>