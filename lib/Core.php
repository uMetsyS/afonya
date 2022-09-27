<?php

namespace Afonya\NewsLog;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;

class Core
{
    private static $MODULE_ID = "afonya_newslog";

    /**
     * Обновление даты последнего обновления
     * @param string $date_new
     * @return DateTime|mixed|string
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    static function dateChange($date_new = '')
    {
        if ($date_new == '') {
            $date_new = new DateTime();
        }

        Option::set(
            ADMIN_MODULE_NAME,
            "date",
            $date_new
        );

        return $date_new;
    }
}
