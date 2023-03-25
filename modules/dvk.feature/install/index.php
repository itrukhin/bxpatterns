<?php
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class dvk_feature extends CModule {

    var $MODULE_ID  = 'dvk.feature'; // требование маркетплейса!

    public function __construct() {

        $arModuleVersion = array();
        include(__DIR__."/version.php");

        $this->MODULE_ID = 'dvk.feature';
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("DVK_FEATURE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("DVK_FEATURE_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("DVK_FEATURE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("DVK_FEATURE_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS='Y';
        $this->MODULE_GROUP_RIGHTS = "Y";
    }

    //Проверяем что система поддерживает D7
    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    public function DoInstall()
    {
        global $APPLICATION;
        if ($this->isVersionD7()) {

            Loader::includeModule($this->MODULE_ID);
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

        } else {
            $APPLICATION->ThrowException(Loc::getMessage("DVK_FEATURE_INSTALL_ERROR_VERSION"));
        }
    }

    public function DoUninstall()
    {
        $conn = \Bitrix\Main\Application::getConnection();
        $conn->queryExecute(sprintf("DELETE FROM b_module_to_module WHERE TO_MODULE_ID = '%s'", $this->MODULE_ID));
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}