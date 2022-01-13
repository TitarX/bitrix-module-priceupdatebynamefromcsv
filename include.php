<?php

use Bitrix\Main\Loader;

// При правильном именовании, классы подключаются автоматически. Имена файлов классов должны быть в нижнем регистре.
Loader::registerAutoloadClasses(
    'perfcode.priceupdate',
    array(
        'Perfcode\PriceUpdate\Events\MainEvents' => 'lib/events/MainEvents.php',
        'Perfcode\PriceUpdate\Helpers\MiscHelper' => 'lib/helpers/MiscHelper.php'
    )
);
