<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Perfcode\PriceUpdateByNameFromCsv\Helpers\MiscHelper;
use Perfcode\PriceUpdateByNameFromCsv\Entities\ParamsTable;

Loc::loadMessages(__FILE__);
Loader::includeModule('perfcode.priceupdatebynamefromcsv');

@set_time_limit(360);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PAGE_TITLE'));

Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/perfcode_priceupdatebynamefromcsv_main.js');
Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/perfcode_priceupdatebynamefromcsv_update.js');

$request = Application::getInstance()->getContext()->getRequest();

$rsParamsCount = ParamsTable::getCount();
if (empty($rsParamsCount) || !is_int($rsParamsCount)) {
    $rsParamsCount = 0;
}

CAdminFileDialog::ShowScript(
    array
    (
        'event' => 'OpenFileDialog',
        'arResultDest' => array('ELEMENT_ID' => 'selected_file_path'),
        'arPath' => array(),
        'select' => 'F',
        'operation' => 'O',
        'showUploadTab' => true,
        'showAddToMenuTab' => false,
        'fileFilter' => 'csv',
        'allowAllFiles' => false,
        'saveConfig' => true
    )
);

if ($request->isPost()) {
    if ($request->get('action') === 'checkfileexists') { // Проверка на существование выбранного файла
        $APPLICATION->RestartBuffer();

        $result = array('result' => 'miss');
        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);
        if (!empty($phpInput['filepath'])) {
            $documentRoot = Application::getDocumentRoot();
            $fullFilePath = $documentRoot . $phpInput['filepath'];
            $file = new File($fullFilePath);
            if ($file->isExists() && $file->isFile()) {
                $result['result'] = 'yes';
            } else {
                $result['result'] = 'no';
            }
        }

        print json_encode($result);

        exit();
    } elseif ($request->get('action') === 'saveparams') { // Сохранение параметров обновления
        $APPLICATION->RestartBuffer();

        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);

        $entryId = 0;
        if ($rsParamsCount !== 1) {
            ParamsTable::getEntity()->getConnection()->queryExecute('TRUNCATE TABLE perfcode_priceupdatebynamefromcsv_params');
        } elseif (!empty($phpInput['entryid']) && is_numeric($phpInput['entryid'])) {
            $entryId = $phpInput['entryid'];
        }

        $phpInput = serialize($phpInput);

        $arrParams = array('VALUE' => $phpInput);

        $updateResult = null;
        if (!empty($entryId)) {
            $updateResult = ParamsTable::update($entryId, $arrParams);
        } else {
            $updateResult = ParamsTable::add($arrParams);
        }
        $result = array();
        if (isset($updateResult) && $updateResult->isSuccess()) {
            $entryId = $updateResult->getId();
            $result['result'] = $entryId;
        } else {
            $result['result'] = 'fail';
        }

        print json_encode($result);

        exit;
    } elseif ($request->get('action') === 'update') { // Обновление
        $APPLICATION->RestartBuffer();

        //

        exit;
    } elseif ($request->getPost('action') === 'message') { // Системное сообщение
        $APPLICATION->RestartBuffer();

        $messageType = $request->getPost('type');
        $messageText = $request->getPost('text');
        $messageArgs = $request->getPost('args');
        if (!is_array($messageArgs)) {
            $messageArgs = array();
        }

        $message = vsprintf(Loc::getMessage($messageText), $messageArgs);
        \CAdminMessage::ShowMessage(array('MESSAGE' => $message, 'TYPE' => $messageType));

        exit();
    }
}

$entryId = '';
$filePath = '';
if (!empty($rsParamsCount)) {
    $dbResult = ParamsTable::getList(array(
        'select' => array('ID', 'VALUE'),
        'order' => array('ID' => 'desc'),
        'limit' => 1
    ));
    if ($arrResult = $dbResult->fetch()) {
        $entryId = $arrResult['ID'];

        $arrParams = unserialize($arrResult['VALUE']);
        if (!empty($arrParams)) {
            if (!empty($arrParams['filepath'])) {
                $filePath = $arrParams['filepath'];
            }
        }
    }
}
?>

<div id="update-info"></div>

<fieldset>
    <legend><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILE_FIELDSET_LEGEND') ?></legend>
    <input type="text" name="selected_file_path" id="selected_file_path" value="<?= $filePath ?>" size="64"
           placeholder="<?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILEPATH_PLACEHOLDER_TITLE') ?>" readonly required>
    <button id='open_file_dialog_button'>Открыть</button>
</fieldset>

<input type="hidden" name="requested-page" id="requested-page" value="<?= $request->getRequestedPage() ?>">
<input type="hidden" name="params-entry-id" id="params-entry-id" value="<?= $entryId ?>">

<br>

<button id="start-update-button">
    <?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILE_START_BUTTON') ?>
</button>
