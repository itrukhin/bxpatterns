<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$moduleId = 'dvk.feature';

Loc::loadMessages(__FILE__);

Loader::includeModule($moduleId);

if(!class_exists('Gelion\BitrixOptions\Form')) {
    CAdminMessage::ShowMessage([
        'MESSAGE' => _lang('CHECK_OPTIONS_ERROR_TITLE'),
        'DETAILS' => _lang('CHECK_MPM_OPTIONS_ERROR'),
        'TYPE' => 'ERROR',
        'HTML' => true
    ]);
    return false;
}

\Gelion\BitrixOptions\Form::generate($moduleId, [[
    'DIV' => 'main',
    'TAB' => _lang('SETTINGS'),
    'TITLE' => _lang('SETTINGS'),
    'ICON' => '',
    'GROUPS' => [
        [
            'TITLE' => _lang('INTEGRATION'),
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