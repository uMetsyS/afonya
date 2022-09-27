<?php

namespace Afonya\NewsLog;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;

class Handler
{
    static $MODULE_ID = "news_log";

    /**
     * Хэндлер после обновления
     *
     * @param $arFields
     * @return bool
     */
    static function onAfterElementUpdateHandler($arFields)
    {
        return self::log($arFields, 'update');
    }

    /**
     * Хэндлер после добавления
     *
     * @param $arFields
     * @return false
     */
    static function onAfterElementAddHandler($arFields)
    {
        return self::log($arFields, 'add');
    }

    /**
     * Хэндлер после удаления
     *
     * @param $arFields
     * @return false
     */
    static function onAfterElementDeleteHandler($arFields)
    {
        return self::log($arFields, 'delete');
    }

    /**
     * Добавление события в лог
     *
     * @param $arFields
     * @param string $action
     * @return bool
     * @throws \Exception
     */
    static function log($arFields, $action = '')
    {
        global $USER;
        // Получаем ID пользователя
        $user_id = $USER->GetID();

        // Получаем тип из URL
        $request = Context::getCurrent()->getRequest();
        $type = $request->get("type");

        if ($type == Option::get(self::$MODULE_ID, "iblock_type", 'news')) {
            $result = Table::add([
                'USER_ID' => "$user_id",
                'BLOCK_ELEMENT_ID' => $arFields['ID'],
                'BLOCK_ID' => $arFields['IBLOCK_ID'],
                'ACTION' => "$action"
            ]);
        } else {
            return false;
        }

        if ($result->isSuccess()) {
            return true;
        } else {
            return false;
        }
    }
}
