<?php
/**
 * 請求書・領収書の自由診療／販売品／その他項目を整形する。
 * - 入力時の改行を反映
 * - 既存の複数明細結合「/」も改行として扱う
 * - 半角カナは全角化してから折り返す（PDFフォントで全角幅になるため）
 */
if (!function_exists('format_appendix_item_html')) {
    function format_appendix_item_html($text, $emptyHtml = '<br>') {
        $text = isset($text) ? (string)$text : '';
        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        $text = trim($text);
        if ($text === '') {
            return $emptyHtml;
        }

        if (function_exists('mb_convert_kana')) {
            $text = mb_convert_kana($text, 'KV', 'UTF-8');
        }
        $text = str_replace('/', "\n", $text);
        $lines = explode("\n", $text);
        $wrapped = array();
        $maxWidth = 40;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            while (mb_strwidth($line, 'UTF-8') > $maxWidth) {
                $chunk = '';
                $width = 0;
                $len = mb_strlen($line, 'UTF-8');
                $cut = 0;
                for ($i = 0; $i < $len; $i++) {
                    $ch = mb_substr($line, $i, 1, 'UTF-8');
                    $w = mb_strwidth($ch, 'UTF-8');
                    if ($width + $w > $maxWidth && $chunk !== '') {
                        break;
                    }
                    $chunk .= $ch;
                    $width += $w;
                    $cut = $i + 1;
                }
                if ($chunk === '') {
                    $chunk = mb_substr($line, 0, 1, 'UTF-8');
                    $cut = 1;
                }
                $wrapped[] = $chunk;
                $line = mb_substr($line, $cut, null, 'UTF-8');
            }
            if ($line !== '') {
                $wrapped[] = $line;
            }
        }

        if (count($wrapped) === 0) {
            return $emptyHtml;
        }

        $escaped = array();
        foreach ($wrapped as $wline) {
            $escaped[] = htmlspecialchars($wline, ENT_QUOTES, 'UTF-8');
        }
        return implode('<br />', $escaped);
    }
}
