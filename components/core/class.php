<?php
declare(strict_types=1);
namespace App\Components;

use App\BxKint;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

class CoreComponent extends \CBitrixComponent {

    public function executeComponent(): array
    {
        $this->checkModules();
        $this->processRequest();

        $this->includeComponentTemplate();

        if(class_exists('\App\BxKint'))
            BxKint::info($this->arResult);
        return $this->arResult;
    }

    protected function processRequest(): bool
    {
        if($this->request->isPost()) {
            $postData = $this->request->getPostList()->toArray();
            return true;
        }
        return false;
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('dvk.feature')) {
            throw new LoaderException('dvk.feature module not installed!');
        }
    }
}