<?php
namespace Dvk\Main\Events;

use Dvk\Main\Data\UserInfo;

class Iblock {

    public static function beforeIBlockElementDelete($id) {

        if(!defined('BX_COMP_MANAGED_CACHE')) {
            $iblockId = \Dvk\Main\Helpers\Iblock::getiblockIdByElementId($id);
            if($iblockId == UserInfo::USER_INFO_IBLOCK_ID) {
                $userInfo = new UserInfo();
                $userInfo->cleanCache();
            }
        }
    }

    public static function afterIBlockElementAddUpdate(&$arFields) {

        if(!defined('BX_COMP_MANAGED_CACHE')) {
            if($arFields['IBLOCK_ID'] == UserInfo::USER_INFO_IBLOCK_ID) {
                $userInfo = new UserInfo();
                $userInfo->cleanCache();
            }
        }
    }
}