<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = [
    "NAME" => "Список новостей (расширенный)",
    "DESCRIPTION" => "Компонент для вывода новостей с возможностью группировки по инфоблокам",
    "ICON" => "/images/news_list.gif",
    "SORT" => 20,
    "CACHE_PATH" => "Y",
    "PATH" => [
        "ID" => "custom",
        "NAME" => "Пользовательские компоненты",
        "SORT" => 10,
        "CHILD" => [
            "ID" => "news",
            "NAME" => "Новости",
            "SORT" => 20
        ]
    ],
];