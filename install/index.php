<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;
use Bitrix\Main\Config\Option;
use Afonya\NewsLog\LogTable;

Class news_log extends CModule
{
    var $MODULE_ID = "news_log";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    function news_log()
    {
        $arModuleVersion = [];

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = "! Логирование действий с новостями";
        $this->MODULE_DESCRIPTION = "Раз в неделю будет отправляться письмо с отчетом по изменению новостей";
    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/news_log/install/admin",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/news_log/install/a
dmin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
        return true;
    }

    function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        RegisterModule($this->MODULE_ID);

        $this->InstallFiles();
        // Отслеживание событий
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler("iblock", "OnAfterIBlockElementUpdate", $this->MODULE_ID, "Afonya\NewsLog\LogCore", "onAfterElementUpdateHandler");
        $eventManager->registerEventHandler("iblock", "onAfterIBlockElementAdd", $this->MODULE_ID, "Afonya\NewsLog\LogCore", "onAfterElementAddHandler");
        $eventManager->registerEventHandler("iblock", "OnAfterIBlockElementDelete", $this->MODULE_ID, "Afonya\NewsLog\LogCore", "onAfterElementDeleteHandler");

        // Создание таблиц БД
        $this->installDB();

        // Добавляем событие
        CEventType::Add(array(
            "LID"           => "ru",
            "EVENT_NAME"    => "NEWS_LOG_EVENT",
            "NAME"          => "Отправка отчета по изменениям",
            "DESCRIPTION"   => "
            #EMAIL_TO# - EMail получателя сообщения (#OWNER_EMAIL#)
            #MESSAGE# - Сообщение
            #SUBJECT# - Тема
            "
        ));

        $em = new CEventMEssage;
        $EventMessageId = $em->Add(array(
            'ACTIVE'     => "Y",
            'EVENT_NAME' => "NEWS_LOG_EVENT",
            "LID"        => array("s1"),
            "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            "EMAIL_TO"   => "#EMAIL_TO#",
            "SUBJECT"    => "#SUBJECT#",
            "BODY_TYPE"  => "text",
            "MESSAGE"    => "#MESSAGE#"
        ));
        Option::set(
            $this->MODULE_ID,
            "EventMessageId",
            $EventMessageId
        );

        // Добавляем агента
        $AgentId = CAgent::AddAgent(
            "Afonya\NewsLog\LogCore::AgentSendEmail();",
            "{$this->MODULE_ID}",
            "Y",
            60 * 60 * 24 * 7,
            "",
            "Y",
            date("d.m.Y H:i:s")
        );
        Option::set(
            $this->MODULE_ID,
            "AgentId",
            $AgentId
        );

        $APPLICATION->IncludeAdminFile("Установка модуля news_log", $DOCUMENT_ROOT."/local/modules/news_log/install/step.php");
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        // Удаляем агента
        CAgent::RemoveAgent(
            "Afonya\NewsLog\LogCore::AgentSendEmail();",
            "{$this->MODULE_ID}");

        // Отключение отслеживания событий
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementUpdate", $this->MODULE_ID, "Afonya\NewsLog\LogCore", "onAfterElementUpdateHandler");
        $eventManager->unRegisterEventHandler("iblock", "onAfterIBlockElementAdd", $this->MODULE_ID, "Afonya\NewsLog\LogCore", "onAfterElementAddHandler");
        $eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementDelete", $this->MODULE_ID, "Afonya\NewsLog\LogCore", "onAfterElementDeleteHandler");

        // Удаление почтового события и шаблона письма
        CEventType::Delete(Option::get($this->MODULE_ID, "EventTypeId"));
        CEventMessage::Delete(Option::get($this->MODULE_ID, "EventMessageId"));

        $this->UnInstallFiles();

        // Удаление таблиц БД
        $this->uninstallDB();

        UnRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile("Деинсталляция модуля news_log", $DOCUMENT_ROOT."/local/modules/news_log/install/unstep.php");
    }

    public function installDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
            LogTable::getEntity()->createDbTable();
        }
    }

    public function uninstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
            $connection = Application::getInstance()->getConnection();
            $connection->dropTable(LogTable::getTableName());
        }
    }
}
?>