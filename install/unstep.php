<?php
if (!check_bitrix_sessid()) {
    return;
}
echo CAdminMessage::ShowNote("Модуль afonya_newslog успешно удален из системы");
