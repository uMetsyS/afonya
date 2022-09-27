<?php

namespace Afonya\NewsLog;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\SiteTable;

class Mail
{
    static $MODULE_ID = "afonya_newslog";

    /**
     * Получить лидера по кол-ву уникальных действий
     *
     * @param $stats
     * @param string[] $date
     * @return string
     */
    static function makeEmail($stats, $date = ['from' => '', 'to' => ''])
    {
        $email_text = "С $date[from] по $date[to]" . PHP_EOL;
        if ($stats['counter']['all'] > 0) {
            $email_text .= "Добавлено {$stats[counter][add]}
Отредактировано {$stats[counter][update]}
Удалено {$stats[counter][delete]}

Максимальное кол-во действий:
";
            foreach ($stats['leaders'] as $leader) {
                $email_text .= $leader . PHP_EOL;
            }
        } else {
            $email_text .= "Изменений не было";
        }

        return $email_text;
    }

    /**
     * Отправить письмо
     *
     * @param $message
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    static function sendMail($message)
    {
        $datetime = new DateTime();

        // Получаем email из конфигурации
        $mail_to = Option::get(self::$MODULE_ID, "email", '');

        // Получаем системный email, если в админке не задан
        if ($mail_to == '') {
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
                'SUBJECT' => 'Отчет по изменениям новостей от ' . $datetime->format("Y-m-d H:i:s")
            ],
        ];

        // Если задан почтовый шаблон, то используем его
        $MESSAGE_ID = Option::get(self::$MODULE_ID, "EventMessageId", '');
        if ($MESSAGE_ID != '') {
            $mail_params['MESSAGE_ID'] = $MESSAGE_ID;
        }

        Event::send($mail_params);

        return true;
    }

    /**
     * Отправка письма через агента
     *
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

        // Формируем и отправляем письмо
        $text = self::makeEmail([
                'counter' => Stats::getStats($date),
                'leaders' => Stats::getLeaders($date)
            ],
            $date
        );
        $email = self::sendMail($text);
        if ($email) // Если письмо отправлено - обновляем дату
        {
            self::dateChange($date['to']);
        }

        return "Afonya\NewsLog\Mail::AgentSendEmail();";
    }
}