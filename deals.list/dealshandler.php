<?
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);


if (!$_POST['ACTION']) {
    return 0;
}

$sAction = $_POST['ACTION'];

// Служебная часть пролога
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Elfsight\utils\sync\helpers as sync_helpers;

CModule::IncludeModule("highloadblock");

// Генерирование сущности и класса для работы с Highload - блоком
$dealsClassEntity = helpers\hl_create_class(27);
$dealsClassName = $dealsClassEntity->getDataClass();

switch ($sAction) {

    // Если в качастве действия выбран просмотр товаров
    case 'goods':
        CBitrixComponent::includeComponentClass("elfgroup:hlblock.list");
        $deal_id = $_POST['DEAL_ID'];
        $arComponentParams = json_decode($_POST['COMPONENT_PARAMS'], true);
        //CBitrixComponent::clearComponentCache("elfgroup:hlblock.list");
        global $dealsFilter;
        $dealsFilter['ID'] = trim($deal_id);
        $APPLICATION->IncludeComponent(
            "elfgroup:hlblock.list",
            "deals",
            $arComponentParams,
            false
        );
        break;


    // Если в качастве действия выбрано сохранение
    case 'save':
        CBitrixComponent::includeComponentClass("elfgroup:hlblock.list");
        $deal_id = $_POST['DEAL_ID'];
        $chengedGoods = $_POST['CHANGED_GOODS'];
        $arComponentParams = json_decode($_POST['COMPONENT_PARAMS'], true);
        $res = HLListComponent::saveDeal($deal_id, $arComponentParams, $chengedGoods);
        CBitrixComponent::clearComponentCache("elfgroup:hlblock.list");
        global $dealsFilter;
        $dealsFilter['ID'] = trim($deal_id);
        $APPLICATION->IncludeComponent(
            "elfgroup:hlblock.list",
            "deals",
            $arComponentParams,
            false
        );
        break;

    // Если в качастве действия выбрано согласование
    case 'conform':
        CBitrixComponent::includeComponentClass("elfgroup:hlblock.list");
        $deal_id = $_POST['DEAL_ID'];
        $arComponentParams = json_decode($_POST['COMPONENT_PARAMS'], true);
        $arComponentParams['CHANGE_STATUS'] = 'AGREED';
        $res = HLListComponent::saveDeal($deal_id, $arComponentParams, false);
        CBitrixComponent::clearComponentCache("elfgroup:hlblock.list");
        global $dealsFilter;
        $dealsFilter['ID'] = trim($deal_id);
        $APPLICATION->IncludeComponent(
            "elfgroup:hlblock.list",
            "deals",
            $arComponentParams,
            false
        );
        break;

    // Если в качастве действия выбрано отмена
    case 'cancel':
        CBitrixComponent::includeComponentClass("elfgroup:hlblock.list");
        $deal_id = $_POST['DEAL_ID'];
        $arComponentParams = json_decode($_POST['COMPONENT_PARAMS'], true);
        $arComponentParams['CHANGE_STATUS'] = 'CANCEL';
        $res = HLListComponent::saveDeal($deal_id, $arComponentParams, false);
        CBitrixComponent::clearComponentCache("elfgroup:hlblock.list");
        $APPLICATION->IncludeComponent(
            "elfgroup:hlblock.list",
            "deals",
            $arComponentParams,
            false
        );
        break;

    // Если в качастве действия добавление товара
    case 'addbycode':
        CBitrixComponent::includeComponentClass("elfgroup:hlblock.list");
        $deal_id = $_POST['DEAL_ID'];
        $arComponentParams = json_decode($_POST['COMPONENT_PARAMS'], true);
        $added_code = $_POST['ADDED_CODE'];
        $res = HLListComponent::addToDeal($deal_id, $arComponentParams, $added_code);
        CBitrixComponent::clearComponentCache("elfgroup:hlblock.list");
        global $dealsFilter;
        $dealsFilter['ID'] = trim($deal_id);
        $APPLICATION->IncludeComponent(
            "elfgroup:hlblock.list",
            "deals",
            $arComponentParams,
            false
        );
        break;

    // Если в качестве действия выбрана фильтрация
    case 'filter':

        $arComponentParams = json_decode($_POST['COMPONENT_PARAMS'], true);

        $APPLICATION->IncludeComponent(
            "elfgroup:hlblock.list",
            "deals",
            $arComponentParams,
            false
        );
        break;

    // Если в качастве действия добавление товара
    case 'history':
        CBitrixComponent::includeComponentClass("elfgroup:hlblock.list");
        $deal_id = $_POST['DEAL_ID'];
        $res = HLListComponent::getHistoryDeal($deal_id);
        echo $res;
        break;

}


