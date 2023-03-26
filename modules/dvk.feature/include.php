<?php
function _lang($name): string
{
    $str = Bitrix\Main\Localization\Loc::getMessage("dvk_main_{$name}");
    return $str ?? 'Not found key ' . $name;
}

\Dvk\Feature\Module::checkEvents(\Dvk\Feature\Module::MODULE_ID);

//$eventManager = \Bitrix\Main\EventManager::getInstance();


