<?php
date_default_timezone_set('Europe/Moscow');
header('Access-Control-Allow-Origin: *'); // for JS error logging

\App\BxKint::init();

/*
 * debug
 */
if (!function_exists('_d')) {
    function _d($a = null, $die = true) {
        if($die) {
            while(ob_get_level())
                ob_end_clean();
        }

        echo '<pre>';
        print_r($a);
        echo '</pre>';
        if($die) exit;
    }
}