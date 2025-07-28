<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockTable;

$arIBlockType = [];
$arIBlock = [];

if (Loader::includeModule("iblock")) {
    $rsIBlockType = CIBlockType::GetList(["sort" => "asc"], ["ACTIVE" => "Y"]);
    while ($arr = $rsIBlockType->Fetch()) {
        if ($ar = CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID)) {
            $arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
        }
    }

    $rsIBlock = CIBlock::GetList(
        ["sort" => "asc"],
        ["TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE" => "Y"]
    );
    while ($arr = $rsIBlock->Fetch()) {
        $arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
    }
}

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "IBLOCK_TYPE" => [
            "PARENT" => "BASE",
            "NAME" => "Тип информационного блока",
            "TYPE" => "LIST",
            "VALUES" => $arIBlockType,
            "REFRESH" => "Y",
        ],
        "IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => "Код информационного блока (оставить пустым для всех инфоблоков типа)",
            "TYPE" => "LIST",
            "VALUES" => $arIBlock,
            "ADDITIONAL_VALUES" => "Y",
        ],
        "NEWS_COUNT" => [
            "PARENT" => "BASE",
            "NAME" => "Количество новостей на странице",
            "TYPE" => "STRING",
            "DEFAULT" => "20",
        ],
        "SORT_BY1" => [
            "PARENT" => "DATA_SOURCE",
            "NAME" => "Поле для первой сортировки новостей",
            "TYPE" => "LIST",
            "DEFAULT" => "ACTIVE_FROM",
            "VALUES" => [
                "ID" => "ID",
                "NAME" => "Название",
                "ACTIVE_FROM" => "Дата начала активности",
                "SORT" => "Индекс сортировки",
                "TIMESTAMP_X" => "Дата изменения",
            ],
            "ADDITIONAL_VALUES" => "Y",
        ],
        "SORT_ORDER1" => [
            "PARENT" => "DATA_SOURCE",
            "NAME" => "Направление для первой сортировки новостей",
            "TYPE" => "LIST",
            "DEFAULT" => "DESC",
            "VALUES" => [
                "ASC" => "По возрастанию",
                "DESC" => "По убыванию",
            ],
        ],
        "SORT_BY2" => [
            "PARENT" => "DATA_SOURCE",
            "NAME" => "Поле для второй сортировки новостей",
            "TYPE" => "LIST",
            "DEFAULT" => "SORT",
            "VALUES" => [
                "ID" => "ID",
                "NAME" => "Название",
                "ACTIVE_FROM" => "Дата начала активности",
                "SORT" => "Индекс сортировки",
                "TIMESTAMP_X" => "Дата изменения",
            ],
            "ADDITIONAL_VALUES" => "Y",
        ],
        "SORT_ORDER2" => [
            "PARENT" => "DATA_SOURCE",
            "NAME" => "Направление для второй сортировки новостей",
            "TYPE" => "LIST",
            "DEFAULT" => "ASC",
            "VALUES" => [
                "ASC" => "По возрастанию",
                "DESC" => "По убыванию",
            ],
        ],
        "FIELD_CODE" => [
            "PARENT" => "DATA_SOURCE",
            "NAME" => "Поля",
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => [
                "ID" => "ID",
                "CODE" => "Символьный код",
                "NAME" => "Название",
                "TAGS" => "Теги",
                "SORT" => "Индекс сортировки",
                "PREVIEW_TEXT" => "Текст анонса",
                "PREVIEW_PICTURE" => "Картинка анонса",
                "DETAIL_TEXT" => "Детальный текст",
                "DETAIL_PICTURE" => "Детальная картинка",
                "DATE_ACTIVE_FROM" => "Дата начала активности",
                "ACTIVE_FROM" => "Дата начала активности",
                "DATE_ACTIVE_TO" => "Дата окончания активности",
                "ACTIVE_TO" => "Дата окончания активности",
            ],
        ],
        "PROPERTY_CODE" => [
            "PARENT" => "DATA_SOURCE",
            "NAME" => "Свойства",
            "TYPE" => "LIST",
            "MULTIPLE" => "Y",
            "VALUES" => [],
            "ADDITIONAL_VALUES" => "Y",
        ],
        "GROUP_BY_IBLOCK" => [
            "PARENT" => "DATA_SOURCE",
            "NAME" => "Группировать элементы по инфоблокам",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ],
        "INCLUDE_IBLOCK_INTO_CHAIN" => [
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => "Включать инфоблок в цепочку навигации",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ],
        "SET_TITLE" => [
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => "Устанавливать заголовок страницы",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ],
        "SET_BROWSER_TITLE" => [
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => "Устанавливать заголовок окна браузера",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ],
        "SET_META_KEYWORDS" => [
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => "Устанавливать ключевые слова страницы",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ],
        "SET_META_DESCRIPTION" => [
            "PARENT" => "ADDITIONAL_SETTINGS",
            "NAME" => "Устанавливать описание страницы",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ],
        "CACHE_TIME" => ["DEFAULT" => 36000000],
        "CACHE_FILTER" => [
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => "Кешировать при установленном фильтре",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
        ],
        "CACHE_GROUPS" => [
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => "Учитывать права доступа",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ],
    ],
];