<?php
declare(strict_types=1);
namespace App\Components;

use App\BxKint;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

class CachedComponent extends \CBitrixComponent {

    public function executeComponent(): array
    {
        $this->checkModules();

        $taggedCache = Application::getInstance()->getTaggedCache();
        if($this->startResultCache($this->arParams['CACHE_TIME'], $this->arParams['CACHE_GROUPS'])) {

            $this->arResult = (array) ElementTable::getRow([
                'select' => ['ID', 'CODE', 'DETAIL_TEXT'],
                'filter' => [
                    'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                    'ID' => $this->arParams['ELEMENT_ID'],
                ]
            ]);
            $taggedCache->registerTag('iblock_id_' . $this->arParams['IBLOCK_ID']);
            $taggedCache->endTagCache();
            $this->includeComponentTemplate();
        }

        if(class_exists('\App\BxKint'))
            BxKint::info($this->arResult);
        return $this->arResult;
    }

    public function onPrepareComponentParams($arParams): array
    {
        /** @global \CUser $USER */
        global $USER;

        $arParams = parent::onPrepareComponentParams($arParams);

        if (!isset($arParams["CACHE_TIME"])) {
            $arParams["CACHE_TIME"] = 86400;
        }
        if($arParams['CACHE_GROUPS'] == 'Y') {
            $arParams['CACHE_GROUPS'] = $USER->GetGroups();
        } else {
            $arParams['CACHE_GROUPS'] = false;
        }

        $arParams['IBLOCK_ID'] = (int) $arParams['IBLOCK_ID'];
        if(!$arParams['IBLOCK_ID']) {
            throw new \Exception('Required param IBLOCK_ID not found!');
        }

        $arParams['ELEMENT_ID'] = (int) $arParams['ELEMENT_ID'];
        if(!$arParams['ELEMENT_ID']) {
            throw new \Exception('Required param ELEMENT_ID not found!');
        }

        return $arParams;
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('iblock')) {
            throw new LoaderException('iblock module not installed!');
        }
    }
}