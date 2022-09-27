<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$aMenu = array(
    array(
        'parent_menu' => 'global_menu_content',
        'sort' => 400,
        'text' => "Лог новостей",
        'title' => "Логирование",
        'url' => 'news_log_admin.php',
        'items_id' => 'menu_references',
        'items' => array(
            array(
                'text' => "Формирование отчета",
                'url' => 'news_log_admin.php?action=send&lang=' . LANGUAGE_ID,
                'more_url' => ['news_log_admin.php?action=send&lang=' . LANGUAGE_ID],
                'title' => "Письмо отправлено",
            ),
        ),
    ),
);

return $aMenu;
