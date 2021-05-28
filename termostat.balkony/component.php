<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc as Loc;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Page\Asset;


$obCache = new CPHPCache;
$life_time = 3600;
$cache_id = 'termostatb';

if ($obCache->InitCache($life_time, $cache_id)) {
    $vars = $obCache->GetVars();
    $cur_result = $vars["termostat_b"];
} else {
    \Bitrix\Main\Loader::includeModule('iblock');

    $iblockId = 5;

    include($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/element_property_class.php");
    $result_with_props = \Bitrix\Iblock\ElementTable::getList(array(
        'filter' => array(
            '=IBLOCK_ID' => $iblockId,

        ),
        'select' => array('ID', 'NAME', 'PREVIEW_TEXT', 'SORT',
            'PROPERTY_CODE' => 'PROPERTY_PROP.CODE', 'PROPERTY_VALUE' => 'PROPERTY.VALUE', 'PROPERTY_TYPE' => 'PROPERTY_PROP.PROPERTY_TYPE',
        ),
        'runtime' => array(
            new Bitrix\Main\Entity\ReferenceField(
                'PROPERTY',
                'Ra\Iblock\ElementProperyTable',
                array(
                    '=this.ID' => 'ref.IBLOCK_ELEMENT_ID'
                ),
                array('join_type' => 'LEFT')
            ),
            new Bitrix\Main\Entity\ReferenceField(
                'PROPERTY_PROP',
                '\Bitrix\Iblock\PropertyTable',
                array(
                    '=this.PROPERTY.IBLOCK_PROPERTY_ID' => 'ref.ID'
                ),
                array('join_type' => 'LEFT')
            ),
        ),
        'order' => array('SORT' => 'ASC', 'ID' => 'ASC'),
        'group' => array('ID')
    ))->fetchAll();

    $result = array();
    $prev_id = 0;
    $arWaterMark = Array(array("name" => "watermark", "position" => "bottomright", "size" => "small", 'type' => 'image', "file" => $_SERVER['DOCUMENT_ROOT'] . "/local/templates/balkorus/assets/img/watermark.png"));

    foreach ($result_with_props as &$el) {
        $el['PROPERTY_CODE'] = ($el['PROPERTY_CODE'] == 'TNAME') ? 'tName' : strtolower($el['PROPERTY_CODE']);
        if ($prev_id != $el['ID']) {
            $result[$el['ID']] = array('name' => $el['NAME'], 'desc' => $el['PREVIEW_TEXT']);
            $result[$el['ID']][$el['PROPERTY_CODE']] = $el['PROPERTY_VALUE'];
            $prev_id = $el['ID'];
        } else {
            if ($el['PROPERTY_CODE'] == 'gallery') {
                $arImage = CFile::GetFileArray($el['PROPERTY_VALUE']);
                $arImgName = explode('|', $arImage['DESCRIPTION']);
                $arResizeImage = CFile::ResizeImageGet(
                    $el['PROPERTY_VALUE'],
                    array("width" => 1140, "height" => 805),
                    BX_RESIZE_IMAGE_EXACT,
                    true,
                    $arWaterMark
                );
                $result[$el['ID']][$el['PROPERTY_CODE']][] = array('img' => $arResizeImage['src'], 'name' => $arImgName);
            } else {
                $result[$el['ID']][$el['PROPERTY_CODE']] = $el['PROPERTY_VALUE'];
                if ($el['PROPERTY_CODE'] == 'progress') {
                    $result[$el['ID']][$el['PROPERTY_CODE']] = (int)$el['PROPERTY_VALUE'];
                }
                if ($el['PROPERTY_CODE'] == 'sale' || $el['PROPERTY_CODE'] == 'popular') {
                    $result[$el['ID']][$el['PROPERTY_CODE']] = ($el['PROPERTY_VALUE'] == 2 || $el['PROPERTY_VALUE'] == 3) ? true : false;
                }

            }

        }

    }
    $json_str = '';
    if (!empty($result)) {
        $json_str = json_encode(array_values($result));

    }
    $cur_result = $json_str;
    if ($obCache->StartDataCache()):
        $obCache->EndDataCache(array(
            "termostat_b" => $cur_result
        ));
    endif;
}

Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/assets/js/tour/css/app.css');

$arResult = $cur_result;

$this->IncludeComponentTemplate();
