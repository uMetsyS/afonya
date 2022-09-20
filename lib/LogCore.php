<?php
namespace Afonya\NewsLog;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;

class LogCore {
    static $MODULE_ID="news_log";

    /**
     * Добавление события в лог
     * @param $arFields
     * @param string $action
     * @return bool
     * @throws \Exception
     */
    static function log($arFields, $action = '') {
        global $USER;
        // Получаем ID пользователя
        $user_id = $USER->GetID();

        // Получаем тип из URL
        $request = Context::getCurrent()->getRequest();
        $type = $request->get("type");

        if ($type == Option::get(self::$MODULE_ID, "iblock_type", 'news'))
        $result = LogTable::add([
            'USER_ID' => "$user_id",
            'BLOCK_ELEMENT_ID' => $arFields['ID'],
            'BLOCK_ID' => $arFields['IBLOCK_ID'],
            'ACTION' => "$action"
        ]);
        else
            return false;

        if ($result->isSuccess())
            return true;
        else
            return false;
    }

    /**
     * Формирование отчета по изменениям
     * @param string[] $date
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    static function getStats($date = ['from' => '', 'to' => '']) {
        $users = $max_user_ids = $users_name = [];
        $counter = ['update'=>0, 'add'=>0, 'delete'=>0, 'max'=>0, 'all'=>0];

        $stats_params = [
            'select' => ["CNT","USER_ID","BLOCK_ELEMENT_ID","ACTION"],
            'group' => ["USER_ID","BLOCK_ELEMENT_ID","ACTION"],
            'runtime' => [
                new Entity\ExpressionField('CNT', 'COUNT(ID)')
            ]
        ];

        if ($date['from']!='')
            $stats_params['filter'][">DATE"] = $date['from'];
        if ($date['to']!='')
            $stats_params['filter']["<=DATE"] = $date['to'];

        $elements = LogTable::getList($stats_params);

        $elements = $elements->fetchAll();
        foreach ($elements as $element)
        {
            if (!isset($users[$element['USER_ID']]))
                $users[$element['USER_ID']] = 0;
            $counter[$element['ACTION']]++;
            $users[$element['USER_ID']]++;
            $counter['all']++;

            if ($users[$element['USER_ID']]>$counter['max']) {
                $max_user_ids = [$element['USER_ID']];
                $counter['max'] = $users[$element['USER_ID']];
            } elseif ($users[$element['USER_ID']]==$counter['max']) {
                $max_user_ids[] = $element['USER_ID'];
            }
        }

        $users_name = self::getLeader($max_user_ids);

        return ['counter' => $counter, 'leaders' => $users_name];
    }

    /**
     * Получить ФИО лидеров по кол-ву уникальных действий
     * @param $max_user_ids
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    static function getLeader($max_user_ids) {
        $users_name = [];
        if ($max_user_ids) {
            $users = UserTable::getList([
                'select' => [
                    'NAME', 'SECOND_NAME', 'LAST_NAME'
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

    /**
     * Получить лидера по кол-ву уникальных действий
     * @param $stats
     * @param string[] $date
     * @return string
     */
    static function makeEmail($stats, $date = ['from' => '', 'to' => '']) {
        $email_text = "С $date[from] по $date[to]". PHP_EOL;
        if ($stats['counter']['all']>0) {
            $email_text .= "Добавлено {$stats[counter][add]}
Отредактировано {$stats[counter][update]}
Удалено {$stats[counter][delete]}

Максимальное кол-во действий: 
";
        foreach ($stats['leaders'] as $leader)
            $email_text .= $leader . PHP_EOL;
        } else {
            $email_text .= "Изменений не было";
        }

        return $email_text;
    }

    /**
     * Отправить письмо
     * @param $message
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    static function sendMail($message) {
        $datetime = new DateTime();

        // Получаем email из конфигурации
        $mail_to = Option::get(self::$MODULE_ID, "email", '');

        // Получаем системный email, если в админке не задан
        if ($mail_to=='') {
            $arSite = SiteTable::getList()->fetch();
            $mail_to = $arSite["EMAIL"];
        }

        $mail_params = [
            "EVENT_NAME" => "NEWS_LOG_EVENT",
            "LID" => "s1",
            "LANGUAGE_ID" => "ru",
            "C_FIELDS" => [
                "EMAIL_TO" => $mail_to,
                'MESSAGE' => $message,
                'SUBJECT' => 'Отчет по изменениям новостей от '.$datetime->format("Y-m-d H:i:s")
            ],
        ];

        // Если задан почтовый шаблон, то используем его
        $MESSAGE_ID = Option::get(self::$MODULE_ID, "EventMessageId", '');
        if ($MESSAGE_ID != '')
            $mail_params['MESSAGE_ID'] = $MESSAGE_ID;

        Event::send($mail_params);

        return true;
    }

    /**
     * Обновление даты последнего обновления
     * @param string $date_new
     * @return DateTime|mixed|string
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    static function dateChange($date_new = '') {
        if ($date_new=='')
            $date_new = new DateTime();

        Option::set(
            ADMIN_MODULE_NAME,
            "date",
            $date_new
        );

        return $date_new;
    }

    /**
     * Хэндлер после обновления
     * @param $arFields
     * @return bool
     */
    static function onAfterElementUpdateHandler($arFields) {
        return self::log($arFields, 'update');
    }

    /**
     * Хэндлер после добавления
     * @param $arFields
     * @return false
     */
    static function onAfterElementAddHandler($arFields)
    {
        return self::log($arFields, 'add');
    }

    /**
     * Хэндлер после удаления
     * @param $arFields
     * @return false
     */
    static function onAfterElementDeleteHandler($arFields)
    {
        return self::log($arFields, 'delete');
    }

    /**
     * Отправка письма через агента
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    static function AgentSendEmail()
    {
        // Получаем дату последнего обновления
        $date_old = Option::get(ADMIN_MODULE_NAME, "date", new DateTime());
        $date = ['from' => $date_old, 'to' => new DateTime()];

        // Получаем отчет
        $stats = self::getStats($date);
        // Формируем и отправляем письмо
        $text = self::makeEmail($stats, $date);
        $email = self::sendMail($text);
        if ($email) // Если письмо отправлено - обновляем дату
            self::dateChange($date['to']);

        return "Afonya\NewsLog\LogCore::AgentSendEmail();";
    }
}