<?php
/** @global \CMain $APPLICATION */
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle('title');

$APPLICATION->IncludeComponent(
    'namespace:component',
    '',
    [
        'CACHE_TYPE' => 'N',
        'CACHE_TIME' => 0,
    ],
    false
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');