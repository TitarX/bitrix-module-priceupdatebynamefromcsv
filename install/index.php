<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class perfcode_priceupdate extends CModule
{
    var $exclusionAdminFiles;

    function __construct()
    {
        $this->MODULE_ID = 'perfcode.priceupdate';
        $this->MODULE_NAME = Loc::getMessage('PERFCODE_PRICEUPDATE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('PERFCODE_PRICEUPDATE_MODULE_DESCRIPTION');

        $this->PARTNER_NAME = '';
        $this->PARTNER_URI = '';

        $arModuleVersion = array();
        include(__DIR__ . '/version.php');
        if (is_array($arModuleVersion)) {
            if (array_key_exists('VERSION', $arModuleVersion)) {
                $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            }

            if (array_key_exists('VERSION_DATE', $arModuleVersion)) {
                $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            }
        }

        $this->exclusionAdminFiles = array(
            '..',
            '.'
        );
    }

    function DoInstall()
    {
        global $APPLICATION;

        $documentRoot = Application::getDocumentRoot();
        $this->copyFiles($documentRoot);

        $this->RegisterEvents();
        $this->InstallDB();

        ModuleManager::registerModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage('PERFCODE_PRICEUPDATE_MODULE_INSTALL'), __DIR__ . '/step.php');
    }

    function DoUninstall()
    {
        global $APPLICATION;

        $this->deleteFiles();

        $this->UnRegisterEvents();
        $this->UnInstallDB();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage('PERFCODE_PRICEUPDATE_MODULE_UNINSTALL'), __DIR__ . '/unstep.php');
    }

    //Определяем место размещения модуля
    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }

    //Проверяем что система поддерживает D7
    public function isVersionD7()
    {
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

    function InstallDB()
    {
        return true;
    }

    function UnInstallDB()
    {
        return true;
    }

    private function copyFiles($documentRoot)
    {
        CopyDirFiles(__DIR__ . '/pages/admin/perfcode_priceupdate_update.php', "{$documentRoot}/bitrix/admin/perfcode_priceupdate_update.php", true, true, false);
    }

    private function deleteFiles()
    {
        DeleteDirFilesEx('/bitrix/admin/perfcode_priceupdate_update.php');
    }

    function RegisterEvents()
    {
        EventManager::getInstance()->registerEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            'Perfcode\PriceUpdate\Events\MainEvents',
            'EpilogHandler',
            1000
        );
    }

    function UnRegisterEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'main',
            'OnEpilog',
            $this->MODULE_ID,
            'Perfcode\PriceUpdate\Events\MainEvents',
            'EpilogHandler'
        );
    }

    function GetModuleRightList()
    {
        return array(
            "reference_id" => array('D'),
            "reference" => array(
                '[D] ' . Loc::getMessage('PERFCODE_PRICEUPDATE_RIGHT_DENIED')
            )
        );
    }
}
