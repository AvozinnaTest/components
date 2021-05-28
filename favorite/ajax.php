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

if ($method === 'POST' && $action === 'getFav') {
	CBitrixComponent::includeComponentClass("dial:dialfavorite");
	$favs = DialFavorite::getUserFavorite();
	$result = json_encode($favs);
	die($result);
}

if ($method === 'POST' && $action === 'DELETE') {
    CBitrixComponent::includeComponentClass("dial:dialfavorite");
	$favs = DialFavorite::removeUserFavorite($ID);
	$result = json_encode($favs);
	die($result);
}

if ($method === 'POST' && $action === 'ADD') {
    CBitrixComponent::includeComponentClass("dial:dialfavorite");
	$favs = DialFavorite::setUserFavorite($ID);
	$result = json_encode($favs);
	die($result);
}
if ($method === 'POST' && $action === 'SEARCH') {
    global $APPLICATION;
    $APPLICATION->IncludeComponent(
        "dial:dialfavorite",
        "",
        Array(
            "ELEMENT_ID" => "",
            "LIST_PAGE"  => "Y",
            "SEARCH_TEXT" => $TEXT
        )
    );
}
