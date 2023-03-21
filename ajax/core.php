<?php
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC", "Y");
define('NO_AGENT_CHECK', true);
/** @global CMain $APPLICATION */
global $APPLICATION;
/** @global CUser $USER */
global $USER;

use \Bitrix\Main;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
$request = Main\Application::getInstance()->getContext()->getRequest();
if (!$request->isAjaxRequest()) die();
