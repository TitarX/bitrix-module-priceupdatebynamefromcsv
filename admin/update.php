<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Perfcode\PriceUpdateByNameFromCsv\Helpers\MiscHelper;

Loc::loadMessages(__FILE__);
Loader::includeModule('perfcode.priceupdatebynamefromcsv');

@set_time_limit(360);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_PAGE_TITLE'));

Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/perfcode_priceupdatebynamefromcsv_main.js');
Asset::getInstance()->addJs(MiscHelper::getAssetsPath('js') . '/perfcode_priceupdatebynamefromcsv_update.js');

$request = Application::getInstance()->getContext()->getRequest();

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
    } elseif ($request->getPost('action') === 'saveparams') { // Сохранение параметров обновления
        $APPLICATION->RestartBuffer();

        //

        exit;
    } elseif ($request->getPost('action') === 'update') { // Обновление
        $APPLICATION->RestartBuffer();

        //

        exit;
    }
}
?>

<div id="update-info"></div>

<fieldset>
    <legend><?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILE_FIELDSET_LEGEND') ?></legend>
    <input type="text" name="selected_file_path" id="selected_file_path" value="" size="64"
           placeholder="<?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILEPATH_PLACEHOLDER_TITLE') ?>" readonly required>
    <button id='open_file_dialog_button'>Открыть</button>
</fieldset>

<input type="hidden" name="requested-page" id="requested-page" value="<?= $request->getRequestedPage() ?>">

<br>

<button id="start-update-button">
    <?= Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_UPDATE_FILE_STAT_BUTTON') ?>
</button>
