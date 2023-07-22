<?php
declare(strict_types=1);
namespace App\Components;

use App\BxKint;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

class CoreComponent extends \CBitrixComponent {

    protected function checkModules()
    {
        if (!Loader::includeModule('dvk.feature')) {
            throw new LoaderException('dvk.feature module not installed!');
        }
    }
    public function executeComponent()
    {
        $this->checkModules();
        $this->includeComponentTemplate();

        if(class_exists('\App\BxKint'))
            BxKint::info($this->arResult);
        return $this->arResult;
    }
}