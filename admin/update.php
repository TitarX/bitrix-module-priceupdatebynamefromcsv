<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);
Loader::includeModule('perfcode.priceupdate');

@set_time_limit(360);
