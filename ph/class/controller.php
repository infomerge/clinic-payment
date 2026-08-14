<?php
/**
 * 画面コントローラ抽象クラス
 */

abstract class CONTROLLER {
	use LOGGING;

	/**
	 * テンプレートエンジン
	 * @var Smarty
	 */
	protected $smarty;

	/**
	 * 認証が必要かどうか
	 * @var boolean
	 */
	protected $required_login = true;
	
	/**
	 * ログイン情報
	 * @var array
	 */
	protected $account_data = null;

	/**
	 * メニュー種別
	 * @var string
	 */
	protected $navi_type = '';

	/**
	 * コンストラクタ
	 * 
	 * @param Smarty $smarty テンプレートエンジン
	 * 
	 */
	public function __construct( $smarty = null ) {
		// 認証が必要なページではログインの確認を行う
		if ( $this->required_login ) {
			$this->checkLogin();
		}
		
		if ( ! isset( $smarty ) ) {
			$smarty = new Smarty();
		}
		$this->smarty = $smarty;
	}

	/**
	 * デストラクタ
	 */
	function __destruct() {
	}

	/**
	 * ログインの確認
	 * 
	 * @return array/false ログイン中のアカウント情報
	 */
	protected function checkLogin() {
		// アカウントテーブルモデル
		$common = new COMMON ();
		
		// セッション情報取得を取得する
		$common->id = $_SESSION['id'];
		$common->password = $_SESSION['password'];
		
		// ログイン情報確認
		$result = $common->checkid();
		
		// 1行取得
		$row = $result->fetchRow();

		// 件数が0ならIDとパスワードの組み合わせが不正
		if ( 0 == $row[0] ) {
			$this->forceLogout();
			return false;
		}

		// ログインアカウントの情報を取得
		$this->account_data =[
			'login_name' => $row[1], // アカウント名
			'authority_id' => $row[2], // 権限ID
			'account_id' => $row[3], // アカウントID
		];
		
		return true;
	}
	
	/**
	 * ログアウトしてエラーメッセージを表示
	 */
	protected function forceLogout() {
		$_SESSION['id'] = '';
		$_SESSION['password'] = '';

		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /index.php?error=error");

		exit();
	}
	
	/**
	 * 入力パラメータ取得
	 * 
	 * @param string $key キー
	 * @param mixed $default デフォルト値
	 * @param string $method
	 */
	protected function getParam( $key, $default = null, $method = null ) {
		// 所得する配列を切り替える
		switch ( strtoupper( $method ) ) {
			case 'GET':
				$value = ( isset( $_GET[$key] ) ) ? $_GET[$key] : $default;
				break;
			case 'POST':
				$value = ( isset( $_POST[$key] ) ) ? $_POST[$key] : $default;
				break;
			default:
				$value = ( isset( $_REQUEST[$key] ) ) ? $_REQUEST[$key] : $default;
				break;
		}
		return $value;
	}

	
}