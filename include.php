<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('news_log', array(
    'Afonya\NewsLog\LogCore' => 'lib/LogCore.php',
    'Afonya\NewsLog\LogTable' => 'lib/LogTable.php',
));