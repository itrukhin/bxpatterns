<?php
declare(strict_types=1);
namespace App\Components;

use App\BxKint;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Dvk\Main\Utils;

class CoreComponent extends \CBitrixComponent {

    public function executeComponent(): array
    {
        $this->checkModules();
        $this->processRequest();

        if($this->isAjax()) {
            Utils::returnJson($this->arResult);
        } else {
            $this->includeComponentTemplate();
        }

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

    protected function isAjax(): bool
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        return ($request->isAjaxRequest() ||
            $request->getQuery('ajax') === 'y' ||
            $request->getPost('ajax') === 'y' ||
            $this->arParams['AJAX_MODE'] === 'Y');
    }

    public function onPrepareComponentParams($params): array
    {
        $params = parent::onPrepareComponentParams($params);

        if (!isset($params["CACHE_TIME"])) {
            $params["CACHE_TIME"] = 86400;
        }
        $params['CACHE_GROUPS'] = ($params['CACHE_GROUPS'] == 'Y');

        return $params;
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('dvk.feature')) {
            throw new LoaderException('dvk.feature module not installed!');
        }
    }

    public function onIncludeComponentLang()
    {
        parent::onIncludeComponentLang();
        $this->includeComponentLang(basename(__FILE__));
    }
}