<?php
define('ADMIN_MODULE_NAME', 'news_log');

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

Bitrix\Main\Loader::registerAutoLoadClasses(null, [
    'Afonya\NewsLog\LogCore' => '/local/modules/'.ADMIN_MODULE_NAME.'/lib/LogCore.php',
    'Afonya\NewsLog\LogTable' => '/local/modules/'.ADMIN_MODULE_NAME.'/lib/LogTable.php'
]);

use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Afonya\NewsLog\LogCore;

$date_old = Option::get(ADMIN_MODULE_NAME, "date", new DateTime());
$date = ['from' => $date_old, 'to' => new DateTime()];

$stats = LogCore::getStats($date);
$text = LogCore::makeEmail($stats, $date);

$request = Context::getCurrent()->getRequest();
if ($request->get("action")=='send') {
    $email = LogCore::sendMail($text);
    if ($email)
        $text .= PHP_EOL."Обновлено: ".LogCore::dateChange($date['to']);
}

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';

echo "<pre>";
echo $text;
echo "</pre>";

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';
