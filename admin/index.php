<?php

define('ADMIN_MODULE_NAME', 'afonya_newslog');

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    'Afonya\NewsLog\Core' => '/local/modules/' . ADMIN_MODULE_NAME . '/lib/Core.php',
    'Afonya\NewsLog\Stats' => '/local/modules/' . ADMIN_MODULE_NAME . '/lib/Stats.php',
    'Afonya\NewsLog\Mail' => '/local/modules/' . ADMIN_MODULE_NAME . '/lib/Mail.php',
    'Afonya\NewsLog\Table' => '/local/modules/' . ADMIN_MODULE_NAME . '/lib/Table.php',
]);

use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Afonya\NewsLog\Core;
use Afonya\NewsLog\Stats;
use Afonya\NewsLog\Mail;

$date_old = Option::get(ADMIN_MODULE_NAME, "date", date('Y-m-d H:i:s'));

$date = ['from' => $date_old, 'to' => new DateTime()];

$text = Mail::makeEmail([
        'counter' => Stats::getStats($date),
        'leaders' => Stats::getLeaders($date)
    ],
    $date
);

$request = Context::getCurrent()->getRequest();
if ($request->get("action") == 'send') {
    $email = Mail::sendMail($text);
    if ($email) {
        $text .= PHP_EOL . "Обновлено: " . Core::dateChange($date['to']);
    }
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

echo "<pre>";
echo $text;
echo "</pre>";

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
