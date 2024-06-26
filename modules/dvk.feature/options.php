<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Dvk\Feature\Module;

$moduleId = 'dvk.feature';

Loc::loadMessages(__FILE__);

Loader::includeModule($moduleId);
Loader::includeModule('mpm.options');
if(!class_exists('Gelion\BitrixOptions\Form')) {
    CAdminMessage::ShowMessage([
        'MESSAGE' => Module::lang('CHECK_OPTIONS_ERROR_TITLE'),
        'DETAILS' => Module::lang('CHECK_MPM_OPTIONS_ERROR'),
        'TYPE' => 'ERROR',
        'HTML' => true
    ]);
    return false;
}

\Gelion\BitrixOptions\Form::generate($moduleId, [[
    'DIV' => 'main',
    'TAB' => Module::lang('SETTINGS'),
    'TITLE' => Module::lang('SETTINGS'),
    'ICON' => '',
    'GROUPS' => [
        [
            'TITLE' => Module::lang('INTEGRATION'),
            'OPTIONS' => [
                'MODULE_ACTIVE' => [
                    'SORT' => 10,
                    'TYPE' => 'CHECKBOX',
                    'FIELDS' => [
                        'TITLE' => 'Модуль активен',
                        'DEFAULT' => 'Y',
                    ],
                ],
                'REMOVE_BX_JQUERY' => [
                    'SORT' => 20,
                    'TYPE' => 'CHECKBOX',
                    'FIELDS' => [
                        'TITLE' => 'Отключать встроенный в Битрикс jQuery',
                        'DEFAULT' => 'Y',
                    ],
                ],
                'OVERRIDE_BX_USER' => [
                    'SORT' => 30,
                    'TYPE' => 'CHECKBOX',
                    'FIELDS' => [
                        'TITLE' => 'Расширять класс пользователя Битрикс',
                        'DEFAULT' => 'Y',
                    ],
                ],
            ],
        ]
    ],
]]);