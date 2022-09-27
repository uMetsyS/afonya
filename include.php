<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('afonya_newslog', array(
    'Afonya\NewsLog\Table' => 'lib/Table.php',
    'Afonya\NewsLog\Core' => 'lib/Core.php',
    'Afonya\NewsLog\Handler' => 'lib/Handler.php',
    'Afonya\NewsLog\Stats' => 'lib/Stats.php',
    'Afonya\NewsLog\Mail' => 'lib/Mail.php',
));
