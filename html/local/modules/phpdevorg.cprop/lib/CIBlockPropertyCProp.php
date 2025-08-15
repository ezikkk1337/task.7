<?php

IncludeModuleLangFile(__FILE__);

class CIBlockPropertyCPropExtended
{
    private static $showedCss = false;
    private static $showedJs = false;

    public static function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'C_EXTENDED',
            'DESCRIPTION' => GetMessage('IEX_CPROP_EXTENDED_DESC') ?: 'Комплексное свойство (расширенное)',
            'GetPropertyFieldHtml' => array(__CLASS__,  'GetPropertyFieldHtml'),
            'ConvertToDB' => array(__CLASS__, 'ConvertToDB'),
            'ConvertFromDB' => array(__CLASS__,  'ConvertFromDB'),
            'GetSettingsHTML' => array(__CLASS__, 'GetSettingsHTML'),
            'PrepareSettings' => array(__CLASS__, 'PrepareUserSettings'),
            'GetLength' => array(__CLASS__, 'GetLength'),
            'GetPublicViewHTML' => array(__CLASS__, 'GetPublicViewHTML')
        );
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        try {
            $hideText = GetMessage('IEX_CPROP_HIDE_TEXT') ?: 'Скрыть';
            $clearText = GetMessage('IEX_CPROP_CLEAR_TEXT') ?: 'Очистить';

            self::showCss();
            self::showJs();

            if(!empty($arProperty['USER_TYPE_SETTINGS']) && is_array($arProperty['USER_TYPE_SETTINGS'])){
                $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);
            }
            else{
                return '<span>'.GetMessage('IEX_CPROP_ERROR_INCORRECT_SETTINGS') ?: 'Настройки свойства не заданы корректно'.'</span>';
            }

            if (empty($arFields)) {
                return '<span>Поля не настроены</span>';
            }

            $result = '';
            $result .= '<div class="mf-gray"><a class="cl mf-toggle">'.$hideText.'</a>';
            if(isset($arProperty['MULTIPLE']) && $arProperty['MULTIPLE'] === 'Y'){
                $result .= ' | <a class="cl mf-delete">'.$clearText.'</a>';
            }
            $result .= '</div>';
            $result .= '<table class="mf-fields-list active">';

            foreach ($arFields as $code => $arItem){
                if(!isset($arItem['TYPE'])) continue;
                
                switch($arItem['TYPE']) {
                    case 'string':
                        $result .= self::showString($code, $arItem['TITLE'], $value, $strHTMLControlName);
                        break;
                    case 'file':
                        $result .= self::showFile($code, $arItem['TITLE'], $value, $strHTMLControlName);
                        break;
                    case 'text':
                        $result .= self::showTextarea($code, $arItem['TITLE'], $value, $strHTMLControlName);
                        break;
                    case 'html':
                        $result .= self::showHtmlEditor($code, $arItem['TITLE'], $value, $strHTMLControlName);
                        break;
                    case 'date':
                        $result .= self::showDate($code, $arItem['TITLE'], $value, $strHTMLControlName);
                        break;
                    case 'element':
                        $result .= self::showBindElement($code, $arItem['TITLE'], $value, $strHTMLControlName);
                        break;
                }
            }

            $result .= '</table>';

            return $result;
        } catch (Exception $e) {
            return '<span>Ошибка: ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
    }

    public static function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return is_array($value) && isset($value['VALUE']) ? $value['VALUE'] : $value;
    }

    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        try {
            $btnAdd = GetMessage('IEX_CPROP_SETTING_BTN_ADD') ?: 'Добавить поле';
            $settingsTitle = GetMessage('IEX_CPROP_SETTINGS_TITLE') ?: 'Настройки полей';

            $arPropertyFields = array(
                'USER_TYPE_SETTINGS_TITLE' => $settingsTitle,
                'HIDE' => array('ROW_COUNT', 'COL_COUNT', 'DEFAULT_VALUE', 'SEARCHABLE', 'SMART_FILTER', 'WITH_DESCRIPTION', 'FILTRABLE', 'MULTIPLE_CNT', 'IS_REQUIRED'),
                'SET' => array(
                    'MULTIPLE_CNT' => 1,
                    'SMART_FILTER' => 'N',
                    'FILTRABLE' => 'N',
                ),
            );

            if (!isset($strHTMLControlName["NAME"])) {
                return '<tr><td colspan="2">Ошибка в параметрах формы</td></tr>';
            }

            self::showJsForSetting($strHTMLControlName["NAME"]);
            self::showCssForSetting();

            $result = '<tr><td colspan="2" align="center">
                <table id="many-fields-table" class="many-fields-table internal">        
                    <tr valign="top" class="heading mf-setting-title">
                       <td>XML_ID</td>
                       <td>'.(GetMessage('IEX_CPROP_SETTING_FIELD_TITLE') ?: 'Название поля').'</td>
                       <td>'.(GetMessage('IEX_CPROP_SETTING_FIELD_SORT') ?: 'Сортировка').'</td>
                       <td>'.(GetMessage('IEX_CPROP_SETTING_FIELD_TYPE') ?: 'Тип поля').'</td>
                    </tr>';

            $arSetting = [];
            if (!empty($arProperty['USER_TYPE_SETTINGS']) && is_array($arProperty['USER_TYPE_SETTINGS'])) {
                $arSetting = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);
            }

            if(!empty($arSetting)){
                foreach ($arSetting as $code => $arItem) {
                    $title = isset($arItem['TITLE']) ? $arItem['TITLE'] : '';
                    $sort = isset($arItem['SORT']) ? $arItem['SORT'] : 500;
                    $type = isset($arItem['TYPE']) ? $arItem['TYPE'] : 'string';
                    
                    $result .= '
                           <tr valign="top">
                               <td><input type="text" class="inp-code" size="20" value="'.htmlspecialchars($code).'"></td>
                               <td><input type="text" class="inp-title" size="35" name="'.$strHTMLControlName["NAME"].'['.$code.'_TITLE]" value="'.htmlspecialchars($title).'"></td>
                               <td><input type="text" class="inp-sort" size="5" name="'.$strHTMLControlName["NAME"].'['.$code.'_SORT]" value="'.htmlspecialchars($sort).'"></td>
                               <td>
                                    <select class="inp-type" name="'.$strHTMLControlName["NAME"].'['.$code.'_TYPE]">
                                        '.self::getOptionList($type).'
                                    </select>                        
                               </td>
                           </tr>';
                }
            }

            $result .= '
                   <tr valign="top">
                        <td><input type="text" class="inp-code" size="20"></td>
                        <td><input type="text" class="inp-title" size="35"></td>
                        <td><input type="text" class="inp-sort" size="5" value="500"></td>
                        <td>
                            <select class="inp-type">'.self::getOptionList().'</select>                        
                        </td>
                   </tr>
                 </table>   
                    
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <input type="button" value="'.$btnAdd.'" onclick="addNewRows()">
                        </td>
                    </tr>
                    </td></tr>';

            return $result;
        } catch (Exception $e) {
            return '<tr><td colspan="2">Ошибка настроек: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
        }
    }

    public static function PrepareUserSettings($arProperty)
    {
        $result = [];
        if(!empty($arProperty['USER_TYPE_SETTINGS']) && is_array($arProperty['USER_TYPE_SETTINGS'])){
            foreach ($arProperty['USER_TYPE_SETTINGS'] as $code => $value) {
                $result[$code] = $value;
            }
        }
        return $result;
    }

    public static function GetLength($arProperty, $arValue)
    {
        try {

            if (!is_array($arProperty) || !is_array($arValue)) {
                return false;
            }


            $settings = null;
            if (!empty($arProperty['USER_TYPE_SETTINGS'])) {
                if (is_string($arProperty['USER_TYPE_SETTINGS'])) {
                    $settings = @unserialize($arProperty['USER_TYPE_SETTINGS']);
                } elseif (is_array($arProperty['USER_TYPE_SETTINGS'])) {
                    $settings = $arProperty['USER_TYPE_SETTINGS'];
                }
            }
            
            if (!is_array($settings)) {
                return false;
            }
            
            $arFields = self::prepareSetting($settings);
            if (empty($arFields)) {
                return false;
            }


            if (!isset($arValue['VALUE']) || !is_array($arValue['VALUE'])) {
                return false;
            }

            foreach($arValue['VALUE'] as $code => $value){
                if (isset($arFields[$code]) && isset($arFields[$code]['TYPE'])) {
                    if($arFields[$code]['TYPE'] === 'file'){
                        if(is_array($value)) {
                            if(!empty($value['name']) || (!empty($value['OLD']) && empty($value['DEL']))){
                                return true;
                            }
                        } elseif (!empty($value)) {
                            return true;
                        }
                    }
                    else{
                        if(!empty($value)){
                            return true;
                        }
                    }
                }
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function ConvertToDB($arProperty, $arValue)
    {
        try {

            if (!is_array($arProperty) || !isset($arProperty['USER_TYPE_SETTINGS'])) {
                return ['VALUE' => '', 'DESCRIPTION' => ''];
            }
            
            if (!is_array($arValue) || !isset($arValue['VALUE']) || !is_array($arValue['VALUE'])) {
                return ['VALUE' => '', 'DESCRIPTION' => ''];
            }

            $arFields = self::prepareSetting($arProperty['USER_TYPE_SETTINGS']);
            if (empty($arFields)) {
                return ['VALUE' => '', 'DESCRIPTION' => ''];
            }


            foreach($arValue['VALUE'] as $code => $value){
                if (isset($arFields[$code]) && isset($arFields[$code]['TYPE'])) {
                    if($arFields[$code]['TYPE'] === 'file' && is_array($value)){
                        $arValue['VALUE'][$code] = self::prepareFileToDB($value);
                    }
                    elseif($arFields[$code]['TYPE'] === 'html'){

                        $htmlFieldName = str_replace(array('[', ']'), '_', $code);
                        if(isset($_POST[$htmlFieldName])){
                            $arValue['VALUE'][$code] = $_POST[$htmlFieldName];
                        }
                    }
                }
            }

            $isEmpty = true;
            foreach ($arValue['VALUE'] as $v){
                if(!empty($v)){
                    $isEmpty = false;
                    break;
                }
            }

            if(!$isEmpty){
                return ['VALUE' => json_encode($arValue['VALUE']), 'DESCRIPTION' => ''];
            }
            else{
                return ['VALUE' => '', 'DESCRIPTION' => ''];
            }
        } catch (Exception $e) {
            return ['VALUE' => '', 'DESCRIPTION' => ''];
        }
    }

    public static function ConvertFromDB($arProperty, $arValue)
    {
        try {
            $return = array();

            if(!empty($arValue['VALUE']) && is_string($arValue['VALUE'])){
                $arData = @json_decode($arValue['VALUE'], true);
                
                if (is_array($arData)) {
                    foreach ($arData as $code => $value){
                        $return['VALUE'][$code] = $value;
                    }
                }
            }
            return $return;
        } catch (Exception $e) {
            return array();
        }
    }


    private static function showString($code, $title, $arValue, $strHTMLControlName)
    {
        $v = (is_array($arValue) && isset($arValue['VALUE'][$code])) ? htmlspecialchars($arValue['VALUE'][$code]) : '';
        $safeTitle = htmlspecialchars($title);
        
        return '<tr>
                <td align="right">'.$safeTitle.': </td>
                <td><input type="text" value="'.$v.'" name="'.$strHTMLControlName['VALUE'].'['.$code.']"/></td>
            </tr>';
    }

    private static function showFile($code, $title, $arValue, $strHTMLControlName)
    {
        $result = '';
        $safeTitle = htmlspecialchars($title);

        $fileId = '';
        if (is_array($arValue) && isset($arValue['VALUE'][$code])) {
            if (!is_array($arValue['VALUE'][$code])) {
                $fileId = $arValue['VALUE'][$code];
            } elseif (!empty($arValue['VALUE'][$code]['OLD'])) {
                $fileId = $arValue['VALUE'][$code]['OLD'];
            }
        }

        if(!empty($fileId) && class_exists('CFile'))
        {
            $arPicture = CFile::GetByID($fileId)->Fetch();
            if($arPicture)
            {
                $strImageStorePath = COption::GetOptionString('main', 'upload_dir', 'upload');
                $sImagePath = '/'.$strImageStorePath.'/'.$arPicture['SUBDIR'].'/'.$arPicture['FILE_NAME'];
                $fileType = self::getExtension($sImagePath);

                if(in_array($fileType, ['png', 'jpg', 'jpeg', 'gif'])){
                    $content = '<img src="'.htmlspecialchars($sImagePath).'" style="max-height: 150px;">';
                }
                else{
                    $content = '<div class="mf-file-name">'.htmlspecialchars($arPicture['FILE_NAME']).'</div>';
                }

                $result = '<tr>
                        <td align="right" valign="top">'.$safeTitle.': </td>
                        <td>
                            <div style="background-color: #e0e8e9; padding: 10px;">
                                '.$content.'<br>
                                <div>
                                    <label><input name="'.$strHTMLControlName['VALUE'].'['.$code.'][DEL]" value="Y" type="checkbox"> '. (GetMessage("IEX_CPROP_FILE_DELETE") ?: 'Удалить файл') . '</label>
                                    <input name="'.$strHTMLControlName['VALUE'].'['.$code.'][OLD]" value="'.htmlspecialchars($fileId).'" type="hidden">
                                </div>
                            </div>                     
                        </td>
                    </tr>';
            }
        }
        else{
            $result .= '<tr>
                    <td align="right">'.$safeTitle.': </td>
                    <td><input type="file" value="" name="'.$strHTMLControlName['VALUE'].'['.$code.']"/></td>
                </tr>';
        }

        return $result;
    }

    private static function showTextarea($code, $title, $arValue, $strHTMLControlName)
    {
        $v = (is_array($arValue) && isset($arValue['VALUE'][$code])) ? htmlspecialchars($arValue['VALUE'][$code]) : '';
        $safeTitle = htmlspecialchars($title);
        
        return '<tr>
                <td align="right" valign="top">'.$safeTitle.': </td>
                <td><textarea rows="8" name="'.$strHTMLControlName['VALUE'].'['.$code.']" style="min-width: 350px;">'.$v.'</textarea></td>
            </tr>';
    }

    private static function showHtmlEditor($code, $title, $arValue, $strHTMLControlName)
    {
        $v = (is_array($arValue) && isset($arValue['VALUE'][$code])) ? $arValue['VALUE'][$code] : '';
        $safeTitle = htmlspecialchars($title);
        
        $editorName = str_replace(array('[', ']'), '_', $strHTMLControlName['VALUE'].'_'.$code);
        
        $result = '<tr>
                <td align="right" valign="top">'.$safeTitle.': </td>
                <td>';
        
        if (class_exists('CFileMan')) {
            ob_start();
            CFileMan::AddHTMLEditorFrame(
                $editorName,
                htmlspecialcharsbx($v),
                $editorName."_TYPE",
                strlen($v) > 0 ? "html" : "text",
                array(
                    'height' => 300,
                    'width' => '100%'
                )
            );
            
            echo '<input type="hidden" name="'.$strHTMLControlName['VALUE'].'['.$code.']" value="">';
            
            $htmlEditor = ob_get_contents();
            ob_end_clean();
            
            $result .= $htmlEditor;
            
            $result .= '<script>
                $(document).ready(function(){
                    if(typeof window.htmlEditorSync === "undefined"){
                        window.htmlEditorSync = {};
                    }
                    
                    window.htmlEditorSync["'.$editorName.'"] = function(){
                        var editorContent = "";
                        if(window.BXHtmlEditor && window.BXHtmlEditor.editors["'.$editorName.'"]){
                            editorContent = window.BXHtmlEditor.editors["'.$editorName.'"].GetContent();
                        }
                        $("input[name=\''.$strHTMLControlName['VALUE'].'['.$code.']\']").val(editorContent);
                    };
                    
                    // Синхронизация при отправке формы
                    $(document).on("submit", "form", function(){
                        if(window.htmlEditorSync["'.$editorName.'"]){
                            window.htmlEditorSync["'.$editorName.'"]();
                        }
                    });
                });
            </script>';
        } else {

            $result .= '<textarea rows="8" name="'.$strHTMLControlName['VALUE'].'['.$code.']" style="min-width: 350px;">'.htmlspecialchars($v).'</textarea>';
        }
        
        $result .= '</td></tr>';
        
        return $result;
    }

    private static function showDate($code, $title, $arValue, $strHTMLControlName)
    {
        $v = (is_array($arValue) && isset($arValue['VALUE'][$code])) ? htmlspecialchars($arValue['VALUE'][$code]) : '';
        $safeTitle = htmlspecialchars($title);
        
        $result = '<tr>
                    <td align="right" valign="top">'.$safeTitle.': </td>
                    <td>
                        <div class="adm-input-wrap adm-input-wrap-calendar">
                            <input class="adm-input adm-input-calendar" type="text" name="'.$strHTMLControlName['VALUE'].'['.$code.']" size="23" value="'.$v.'">
                            <span class="adm-calendar-icon"
                                  onclick="BX.calendar({node: this, field:\''.$strHTMLControlName['VALUE'].'['.$code.']\', form: \'\', bTime: true, bHideTime: false});"></span>
                        </div>
                    </td>
                </tr>';

        return $result;
    }

    private static function showBindElement($code, $title, $arValue, $strHTMLControlName)
    {
        $v = (is_array($arValue) && isset($arValue['VALUE'][$code])) ? $arValue['VALUE'][$code] : '';
        $safeTitle = htmlspecialchars($title);

        $elUrl = '';
        if(!empty($v) && class_exists('CIBlockElement')){
            $arElem = CIBlockElement::GetList([], ['ID' => intval($v)],false, ['nPageSize' => 1], ['ID', 'IBLOCK_ID', 'IBLOCK_TYPE_ID', 'NAME'])->Fetch();
            if(!empty($arElem)){
                $elUrl .= '<a target="_blank" href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$arElem['IBLOCK_ID'].'&ID='.$arElem['ID'].'&type='.$arElem['IBLOCK_TYPE_ID'].'">'.htmlspecialchars($arElem['NAME']).'</a>';
            }
        }

        $result = '<tr>
                <td align="right">'.$safeTitle.': </td>
                <td>
                    <input name="'.$strHTMLControlName['VALUE'].'['.$code.']" id="'.$strHTMLControlName['VALUE'].'['.$code.']" value="'.htmlspecialchars($v).'" size="8" type="text">
                    <input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=ru&IBLOCK_ID=0&n='.$strHTMLControlName['VALUE'].'&k='.$code.'\', 900, 700);">&nbsp;
                    <span>'.$elUrl.'</span>
                </td>
            </tr>';

        return $result;
    }

    private static function showCss()
    {
        if(!self::$showedCss) {
            self::$showedCss = true;
            echo '<style>
                .cl {cursor: pointer;}
                .mf-gray {color: #797777;}
                .mf-fields-list {display: none; padding-top: 10px; margin-bottom: 10px; margin-left: -300px; border-bottom: 1px #e0e8ea solid;}
                .mf-fields-list.active {display: block;}
                .mf-fields-list td {padding-bottom: 5px;}
                .mf-fields-list td:first-child {width: 300px; color: #616060;}
                .mf-fields-list td:last-child {padding-left: 5px;}
                .mf-fields-list input[type="text"] {width: 350px;}
                .mf-fields-list textarea {min-width: 350px; max-width: 650px; color: #000;}
                .mf-fields-list img {max-height: 150px; margin: 5px 0;}
                .mf-fields-list input[type="text"].adm-input-calendar {width: 170px;}
                .mf-file-name {word-break: break-word; padding: 5px 5px 0 0; color: #101010;}
                .many-fields-table {margin: 0 auto; width: 100%;}
                .mf-setting-title td {text-align: center;}
                .many-fields-table td {text-align: center; padding: 5px;}
            </style>';
        }
    }

    private static function showJs()
    {
        $showText = GetMessage('IEX_CPROP_SHOW_TEXT') ?: 'Показать';
        $hideText = GetMessage('IEX_CPROP_HIDE_TEXT') ?: 'Скрыть';

        if(!self::$showedJs) {
            self::$showedJs = true;
            CJSCore::Init(array("jquery"));
            echo '<script>
                $(document).ready(function(){
                    $(document).on("click", "a.mf-toggle", function (e) {
                        e.preventDefault();
                        var table = $(this).closest("tr").find("table.mf-fields-list");
                        $(table).toggleClass("active");
                        $(this).text($(table).hasClass("active") ? "'.$hideText.'" : "'.$showText.'");
                    });
                    
                    $(document).on("click", "a.mf-delete", function (e) {
                        e.preventDefault();
                        var container = $(this).closest("tr");
                        
                        // Очистка текстовых полей и textarea
                        container.find("input[type=text], textarea").val("");
                        
                        // Очистка HTML редакторов
                        if(window.BXHtmlEditor){
                            for(var editorId in window.BXHtmlEditor.editors){
                                if(container.find("[id*=" + editorId + "]").length > 0){
                                    window.BXHtmlEditor.editors[editorId].SetContent("");
                                }
                            }
                        }
                        
                        // Отметка файлов на удаление
                        container.find("input[type=checkbox]").prop("checked", true);
                        
                        container.hide("slow");
                    });
                });
            </script>';
        }
    }

    private static function showJsForSetting($inputName)
    {
        CJSCore::Init(array("jquery"));
        $safeInputName = htmlspecialchars($inputName);
        
        echo '<script>
            function addNewRows() {
                $("#many-fields-table").append(
                    \'<tr valign="top">\' +
                    \'<td><input type="text" class="inp-code" size="20"></td>\' +
                    \'<td><input type="text" class="inp-title" size="35"></td>\' +
                    \'<td><input type="text" class="inp-sort" size="5" value="500"></td>\' +
                    \'<td><select class="inp-type">'.self::getOptionList().'</select></td>\' +
                    \'</tr>\'
                );
            }

            $(document).on("change", ".inp-code", function(){
                var code = $(this).val();
                var row = $(this).closest("tr");

                if(code.length <= 0){
                    row.find("input.inp-title, input.inp-sort, select.inp-type").removeAttr("name");
                }
                else{
                    row.find("input.inp-title").attr("name", "'.$safeInputName.'[" + code + "_TITLE]");
                    row.find("input.inp-sort").attr("name", "'.$safeInputName.'[" + code + "_SORT]");
                    row.find("select.inp-type").attr("name", "'.$safeInputName.'[" + code + "_TYPE]");
                }
            });

            $(document).on("input", ".inp-sort", function(){
                $(this).val($(this).val().replace(/[^0-9]/g, ""));
            });
        </script>';
    }

    private static function showCssForSetting()
    {

    }

    private static function prepareSetting($arSetting)
    {
        if (!is_array($arSetting)) {
            return [];
        }
        
        $arResult = [];

        foreach ($arSetting as $key => $value){
            if(strpos($key, '_TITLE') !== false) {
                $code = str_replace('_TITLE', '', $key);
                $arResult[$code]['TITLE'] = $value;
            }
            elseif(strpos($key, '_SORT') !== false) {
                $code = str_replace('_SORT', '', $key);
                $arResult[$code]['SORT'] = intval($value);
            }
            elseif(strpos($key, '_TYPE') !== false) {
                $code = str_replace('_TYPE', '', $key);
                $arResult[$code]['TYPE'] = $value;
            }
        }


        uasort($arResult, function($a, $b) {
            $sortA = isset($a['SORT']) ? $a['SORT'] : 500;
            $sortB = isset($b['SORT']) ? $b['SORT'] : 500;
            return $sortA - $sortB;
        });

        return $arResult;
    }

    private static function getOptionList($selected = 'string')
    {
        $arOption = [
            'string' => GetMessage('IEX_CPROP_FIELD_TYPE_STRING') ?: 'Строка',
            'file' => GetMessage('IEX_CPROP_FIELD_TYPE_FILE') ?: 'Файл',
            'text' => GetMessage('IEX_CPROP_FIELD_TYPE_TEXT') ?: 'Текст',
            'html' => GetMessage('IEX_CPROP_FIELD_TYPE_HTML') ?: 'HTML',
            'date' => GetMessage('IEX_CPROP_FIELD_TYPE_DATE') ?: 'Дата',
            'element' => GetMessage('IEX_CPROP_FIELD_TYPE_ELEMENT') ?: 'Привязка к элементу'
        ];

        $result = '';
        foreach ($arOption as $code => $name){
            $sel = ($code === $selected) ? 'selected' : '';
            $result .= '<option value="'.htmlspecialchars($code).'" '.$sel.'>'.htmlspecialchars($name).'</option>';
        }

        return $result;
    }

    private static function prepareFileToDB($arValue)
    {
        if (!is_array($arValue)) {
            return false;
        }
        
        if(!empty($arValue['DEL']) && $arValue['DEL'] === 'Y' && !empty($arValue['OLD'])){
            if (class_exists('CFile')) {
                CFile::Delete($arValue['OLD']);
            }
            return false;
        }
        elseif(!empty($arValue['OLD'])){
            return $arValue['OLD'];
        }
        elseif(!empty($arValue['name']) && class_exists('CFile')){
            return CFile::SaveFile($arValue, 'iblock');
        }

        return false;
    }

    private static function getExtension($filePath)
    {
        if (empty($filePath)) return '';
        $parts = explode('.', $filePath);
        return strtolower(end($parts));
    }
}