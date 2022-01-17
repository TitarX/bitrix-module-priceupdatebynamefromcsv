<?php

use Bitrix\Main\Loader;

// При правильном именовании, классы подключаются автоматически. Имена файлов классов должны быть в нижнем регистре.
Loader::registerAutoloadClasses(
    'perfcode.priceupdatebynamefromcsv',
    array(
        'Perfcode\PriceUpdateByNameFromCsv\Events\MainEvents' => 'lib/events/MainEvents.php',
        'Perfcode\PriceUpdateByNameFromCsv\Helpers\MiscHelper' => 'lib/helpers/MiscHelper.php'
    )
);
