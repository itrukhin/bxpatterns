<?php
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define('NO_AGENT_CHECK', true);
define("STATISTIC_SKIP_ACTIVITY_CHECK", true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
header('Access-Control-Allow-Origin: *');

use App\Log;
use Bitrix\Main\Application;

$request = Application::getInstance()->getContext()->getRequest();

$jsError = $request->get('e');

$log = new Log('js');
if($errData = json_decode($jsError, true)) {
    $log->error($errData);
    if($_ENV['APP_LOG_TELEGRAM_JS'] == 'on') {
        $log->telegram(\Psr\Log\LogLevel::ERROR, 'JS: ' . $errData[0]);
    }
}
