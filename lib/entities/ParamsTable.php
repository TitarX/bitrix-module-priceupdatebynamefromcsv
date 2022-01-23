<?php

namespace Perfcode\PriceUpdateByNameFromCsv\Entities;

use \Bitrix\Main\Entity;

class ParamsTable extends Entity\DataManager
{
    /**
     * Метод возвращает имя таблицы
     *
     * @access public
     * @static
     *
     * @return string Имя таблицы
     */
    public static function getTableName()
    {
        return 'perfcode_priceupdatebynamefromcsv_params';
    }

    /**
     * Метод возвращает карту полей таблицы базы данных
     *
     * @access public
     * @static
     *
     * @return array Массив объектов, описывающих поля таблицы в базе данных
     * @throws \Bitrix\Main\SystemException
     */
    public static function getMap()
    {
        return array(
            new Entity\IntegerField(
                'ID',
                array(
                    'primary' => true,
                    'autocomplete' => true,
                )
            ),
            new Entity\TextField(
                'VALUE',
                array(
                    'default_value' => '',
                )
            )
        );
    }
}
