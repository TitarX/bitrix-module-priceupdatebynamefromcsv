<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Perfcode\PriceUpdateByNameFromCsv\Helpers\MiscHelper;

Loc::loadMessages(__FILE__);
Loader::includeModule('perfcode.priceupdatebynamefromcsv');

@set_time_limit(360);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_TITLE'));

Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/perfcode_priceupdatebynamefromcsv_update.js');

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
?>

<input type="text" name="selected_file_path" id="selected_file_path">
<button id='open_file_dialog_button'>Открыть</button>
