<?php
namespace Afonya\NewsLog;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\Validator;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

class LogTable extends DataManager
{
    public static function getTableName()
    {
        return 'news_log_table';
    }

    public static function getMap()
    {
        return array(
            new IntegerField('ID', array(
                'autocomplete' => true,
                'primary' => true,
                'title' => Loc::getMessage('ID'),
            )),
            new IntegerField('USER_ID', array(
                'required' => true,
                'title' => 'ID пользователя',
                'default_value' => function () {
                    return Loc::getMessage('NAME_DEFAULT_VALUE');
                },
            )),
            new IntegerField('BLOCK_ID', array(
                'required' => true,
                'title' => 'ID информационного блока',
                'default_value' => function () {
                    return Loc::getMessage('NAME_DEFAULT_VALUE');
                },
            )),
            new IntegerField('BLOCK_ELEMENT_ID', array(
                'required' => true,
                'title' => 'ID элемента',
                'default_value' => function () {
                    return Loc::getMessage('NAME_DEFAULT_VALUE');
                },
            )),
            new StringField('ACTION', array(
                'required' => true,
                'title' => 'Тип изменения',
                'default_value' => function () {
                    return 'update';
                },
                'validation' => function () {
                    return array(
                        new Validator\Length(null, 255),
                    );
                },
            )),
            new DatetimeField('DATE', array(
                'required' => true,
                'title' => 'Дата изменения',
                'default_value' => function () {
                    return new DateTime();
                },
            ))
        );
    }
}