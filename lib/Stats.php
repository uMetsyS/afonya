<?php

namespace Afonya\NewsLog;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\UserTable;
use Bitrix\Main\Entity;

class Stats
{
    static $MODULE_ID = "afonya_newslog";

    /**
     * Формирование отчета по изменениям
     *
     * @param string[] $date
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    static function getStats($date = ['from' => '', 'to' => ''])
    {
        $counter = ['update' => 0, 'add' => 0, 'delete' => 0, 'max' => 0, 'all' => 0];

        $stats_params = [
            'select' => ["CNT", "BLOCK_ELEMENT_ID", "ACTION"],
            'group' => ["BLOCK_ELEMENT_ID", "ACTION"],
            'runtime' => [
                new Entity\ExpressionField('CNT', 'COUNT(ID)')
            ]
        ];

        if ($date['from'] != '') {
            $stats_params['filter'][">DATE"] = $date['from'];
        }
        if ($date['to'] != '') {
            $stats_params['filter']["<=DATE"] = $date['to'];
        }

        $elements = Table::getList($stats_params);

        $elements = $elements->fetchAll();
        foreach ($elements as $element) {
            $counter[$element['ACTION']]++;
            $counter['all']++;
        }

        return $counter;
    }

    /**
     * Получить ФИО лидеров по кол-ву уникальных действий
     *
     * @param $max_user_ids
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    static function getLeaders($date = ['from' => '', 'to' => ''])
    {
        $users = $max_user_ids = $users_name = [];
        $counter = ['max' => 0];

        $stats_params = [
            'select' => ["CNT", "USER_ID", "BLOCK_ELEMENT_ID", "ACTION"],
            'group' => ["USER_ID", "BLOCK_ELEMENT_ID", "ACTION"],
            'runtime' => [
                new Entity\ExpressionField('CNT', 'COUNT(ID)')
            ]
        ];

        if ($date['from'] != '') {
            $stats_params['filter'][">DATE"] = $date['from'];
        }
        if ($date['to'] != '') {
            $stats_params['filter']["<=DATE"] = $date['to'];
        }

        $elements = Table::getList($stats_params);

        $elements = $elements->fetchAll();
        foreach ($elements as $element) {
            if (!isset($users[$element['USER_ID']])) {
                $users[$element['USER_ID']] = 0;
            }
            $users[$element['USER_ID']]++;

            if ($users[$element['USER_ID']] > $counter['max']) {
                $max_user_ids = [$element['USER_ID']];
                $counter['max'] = $users[$element['USER_ID']];
            } elseif ($users[$element['USER_ID']] == $counter['max']) {
                $max_user_ids[] = $element['USER_ID'];
            }
        }

        if ($max_user_ids) {
            $users = UserTable::getList([
                'select' => [
                    'NAME',
                    'SECOND_NAME',
                    'LAST_NAME'
                ],
                'filter' => [
                    '@ID' => $max_user_ids
                ]
            ]);
            while ($user = $users->fetch()) {
                $users_name[] = implode(" ", array_filter($user));
            }
        }
        return $users_name;
    }
}