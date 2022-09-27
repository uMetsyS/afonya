<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

$nota_references_default_option = array(
    "active" => "Y", // Статус агента отправки сообщний
    "time" => "7", // Частота отправки сообщений (дней)
    "iblock_type" => "news", // Тип инфоблока
    "email" => "", // Email администратора
    "date" => date('d.m.Y H:i:s'), // Дата последнего обнволения
    "EventMessageId" => "" // Почтовый шаблон
);
