<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'news_log');

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

$tabControl = new CAdminTabControl("tabControl", array(
    array(
        "DIV" => "edit1",
        "TAB" => "Основные",
        "TITLE" => "Основные настройки логирования",
    ),
));

if ((!empty($save) || !empty($restore)) && $request->isPost() && check_bitrix_sessid()) {
    if (!empty($restore)) {
        Option::delete(ADMIN_MODULE_NAME);
        CAdminMessage::showMessage(array(
            "MESSAGE" => "Восстановлены настройки по умолчанию",
            "TYPE" => "OK",
        ));
    } elseif ($request->getPost('active') || $request->getPost('iblock_type') || $request->getPost('time') || $request->getPost('email') || $request->getPost('EventMessageId')) {
        $active = "N";
        if ($request->getPost('active')=='Y') {
            $active = "Y";
        }
        Option::set(
            ADMIN_MODULE_NAME,
            "active",
            $active
        );
        Option::set(
            ADMIN_MODULE_NAME,
            "iblock_type",
            $request->getPost('iblock_type')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            "email",
            $request->getPost('email')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            "date",
            $request->getPost('date')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            "EventMessageId",
            $request->getPost('EventMessageId')
        );
        Option::set(
            ADMIN_MODULE_NAME,
            "AgentId",
            $request->getPost('AgentId')
        );
        // Если меняется период, то изменяем агента
        $time = $request->getPost('time');
        if ($time > 0) {
            Option::set(
                ADMIN_MODULE_NAME,
                "time",
                $time
            );
        }

        $AgentId = Option::get(ADMIN_MODULE_NAME, "AgentId", '');
        if ($AgentId > 0)
            CAgent::Update($AgentId, array('ACTIVE' => $active, 'AGENT_INTERVAL' => 60 * 60 * 24 * $request->getPost('time')));

        CAdminMessage::showMessage(array(
            "MESSAGE" => "Настройки сохранены",
            "TYPE" => "OK",
        ));
    } else {
        CAdminMessage::showMessage("Введено неверное значение");
    }
}

$tabControl->begin();
?>

<form name="f_log" method="post" action="<?=sprintf('%s?&mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID)?>">
    <?php
    echo bitrix_sessid_post();
    $tabControl->beginNextTab();
    ?>
    <tr>
        <td width="40%">
            <label for="time"><?="Статус агента отправки сообщений" ?>:</label>
        <td width="60%">
            <input type="checkbox"
                   name="active"
                   value="Y"
                   <?=(Option::get(ADMIN_MODULE_NAME, "active", 'Y')=='Y')?'checked':'';?>
            />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="iblock_type"><?="Тип инфоблока" ?>:</label>
        <td width="60%">
            <input type="text"
                   size="50"
                   maxlength="10"
                   name="iblock_type"
                   value="<?=Option::get(ADMIN_MODULE_NAME, "iblock_type", 'news');?>"
            />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="date"><?="Дата последнего обновления" ?>:</label>
        <td width="60%">
            <?echo CAdminCalendar::CalendarDate("date", Option::get(ADMIN_MODULE_NAME, "date", date('Y-m-d h:m:s')), 20, true)?>
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="time"><?="Частота отправки сообщений (дней)" ?>:</label>
        <td width="60%">
            <input type="text"
                   size="50"
                   maxlength="5"
                   name="time"
                   value="<?=Option::get(ADMIN_MODULE_NAME, "time", '7');?>"
            />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="email"><?="Email администратора" ?>:</label>
        <td width="60%">
            <input type="text"
                   size="50"
                   maxlength="50"
                   name="email"
                   value="<?=Option::get(ADMIN_MODULE_NAME, "email", '');?>"
            />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="EventMessageId"><?="Почтовый шаблон" ?>:</label>
        <td width="60%">
            <input type="text"
                   size="50"
                   maxlength="50"
                   name="EventMessageId"
                   value="<?=Option::get(ADMIN_MODULE_NAME, "EventMessageId", '');?>"
            />
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="EventMessageId"><?="ID Агента" ?>:</label>
        <td width="60%">
            <input type="text"
                   size="50"
                   maxlength="50"
                   name="AgentId"
                   value="<?=Option::get(ADMIN_MODULE_NAME, "AgentId", '');?>"
            />
        </td>
    </tr>
    <?php
    $tabControl->buttons();
    ?>
    <input type="submit"
           name="save"
           value="<?=Loc::getMessage("MAIN_SAVE") ?>"
           title="<?=Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
           class="adm-btn-save"
    />
    <input type="submit"
           name="restore"
           title="<?=Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
           onclick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
           value="<?=Loc::getMessage("MAIN_RESTORE_DEFAULTS") ?>"
    />
    <?php
    $tabControl->end();
    ?>
</form>