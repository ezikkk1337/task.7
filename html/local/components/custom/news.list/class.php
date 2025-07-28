<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Application;
use Bitrix\Main\Web\Uri;
use Bitrix\Iblock\Elements\ElementNewsTable;

class CustomNewsListComponent extends CBitrixComponent
{
    protected $arIBlocks = [];
    protected $arItems = [];
    

    protected function includeModuleFiles()
    {
        if (!Loader::includeModule("iblock")) {
            $this->abortResultCache();
            ShowError("Модуль информационных блоков не установлен");
            return false;
        }
        
        return true;
    }


    protected function checkParameters()
    {
        if (empty($this->arParams["IBLOCK_TYPE"])) {
            $this->abortResultCache();
            ShowError("Не задан тип информационного блока");
            return false;
        }


        $this->arParams["NEWS_COUNT"] = intval($this->arParams["NEWS_COUNT"]) > 0 ? 
            intval($this->arParams["NEWS_COUNT"]) : 20;
        
        $this->arParams["SORT_BY1"] = !empty($this->arParams["SORT_BY1"]) ? 
            $this->arParams["SORT_BY1"] : "ACTIVE_FROM";
        
        $this->arParams["SORT_ORDER1"] = !empty($this->arParams["SORT_ORDER1"]) ? 
            $this->arParams["SORT_ORDER1"] : "DESC";
        
        $this->arParams["SORT_BY2"] = !empty($this->arParams["SORT_BY2"]) ? 
            $this->arParams["SORT_BY2"] : "SORT";
        
        $this->arParams["SORT_ORDER2"] = !empty($this->arParams["SORT_ORDER2"]) ? 
            $this->arParams["SORT_ORDER2"] : "ASC";

        if (!is_array($this->arParams["FIELD_CODE"])) {
            $this->arParams["FIELD_CODE"] = [];
        }

        if (!is_array($this->arParams["PROPERTY_CODE"])) {
            $this->arParams["PROPERTY_CODE"] = [];
        }

        $this->arParams["GROUP_BY_IBLOCK"] = $this->arParams["GROUP_BY_IBLOCK"] !== "N";

        return true;
    }


    protected function getIBlocks()
    {
        $arFilter = [
            "TYPE" => $this->arParams["IBLOCK_TYPE"],
            "ACTIVE" => "Y"
        ];


        if (!empty($this->arParams["IBLOCK_ID"])) {
            $arFilter["ID"] = intval($this->arParams["IBLOCK_ID"]);
        }

        $rsIBlock = CIBlock::GetList(
            ["SORT" => "ASC"],
            $arFilter
        );

        while ($arIBlock = $rsIBlock->Fetch()) {
            $this->arIBlocks[$arIBlock["ID"]] = $arIBlock;
        }

        return !empty($this->arIBlocks);
    }


    protected function getElements()
    {
        if (empty($this->arIBlocks)) {
            return false;
        }

        $arSelect = array_merge(
            ["ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM"],
            $this->arParams["FIELD_CODE"]
        );

        if (!empty($this->arParams["PROPERTY_CODE"])) {
            foreach ($this->arParams["PROPERTY_CODE"] as $propertyCode) {
                if (!empty($propertyCode)) {
                    $arSelect[] = "PROPERTY_" . $propertyCode;
                }
            }
        }

        $arFilter = [
            "IBLOCK_ID" => array_keys($this->arIBlocks),
            "ACTIVE" => "Y",
            "ACTIVE_DATE" => "Y"
        ];

        $arOrder = [
            $this->arParams["SORT_BY1"] => $this->arParams["SORT_ORDER1"],
            $this->arParams["SORT_BY2"] => $this->arParams["SORT_ORDER2"]
        ];

        $rsElements = CIBlockElement::GetList(
            $arOrder,
            $arFilter,
            false,
            ["nTopCount" => $this->arParams["NEWS_COUNT"]],
            $arSelect
        );

        while ($arElement = $rsElements->GetNextElement()) {
            $arFields = $arElement->GetFields();
            $arProps = $arElement->GetProperties();

            $arFields["PROPERTIES"] = $arProps;

            if (!empty($arFields["PREVIEW_PICTURE"])) {
                $arFields["PREVIEW_PICTURE"] = CFile::GetFileArray($arFields["PREVIEW_PICTURE"]);
            }

            if (!empty($arFields["DETAIL_PICTURE"])) {
                $arFields["DETAIL_PICTURE"] = CFile::GetFileArray($arFields["DETAIL_PICTURE"]);
            }

            if (!empty($arFields["ACTIVE_FROM"])) {
                $arFields["DISPLAY_ACTIVE_FROM"] = CIBlockFormatProperties::DateFormat(
                    "d.m.Y",
                    MakeTimeStamp($arFields["ACTIVE_FROM"], CSite::GetDateFormat())
                );
            }

            $this->arItems[] = $arFields;
        }

        return true;
    }

    protected function groupElementsByIBlock()
    {
        if (!$this->arParams["GROUP_BY_IBLOCK"] || empty($this->arItems)) {
            return $this->arItems;
        }

        $arGroupedItems = [];

        foreach ($this->arItems as $arItem) {
            $iblockId = $arItem["IBLOCK_ID"];
            
            if (!isset($arGroupedItems[$iblockId])) {
                $arGroupedItems[$iblockId] = [
                    "IBLOCK_INFO" => $this->arIBlocks[$iblockId],
                    "ITEMS" => []
                ];
            }
            
            $arGroupedItems[$iblockId]["ITEMS"][] = $arItem;
        }

        return $arGroupedItems;
    }


    protected function setPageMetaData()
    {
        global $APPLICATION;

        if ($this->arParams["SET_TITLE"] == "Y") {
            if (!empty($this->arParams["IBLOCK_ID"]) && count($this->arIBlocks) == 1) {
                $iblock = reset($this->arIBlocks);
                $APPLICATION->SetTitle($iblock["NAME"]);
            } else {
                $APPLICATION->SetTitle("Новости");
            }
        }

        if ($this->arParams["SET_BROWSER_TITLE"] == "Y") {
            if (!empty($this->arParams["IBLOCK_ID"]) && count($this->arIBlocks) == 1) {
                $iblock = reset($this->arIBlocks);
                if (!empty($iblock["ELEMENT_META_TITLE"])) {
                    $APPLICATION->SetPageProperty("title", $iblock["ELEMENT_META_TITLE"]);
                }
            }
        }

        if ($this->arParams["SET_META_KEYWORDS"] == "Y") {
            if (!empty($this->arParams["IBLOCK_ID"]) && count($this->arIBlocks) == 1) {
                $iblock = reset($this->arIBlocks);
                if (!empty($iblock["ELEMENT_META_KEYWORDS"])) {
                    $APPLICATION->SetPageProperty("keywords", $iblock["ELEMENT_META_KEYWORDS"]);
                }
            }
        }

        if ($this->arParams["SET_META_DESCRIPTION"] == "Y") {
            if (!empty($this->arParams["IBLOCK_ID"]) && count($this->arIBlocks) == 1) {
                $iblock = reset($this->arIBlocks);
                if (!empty($iblock["ELEMENT_META_DESCRIPTION"])) {
                    $APPLICATION->SetPageProperty("description", $iblock["ELEMENT_META_DESCRIPTION"]);
                }
            }
        }
    }

    protected function prepareResult()
    {
        $this->arResult = [
            "ITEMS" => $this->groupElementsByIBlock(),
            "IBLOCKS" => $this->arIBlocks,
            "GROUPED" => $this->arParams["GROUP_BY_IBLOCK"],
            "ITEMS_COUNT" => count($this->arItems)
        ];
    }

    public function executeComponent()
    {
        try {
            if (!$this->includeModuleFiles()) {
                return;
            }

            if (!$this->checkParameters()) {
                return;
            }

            if ($this->startResultCache()) {
                if (!$this->getIBlocks()) {
                    $this->abortResultCache();
                    ShowError("Не найдены информационные блоки");
                    return;
                }

                if (!$this->getElements()) {
                    $this->abortResultCache();
                }

                $this->prepareResult();
                $this->setPageMetaData();
                
                $this->includeComponentTemplate();
            }
        } catch (Exception $e) {
            $this->abortResultCache();
            ShowError($e->getMessage());
        }
    }
}