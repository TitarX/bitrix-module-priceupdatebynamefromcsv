<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);

class perfcode_priceupdatebynamefromcsv extends CModule
{
    var $exclusionAdminFiles;

    function __construct()
    {
        $this->MODULE_ID = 'perfcode.priceupdatebynamefromcsv';
        $this->MODULE_NAME = Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_MODULE_DESCRIPTION');

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
        global $errors;

        $errors = '';
        if (!ModuleManager::isModuleInstalled('iblock')) {
            $errors = Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_MODULE_NOT_INSTALLED_IBLOCK');
        } elseif (!ModuleManager::isModuleInstalled('sale')) {
            $errors = Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_MODULE_NOT_INSTALLED_SALE');
        } elseif (!ModuleManager::isModuleInstalled('catalog')) {
            $errors = Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_MODULE_NOT_INSTALLED_CATALOG');
        } elseif (!ModuleManager::isModuleInstalled('currency')) {
            $errors = Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_MODULE_NOT_INSTALLED_CURRENCY');
        } else {
            $documentRoot = Application::getDocumentRoot();
            $this->copyFiles($documentRoot);
            $this->createDirectories($documentRoot);

            $this->RegisterEvents();
            $this->InstallDB();

            ModuleManager::registerModule($this->MODULE_ID);
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_MODULE_INSTALL'), __DIR__ . '/step.php');
    }

    function DoUninstall()
    {
        global $APPLICATION;
        global $errors;

        $errors = '';

        $this->deleteFiles();
        $this->deleteDirectories();

        $this->UnRegisterEvents();
        $this->UnInstallDB();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_MODULE_UNINSTALL'), __DIR__ . '/unstep.php');
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
        global $APPLICATION;
        global $DB;
        global $errors;

        $documentRoot = Application::getDocumentRoot();
        $errors = $DB->RunSQLBatch("{$documentRoot}/bitrix/modules/perfcode.priceupdatebynamefromcsv/install/db/" . strtolower($DB->type) . '/install.sql');
        if (!empty($errors)) {
            $APPLICATION->ThrowException(implode('. ', $errors));
            return false;
        }

        return true;
    }

    function UnInstallDB()
    {
        global $APPLICATION;
        global $DB;
        global $errors;

        $documentRoot = Application::getDocumentRoot();
        $errors = $DB->RunSQLBatch("{$documentRoot}/bitrix/modules/perfcode.priceupdatebynamefromcsv/install/db/" . strtolower($DB->type) . '/uninstall.sql');
        if (!empty($errors)) {
            $APPLICATION->ThrowException(implode('. ', $errors));
            return false;
        }

        return true;
    }

    private function copyFiles($documentRoot)
    {
        CopyDirFiles(__DIR__ . '/pages/admin/perfcode_priceupdatebynamefromcsv_update.php', "{$documentRoot}/bitrix/admin/perfcode_priceupdatebynamefromcsv_update.php", true, true, false);
    }

    private function deleteFiles()
    {
        DeleteDirFilesEx('/bitrix/admin/perfcode_priceupdatebynamefromcsv_update.php');
    }

    private function createDirectories($documentRoot)
    {
        $uploadDirectoryName = Option::get('main', 'upload_dir');

        $perfcodeDirectoryPath = "{$documentRoot}/{$uploadDirectoryName}/perfcode";
        if (!Directory::isDirectoryExists($perfcodeDirectoryPath)) {
            Directory::createDirectory($perfcodeDirectoryPath);
        }

        $priceupdatebynamefromcsvDirectoryPath = "{$perfcodeDirectoryPath}/priceupdatebynamefromcsv";
        if (!Directory::isDirectoryExists($priceupdatebynamefromcsvDirectoryPath)) {
            Directory::createDirectory($priceupdatebynamefromcsvDirectoryPath);
        }
    }

    private function deleteDirectories()
    {
        $uploadDirectoryPath = Option::get('main', 'upload_dir');
        DeleteDirFilesEx("/{$uploadDirectoryPath}/perfcode/priceupdatebynamefromcsv");
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
                '[D] ' . Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_RIGHT_DENIED')
            )
        );
    }
}
