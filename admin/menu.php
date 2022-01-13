<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arAdminMenu = array(
   'parent_menu' => 'global_menu_services',
   'sort' => 1000,
   'text' => Loc::getMessage('PERFCODE_PRICEUPDATE_MENU_TEXT'),
   'title' => Loc::getMessage('PERFCODE_PRICEUPDATE_MENU_TITLE'),
   'url' => '',
   'icon' => '',
   'page_icon' => '',
   'items_id' => 'perfcode_priceupdate_menu',
   'items' => array(
       array(
           'text' => Loc::getMessage('PERFCODE_PRICEUPDATE_SUBMENU_TEXT'),
           'title' => Loc::getMessage('PERFCODE_PRICEUPDATE_SUBMENU_TITLE'),
           'url' => 'perfcode_priceupdate_action.php?lang=' . LANGUAGE_ID,
           'icon' => ''
       )
   )
);

if (!empty($arAdminMenu)) {
    return $arAdminMenu;
} else {
    return false;
}
