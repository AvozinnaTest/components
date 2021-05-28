<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
use Bitrix\Main\Context;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$request = Context::getCurrent()->getRequest();
$method = $request->getRequestMethod();
$action = $request->getPost("ACTION");
$ID = $request->getPost("ID");
$TEXT = $request->getPost("TEXT");

// смена
if ($method === 'POST' && $action === 'CHANGE') {
    CBitrixComponent::includeComponentClass("dial:regions");
	$reg = DialRegion::setUserCity($ID);
	$result = json_encode($reg);
	die($result);
}
//поиск
if ($method === 'POST' && $action === 'SEARCH') {
    CBitrixComponent::includeComponentClass("dial:regions");
    $reg = DialRegion::searchCity($TEXT);
    $result = json_encode($reg);
    die($result);
}
//согласие на выбранный
if ($method === 'POST' && $action === 'ISSURE') {
    CBitrixComponent::includeComponentClass("dial:regions");
    $reg = DialRegion::removeCookieCity('USER_CITY_NOTSURE');
    $result = json_encode($reg);
    die($result);
}