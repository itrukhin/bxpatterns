<?php
use Bitrix\Main\Localization\Loc;

$module_id = 'dvk.feature'; //обязательно, иначе права доступа не работают!

Loc::loadMessages(__FILE__);

/**
 * @var CMain
 */
global $APPLICATION;

if ($APPLICATION->GetGroupRight($module_id) < "R") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

\Bitrix\Main\Loader::includeModule($module_id);
$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

#Описание опций
$aTabs = array(
    array(
        "DIV" => "rights",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")
    ),
);

#Визуальный вывод
$tabControl = new \CAdminTabControl('tabControl', $aTabs);

?>
<?php $tabControl->Begin(); ?>
    <form method='post' action='<?php echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request['lang']?>' name='dvk_feature_settings'>

        <?php /*foreach ($aTabs as $aTab) {
            if($aTab['OPTIONS']) { ?>
                <? $tabControl->BeginNextTab(); ?>
                <? __AdmSettingsDrawList($module_id, $aTab['OPTIONS']); ?>
                <? $tabControl->EndTab(); ?>
            <? }
        } */?>

        <?php
        $tabControl->BeginNextTab();
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
        $tabControl->EndTab();


        $tabControl->Buttons(); ?>

        <input type="submit" name="Update" value="<?php echo GetMessage('MAIN_SAVE')?>">
        <input type="reset" name="reset" value="<?php echo GetMessage('MAIN_RESET')?>">
        <?=bitrix_sessid_post();?>
    </form>
<?php $tabControl->End(); ?>