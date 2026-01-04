<?php

/**
 * デバッグ用クラス
 */
class Debug
{

    protected static $start_time;
    protected static $profiles = array();

    /** インスタンスを生成させない */
    protected function __construct()
    {

    }

    /**
     * プロファイリング開始
     * @param string $name
     */
    public static function startProfile($name='default')
    {
        $backtrace = debug_backtrace();
        self::$profiles[$name]['profile_name'] = $name;
        self::$profiles[$name]['start_time']   = microtime(true);
        self::$profiles[$name]['start_file']   = "{$backtrace[0]['file']} ({$backtrace[0]['line']})";
        self::$profiles[$name]['start_memory'] = memory_get_usage();
    }

    /**
     * プロファイリング終了
     * @param  string $name
     * @return array
     */
    public static function endProfile($name='default')
    {
        $backtrace                                  = debug_backtrace();
        self::$profiles[$name]['end_time']          = microtime(true);
        self::$profiles[$name]['end_memory']        = memory_get_usage();
        self::$profiles[$name]['end_file']          = "{$backtrace[0]['file']} ({$backtrace[0]['line']})";
        $exec_time                                  = self::$profiles[$name]['end_time'] - self::$profiles[$name]['start_time'];
        $memory_usage                               = self::$profiles[$name]['end_memory'] - self::$profiles[$name]['start_memory'];
        $memory_peak_usage                          = memory_get_peak_usage();
        self::$profiles[$name]['execution_time']    = sprintf('%0.5fSec', $exec_time);
        self::$profiles[$name]['memory_usage']      = sprintf('%0.5fMB', $memory_usage / BYTE_1MB) . sprintf('(%sbyte)', number_format($memory_usage));
        self::$profiles[$name]['memory_peak_usage'] = sprintf('%0.5fMB', $memory_peak_usage / BYTE_1MB) . sprintf('(%sbyte)', number_format($memory_peak_usage));
        return self::$profiles[$name];
    }

    /**
     * デバッグダンプ
     * @param mixed   $value   値
     * @param boolean $display 画面に表示するか否か
     */
    public static function dump($value=null, $display = true)
    {

        //バックトレース取得
        $backtrace = debug_backtrace();

        //XDEBUGの制限解除
        ini_set('xdebug.var_display_max_children', -1);
        ini_set('xdebug.var_display_max_data', -1);
        ini_set('xdebug.var_display_max_depth', -1);

        //出力をバッファリング
        ob_start();
        var_dump($value);
        $ob    = ob_get_clean();
        $value = PHP_EOL . html_entity_decode(preg_replace('/\]\=\>\n(\s+)/m', '] => ', $ob), ENT_QUOTES);
        $value = mb_convert_encoding($value, mb_internal_encoding(), 'UTF-8,SJIS,SJIS-win,EUC-JP');

        if($display){
            echo '<pre class="debug-output" style="border:1px solid #bdc3c7; border-radius:2px; background:#ecf0f1; color:#34495e; padding:4px; clear:both; margin-top:2px; margin-bottom:2px;">';
            echo '<span style="color:#c0392b; font-weight:bold;">' . "Dump - File: {$backtrace[0]['file']}, Line: {$backtrace[0]['line']}" . '</span>';
            $pattern     = array(
                '/NULL/',
                '/bool\((.+)\)/',
                '/string\((\d+)\)/',
                '/int\((\d+)\)/',
                '/array\((\d+)\)/',
                '/(object.+) \((\d+)\)/',
                '/float\((.+)\)/',
                '/(\[(.+)\] => )/',
                '/\{\n/',
                '/\}\n/'
            );
            $replacement = array(
                '<span style="color:#2980b9;font-weight:bold;">null</span>',
                '<span style="color:#2980b9;font-weight:bold;">bool</span> $1',
                '<span style="color:#2980b9;font-weight:bold;">string[$1]</span>',
                '<span style="color:#2980b9;font-weight:bold;">int</span> $1',
                '<span style="color:#2980b9;font-weight:bold;">array[$1]</span>',
                '<span style="color:#2980b9;font-weight:bold;">$1[$2]</span>',
                '<span style="color:#2980b9;font-weight:bold;">float</span> $1',
                '<span style="color:#8e44ad;">$2</span> => ',
                '<span style="color:#2980b9;font-weight:bold;">' .  "(\n" . '</span>',
                '<span style="color:#2980b9;font-weight:bold;">' .  ")\n" . '</span>',
            );

            $display_value = preg_replace($pattern, $replacement, $value);
            echo $display_value;
            echo '</pre>';
        }

        //$message = "File: {$backtrace[0]['file']}, Line: {$backtrace[0]['line']}";
        //$message .= $value;
        //error_log($message);
    }

    /**
     * デバッグダンプCLI用
     * @param mixed   $value   値
     * @param boolean $display 画面に表示するか否か
     */
    public static function cliDump($value=null, $display = true)
    {

        //バックトレース取得
        $backtrace = debug_backtrace();

        //XDEBUGの制限解除
        ini_set('xdebug.var_display_max_children', -1);
        ini_set('xdebug.var_display_max_data', -1);
        ini_set('xdebug.var_display_max_depth', -1);

        //出力をバッファリング
        ob_start();
        var_dump($value);
        $ob    = ob_get_clean();
        $value = html_entity_decode(preg_replace('/\]\=\>\n(\s+)/m', '] => ', $ob), ENT_QUOTES);
        $value = mb_convert_encoding($value, mb_internal_encoding(), 'UTF-8,SJIS,SJIS-win,EUC-JP');

        if($display){
            echo $value;
        }

        //$message = "File: {$backtrace[0]['file']}, Line: {$backtrace[0]['line']}";
        //$message .= $value;
        //Logger::debug($message);
    }

}
