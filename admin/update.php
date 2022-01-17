<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);
Loader::includeModule('perfcode.priceupdatebynamefromcsv');

@set_time_limit(360);
