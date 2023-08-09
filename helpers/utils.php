<?php
namespace Dvk\Main;

use Bitrix\Main\Application;
use Bitrix\Main\Composite\Engine;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

/**
 * @package Main
 */
class Utils {

    public static function isProdUrl() {

        return ($_ENV['APP_URL'] == $_ENV['APP_PRODUCTION_URL']);
    }

    public static function validateUid($uid) {

        return (bool) preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uid);
    }

    public static function decodeUtf8JsonEntities($text) {

        $prevMatch = null;
        $matches = [];

        while(preg_match('/&#\d+;/', $text, $matches)) {

            $entity = $matches[0];
            if($entity) {
                if($entity == $prevMatch) {
                    break;
                }
                $prevMatch = $entity;
                $text = str_replace($entity, mb_convert_encoding($entity, 'UTF-8', 'HTML-ENTITIES'), $text);

            } else {
                break;
            }
        }

        return $text;
    }

    public static function decodeUtf8EscapeSequences($str) {

        // [U+D800 - U+DBFF][U+DC00 - U+DFFF]|[U+0000 - U+FFFF]
        $regex = '/\\\u([dD][89abAB][\da-fA-F]{2})\\\u([dD][c-fC-F][\da-fA-F]{2})
              |\\\u([\da-fA-F]{4})/sx';

        return preg_replace_callback($regex, function($matches) {

            if (isset($matches[3])) {
                $cp = hexdec($matches[3]);
            } else {
                $lead = hexdec($matches[1]);
                $trail = hexdec($matches[2]);

                // http://unicode.org/faq/utf_bom.html#utf16-4
                $cp = ($lead << 10) + $trail + 0x10000 - (0xD800 << 10) - 0xDC00;
            }

            // https://tools.ietf.org/html/rfc3629#section-3
            // Characters between U+D800 and U+DFFF are not allowed in UTF-8
            if ($cp > 0xD7FF && 0xE000 > $cp) {
                $cp = 0xFFFD;
            }

            // https://github.com/php/php-src/blob/php-5.6.4/ext/standard/html.c#L471
            // php_utf32_utf8(unsigned char *buf, unsigned k)

            if ($cp < 0x80) {
                return chr($cp);
            } else if ($cp < 0xA0) {
                return chr(0xC0 | $cp >> 6).chr(0x80 | $cp & 0x3F);
            }

            return html_entity_decode('&#'.$cp.';');
        }, $str);
    }

    public static function sortListByStringField($list, $field) {

        global $sort_by_field_key;
        $sort_by_field_key = $field;

        uasort($list, function($a, $b) {
            global $sort_by_field_key;
            $a = $a[$sort_by_field_key];
            $b = $b[$sort_by_field_key];
            return strcmp($a, $b);
        });

        return $list;
    }

    public static function sortListByNumField($list, $field) {

        global $sort_by_field_key;
        $sort_by_field_key = $field;

        uasort($list, function($a, $b) {
            global $sort_by_field_key;
            $a = (int) $a[$sort_by_field_key];
            $b = (int) $b[$sort_by_field_key];
            if($a > $b) {
                return 1;
            } else if($a < $b) {
                return -1;
            }
            return 0;
        });

        return $list;
    }

    public static function translit($string) {

        $string = mb_strtolower(trim($string));
        $string = str_replace("\r", " ", $string);
        $string = str_replace("\n", "", $string);
        $string = str_replace("\t", "", $string);
        $string = str_replace(".", "", $string);
        $string = str_replace(",", "", $string);
        $string = str_replace(":", "", $string);
        $string = str_replace(";", "", $string);
        $string = str_replace("/", " ", $string);
        $string = str_replace(" ", "-", $string);
        $string = str_replace("--", "-", $string);
        $string = str_replace("(", "", $string);
        $string = str_replace(")", "", $string);

        $liters = array(
            'a',   'b',   'v',

            'g',   'd',   'e',

            'yo',   'zh',  'z',

            'i',   'j',   'k',

            'l',   'm',   'n',

            'o',   'p',   'r',

            's',   't',   'u',

            'f',   'h',   'c',

            'ch',  'sh',  'shch',

            'soft_sign',  'y',   'hard_sign',

            'eh',   'yu',  'ya',
        );

        $converter = array();
        foreach($liters as $liter) {
            $cyr_sign = Loc::getMessage('ALNS_TRANSL_' . strtoupper($liter));
            if(in_array($liter, array('soft_sign', 'hard_sign'))) {
                $converter[$cyr_sign] = '';
            } else {
                $converter[$cyr_sign] = $liter;
            }
        }

        $letters = preg_split('/(?<!^)(?!$)/u', $string);

        $res = '';

        foreach($letters as $letter) {
            if(array_key_exists($letter, $converter)) {
                $trans_letter = $converter[$letter];
                if($trans_letter == 'h') {
                    $last_letter = substr($res, -1);
                    if(in_array($last_letter, array('c', 's', 'e', 'h'))) {
                        $trans_letter = 'kh';
                    }
                } else if($trans_letter == 'eh') {
                    $trans_letter = 'e';
                }
                $res .= $trans_letter;
            } else {
                $res .= $letter;
            }
        }

        return $res;
    }

    /**
     * Склонение существительных после числительных.
     *
     * @param string $value Значение
     * @param array $words Массив вариантов, например: array('товар', 'товара', 'товаров')
     * @param bool $show Включает значение $value в результирующею строку
     * @return string
     */
    public static function numWord($value, $words, $show = true)
    {
        $num = $value % 100;
        if ($num > 19) {
            $num = $num % 10;
        }

        $out = ($show) ? $value . ' ' : '';
        switch ($num) {
            case 1:
                $out .= $words[0];
                break;
            case 2:
            case 3:
            case 4:
                $out .= $words[1];
                break;
            default:
                $out .= $words[2];
                break;
        }

        return $out;
    }

    public static function show404() {
        /**
         * @global \CMain $APPLICATION
         */
        global $APPLICATION;
        if ($APPLICATION->RestartWorkarea())
        {
            if (!defined("BX_URLREWRITE"))
                define("BX_URLREWRITE", true);
            Engine::setEnable(false);
            require(Application::getDocumentRoot()."/404.php");
            die();
        }
    }

    public static function cutBySign($str, $length, $sign = ';') {

        $substr = $str;
        if($length && mb_strlen($str) > $length) {
            if(($pos = mb_strrpos($substr, $sign)) === $length) {
                return mb_substr($substr, 0, $pos);
            }
            $substr = mb_substr($str, 0, $length);
            if(($pos = mb_strrpos($substr, $sign)) !== false) {
                $substr = mb_substr($substr, 0, $pos);
            }
        }

        return $substr;
    }

    public static function fineText($text, $break = true, $maxLen = false) {

        $text = strip_tags($text);
        $text = htmlspecialchars_decode($text);

        $text = preg_replace('/([,.;])(?=\D)/', '$1 ', $text);
        if($break) {
            $text = str_replace("\r\n", "\n", $text);
            $text = str_replace("\r", "\n", $text);
            $text = str_replace("\n", '<br />', $text);
        }
        $text = preg_replace('|\s+|', ' ', $text);
        $text = trim($text);

        $text = self::mb_wordwrap($text, 20, ' ', true);

        if($maxLen && strlen($text) > $maxLen) {
            $text = substr($text, 0, $maxLen-3) . "...";
        }

        return $text;
    }

    public static function mb_ucfirst(string $str, string $encoding = null): string
    {
        if ($encoding === null) {
            $encoding = mb_internal_encoding();
        }
        return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) . mb_strtolower(mb_substr($str, 1, null, $encoding));
    }

    public static function lowerCyrCase($name) {

        $nameWords = explode(' ', $name);

        $upperWords = 0;
        $lowerWords = 0;
        foreach($nameWords as $i => $nameWord) {
            if(preg_match('/([а-яё]+)/u', $nameWord)) {
                $lowerWords++;
            }
        }
        foreach($nameWords as $i => $nameWord) {
            if(preg_match('/^([А-ЯЁ]+)$/u', $nameWord)) {
                $upperWords++;
            }
        }

        if($upperWords <= $lowerWords) {
            return $name;
        }

        foreach($nameWords as $i => $nameWord) {
            if(preg_match('/^([А-ЯЁ]+)$/u', $nameWord)) {
                $nameWords[$i] = mb_strtolower($nameWord);
            }
        }

        return self::mb_ucfirst(join(' ', $nameWords));
    }

    public static function mb_wordwrap($str, $width = 75, $break = "\n", $cut = false, $charset = null) {
        if ($charset === null) $charset = mb_internal_encoding();

        $pieces = explode($break, $str);
        $result = array();
        foreach ($pieces as $piece) {
            $current = $piece;
            while ($cut && mb_strlen($current) > $width) {
                $result[] = mb_substr($current, 0, $width, $charset);
                $current = mb_substr($current, $width, 2048, $charset);
            }
            $result[] = $current;
        }
        return implode($break, $result);
    }

    /**
     * @param $text
     * @return array|string|string[]|null
     * https://stackoverflow.com/questions/12126917/regexp-add-space-after-comma-but-not-when-comma-is-thousands-separator
     */
    public static function fixCommaSpaces($text) {

        return preg_replace("/([,.;:])([a-zа-я])/iu",'\1 \2', $text);
    }

    public static function canonicalURL() {

        $scriptUrl = trim($_SERVER['SCRIPT_URL'], "/");
        if(!empty($scriptUrl)) {
            $scriptUrl .= '/';
        }
        return rtrim($_ENV['APP_URL'], "/") . '/' . $scriptUrl;
    }

    public static function dateFormat($date, $format) {

        if(!$date) {
            return null;
        }

        if($date instanceof DateTime) {
            $objDate = $date;
        } else if(strlen($date)) {
            $objDate = new DateTime($date);
        }

        return $objDate->format($format);
    }

    /**
     * @param $tdDate
     * @return DateTime|null
     * @throws \Bitrix\Main\ObjectException
     */
    public static function getTdDate($tdDate) {

        $tdDate = (int) $tdDate;
        if(!$tdDate || $tdDate == 1000000) {
            return null;
        }
        $month= substr($tdDate, -2);
        $year = substr($tdDate, 0, 4);
        $strDate = sprintf("01.%s.%s", $month, $year);

        return new DateTime($strDate);
    }

    public static function redirect($url, $status = 'HTTP/1.1 301 Moved Permanently') {

        if(stripos($url, '/') === 0) {
            $url = self::siteUrl() . $url;
        }

        header($status);
        header("Location: " . $url);
        die();
    }

    public static function siteUrl() {
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        }
        else{
            $protocol = 'http';
        }
        return $protocol . "://" . parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);
    }

    public static function validYear($year) {

        $year = (int) $year;
        $current = intval(date('Y'));
        if($year >= 1990 && $year <= $current) {
            return $year;
        }
        return false;
    }

    /**
     * Сравнение строк на основе расстояния Левенштейна
     * @param $str1
     * @param $str2
     * @param array $exclude - массив символов, не участвующих в сравнении
     * @return float|int - процент схожести 0...100%
     */
    public static function compLevenstein($str1, $str2, $exclude = array(' ')) {

        $str1 = mb_strtoupper(trim((string) $str1));
        $str2 = mb_strtoupper(trim((string) $str2));

        if(is_array($exclude) && count($exclude) > 0) {
            foreach($exclude as $s) {
                $str1 = str_ireplace($s, '', $str1);
                $str2 = str_ireplace($s, '', $str2);
            }
        }

        $str1len = strlen($str1);
        $str2len = strlen($str2);

        if($str1len < 1 || $str2len < 1) {
            return 0;
        }

        if($str1 == $str2) {
            return 100;
        }

        $lev = levenshtein($str1, $str2);

        if($str1len < $str2len) {
            $ratio = ($str2len - $lev) / $str2len;
        } else {
            $ratio = ($str1len - $lev) / $str1len;
        }

        $ratio = round($ratio * 100);

        return $ratio;
    }

    public static function returnJson($data, $status = 200) {

        header("Content-Type: application/json");
        if($status !== 200) {
            http_response_code($status);
        }
        $GLOBALS['APPLICATION']->RestartBuffer();
        echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        die();
    }

    public static function getMaxUploadSize() {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = self::parseSize(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    public static function parseSize($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        else {
            return round($size);
        }
    }

    public static function getFileSize($file, $digits = 1){

        $filePath = $file;
        if(!is_file($filePath)) {
            if (!realpath($filePath)) {
                $filePath = $_SERVER["DOCUMENT_ROOT"] . '/' . ltrim($filePath, "/");
            }
        }
        if (is_file($filePath)) {

            clearstatcache(true, $filePath);
            $fileSize = filesize($filePath);
            $sizes = array("TB", "GB", "MB", "KB", "B");
            $total = count($sizes);
            while ($total-- && $fileSize > 1024) {
                $fileSize /= 1024;
            }
            return round($fileSize, $digits) . " " . $sizes[$total];
        }
        return false;
    }

    public static function formatBytes($bytes, $precision = 2) {

        $base = log($bytes, 1024);
        $suffixes = array('', 'Kb', 'Mb', 'Gb', 'Tb');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];

    }

    public static function randomPassword($length = 8) {

        $alphabet = 'abcdefghijkmnoprstuwxyzABCDEFGHJKLMNPQRSTUWXYZ23456789';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}
