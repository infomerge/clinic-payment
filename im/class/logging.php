<?php
trait LOGGING {

	/**
	 * エラーメッセージ
	 * @var array
	 */
	protected $error_message = [];

	/**
	 * エラーメッセージを追加する
	 * @param string|array $message
	 */
	public function addErrorMessage ( $message ) {
		if ( ! is_array( $this->error_message ) ) {
			$this->error_message = [];
		}
	
		if ( is_array( $message ) ) {
			$message = implode( "\n", $message );
		}
		$this->error_message[] = $message;
	}
	
	/**
	 * エラーメッセージを返す
	 * @param boolean $clear 
	 * @return array
	 */
	public function getErrorMessage ( $clear = true ) {
		$error_message = $this->error_message;
		if ( $clear ) {
			$this->error_message = [];
		}
		
		return $error_message;
	}
}