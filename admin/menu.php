<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arAdminMenu = array(
   'parent_menu' => 'global_menu_store',
   'sort' => 1000,
   'text' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_MENU_TEXT'),
   'title' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_MENU_TITLE'),
   'url' => '',
   'icon' => '',
   'page_icon' => '',
   'items_id' => 'perfcode_priceupdatebynamefromcsv_menu',
   'items' => array(
       array(
           'text' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_SUBMENU_TEXT'),
           'title' => Loc::getMessage('PERFCODE_PRICEUPDATEBYNAMEFROMCSV_SUBMENU_TITLE'),
           'url' => 'perfcode_priceupdatebynamefromcsv_update.php?lang=' . LANGUAGE_ID,
           'icon' => ''
       )
   )
);

if (!empty($arAdminMenu)) {
    return $arAdminMenu;
} else {
    return false;
}
