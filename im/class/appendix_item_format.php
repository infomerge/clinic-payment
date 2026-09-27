<?php
/**
 * 請求書・領収書の自由診療／販売品／その他項目を整形する。
 * - 入力時の改行を反映
 * - 既存の複数明細結合「/」も改行として扱う
 * - 半角カナは PDF フォントで全角幅になるため全角化してから折り返す
 * - 各行は nowrap で mPDF のハイフン折り返しを防ぐ
 */
if (!function_exists('appendix_item_visual_width')) {
    function appendix_item_visual_width($text) {
        $width = 0;
        $len = mb_strlen($text, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $ch = mb_substr($text, $i, 1, 'UTF-8');
            $ord = 0;
            $utf4 = mb_convert_encoding($ch, 'UCS-4BE', 'UTF-8');
            if ($utf4 !== false && strlen($utf4) === 4) {
                $unpacked = unpack('N', $utf4);
                $ord = isset($unpacked[1]) ? $unpacked[1] : 0;
            }
            # 半角カタカナ（濁点・半濁点含む）は請求書フォント上で全角幅
            if ($ord >= 0xFF61 && $ord <= 0xFF9F) {
                $width += 2;
            } else {
                $width += mb_strwidth($ch, 'UTF-8');
            }
        }
        return $width;
    }
}

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
            while (appendix_item_visual_width($line) > $maxWidth) {
                $chunk = '';
                $width = 0;
                $len = mb_strlen($line, 'UTF-8');
                $cut = 0;
                for ($i = 0; $i < $len; $i++) {
                    $ch = mb_substr($line, $i, 1, 'UTF-8');
                    $w = appendix_item_visual_width($ch);
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

        $blocks = array();
        foreach ($wrapped as $wline) {
            $blocks[] = '<div style="text-align:center;font-size:12px;white-space:nowrap;">'
                . htmlspecialchars($wline, ENT_QUOTES, 'UTF-8')
                . '</div>';
        }
        return implode('', $blocks);
    }
}
