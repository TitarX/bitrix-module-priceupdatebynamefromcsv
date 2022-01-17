<?php

namespace Perfcode\PriceUpdateByNameFromCsv\Events;

use \Bitrix\Main\Loader;

Loader::includeModule('perfcode.priceupdatebynamefromcsv');

class MainEvents
{
    public static function EpilogHandler()
    {
        //
    }
}
