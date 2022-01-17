<?php

namespace Perfcode\PriceUpdateByNameFromCsv\Helpers;

class MiscHelper
{
    public static function getModuleId()
    {
        return 'perfcode.priceupdatebynamefromcsv';
    }

    public static function getAssetsPath($type)
    {
        $moduleId = self::getModuleId();
        $assetsPath = '';
        switch ($type) {
            case 'css':
            {
                $assetsPath = "/bitrix/css/{$moduleId}";
                break;
            }
            case 'js':
            {
                $assetsPath = "/bitrix/js/{$moduleId}";
                break;
            }
            case 'img':
            {
                $assetsPath = "/bitrix/images/{$moduleId}";
                break;
            }
        }
        return $assetsPath;
    }
}
