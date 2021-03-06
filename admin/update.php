<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Bitrix\Main\ModuleManager;
use Perfcode\PriceUpdateByNameFromCsv\Helpers\MiscHelper;
use Perfcode\PriceUpdateByNameFromCsv\Entities\ParamsTable;

Loc::loadMessages(__FILE__);
Loader::includeModule('perfcode.priceupdatebynamefromcsv');

const PRICE_TYPE_ID = 1;

@set_time_limit(360);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PAGE_TITLE'));

Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/perfcode_priceupdatebynamefromcsv_main.js');
Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/perfcode_priceupdatebynamefromcsv_update.js');

$request = Application::getInstance()->getContext()->getRequest();

$rsParamsCount = ParamsTable::getCount();
if (empty($rsParamsCount) || !is_numeric($rsParamsCount)) {
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
            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_ERROR_PARAM_EMPTY_TEXT';
            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PRODUCT_NAME_LABEL'));
        } elseif (empty($phpInput['price'])) {
            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_ERROR_PARAM_EMPTY_TEXT';
            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PRICE_LABEL'));
        } elseif (empty($phpInput['currency'])) {
            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_ERROR_PARAM_EMPTY_TEXT';
            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_CURRENCY_LABEL'));
        } elseif (empty($phpInput['iblock'])) {
            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_ERROR_PARAM_EMPTY_TEXT';
            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_IBLOCK_LABEL'));
        } elseif (empty($phpInput['manufacturer'])) {
            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_ERROR_PARAM_EMPTY_TEXT';
            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_MANUFACTURER_LABEL'));
        }

        $arCsvProductName = array();
        $arCsvProductPrice = array();
        $arCsvProductCurrency = array();
        if (empty($errorText)) {
            $documentRoot = Application::getDocumentRoot();
            $csvFilePath = "{$documentRoot}{$phpInput['filepath']}";

            $isDoConvertEncoding = false;
            $csvRow = 0;
            $productNameIndex = -1;
            $priceIndex = -1;
            $currencyIndex = -1;
            if (($handle = fopen($csvFilePath, 'r')) !== false) {
                while (($data = fgetcsv($handle, 0, ';')) !== false && empty($errorText)) {
                    if ($csvRow === 0) { // Первая строка, определяем индексы колонок
                        if (!mb_check_encoding($data, 'UTF-8')) {
                            $data = mb_convert_encoding($data, 'UTF-8', 'WINDOWS-1251');
                            $isDoConvertEncoding = true;
                        }

                        $productNameIndex = MiscHelper::getArrayIndexByValueOrSerialNumber($data, strval($phpInput['productname']));
                        if (!isset($productNameIndex)) {
                            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_COLUMN_NOT_FOUND_ERROR_TEXT';
                            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PRODUCT_NAME_LABEL'));
                            break;
                        }
                        $priceIndex = MiscHelper::getArrayIndexByValueOrSerialNumber($data, strval($phpInput['price']));
                        if (!isset($priceIndex)) {
                            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_COLUMN_NOT_FOUND_ERROR_TEXT';
                            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PRICE_LABEL'));
                            break;
                        }
                        $currencyIndex = MiscHelper::getArrayIndexByValueOrSerialNumber($data, strval($phpInput['currency']));
                        if (!isset($currencyIndex)) {
                            $errorText = 'PERFCODE_PRICEUPDATEBYNAMEFROMCSV_COLUMN_NOT_FOUND_ERROR_TEXT';
                            $errorArgs = array('#PARAM_NAME#' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_CURRENCY_LABEL'));
                            break;
                        }
                    } else { // Не первая строка, собираем данные
                        if ($isDoConvertEncoding) {
                            $data = mb_convert_encoding($data, 'UTF-8', 'WINDOWS-1251');
                        }

                        $arCsvProductName[$csvRow - 1] = trim($data[$productNameIndex]);
                        $arCsvProductPrice[$csvRow - 1] = trim($data[$priceIndex]);
                        $arCsvProductCurrency[$csvRow - 1] = trim($data[$currencyIndex]);
                    }
                    $csvRow++;
                }
                fclose($handle);
            }
        }

        if (empty($errorText)) {
            $successUpdateCount = 0;
            $failUpdateCount = 0;

            $arOrder = array('SORT' => 'ASC');
            $arFilter = array(
                'IBLOCK_ID' => $phpInput['iblock'],
                'PROPERTY_XLS_BREND_VALUE' => $phpInput['manufacturer'],
                'NAME' => $arCsvProductName
            );
            $arGroup = false;
            $arNav = false;
            $arSelect = array('IBLOCK_ID', 'NAME', 'ID', 'CATALOG_GROUP_' . PRICE_TYPE_ID);
            $dbResult = CIBlockElement::GetList($arOrder, $arFilter, $arGroup, $arNav, $arSelect);
            while ($arrResult = $dbResult->Fetch()) {
                $csvProductIndex = array_search($arrResult['NAME'], $arCsvProductName);
                if (is_numeric($csvProductIndex)) {
                    // Проверяем версию модуля catalog, класс Bitrix\Catalog\Model\Price добавлен в версии 17.6.0
                    $isCatalogModelPriceExists = CheckVersion(ModuleManager::getVersion('catalog'), '17.6.0');

                    $arFieldPrice = array(
                        'PRODUCT_ID' => $arrResult['ID'],
                        'CATALOG_GROUP_ID' => PRICE_TYPE_ID,
                        'PRICE' => $arCsvProductPrice[$csvProductIndex],
                        'CURRENCY' => $arCsvProductCurrency[$csvProductIndex]
                    );

                    if ($isCatalogModelPriceExists) {
                        $dbPrice = Bitrix\Catalog\Model\Price::getList(
                            array(
                                'filter' => array(
                                    'PRODUCT_ID' => $arrResult['ID'],
                                    'CATALOG_GROUP_ID' => PRICE_TYPE_ID
                                )
                            )
                        );

                        if ($arPrice = $dbPrice->fetch()) {
                            $updatePriceResult = Bitrix\Catalog\Model\Price::update($arPrice['ID'], $arFieldPrice);
                            if ($updatePriceResult->isSuccess()) {
                                $successUpdateCount++;
                            } else {
                                $failUpdateCount++;
                            }
                        } else {
                            $addPriceResult = Bitrix\Catalog\Model\Price::add($arFieldPrice);
                            if ($addPriceResult->isSuccess()) {
                                $successUpdateCount++;
                            } else {
                                $failUpdateCount++;
                            }
                        }
                    } else {
                        $productPrice = $arrResult['CATALOG_PRICE_' . PRICE_TYPE_ID];
                        $productPriceId = $arrResult['CATALOG_PRICE_ID_' . PRICE_TYPE_ID];

                        if (!empty($productPrice)) {
                            $updatePriceResult = CPrice::Update($productPriceId, $arFieldPrice);
                            if ($updatePriceResult !== false) {
                                $successUpdateCount++;
                            } else {
                                $failUpdateCount++;
                            }
                        } else {
                            $addPriceResult = CPrice::Add($arFieldPrice);
                            if ($addPriceResult !== false) {
                                $successUpdateCount++;
                            } else {
                                $failUpdateCount++;
                            }
                        }
                    }
                }
            }

            $result['result'] = 'success';
            $result['updatecounts'] = array(
                '#SUCCESS_UPDATE_COUNT#' => strval($successUpdateCount),
                '#FAIL_UPDATE_COUNT#' => strval($failUpdateCount)
            );
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
        CAdminMessage::ShowMessage(array('MESSAGE' => $message, 'TYPE' => $messageType));

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
