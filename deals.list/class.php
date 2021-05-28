<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Highloadblock as HL;
use \Bitrix\Main\Entity;
use Elfsight\utils\sync\helpers as sync_helpers;
use Bitrix\Main\Diag;

Loc::loadMessages(__FILE__);

class HLListComponent extends CBitrixComponent
{
    protected $queryParams = array();
    protected $preFilter = array();
    protected $navigation = false;
    protected $nav = null;

    protected function checkModules()
    {
        $reqModules = $this->getModules();
        if (!is_array($reqModules) || empty($reqModules)) {
            $reqModules = array('highloadblock');
        }
        foreach ($reqModules as $module) {
            if (!Main\Loader::includeModule($module)) {
                throw new Exception(Loc::getMessage('HL_LIST_MODULE_NOT_FOUND', array('#MODULE_ID#' => $module)));
            }
        }
    }

    protected function getModules()
    {
        return array(
            'highloadblock',
            'iblock',
            'sale',
            'catalog'
        );
    }

    protected function initPageNavigation()
    {
        $nav = new \Bitrix\Main\UI\PageNavigation("nav-rows");
        $nav->allowAllRecords($this->arParams["PAGER_SHOW_ALL"] == "Y")
            ->setPageSize($this->arParams["PAGE_COUNT"])
            ->initFromUri();

        if ($this->arParams["AJAX_NAV_PAGE_NUMBER"] > 0) $nav->setCurrentPage($this->arParams["AJAX_NAV_PAGE_NUMBER"]);

        if ($this->arParams['DISPLAY_TOP_PAGER'] || $this->arParams['DISPLAY_BOTTOM_PAGER']) {
            $this->navigation = array(
                "page_size" => $nav->getPageSize(),
                "page_number" => $nav->getCurrentPage(),
                "page_count" => $this->arParams["PAGE_COUNT"],
                "showAll" => $this->arParams["PAGER_SHOW_ALL"],
                "allRecords" => ($nav->allRecordsShown() ? "Y" : "N")
            );
        } else {
            $this->navigation = false;
        }
        $this->nav = $nav;
        //print_r($this->arParams["AJAX_NAV_PAGE_NUMBER"]);
    }

    protected function extractDataFromCache()
    {
        if ($this->arParams['CACHE_TYPE'] == 'N') {
            return false;
        }
        global $USER;
        $additionalCacheDependencies = array($this->navigation, $USER->GetID());

        return !($this->startResultCache(
            false,
            $additionalCacheDependencies
        ));
    }

    protected function putDataToCache()
    {
        $this->endResultCache();
    }

    protected function abortDataCache()
    {
        $this->abortResultCache();
    }

    public function onPrepareComponentParams($params)
    {
        $params["BLOCK_ID"] = intval($params["BLOCK_ID"]);
        if (!isset($params["CACHE_TIME"])) {
            $params["CACHE_TIME"] = 86400;
        }
        $params["DETAIL_URL"] = trim($params["DETAIL_URL"]);
        $params["CACHE_FILTER"] = ($params["CACHE_FILTER"] == "Y" ? "Y" : "N");
        $params["PAGE_COUNT"] = intval($params["PAGE_COUNT"]);

        $arAllowedSortOrders = array("ASC", "DESC");
        if (!in_array($params["SORT_ORDER"], $arAllowedSortOrders))
            $params["SORT_ORDER"] = "ASC";

        if ($params["CACHE_TYPE"] == "N"
            || ($params["CACHE_TYPE"] == "A" && Main\Config\Option::get("main", "component_cache_on", "Y") == "N")
        ) {
            $params["CACHE_TIME"] = 0;
        }

        if (empty($params["FILTER_NAME"])
            || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $params["FILTER_NAME"])
        ) {
            $this->preFilter = array();
        } else {
            global ${$params["FILTER_NAME"]};
            $this->preFilter = ${$params["FILTER_NAME"]};
            if (!is_array($this->preFilter)) {
                $this->preFilter = array();
            }
        }

        if (!empty($this->preFilter) && $params["CACHE_FILTER"] === "N") {
            $params["CACHE_TIME"] = 0;
        }

        if (empty($params["USER_PROPERTY"]) || !is_array($params["USER_PROPERTY"])) {
            $params["USER_PROPERTY"] = array();
        } else {
            $params["USER_PROPERTY"] = array_unique(array_filter($params["USER_PROPERTY"]));
        }

        return $params;
    }

    public function initResult()
    {
        $this->arResult = array(
            "BLOCK_ID" => null,
            "BLOCK_NAME" => null,
            "ITEMS" => array(),
            "FIELDS" => array()
        );

        if ($this->nav instanceof \Bitrix\Main\UI\PageNavigation) {
            $this->arResult['NAV_OBJECT'] = $this->nav;
        }
    }

    public function prepareQuery()
    {
        $this->queryParams["order"] = array(
            $this->arParams["SORT_FIELD"] => $this->arParams["SORT_ORDER"]
        );

        $this->queryParams["filter"] = $this->preFilter;

        global $USER;
        //предварительная фильтрация пользователя
        if (in_array(12, $USER->GetUserGroupArray())) {
           if ($userdata = $USER->GetByID($USER->GetID())->fetch()) {
               $this->queryParams["filter"]['UF_CLIENT_1C'] = $userdata['UF_CODE_1C'];
           }
        }

        $this->queryParams["select"] = array_merge(array('ID'), $this->arParams['USER_PROPERTY']);
        $this->queryParams["runtime"] = array(
            new \Bitrix\Main\Entity\ExpressionField("RAND", "RAND()")
        );

        if ($this->arParams["PAGE_COUNT"] > 0) {
            $this->queryParams["limit"] = $this->arParams["PAGE_COUNT"];
        }
    }

    public function makeQuery()
    {
        if (!intval($this->arParams["BLOCK_ID"])) {
            throw new \Exception(Loc::getMessage('HL_LIST_HLBLOCK_ID_NOT_SET'));
        }

        $hlblockId = $this->arParams['BLOCK_ID'];
        $hlBlock = HL\HighloadBlockTable::getById($hlblockId)->fetch();

        if (empty($hlBlock)) {
            throw new \Exception(Loc::getMessage('HL_LIST_HLBLOCK_NOT_FOUND'));
        }

        $this->arResult['BLOCK_ID'] = $hlBlock['ID'];
        $this->arResult['BLOCK_NAME'] = $hlBlock['NAME'];

        $entity = HL\HighloadBlockTable::compileEntity($hlBlock);
        $dataClass = $entity->getDataClass();

        $fields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('HLBLOCK_' . $hlBlock['ID'], 0, LANGUAGE_ID);
        foreach ($fields as $key => $field) {
            if (!in_array($key, $this->arParams['USER_PROPERTY'])) {
                unset($fields[$key]);
            }
        }

        $this->arResult['FIELDS'] = $fields;

        if (!isset($fields[$this->arParams["SORT_FIELD"]])) {
            $this->arParams["SORT_FIELD"] = "ID";
        }

        $rows = $dataClass::getList(array(
            'order' => $this->queryParams["order"],
            'filter' => $this->queryParams["filter"],
            'select' => $this->queryParams["select"],
            'runtime' => $this->queryParams["runtime"],
            'count_total' => true,
            'offset' => $this->arResult["NAV_OBJECT"]->getOffset(),
            'limit' => $this->arResult["NAV_OBJECT"]->getLimit(),
        ));

        $this->arResult["NAV_OBJECT"]->setRecordCount($rows->getCount());
        $this->arResult["NAV_STRING"] = $this->getNavString($this->arResult["NAV_OBJECT"]);

        while ($row = $rows->fetch()) {
            foreach ($row as $field => $value) {
                if ($field == 'ID') {
                    continue;
                }

                $row['~' . $field] = $value;
                $arUserField = $fields[$field];
                $html = call_user_func_array(
                    array($arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtml"),
                    array(
                        $arUserField,
                        array(
                            "NAME" => "FIELDS[" . $row['ID'] . "][" . $arUserField["FIELD_NAME"] . "]",
                            "VALUE" => htmlspecialcharsbx($value)
                        )
                    )
                );

                if ($html == '') {
                    $html = '&nbsp;';
                }
                $row[$field] = $html;
            }

            $this->arResult['ITEMS'][] = $row;
        }


        if ($this->queryParams["filter"]['ID'] > 0) {
            $this->arResult['ITEMS'] = $this->addCatalogInfoOneDeal($this->arResult['ITEMS']);
            //$this->arResult['ITEMS'] = $this->getShipmentsByDeal($this->arResult['ITEMS']);
        }
        $this->arResult['ITEMS'] = $this->getShipmentsByDeal($this->arResult['ITEMS']);//инфа об отгрузке нужна сразу


    }

    public function makeUrl()
    {
        if (strlen($this->arParams['DETAIL_URL']) > 0 && count($this->arResult['ITEMS']) > 0) {
            foreach ($this->arResult['ITEMS'] as $key => &$item) {
                $item['~DETAIL_PAGE_URL'] = str_replace(
                    array("#ID#", "#BLOCK_ID#"),
                    array($item["ID"], $this->arResult["BLOCK_ID"]),
                    $this->arParams['DETAIL_URL']
                );
                $item['DETAIL_PAGE_URL'] = htmlspecialcharsbx($item['~DETAIL_PAGE_URL']);
            }
        }
    }

    public function getNavString($nav)
    {
        if (!($nav instanceof \Bitrix\Main\UI\PageNavigation)) {
            return false;
        }

        global $APPLICATION;

        ob_start();
        $APPLICATION->IncludeComponent(
            "bitrix:main.pagenavigation",
            $this->arParams["PAGER_TEMPLATE"],
            array(
                "NAV_OBJECT" => $nav,
                "SEF_MODE" => $this->arParams["PAGER_SEF_MODE"],
            ),
            $this,
            array(
                "HIDE_ICONS" => "Y",
            )
        );
        return ob_get_clean();
    }

    public function addCatalogInfo($itemsArray)
    {
        $addedInfoItems = [];

        foreach ($itemsArray as $i => $item) {
            $item['ITEMS_ARRAY'] = CUtil::JsObjectToPhp($item['UF_ITEMS_JSON'], true);
            //$addedInfoItems[$item['UF_DEAL_CODE']]['FROM_DEAL']=$item['ITEMS_ARRAY'];
            foreach ($item['ITEMS_ARRAY'] as $k => &$good) {
                if ($good['НоменклатураКод'] != '') $filterProducts[] = $good['НоменклатураКод'];
            }
            $arSelect = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_PRODUCT_CODE_1C", "DETAIL_PAGE_URL","PROPERTY_PRODUCT_MULTIPLICITY");
            $arFilter = Array("IBLOCK_ID" => IntVal(PRODUCTS_IBLOCK_ID), 'ACTIVE' => 'Y', '?PROPERTY_PRODUCT_CODE_1C' => implode('|', $filterProducts));
            $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize" => 10), $arSelect);
            $all = [];
            while ($ob = $res->GetNext()) {
                $addedInfoItems[$item['UF_DEAL_CODE']]['FROM_CATALOG'][$good['НомерСтроки']] = $ob;
            }
        }
        return $addedInfoItems;
    }

    /**
     * дополнительная информация по товарам из каталога
     * @param $itemsArrayP
     * @return mixed
     */
    public function addCatalogInfoOneDeal($itemsArrayP)
    {
        $filterProducts = [];
        $itemsArray = $itemsArrayP;
        $addedInfoItems = [];
        $codesNumber = []; //массив соответствия кодов номерам строк в сделке
        $codesIds = []; //массив соответсвия кодов к product_id
        $codesQuantity = [];
        $codesPrices = [];
        foreach ($itemsArray as $i => &$item) {
            $item['ITEMS_ARRAY'] = CUtil::JsObjectToPhp($item['UF_ITEMS_JSON'], true);
            // print_r($item['ITEMS_ARRAY']);
            $item['UF_ID_BITRIX'] = preg_replace('/\D/', '', trim($item['UF_ID_BITRIX']));

            $item['ITEMS_SUMM'] = 0;
            $item['ITEMS_COUNT'] = 0;

            foreach ($item['ITEMS_ARRAY'] as $g => &$good) {
                if ($good['Количество'] == 0) {
                    unset($itemsArray[$i]['ITEMS_ARRAY'][$g]);
                    continue;
                }
                $codesPrices[$good['НоменклатураКод']] = $good['Цена'];
                $codesQuantity[$good['НоменклатураКод']] = $good['Количество'];
                $item['ITEMS_SUMM'] += floatval($good['Сумма']);
                $good['Цена'] = number_format($good['Цена'], 2, ',', ' ');
                $good['Сумма'] = number_format($good['Сумма'], 2, ',', ' ');
                $good['Номенклатура'] = str_replace('\\', '', $good['Номенклатура']);
                $item['ITEMS_COUNT']++;
                if ($good['НоменклатураКод'] != '') $filterProducts[] = $good['НоменклатураКод'];
                // $good['DETAIL_PAGE_URL'] = $arResult['ARR_ITEMS_FROM_CATALOG'][$item['UF_DEAL_CODE']]['FROM_CATALOG'][$good['НомерСтроки']]['DETAIL_PAGE_URL'];
                $codesNumber[$good['НоменклатураКод']] = $good['НомерСтроки'];

            }
            $item['ITEMS_SUMM'] = number_format($item['ITEMS_SUMM'], 2, ',', ' ');
            $item['UF_ITEMS_SUMM'] = $item['ITEMS_SUMM'];
            $item['UF_ITEMS_COUNT'] = $item['ITEMS_COUNT'];

            //добавляем инфу из каталога
            $arSelect = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_PRODUCT_CODE_1C", "DETAIL_PAGE_URL", "PROPERTY_STICKERS","PROPERTY_PRODUCT_MULTIPLICITY","PROPERTY_PRODUCT_WEIGHT","PROPERTY_PRODUCT_VOLUME");
            $arFilter = Array("IBLOCK_ID" => IntVal(PRODUCTS_IBLOCK_ID), 'ACTIVE' => 'Y', '?PROPERTY_PRODUCT_CODE_1C' => implode('|', $filterProducts));
            $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize" => 50), $arSelect);
            $all = [];
            while ($ob = $res->GetNext()) {

                $itemsArray[$i]['FROM_CATALOG'][$codesNumber[$ob['PROPERTY_PRODUCT_CODE_1C_VALUE']]] = $ob;
                $codesIds[$ob['ID']] = $ob['PROPERTY_PRODUCT_CODE_1C_VALUE'];
            }

            //добавляем инфу из заказа
            if ($item['UF_ID_BITRIX'] > 0) {
                $res = CSaleBasket::GetList(array(), array("ORDER_ID" => $item['UF_ID_BITRIX'])); // ID заказа
                while ($arBasketItem = $res->Fetch()) {
                    $codeproduct = $codesIds[$arBasketItem['PRODUCT_ID']];
                    if (isset($codesQuantity[$codeproduct]) && $codesQuantity[$codeproduct] != intval($arBasketItem['QUANTITY'])) {
                        $itemsArray[$i]['FROM_ORDER'][$codesNumber[$codeproduct]]['QUANTITY'] = intval($arBasketItem['QUANTITY']);
                    }
                    if (isset($codesPrices[$codeproduct]) && $codesPrices[$codeproduct] != intval($arBasketItem['PRICE'])) {
                        $itemsArray[$i]['FROM_ORDER'][$codesNumber[$codeproduct]]['PRICE'] = floatval($arBasketItem['PRICE']);
                    }
                }
            }
        }
        return $itemsArray;
    }







    /**
     * сохранение измененной сделки
     * @param $id
     * @param bool $params
     * @param bool $changedGoods
     * @return array|false
     * @throws Main\ArgumentException
     */
    public function saveDeal($id, $params = false, $changedGoods = false)
    {
        $res = [];
        $hlblockId = $params['BLOCK_ID'];


        $dealsClassEntity = helpers\hl_create_class($hlblockId);
        $dealsClassName = $dealsClassEntity->getDataClass();
        $deal = $dealsClassName::getList(array(
            'filter' => ['ID' => $id],
            'select' => ['*'],
        ));
        if ($item = $deal->fetch()) {
            $res = $item;
            $changedFields = [];


            if ($changedGoods !== false && !empty($changedGoods)) { // изменение количества у товаров
                $items_array = CUtil::JsObjectToPhp($item['UF_ITEMS_JSON'], true);
                foreach ($changedGoods as $newStr => $newQuant) {
                    foreach ($items_array as &$was) {
                        if ($newStr == $was['НомерСтроки'] && $newQuant != $was['Количество']) {
                            $was['Количество'] = $newQuant;
                            $was['Сумма'] = $newQuant * $was['Цена'];
                        }
                        $was['Номенклатура'] = stripcslashes($was['Номенклатура']);
                    }
                }

                $changedFields['UF_ITEMS_SUMM'] = 0;
                $changedFields['UF_ITEMS_COUNT'] = 0;
                foreach ($items_array as $it) {
                    $changedFields['UF_ITEMS_SUMM'] += floatval($it['Сумма']);
                    if ($it['Количество'] > 0) $changedFields['UF_ITEMS_COUNT']++;
                }
                if ($changedFields['UF_ITEMS_SUMM'] > 0) $changedFields['UF_ITEMS_SUMM'] = number_format($changedFields['UF_ITEMS_SUMM'], 2, ',', ' ');
                $changedFields['UF_ITEMS_JSON'] = CUtil::PhpToJSObject($items_array, false, true);
                $changedFields['UF_STATUS'] = 'NEED_CHECK'; // для отправки в 1с
            }

            if ($params['CHANGE_STATUS'] != '') $changedFields['UF_STATUS'] = $params['CHANGE_STATUS']; // для отправки в 1с
            $changedFields['UF_SEND_TO_1C'] = 'Y';

            $dealsClassName::update($id, $changedFields);
        }

        return $res;
    }

    /**
     * добавление товаров в сделку
     * @param $id
     * @param bool $params
     * @param bool $addedCode
     * @return array
     * @throws Main\ArgumentException
     */
    public function addToDeal($id, $params = false, $addedCode = false)
    {
        $result = [];
        global $USER;
        //найти сделку
        $hlblockId = $params['BLOCK_ID'];
        $mustUpdated = false;

        $addedGood = [];

        $dealsClassEntity = helpers\hl_create_class($hlblockId);
        $dealsClassName = $dealsClassEntity->getDataClass();
        $deal = $dealsClassName::getList(array(
            'filter' => ['ID' => $id],
            'select' => ['*'],
        ));
        if ($item = $deal->fetch()) {

            //получить из сделки список товаров
            $items_array = CUtil::JsObjectToPhp($item['UF_ITEMS_JSON'], true);

            //поискать добавляемый код в списке товаров
            $goodIsInDeal = false;
            $keyInDeal = false;
            $maxStrNumber = 0;  //найти номер следующей добавляемой строки
            foreach ($items_array as $k => $good) {
                if (!$goodIsInDeal && $good['НоменклатураКод'] == $addedCode) {
                    $goodIsInDeal = true;
                    $keyInDeal = $k;
                }
                if ($good['НомерСтроки'] > $maxStrNumber) $maxStrNumber = $good['НомерСтроки'];
            }
            if ($goodIsInDeal) {
                //если ранее был удален

                if ($keyInDeal === false) return $result = ['success' => true, 'msg' => 'Товар уже есть в сделке'];
                else {
                    $arSelect = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_PRODUCT_CODE_1C", "DETAIL_PAGE_URL", "PROPERTY_PRODUCT_WEIGHT", "PROPERTY_PRODUCT_VOLUME", "PROPERTY_PRODUCT_UNIT_OF_MEASURE", "PROPERTY_PRODUCT_MULTIPLICITY");
                    $arFilter = Array("IBLOCK_ID" => IntVal(PRODUCTS_IBLOCK_ID), 'ACTIVE' => 'Y', 'PROPERTY_PRODUCT_CODE_1C' => $addedCode);
                    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize" => 1), $arSelect);
                    if ($el = $res->GetNext()) {
                        $items_array[$keyInDeal]['Количество'] = ($el['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'] > 0) ? $el['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'] : 1;
                        $items_array[$keyInDeal]['Сумма'] = $items_array[$keyInDeal]['Количество'] * $items_array[$keyInDeal]['Цена'];
                    }
                    $mustUpdated = true;
                }
            } else { //если нет, искать в каталоге
                //найти элемент по коду
                $arSelect = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_PRODUCT_CODE_1C", "DETAIL_PAGE_URL", "PROPERTY_PRODUCT_WEIGHT", "PROPERTY_PRODUCT_VOLUME", "PROPERTY_PRODUCT_UNIT_OF_MEASURE", "PROPERTY_PRODUCT_MULTIPLICITY");
                $arFilter = Array("IBLOCK_ID" => IntVal(PRODUCTS_IBLOCK_ID), 'ACTIVE' => 'Y', 'PROPERTY_PRODUCT_CODE_1C' => $addedCode);
                $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize" => 1), $arSelect);
                if ($el = $res->GetNext()) {
                    //получить его цену,вес,единицу измерения,кратность
                    $arPrice = CCatalogProduct::GetOptimalPrice($el['ID'], 1, $USER->GetUserGroupArray());
                    $addedGood = [];
                    foreach (array_keys($items_array[0]) as $key) {
                        switch ($key) {
                            case 'НомерСтроки':
                                $addedGood[$key] = $maxStrNumber + 1;
                                break;
                            case 'ЕдиницаИзмерения':
                                $addedGood[$key] = ($el['PROPERTY_PRODUCT_UNIT_OF_MEASURE_VALUE'] != '') ? $el['PROPERTY_PRODUCT_UNIT_OF_MEASURE_VALUE'] : 'шт';
                                break;
                            case 'Количество':
                                $addedGood[$key] = ($el['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'] > 0) ? $el['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'] : 1;
                                break;
                            case 'Номенклатура':
                                $addedGood[$key] = $el['NAME'];
                                break;
                            case 'НоменклатураКод':
                                $addedGood[$key] = $addedCode;
                                break;
                            case 'Сумма':
                                $addedGood[$key] = ($el['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'] > 0) ? $arPrice['PRICE']['PRICE'] * $el['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'] : $arPrice['PRICE']['PRICE'];
                                break;
                            case 'Цена':
                                $addedGood[$key] = $arPrice['PRICE']['PRICE'];
                                break;
                            case 'Вес':
                                $addedGood[$key] = $el['PROPERTY_PRODUCT_WEIGHT_VALUE'];
                                break;
                            case 'Объем':
                                $addedGood[$key] = $el['PROPERTY_PRODUCT_VOLUME_VALUE'];
                                break;
                            default:
                                $addedGood[$key] = '';
                                break;
                        }
                    }

                    /*$addedGood = [
                        ['НомерСтроки'] => ($maxStrNumber+1),
                        ['ЕдиницаИзмерения'] => ($el['PROPERTY_PRODUCT_UNIT_OF_MEASURE_VALUE']!='') ? $el['PROPERTY_PRODUCT_UNIT_OF_MEASURE_VALUE'] : 'шт',
                        ['ЕдиницаИзмеренияКод'] => '',
                        ['ЕдиницаИзмеренияМест'] => '',
                        ['ЕдиницаИзмеренияМестКод'] => '',
                        ['Количество'] => ($el['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'] > 0) ? $el['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'] : 1,
                        ['КоличествоМест'] => '',
                        ['Коэффициент'] => '',
                        ['Номенклатура'] => $el['NAME'],
                        ['НоменклатураКод'] => $addedCode,
                        ['ПлановаяСебестоимость'] => '',
                        ['ПроцентСкидкиНаценки'] => '',
                        ['Размещение'] => '',
                        ['РазмещениеКод'] => '',
                        ['СтавкаНДС'] => '20%',
                        ['Сумма'] => ($el['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'] > 0) ? $arPrice['PRICE']['PRICE']*$el['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'] : $arPrice['PRICE']['PRICE'],
                        ['СуммаНДС'] => '',
                        ['ХарактеристикаНоменклатуры'] => '',
                        ['ХарактеристикаНоменклатурыКод'] => '',
                        ['Цена'] => $arPrice['PRICE']['PRICE'],
                        ['ПроцентАвтоматическихСкидок'] => '',
                        ['УсловиеАвтоматическойСкидки'] => '',
                        ['ЗначениеУсловияАвтоматическойСкидки'] => '',
                        ['КлючСтроки'] => '',
                        ['СерияНоменклатуры'] => '',
                        ['СерияНоменклатурыКод'] => '',
                        ['Вес'] => $el['PROPERTY_PRODUCT_WEIGHT_VALUE'],
                        ['Объем'] => $el['PROPERTY_PRODUCT_VOLUME_VALUE'],
                        ['ПлановаяЦенаРеализации'] => '',
                        ['ПлановаяЦенаА1'] =>'',
                        ['ПлановаяЦенаУВ'] => '',
                        ['КолУп1'] => '',
                        ['КолУп2'] => '',
                        ['Коммент'] => '',
                        ['ПроцентТранспорт'] => '',
                        ['СуммаТранспорт'] => '',
                        ['БСБ'] => '',
                        ['ПСБ'] => '',
                        ['КолвоБаллов'] => '',
                        ['ЦенаКонкурента'] => '',
                        ['Отметка'] => false,
                        ['ПроцентБонуса'] => '',
                        ['СуммаБонуса'] => '',
                    ];*/
                    //добавить новый товар в массив товаров
                    if (!empty($addedGood)) {
                        $items_array[] = $addedGood;
                        $mustUpdated = true;
                    }
                }
            }
            if ($mustUpdated) {
                //обновить json товаров в сделке
                $changedFields['UF_ITEMS_JSON'] = CUtil::PhpToJSObject($items_array, true);
                $changedFields['UF_SEND_TO_1C'] = 'Y';
                $dealsClassName::update($id, $changedFields);
                $result = ['success' => true, 'msg' => 'Товар добавлен'];
                CBitrixComponent::clearComponentCache("elfgroup:hlblock.list");
            }

        }


        return $result;


    }

    /**
     * helper for getlist
     * @param $name
     * @return Entity\DataManager|bool
     * @throws Main\ArgumentException
     */
    public function getHLNameEntity($name)
    {
        $getHL = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter' => array('=NAME' => $name)));
        $hlClassName = false;
        if ($row = $getHL->fetch()) {
            $HL = $row["ID"];
            $HLEntity = helpers\hl_create_class($HL);
            $hlClassName = $HLEntity->getDataClass();
        }
        return $hlClassName;
    }


    /**
     * получение идентификатора сделки по коду
     * @param $uniq_code
     * @return bool|mixed
     * @throws Main\ArgumentException
     */
    public function getDeal1cCode($uniq_code)
    {
        $uf_id_1c = false;
        $billClassName = HLListComponent::getHLNameEntity('Deals');
        $uf_id_1c_Res = $billClassName::getList(array(
            'filter' => ['UF_DEAL_CODE' => $uniq_code],
            'select' => ['UF_ID_1C'],
            "order" => array("ID" => "DESC"),
        ))->fetch();
        if ($uf_id_1c_Res['UF_ID_1C']) $uf_id_1c = $uf_id_1c_Res['UF_ID_1C'];
        return $uf_id_1c;
    }

    /**
     * получение отгрузок сделки
     * @param $itemsArrayP
     * @return mixed
     * @throws Main\ArgumentException
     */
    public function getShipmentsByDeal($itemsArrayP)
    {
        $itemsArray = $itemsArrayP;
        foreach ($itemsArray as $i => &$item) {
            $shipments = [];
            $billClassName = HLListComponent::getHLNameEntity('Waybill');
            $shipmentsRes = $billClassName::getList(array(
                'filter' => ['UF_DEAL_CODE' => $item['UF_DEAL_CODE']],
                'select' => ['*'],
                "order" => array("ID" => "DESC"),
            ));
            $allShipmentItems = [];
            while ($shipment = $shipmentsRes->fetch()) {
                $shipmentItems = CUtil::JsObjectToPhp($shipment['UF_ITEMS'], true);

                $shipment['SUMM'] = 0;
                foreach ($shipmentItems as $ish) {
                    $shipment['SUMM'] += floatval($ish['Сумма']);
                    $allShipmentItems[$ish['НоменклатураКод']] += $ish['Количество'];
                }
                $shipment['SUMM'] = number_format($shipment['SUMM'], 2, ',', ' ');
                $shipments[] = $shipment;
            }
            $item['SHIPMENTS'] = $shipments;
            $item['IS_SHIPPED'] = true;
            if (!empty($shipments)) {
                $deal_items = CUtil::JsObjectToPhp($item['UF_ITEMS_JSON'], true);
                foreach ($deal_items as &$good) {
                    if ($good['Количество'] == 0) continue;
                    $itemsArray[$i]['FROM_SHIPMENT'][$good['НомерСтроки']] = (isset($allShipmentItems[$good['НоменклатураКод']]) && $good['Количество'] == $allShipmentItems[$good['НоменклатураКод']]) ? true : false;
                    if ($itemsArray[$i]['FROM_SHIPMENT'][$good['НомерСтроки']] == false) $item['IS_SHIPPED'] = false;
                }
            }

        }

        return $itemsArray;
    }





    /**
     * счет сделки
     * @param $id
     * @param bool $params
     * @return array|false|string
     * @throws Main\ArgumentException
     */
    public function dealToBill($code, $params = false)
    {
        $res = '';
        $hlblockId = $params['BLOCK_ID'];

        $dealsClassEntity = helpers\hl_create_class($hlblockId);
        $dealsClassName = $dealsClassEntity->getDataClass();
        $deal = $dealsClassName::getList(array(
            'filter' => ['UF_DEAL_CODE' => $code],
            'select' => ['*'],
        ));
        if ($item = $deal->fetch()) {
            $resDeal = $item;
            $changedFields = [];
            $buyerName = '';
            $managerName = '';

            $rsUser = CUser::GetList(($by="ID"), ($order="ASC"),  ['UF_CODE_1C' => $item['UF_CLIENT_1C']],["SELECT"=>['LAST_NAME','NAME','SECOND_NAME','UF_ORG_NAME','UF_MANAGER_NAME'] ] );
            if($arUser = $rsUser->Fetch()) {
                $buyerName = ($arUser['UF_ORG_NAME']) ? $arUser['UF_ORG_NAME'] : $arUser['LAST_NAME'].' '.$arUser['NAME'].''.$arUser['SECOND_NAME'];
                $managerName = $arUser['UF_MANAGER_NAME'];
                $arSelectM = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_MANAGER_PHONE_ADN", "PROPERTY_MANAGER_PHONE_ADN","PROPERTY_MANAGER_CODE_1C","PROPERTY_MANAGER_EMAIL");
                $arFilterM = Array("IBLOCK_ID" => IntVal(MANAGERS_IBLOCK_ID), 'ACTIVE' => 'Y', 'PROPERTY_MANAGER_CODE_1C' => $managerName);
                $resMan = CIBlockElement::GetList(Array(), $arFilterM, false, Array(), $arSelectM);
                if ($man = $resMan->GetNext()) {
                    $managerName = $man['NAME'];
                    if ($man['PROPERTY_MANAGER_PHONE_ADN_VALUE']) $managerName .= ' доб. '.$man['PROPERTY_MANAGER_PHONE_ADN_VALUE'];
                    if ($man['PROPERTY_MANAGER_EMAIL_VALUE']) $managerName .= ', '.$man['PROPERTY_MANAGER_EMAIL_VALUE'];
                }
            }



            $billClassName = HLListComponent::getHLNameEntity('BillTpl');
            $tpl = $billClassName::getList(array(
                'filter' => ['UF_AFFILIATE' => $item['UF_AFFILIATE']],
                'select' => ['*'],
                "order" => array("ID" => "DESC"),
            ))->fetch();


            $UF_LOGO_RIGHT = ($tpl['UF_LOGO_RIGHT'] > 0) ? CFile::ResizeImageGet($tpl['UF_LOGO_RIGHT'], array('width' => 300, 'height' => 100), BX_RESIZE_IMAGE_PROPORTIONAL, false) : $tpl['UF_LOGO_RIGHT'];
            $UF_LOGO_COMPANY = ($tpl['UF_LOGO_COMPANY'] > 0) ? CFile::ResizeImageGet($tpl['UF_LOGO_COMPANY'], array('width' => 300, 'height' => 100), BX_RESIZE_IMAGE_PROPORTIONAL, false) : $tpl['UF_LOGO_COMPANY'];
            $UF_PICTURE_LK = ($tpl['UF_PICTURE_LK'] > 0) ? CFile::ResizeImageGet($tpl['UF_PICTURE_LK'], array('width' => 600, 'height' => 300), BX_RESIZE_IMAGE_PROPORTIONAL, false) : $tpl['UF_PICTURE_LK'];
            $UF_STAMP = ($tpl['UF_STAMP'] > 0) ? CFile::ResizeImageGet($tpl['UF_STAMP'], array('width' => 120, 'height' => 120), BX_RESIZE_IMAGE_PROPORTIONAL, false) : $tpl['UF_STAMP'];
            $UF_BOTTOM_BANNER = ($tpl['UF_BOTTOM_BANNER'] > 0) ? CFile::ResizeImageGet($tpl['UF_BOTTOM_BANNER'], array('width' => 200, 'height' => 200), BX_RESIZE_IMAGE_PROPORTIONAL, false) : $tpl['UF_BOTTOM_BANNER'];

            $header = ' <table border="0">
                    <tr><td width="50%">' . nl2br($tpl['UF_CONTACTS']) . '</td><td><img src="' . $UF_LOGO_RIGHT['src'] . '" alt=""></td></tr>
                    <tr><td width="50%"><br><h2 style="text-align: center">' . nl2br($tpl['UF_PHONE']) . '</h2></td><td><img src="' . $UF_LOGO_COMPANY['src'] . '" alt=""></td></tr>
                    <tr><td width="100%" colspan="2"><br><img src="' . $UF_PICTURE_LK['src'] . '" alt=""><br></td></tr>
                    <tr><td width="100%" colspan="2"><p style="font-weight:bold;">' . nl2br($tpl['UF_TEXT_TOP']) . '</p>
                    </td></tr>
                    </table>
            ';

            $items_array = CUtil::JsObjectToPhp($item['UF_ITEMS_JSON'], true);

            $res = $header . '<h1 style="text-align:center;font-style: italic;">Счет №' . $item['UF_ID_1C'] . ' от ' . explode(' ', $item['UF_DATE_CHANGE'])[0] . '</h1>
                    <br>
                    <table border="0" >
                    <tr><td width="20%">Поставщик:</td><td width="80%">' . nl2br($tpl['UF_POST']) . '</td></tr>
                    <tr><td width="20%">Покупатель:</td><td width="80%">'.$buyerName.'</td></tr>
                    <tr><td width="20%"></td><td width="80%">'.$item['UF_CONTRACT'].'</td></tr>
                    </table>
                    <br><br>
                    
                    <table border="1" width="100%" cellpadding="3">
                    <tr>
                    <td width="4%" align="center" valign="middle" style="vertical-align: middle">№ п/п</td>
                    <td width="40%" align="center" valign="middle">Наименование</td>
                    <td width="5%" align="center" valign="middle">Единица</td>
                    <td width="5%" align="center" valign="middle">Кол-во</td>
                    <td width="12%" align="center" valign="middle">Цена</td>
                    <td width="12%" align="center" valign="middle">Сумма,руб</td>
                    <td width="8%" align="center" valign="middle">Вес,кг</td>
                    <td width="15%" align="center" valign="middle">Артикул</td>
                    </tr>';
            $deal_summ = 0;
            $deal_weight = 0;

            foreach ($items_array as $k => $good) {
                if ($good['НоменклатураКод'] != '') $filterProducts[] = $good['НоменклатураКод'];
            }
            $arSelect = Array("ID", "NAME", "IBLOCK_ID", "PROPERTY_PRODUCT_CODE_1C", "DETAIL_PAGE_URL");
            $arFilter = Array("IBLOCK_ID" => IntVal(PRODUCTS_IBLOCK_ID), 'ACTIVE' => 'Y', '?PROPERTY_PRODUCT_CODE_1C' => implode('|', $filterProducts));
            $resProd = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            $prodInfo = [];
            while ($ob = $resProd->GetNext()) {
                $prodInfo[$ob['PROPERTY_PRODUCT_CODE_1C_VALUE']] = 'https://'.$_SERVER['HTTP_HOST'].$ob['DETAIL_PAGE_URL'];
            }

            foreach ($items_array as $item) {

                if ($item['Количество'] > 0) {
                    $res .= '<tr>
                    <td width="4%" align="center" valign="middle" style="vertical-align: middle">' . $item['НомерСтроки'] . '</td>
                    <td width="40%" align="left" valign="middle"><a href="'.$prodInfo[$item['НоменклатураКод']].'">' . stripcslashes($item['Номенклатура']) . '</a></td>
                    <td width="5%" align="center" valign="middle">' . $item['ЕдиницаИзмерения'] . '</td>
                    <td width="5%" align="center" valign="middle">' . $item['Количество'] . '</td>
                    <td width="12%" align="right" valign="middle">' . $item['Цена'] . '</td>
                    <td width="12%" align="right" valign="middle">' . $item['Сумма'] . '</td>
                    <td width="8%" align="center" valign="middle">' . $item['Вес'] . '</td>
                    <td width="15%" align="center" valign="middle">' . $item['НоменклатураКод'] . '</td>
                    </tr>
                ';
                    $deal_summ += $item['Сумма'];
                    $deal_weight += $item['Вес'];
                }

            }
            $res .= '
            <table border="0" width="100%" cellpadding="3">
                    <tr>
                    <td width="4%" align="center" valign="middle"></td>
                    <td width="40%" align="center" valign="middle"></td>
                    <td width="5%" align="center" valign="middle"></td>
                    <td width="5%" align="center" valign="middle"></td>
                    <td width="12%" align="right" valign="middle">Итого:</td>
                    <td width="12%" align="right" valign="middle">'.number_format($deal_summ, 2, '.', ' ').'</td>
                    <td width="8%" align="center" valign="middle"></td>
                    <td width="15%" align="center" valign="middle"></td>
                    </tr></table>';

            $nds = ($resDeal['UF_VAT']=='Да') ? number_format($deal_summ*0.2, 2, '.', ' ') : 'Без НДС';
            $res .= '
            <table border="0" width="100%" cellpadding="3">
                    <tr>
                    <td width="4%" align="center" valign="middle"></td>
                    <td width="40%" align="center" valign="middle"></td>
                    <td width="5%" align="center" valign="middle"></td>
                    <td width="5%" align="center" valign="middle"></td>
                    <td width="12%" align="right" valign="middle">в т.ч. НДС:</td>
                    <td width="12%" align="right" valign="middle">'.$nds.'</td>
                    <td width="8%" align="center" valign="middle"></td>
                    <td width="15%" align="center" valign="middle"></td>
                    </tr></table>';

            CModule::IncludeModule("sale");
            // <p>Всего к оплате: '.Number2Word_Rus($deal_summ).'</p>
            $res .= '</table>
                     <br><br><br><br>
                     <p>Всего к оплате: ' . Number2Word_Rus($deal_summ) . '</p>
                     <table border="1" width="200px" cellpadding="3"><tr><td>Вec изделий: <b style="font-style:700;">' . $deal_weight . ' кг</b></td></tr></table>
                     <table border="0">
                    <tr><td width="40%"><br><p>Руководитель:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Кобылин И.В.</p></td>
                        <td width="35%" align="left" valign="top" rowspan="5"><img src="' . $UF_STAMP['src'] . '" alt=""></td>
                        <td width="30%" align="right" valign="middle" rowspan="5"><br><br><br><br><br><br><br><br><br><br><img src="' . $UF_BOTTOM_BANNER['src'] . '" alt=""></td></tr>
                    <tr><td width="35%"><br><p>Главный бухгалтер:&nbsp;&nbsp;&nbsp;Цурикова Н.В.</p></td></tr>
                    </table>
            ';
            $res .= '<table border="0">
                    <tr><td><br><p>М.П.</p></td></tr>
                    <tr><td><br><p>Контактное лицо:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ______________________________________________ </p></td></tr>
                    <tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    '.$managerName.'</td></tr>
                    </table>
            ';
        }

        return $res;
    }


    /**
     * история сделок
     * @return Entity\DataManager
     * @throws Main\ArgumentException
     */
    public function getHistoryDealEntity()
    {
        $getHistory = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter' => array('=NAME' => "DealsHistory")));
        if ($row = $getHistory->fetch()) {
            $historyHL = $row["ID"];
            $historyClassEntity = helpers\hl_create_class($historyHL);
            $historyClassName = $historyClassEntity->getDataClass();
        }
        return $historyClassName;
    }


    /**
     * helper arraydiff
     * @param $old
     * @param $new
     * @return array
     */
    public function calculateDifference($old, $new)
    {
        $difference = array();
        $oldByCode = [];
        $newByCode = [];
        foreach ($old as $good) {
            if (!in_array($good['НоменклатураКод'], $oldByCode)) $oldByCode[] = $good['НоменклатураКод'];
        }
        foreach ($new as $good) {
            if (!in_array($good['НоменклатураКод'], $newByCode)) $newByCode[] = $good['НоменклатураКод'];
        }
        $difference = array_diff($newByCode, $oldByCode);
        return $difference;
    }

    /**
     * helper arrayview
     * @param $array
     * @return array
     */
    public function setArrayItemsByCode($array)
    {
        $returnItems = [];
        foreach ($array as $item){
            $returnItems[$item['НоменклатураКод']] = $item;
        }
        return $returnItems;
    }

    /**
     * запись в историю
     * @param $action
     * @param bool $id_deal
     * @param bool $dealsClassName
     * @param bool $newFields
     * @return array
     * @throws Main\ArgumentException
     */
    public function setHistoryDeal($action, $id_deal = false, $dealsClassName = false, $newFields = false)
    {
        $msg = [];

        switch ($action) {
            case 'ADD':
                $msg[] = Loc::getMessage('HL_HISTORY_ADD');
                $msg[] = Loc::getMessage('HL_HISTORY_STATUS_CHECKED');
                $msg[] = Loc::getMessage('HL_HISTORY_BILL');
                break;

            case 'UPDATE':

                $deal = $dealsClassName::getList(array(
                    'filter' => ['ID' => $id_deal],
                    'select' => ['*'],
                ));
                if ($item = $deal->fetch()) {

                    $oldItemsPre = CUtil::JsObjectToPhp($item['UF_ITEMS_JSON'], true);
                    $newItemsPre = CUtil::JsObjectToPhp($newFields['UF_ITEMS_JSON'], true);

                    $oldItems = HLListComponent::setArrayItemsByCode($oldItemsPre);
                    $newItems = HLListComponent::setArrayItemsByCode($newItemsPre);

                    $changeUser = ($newFields['UF_STATUS'] == 'NEED_CHECK') ? '<b class="orange">Вы</b>' : '<b class="blue">ГК Эльф</b>';

                    $botstatus = ($newFields['UF_STATUS'] == 'AGREED' && $newFields['UF_SEND_TO_1C'] != 'Y') ? '_BOT' : '';


                    if (count($oldItems) < count($newItems)) {
                        //если добавлены товары
                        $changeUser = ($newFields['UF_SEND_TO_1C'] == 'Y') ? '<b class="orange">Вы</b>' : '<b class="blue">ГК Эльф</b>';
                        $different = HLListComponent::calculateDifference($oldItems, $newItems);
                        foreach ($newItems as $good) {
                            if (in_array($good['НоменклатураКод'], $different)) {
                                $msg[] = $changeUser . ' ' . Loc::getMessage('HL_HISTORY_GOOD_ADD') . ' ' . $good['НоменклатураКод'] . ' - ' . $good['Номенклатура'];
                            }
                        }
                    } elseif ($newFields['UF_STATUS'] == 'NEED_CHECK') { //если клиент сам редактировал сделку удаление по правилу - количество 0

                        foreach ($oldItems as $k => $good) {
                            //если изменилось количество
                            if ($good['Количество'] != $newItems[$k]['Количество']) {
                                $changeQ = $newItems[$k]['Количество'] - $good['Количество'];
                                if (intval($newItems[$k]['Количество']) > 0) $msg[] = $changeUser . ' ' . Loc::getMessage('HL_HISTORY_GOOD_QUANTITY') . ' ' . $good['НоменклатураКод'] . ' - ' . $good['Номенклатура'] . ' ' . my_gmp_sign($changeQ) . $changeQ . ' ' . $good['ЕдиницаИзмерения'] . ' ' . '[' . $newItems[$k]['Количество'] . ' ' . $good['ЕдиницаИзмерения'] . ']';
                                else {
                                    $msg[] = $changeUser . ' ' . Loc::getMessage('HL_HISTORY_GOOD_DELETE') . ' ' . $good['НоменклатураКод'] . ' - ' . $good['Номенклатура'];
                                }
                            }
                            /*//если изменилась цена - тогда здесь  не нужно
                            if ($good['Цена'] != $newItems[$k]['Цена']) {
                                $changeP = $newItems[$k]['Цена'] - $good['Цена'];
                                $msg[] = $changeUser . ' ' . Loc::getMessage('HL_HISTORY_GOOD_PRICE') . ' ' . $good['НоменклатураКод'] . ' - ' . $good['Номенклатура'] . ' ' . my_gmp_sign($changeP) . $changeP . '.руб';
                            }*/
                        }
                    } elseif ($newFields['UF_STATUS'] == 'CHECKED') { //если сделка пришла из 1с удаление - удаление самих товаров
                        if (count($newItems) < count($oldItems)) {
                            $different = HLListComponent::calculateDifference($newItems, $oldItems);
                            foreach ($oldItems as $good) {
                                if (in_array($good['НоменклатураКод'], $different)) {
                                    $msg[] = $changeUser . ' ' . Loc::getMessage('HL_HISTORY_GOOD_DELETE') . ' ' . $good['НоменклатураКод'] . ' - ' . $good['Номенклатура'];
                                }
                            }
                        }

                        foreach ($oldItems as $k => $good) {

                            if (!isset( $newItems[$k])) continue;

                            //если изменилось количество
                            if ($good['Количество'] != $newItems[$k]['Количество']) {
                                $changeQ = $newItems[$k]['Количество'] - $good['Количество'];
                                if (intval($newItems[$k]['Количество']) > 0) $msg[] = $changeUser . ' ' . Loc::getMessage('HL_HISTORY_GOOD_QUANTITY') . ' ' . $good['НоменклатураКод'] . ' - ' . $good['Номенклатура'] . ' ' . my_gmp_sign($changeQ) . $changeQ . ' ' . $good['ЕдиницаИзмерения'] . ' ' . '[' . $newItems[$k]['Количество'] . ' ' . $good['ЕдиницаИзмерения'] . ']';
                                /*else {
                                    $msg[] = $changeUser . ' ' . Loc::getMessage('HL_HISTORY_GOOD_DELETE') . ' ' . $good['НоменклатураКод'] . ' - ' . $good['Номенклатура'];
                                }*/
                            }
                            //если изменилась цена
                            if ($good['Цена'] != $newItems[$k]['Цена']) {
                                $changeP = $newItems[$k]['Цена'] - $good['Цена'];
                                $msg[] = $changeUser . ' ' . Loc::getMessage('HL_HISTORY_GOOD_PRICE') . ' ' . $good['НоменклатураКод'] . ' - ' . $good['Номенклатура'] . ' ' . my_gmp_sign($changeP) . $changeP . '.руб';
                            }
                        }
                    }


                    if ($item['UF_STATUS'] != $newFields['UF_STATUS'] && Loc::getMessage('HL_HISTORY_STATUS_' . $newFields['UF_STATUS']) != '') {
                        $msg[] = Loc::getMessage('HL_HISTORY_STATUS_' . $newFields['UF_STATUS'] . $botstatus);
                        if ($newFields['UF_STATUS'] == 'CHECKED') $msg[] = Loc::getMessage('HL_HISTORY_BILL');
                    }


                }

                break;
        }

        //добавить все $msg в историю (ID|UF_DEAL|UF_DATE|UF_TEXT)
        $historyClassName = HLListComponent::getHistoryDealEntity();

        $filialTimeDiff = self::getFilialDiffTime();
        $dateupd = (isset($filialTimeDiff[$newFields['UF_AFFILIATE']])) ? strtotime($filialTimeDiff[$newFields['UF_AFFILIATE']]) : time();

        foreach ($msg as $text) {
            $result = $historyClassName::add([
                'UF_DEAL' => $id_deal,
                'UF_DATE' => $dateupd,
                'UF_TEXT' => $text,
            ]);
        }


        return $msg;
    }

    /**
     * получение истории сделки
     * @param $id_deal
     * @return string
     * @throws Main\ArgumentException
     */
    public function getHistoryDeal($id_deal)
    {
        $historyClassName = HLListComponent::getHistoryDealEntity();
        $history = $historyClassName::getList(array(
            'filter' => ['UF_DEAL' => $id_deal],
            'select' => ['*'],
            "order" => array("ID" => "DESC"),
        ));
        $result = '';
        while ($item = $history->fetch()) {
            $result .= '<b>' . date('d.m.Y H:i', $item['UF_DATE']) . '</b> ' . str_replace('\\', '', $item{'UF_TEXT'});
            $result .= '<br>';
        }
        return $result;

    }




    public function checkShipped($idw){
        $isShipped = false;
        $trClass = self::getHLNameEntity('Transport');
        $rstr = $trClass::getList(array(
            "select" => ["*"],
            "order" => ["ID" => "ASC"],
            "filter" => ["UF_W_CODE_1C" => $idw],
        ));
        if ($artr = $rstr->Fetch()) {
           if ($artr['UF_SHIPPED'] == 'Да') $isShipped = $artr;
        }
        return $isShipped;
    }


    /**
     * получение сделок из 1с
     * @param string $log
     * @return string
     * @throws Main\ArgumentException
     */
    public function goImportDealFrom1c($log = 'logdeals.txt')
    {
        $returnArray = [];
        $filialTimeDiff = self::getFilialDiffTime();


        $dealsClass = self::getHLNameEntity('Deals');
        $rsDeals = $dealsClass::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
        ));
        $oldDeals = [];
        while ($arDeals = $rsDeals->Fetch()) {
            $oldDeals[$arDeals['UF_DEAL_CODE']] = $arDeals['ID'];
        }

        //$json = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/data/sync/import/deals/test_deals3.json");
        $json = file_get_contents('php://input');
        $arrFromPost = json_decode($json, true);

        Diag\Debug::writeToFile(date('d.m.Y H:i:s'), 'Получение сделок из 1с', $log);
        Diag\Debug::writeToFile($arrFromPost, 'deal', $log);

        foreach ($arrFromPost as $deal_id => $deal) {
            $dateupd = (isset($filialTimeDiff[$deal['Подразделение']])) ? date('d.m.Y H:i:s', strtotime($filialTimeDiff[$deal['Подразделение']])) : date('d.m.Y H:i:s');

            if (!isset($deal['Товары']) || !isset($deal['Номер']) || !isset($deal['Контрагент'])) continue;

            if ($deal['СайтСтатусЗаказа'] == 'RECEIVE') {
                $currentDeal = array(
                    'UF_SEND_TO_1C' => 'N',
                );
            } else {

                $item = [];
                $item['ITEMS_SUMM'] = 0;
                $item['ITEMS_COUNT'] = 0;
                $item['ITEMS_JSON'] = [];
                foreach ($deal['Товары'] as $g => &$good) {
                    $item['ITEMS_JSON'][] = [
                        'НомерСтроки' => $good['НомерСтроки'],
                        'ЕдиницаИзмерения' => $good['ЕдиницаИзмерения'],
                        'Количество' => $good['Количество'],
                        'Номенклатура' => stripcslashes($good['Номенклатура']),
                        'НоменклатураКод' => $good['НоменклатураКод'],
                        'Сумма' => $good['Сумма'],
                        'Цена' => $good['ЦенаИтоговая'],
                        'Вес' => $good['Вес'],
                        'Объем' => $good['Объем'],
                        'Коммент' => $good['Коммент'],
                    ];

                    if (intval($good['Количество']) > 0) {
                        $item['ITEMS_SUMM'] += floatval($good['Сумма']);
                        $item['ITEMS_COUNT']++;
                    }
                }
                $item['ITEMS_SUMM'] = number_format($item['ITEMS_SUMM'], 2, ',', ' ');

                $currentDeal = array(
                    'UF_DEAL_CODE' => $deal_id,
                    'UF_ID_1C' => $deal['Номер'],
                    'UF_VAT' => $deal['УчитыватьНДС'],
                    'UF_LOCK' => $deal['СайтСделкаЗаблокированаМенеджером'],
                    'UF_ID_BITRIX' => $deal['ДополнениеКАдресуДоставки'],
                    'UF_CLIENT_1C' => $deal['Контрагент'],
                    'UF_STATUS' => $deal['СайтСтатусЗаказа'],
                    'UF_DATE' => $deal['ДатаСоздания'],
                    'UF_DATE_CHANGE' => $dateupd,
                    'UF_AFFILIATE' => $deal['Подразделение'],
                    //'UF_ITEMS_JSON' => json_encode($deal['Товары']),
                    'UF_ITEMS_JSON' => CUtil::PhpToJSObject($item['ITEMS_JSON']),
                    'UF_ITEMS_SUMM' => $item['ITEMS_SUMM'],
                    'UF_ITEMS_COUNT' => $item['ITEMS_COUNT'],
                    'UF_DATE_TIMESTAMP' => strtotime(trim($deal['ДатаСоздания'])),
                    'UF_CONTRACT' => $item['ДоговорКонтрагента']
                );
            }


            if (isset($oldDeals[$deal_id])) {
                // update deal
                $result = $dealsClass::update($oldDeals[$deal_id], $currentDeal);
                $returnArray[$deal_id] = 'Обновлено на сайте ' . $currentDeal['UF_DATE_CHANGE'];

            } else {
                // add new deal
                $result = $dealsClass::add($currentDeal);
                $oldDeals[$deal_id] = $result->getId();
                $returnArray[$deal_id] = 'Добавлено на сайт ' . $currentDeal['UF_DATE_CHANGE'];
            }
        }
        return CUtil::PhpToJSObject($returnArray);
    }

    /**
     * отправка измененных сделок
     * @param string $log
     * @return string|string[]
     * @throws Main\ArgumentException
     */
    public function goExportDealto1c($log = 'logdeals.txt')
    {
        $returnArray = [];

        $dealsClass = self::getHLNameEntity('Deals');
        $rsDeals = $dealsClass::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
            "filter" => $arrFilter = array("UF_SEND_TO_1C" => 'Y')
        ));
        $changedDeals = [];
        while ($arDeals = $rsDeals->Fetch()) {
            $sendingArr = $arDeals;
            unset($sendingArr['ID']);
            unset($sendingArr['UF_SEND_TO_1C']);
            unset($sendingArr['UF_DATE']);
            unset($sendingArr['UF_DATE_CHANGE']);
            $sendingArr['UF_ITEMS_JSON'] = CUtil::JsObjectToPhp($arDeals['UF_ITEMS_JSON'],true);
            $changedDeals[$arDeals['UF_DEAL_CODE']] = $sendingArr;
        }

        \Bitrix\Main\Diag\Debug::writeToFile(date('d.m.Y H:i:s'), 'Отправка измененных сделок в 1с', $log);
        \Bitrix\Main\Diag\Debug::writeToFile($changedDeals, 'deal', $log);

        // 1cnik cant decode return json_encode($changedDeals);
        return str_replace("'",'"', stripslashes(CUtil::PhpToJSObject($changedDeals)));

    }

    /**
     * получение отгрузок
     * @param string $log
     * @return string|string[]
     * @throws Main\ArgumentException
     */
    public function goImportShipmentFrom1c($log = 'logdeals.txt')
    {
        $returnArray = [];

        $wbClass = self::getHLNameEntity('WayBill');
        $rsWB = $wbClass::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
        ));
        $oldWB = [];
        while ($arWB = $rsWB->Fetch()) {
            $oldWB[$arWB['UF_W_CODE_1C']] = $arWB['ID'];
        }

        //$json = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/data/sync/import/deals/test_waybill3.json");
        $json = file_get_contents('php://input');
        $arrFromPost = json_decode($json,true);

        \Bitrix\Main\Diag\Debug::writeToFile(date('d.m.Y H:i:s'), 'Получение отгрузок из 1с', $log);
        \Bitrix\Main\Diag\Debug::writeToFile($arrFromPost, 'shipment', $log);

        foreach ($arrFromPost as $wb_id=>$wb) {

            if (!isset($wb['Товары']) || !isset($wb['Номер'])) continue;

            $currentWB = array(
                'UF_W_CODE_1C' => $wb_id,
                'UF_ID_1C' => $wb['Номер'],
                'UF_WB_DATE' => $wb['ДатаСоздания'],
                'UF_TIMESTAMP' => strtotime($wb['ДатаОтгрузки']),
                'UF_AFFILIATE' => $wb['Подразделение'],
                'UF_WB_STATUS' => $wb['СтатусСборки'],
                'UF_CLIENT_1C' => $wb['Контрагент'],
                'UF_DELIVERY' => $wb['Доставка'],
                'UF_SELF' => $wb['Самовывоз'],
                'UF_ADDRESS' => $wb['АдресДоставки'],
                'UF_WB_SHIPMENT' => $wb['ДатаОтгрузки'],
            );

            $items = [];

            foreach ($wb['Товары'] as $good){
                $currentWB['UF_DEAL_CODE'] = $good['УникальныйИдентификатор'];
                $items[] = [
                    'НомерСтроки' => $good['НомерСтроки'],
                    'ЕдиницаИзмерения' => $good['ЕдиницаИзмерения'],
                    'Количество' => $good['Количество'],
                    'Номенклатура' => $good['Номенклатура'],
                    'НоменклатураКод' => $good['НоменклатураКод'],
                    'Сумма' => $good['Сумма'],
                    'Цена' => $good['ЦенаИтоговая'],
                    'Вес' => $good['Вес'],
                    'Объем' => $good['Объем'],
                ];
            }
            $currentWB['UF_ITEMS'] = CUtil::PhpToJSObject($items);

            if (isset($oldWB[$wb_id])) {
                // update deal
                $result = $wbClass::update($oldWB[$wb_id], $currentWB);
                $returnArray[$wb_id] = 'Обновлено на сайте '.date('d.m.Y h:i:s');

            } else {
                // add new
                $result = $wbClass::add($currentWB);
                $oldWB[$wb_id] = $result->getId();
                $returnArray[$wb_id] = 'Добавлено на сайт '.date('d.m.Y h:i:s');
                $dealClassName = self::getHLNameEntity('Deals');
                $uf_deal_Res = $dealClassName::getList(array(
                    'filter' => ['UF_DEAL_CODE' => $currentWB['UF_DEAL_CODE']],
                    'select' => ['ID'],
                    "order" => array("ID"=>"DESC"),
                ));
                if ($idDeal = $uf_deal_Res->fetch()) { // если появилась отгрузка - клиент на всё согласный
                    $dealClassName::update($idDeal, ['UF_STATUS'=>'AGREED']);
                }
            }
        }
        // 1cnik cant decode return json_encode($returnArray);
        return str_replace("'",'"', stripslashes(CUtil::PhpToJSObject($returnArray)));
    }

    /**
     * получение транспортных заявок
     * @param string $log
     * @return string|string[]
     * @throws Main\ArgumentException
     */
    public function goImportTransportFrom1c($log = 'logdeals.txt')
    {
        $returnArray = [];


        $trClass = self::getHLNameEntity('Transport');
        $rstr = $trClass::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "ASC"),
        ));
        $oldtr = [];
        while ($artr = $rstr->Fetch()) {
            $oldtr[$artr['UF_DOC']] = $artr['ID'];
        }

        //$json = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/data/sync/import/deals/test_transport.json");
        $json = file_get_contents('php://input');

        $arrFromPost = json_decode($json,true);

        $yes = ['да', 'Да','ДА'];


        \Bitrix\Main\Diag\Debug::writeToFile(date('d.m.Y H:i:s'), 'Получение транспорта из 1с', $log);
        \Bitrix\Main\Diag\Debug::writeToFile($arrFromPost, 'transport', $log);

        foreach ($arrFromPost as $tr_id=>$tr) {

            if (!isset($tr['ДокументыРеализации']) ) continue; //|| !in_array($tr['Отгружен'],$yes)

            $currenttr = array(
                'UF_TR_CODE_1C' => $tr_id,
                'UF_ID_1C' => $tr['Код'],
                'UF_SHIPMENTDATE' => $tr['ДатаОтгрузки'],
                'UF_SHIPPED' => $tr['Отгружен'],
            );

            foreach ($tr['ДокументыРеализации'] as $good){
                $currenttr['UF_DEAL_CODE'] = $good['УникальныйИдентификатор'];
                $currenttr['UF_DOC'] = $good['ДокументНомер'];
                $currenttr['UF_W_CODE_1C'] = $good['УникальныйИдентификатор'];



                if (isset($oldtr[$currenttr['UF_DOC']])) {
                    // update
                    $result = $trClass::update($oldtr[$currenttr['UF_DOC']], $currenttr);
                    $returnArray[$currenttr['UF_W_CODE_1C']] = 'Обновлено на сайте '.date('d.m.Y h:i:s');

                } else {
                    // add new
                    $result = $trClass::add($currenttr);
                    $oldtr[$currenttr['UF_DOC']] = $result->getId();
                    $returnArray[$currenttr['UF_W_CODE_1C']] = 'Добавлено на сайт '.date('d.m.Y h:i:s');
                }
            }


        }
        // 1cnik cant decode return json_encode($returnArray);
        return str_replace("'",'"', stripslashes(CUtil::PhpToJSObject($returnArray)));
    }

    public function getFilialDiffTime()
    {
        $diffTime = [];
        $timeShiftR = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => AFFILIATES_IBLOCK_ID,
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'IBLOCK_ID',
                'PROPERTY_TIME_DIFFERENCE',
            ]
        );
        while ($timeShiftAr = $timeShiftR->GetNext()){
            if ($timeShiftAr['PROPERTY_TIME_DIFFERENCE_VALUE']!='') $diffTime[$timeShiftAr['NAME']] = $timeShiftAr['PROPERTY_TIME_DIFFERENCE_VALUE'].' hour';
        }
        //$timeShift && $this->timeShift = $timeShift * DateTimeHelper::HOUR;
        return $diffTime;
    }

    public function executeComponent()
    {
        try {
            $this->checkModules();
            $this->initPageNavigation();
            if (!$this->extractDataFromCache()) {
                $this->initResult();
                $this->prepareQuery();
                $this->makeQuery();
                $this->makeUrl();
                $this->includeComponentTemplate();
                $this->putDataToCache();
            }
        } catch (Exception $e) {
            $this->abortDataCache();
            ShowError($e->getMessage());
        }
        return true;
    }
}
?>