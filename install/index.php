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

        // Действия при установке модуля

        $this->RegisterEvents();
        $this->InstallDB();

        RegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage('PERFCODE_PRICEUPDATE_MODULE_INSTALL'), __DIR__ . '/step.php');
    }

    function DoUninstall()
    {
        global $APPLICATION;

        // Действия при удалении модуля

        $this->UnRegisterEvents();
        $this->UnInstallDB();

        UnRegisterModule($this->MODULE_ID);

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
        //
    }

    function UnInstallDB()
    {
        //
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
