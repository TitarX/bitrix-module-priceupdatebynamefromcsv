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

        $result = array();

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

        $result = array('result' => 'success');

        $errorText = '';
        $errorArgs = array();

        $phpInput = file_get_contents('php://input');
        $phpInput = json_decode($phpInput, true);

        if (empty($phpInput['productname'])) {
            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_ERROR_TEXT';
            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PRODUCT_NAME_LABEL'));
        } elseif (empty($phpInput['price'])) {
            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_ERROR_TEXT';
            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PRICE_LABEL'));
        } elseif (empty($phpInput['currency'])) {
            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_ERROR_TEXT';
            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_CURRENCY_LABEL'));
        } elseif (empty($phpInput['iblock'])) {
            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_ERROR_TEXT';
            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_IBLOCK_LABEL'));
        } elseif (empty($phpInput['manufacturer'])) {
            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_ERROR_TEXT';
            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_MANUFACTURER_LABEL'));
        }

        if (empty($errorText)) {
            $documentRoot = Application::getDocumentRoot();
            $csvFilePath = "{$documentRoot}{$phpInput['filepath']}";

            $isDoConvertEncoding = false;
            $isFirstRow = true;
            $productNameIndex = -1;
            $priceIndex = -1;
            $currencyIndex = -1;
            if (($handle = fopen($csvFilePath, 'r')) !== false) {
                while (($data = fgetcsv($handle, 0, ';')) !== false) {
                    if ($isFirstRow) { // Первая строка
                        if (!mb_check_encoding($data, 'UTF-8')) {
                            $data = mb_convert_encoding($data, 'UTF-8', 'WINDOWS-1251');
                            $isDoConvertEncoding = true;
                        }

//                        if (array_search()) {
//                            //
//                        }

                        $isFirstRow = false;
                    } else {
                        //
                    }
                }
                fclose($handle);
            }
        } else {
            $result['result'] = 'fail';
            $result['error'] = $errorText;
            $result['errorargs'] = $errorArgs;
        }

        print json_encode($result);

        exit;
    } elseif ($request->getPost('action') === 'message') { // Системное сообщение
        $APPLICATION->RestartBuffer();

        $messageType = $request->getPost('type');
        $messageText = $request->getPost('text');
        $messageArgs = $request->getPost('args');
        if (!is_array($messageArgs)) {
            $messageArgs = json_decode($messageArgs, true);
            if (empty($messageArgs)) {
                $messageArgs = array();
            }
        }

        $message = Loc::getMessage($messageText, $messageArgs);
        \CAdminMessage::ShowMessage(array('MESSAGE' => $message, 'TYPE' => $messageType));

        exit();
    }
}

$entryId = '';
$filePath = '';
$productNameCsv = '';
$priceCsv = '';
$currencyCsv = '';
$iBlock = '';
$manufacturer = '';
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
            if (!empty($arrParams['productname'])) {
                $productNameCsv = $arrParams['productname'];
            }
            if (!empty($arrParams['price'])) {
                $priceCsv = $arrParams['price'];
            }
            if (!empty($arrParams['currency'])) {
                $currencyCsv = $arrParams['currency'];
            }
            if (!empty($arrParams['iblock'])) {
                $iBlock = $arrParams['iblock'];
            }
            if (!empty($arrParams['manufacturer'])) {
                $manufacturer = $arrParams['manufacturer'];
            }
        }
    }
}
?>

<div id="update-info"></div>

<fieldset>
    <legend><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILE_FIELDSET_LEGEND') ?></legend>
    <div><strong><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_CSV_INFO_LABEL') ?></strong></div>
    <br>
    <div>
        <label for="selected_file_path"><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILEPATH_LABEL') ?>:</label>
        <br>
        <input type="text" name="selected_file_path" id="selected_file_path" value="<?= $filePath ?>" size="64" readonly required>
        <button id='open_file_dialog_button'>Открыть</button>
    </div>
    <br>
    <div>
        <label for="product-name-csv"><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PRODUCT_NAME_LABEL') ?>:</label>
        <br>
        <input type="text" name="product-name-csv" id="product-name-csv" value="<?= $productNameCsv ?>" required>
    </div>
    <br>
    <div>
        <label for="price-csv"><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PRICE_LABEL') ?>:</label>
        <br>
        <input type="text" name="price-csv" id="price-csv" value="<?= $priceCsv ?>" required>
    </div>
    <br>
    <div>
        <label for="currency-csv"><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_CURRENCY_LABEL') ?>:</label>
        <br>
        <input type="text" name="currency-csv" id="currency-csv" value="<?= $currencyCsv ?>" required>
    </div>
</fieldset>

<br>

<fieldset>
    <legend><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_CATALOG_FIELDSET_LEGEND') ?></legend>
    <div>
        <label for="iblock-id"><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_IBLOCK_LABEL') ?>:</label>
        <br>
        <input type="number" name="iblock-id" id="iblock-id" value="<?= $iBlock ?>" required>
    </div>
    <br>
    <div>
        <label for="manufacturer-property"><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_MANUFACTURER_LABEL') ?>:</label>
        <br>
        <input type="text" name="manufacturer-property" id="manufacturer-property" value="<?= $manufacturer ?>" required>
    </div>
</fieldset>

<input type="hidden" name="requested-page" id="requested-page" value="<?= $request->getRequestedPage() ?>">
<input type="hidden" name="params-entry-id" id="params-entry-id" value="<?= $entryId ?>">

<br>

<button id="start-update-button">
    <?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILE_START_BUTTON') ?>
</button>
