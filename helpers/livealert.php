<?php
declare(strict_types=1);
namespace Dvk\Main\Helpers;

// Event handler example:
// public static function onEndBufferContent(&$content) {
//
//        $alertScript = LiveAlert::getJs();
//        if(!empty($alertScript)) {
//            $content = str_replace("</body>", $alertScript . "</body>", $content);
//        }
//    }


class LiveAlert {

    const TYPE_DANGER = 'danger';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';

    public static function danger(string ...$strings) {

        $sessKey = 'DVK_ALERT_' . strtoupper(self::TYPE_DANGER);
        $argv = func_get_args();
        $message = array_shift( $argv );
        if(!empty($argv)) {
            $_SESSION[$sessKey] = vsprintf($message, $argv);
        } else {
            $_SESSION[$sessKey] = $message;
        }
    }

    public static function success(string ...$strings) {

        $sessKey = 'DVK_ALERT_' . strtoupper(self::TYPE_SUCCESS);
        $argv = func_get_args();
        $message = array_shift( $argv );
        if(!empty($argv)) {
            $_SESSION[$sessKey] = vsprintf($message, $argv);
        } else {
            $_SESSION[$sessKey] = $message;
        }
    }

    public static function warning(string ...$strings) {

        $sessKey = 'DVK_ALERT_' . strtoupper(self::TYPE_WARNING);
        $argv = func_get_args();
        $message = array_shift( $argv );
        if(!empty($argv)) {
            $_SESSION[$sessKey] = vsprintf($message, $argv);
        } else {
            $_SESSION[$sessKey] = $message;
        }
    }

    public static function clean() {

        $sessKey = 'DVK_ALERT_' . strtoupper(self::TYPE_DANGER);
        unset($_SESSION[$sessKey]);

        $sessKey = 'DVK_ALERT_' . strtoupper(self::TYPE_SUCCESS);
        unset($_SESSION[$sessKey]);

        $sessKey = 'DVK_ALERT_' . strtoupper(self::TYPE_WARNING);
        unset($_SESSION[$sessKey]);
    }

    public static function getJs() {

        $js = [];

        $sessKey = 'DVK_ALERT_' . strtoupper(self::TYPE_DANGER);
        if(!empty($_SESSION[$sessKey])) {
            $msg = $_SESSION[$sessKey];
            $js[] = sprintf("liveAlert('%s', 'danger');", htmlspecialchars($msg, ENT_QUOTES));
        }

        $sessKey = 'DVK_ALERT_' . strtoupper(self::TYPE_SUCCESS);
        if(!empty($_SESSION[$sessKey])) {
            $msg = $_SESSION[$sessKey];
            $js[] = sprintf("liveAlert('%s', 'success');", htmlspecialchars($msg, ENT_QUOTES));
        }

        $sessKey = 'DVK_ALERT_' . strtoupper(self::TYPE_WARNING);
        if(!empty($_SESSION[$sessKey])) {
            $msg = $_SESSION[$sessKey];
            $js[] = sprintf("liveAlert('%s', 'warning');", htmlspecialchars($msg, ENT_QUOTES));
        }

        if(empty($js)) {
            return '';
        }

        return sprintf("<script>%s $.get('/ajax/clean_alerts.php');</script>", join(' ', $js));
    }
}