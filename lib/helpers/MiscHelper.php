<?php

namespace Perfcode\PriceUpdateByNameFromCsv\Helpers;

class MiscHelper
{
    public static function getModuleId(): string
    {
        return 'perfcode.priceupdatebynamefromcsv';
    }

    public static function getAssetsPath(string $type): string
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

    public static function getProgressBar(int $total, int $value, string $message): void
    {
        $total1 = $total / 100;
        $progressValue = 100;
        if ($total1 > 0) {
            $progressValue = ($total - $value) / $total1;
        }

        \CAdminMessage::ShowMessage(
            array(
                'MESSAGE' => $message,
                'DETAILS' => '' . '#PROGRESS_BAR#' . '',
                'HTML' => true,
                'TYPE' => 'PROGRESS',
                'PROGRESS_WIDTH' => '600',
                'PROGRESS_TOTAL' => 100,
                'PROGRESS_VALUE' => $progressValue
            )
        );
    }

    public static function getArrayIndexByValueOrSerialNumber(array $arData, string $val): ?int
    {
        $result = null;

        $ind = array_search($val, $arData);
        if ($ind === false) {
            if (is_numeric($val)) {
                $ind = intval($val);
                $ind -= 1;
                if (isset($arData[$ind])) {
                    $result = $ind;
                }
            }
        } else {
            $result = intval($ind);
        }

        return $result;
    }
}
