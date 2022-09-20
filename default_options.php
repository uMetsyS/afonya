<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Type\DateTime;

$nota_references_default_option = array(
    "active" => "Y", // Статус агента отправки сообщний
    "time" => "7", // Частота отправки сообщений (дней)
    "iblock_type" => "news", // Тип инфоблока
    "email" => "", // Email администратора
    "date" => DateTime(), // Дата последнего обнволения
    "EventMessageId" => "" // Почтовый шаблон
);
