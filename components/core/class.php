<?php
use Bitrix\Main;
class CoreComponent extends \CBitrixComponent {

    public function onPrepareComponentParams($params)
    {
        $params = parent::onPrepareComponentParams($params);
        $params["CACHE_TIME"] = 0;
        return $params;
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();

        if(class_exists('\App\BxKint')) {
            \App\BxKint::info($this->arResult);
        }
        return $this->arResult;
    }
}