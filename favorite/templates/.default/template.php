<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}
IncludeTemplateLangFile(__FILE__);
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);

$request = Context::getCurrent()->getRequest();
$view = $request->get("view");

if (!empty($arResult['NAV_RESULT'])) {
    $navParams = array(
        'NavPageCount' => $arResult['NAV_RESULT']->NavPageCount,
        'NavPageNomer' => $arResult['NAV_RESULT']->NavPageNomer,
        'NavNum'       => $arResult['NAV_RESULT']->NavNum
    );
} else {
    $navParams = array(
        'NavPageCount' => 1,
        'NavPageNomer' => 1,
        'NavNum'       => $this->randString()
    );
}

$elementEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT');
$elementDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE');
$elementDeleteParams = array('CONFIRM' => GetMessage('CT_BCS_TPL_ELEMENT_DELETE_CONFIRM'));
?>

<? if ($arParams['LIST_PAGE'] === 'Y'): ?>

            <? if ($arResult['ITEMS']): ?>
                <div class="personal-favorites__list">

                    <? foreach ($arResult['ITEMS'] as $item): ?>
                        <?
                        $areaIds[$item['ID']] = $this->GetEditAreaId($item['ID']);
                        $uniqueId = $areaIds[$item['ID']];
                        $this->AddEditAction($uniqueId, $item['EDIT_LINK'], $elementEdit);
                        $this->AddDeleteAction($uniqueId, $item['DELETE_LINK'], $elementDelete, $elementDeleteParams);
                        ?>
                        <div class="personal-favorites__item">

                            <?//p($item);?>

                            <? $APPLICATION->IncludeComponent(
                                'bitrix:catalog.item',
                                'card',
                                array(
                                    'RESULT' => array(
                                        'ITEM' => $item,
                                        'AREA_ID' => $areaIds[$item['ID']],
                                    ),
                                    'PARAMS' => array('NEED_RELOAD' => 'Y')
                                ),
                                $component,
                                array('HIDE_ICONS' => 'Y')
                            );
                            ?>
                        </div>
                    <? endforeach; ?>
                </div>

                <div class="catalog-section__navigation js-pages" data-pagination-num="<?= $navParams['NavNum'] ?>">
                    <? if ($navParams['NavPageCount'] > 1): ?>
                        <?= $arResult['NAV_STRING'] ?>
                        <div class="catalog-section-load">
                            <svg class="catalog-section-load__icon" width="20" height="19" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.001 18.2159C15.0293 18.2159 19.121 14.2606 19.121 9.39994C19.1224 9.30771 19.1047 9.21615 19.0692 9.13056C19.0336 9.04498 18.9808 8.96708 18.9138 8.9014C18.8468 8.83572 18.767 8.78356 18.6789 8.74796C18.5909 8.71236 18.4964 8.69403 18.401 8.69403C18.3056 8.69403 18.2111 8.71236 18.1231 8.74796C18.0351 8.78356 17.9553 8.83572 17.8883 8.9014C17.8213 8.96708 17.7685 9.04498 17.7329 9.13056C17.6973 9.21615 17.6797 9.30771 17.681 9.39994C17.681 13.5084 14.2511 16.8239 10.001 16.8239C5.75095 16.8239 2.32102 13.5084 2.32102 9.39994C2.32102 5.29153 5.75094 1.97594 10.001 1.97594C11.5899 1.97594 13.1846 2.58608 14.4785 3.49119L12.8285 3.60719C12.6376 3.61396 12.4573 3.69375 12.3272 3.82903C12.1971 3.96431 12.1279 4.14399 12.1349 4.32856C12.1419 4.51313 12.2244 4.68747 12.3643 4.81324C12.5042 4.939 12.6901 5.00589 12.881 4.99919C12.8986 4.9974 12.9161 4.99498 12.9335 4.99194L16.2935 4.75994C16.3964 4.75283 16.4964 4.72445 16.5869 4.67672C16.6775 4.62899 16.7563 4.56303 16.8182 4.48329C16.88 4.40356 16.9235 4.31191 16.9455 4.21454C16.9676 4.11718 16.9678 4.01637 16.946 3.91894L16.226 0.670943C16.1852 0.490198 16.0719 0.332516 15.9108 0.232582C15.7498 0.132649 15.5542 0.0986503 15.3673 0.138068C15.1803 0.177487 15.0172 0.287089 14.9138 0.442768C14.8104 0.598446 14.7752 0.787447 14.816 0.968193L15.0935 2.20794C13.634 1.23592 11.8658 0.583943 10.001 0.583943C4.97271 0.583944 0.881022 4.53924 0.881023 9.39994C0.881023 14.2606 4.97271 18.2159 10.001 18.2159Z"
                                      fill="#565656" />
                            </svg>
                            <div class="catalog-section-load__text"><?= Loc::getMessage("FAVORITES_LOAD_CONTENT") ?></div>
                        </div>
                        <div class="catalog-section-pages">
                            <div class="catalog-section-pages__title"><?= Loc::getMessage("FAVORITES_VIEW_SET") ?></div>
                            <div class="catalog-section-pages__list">
                                <? $viewCount = [12, 24, 48] ?>
                                <? foreach ($viewCount as $key => $arView): ?>
                                    <a class="catalog-section-pages__item <?= $arView == $view || (empty($view) && $key === 0) ? ' catalog-section-pages__item_active' : '' ?>"
                                       href="<?= $APPLICATION->GetCurPageParam('view=' . $arView,
                                           ['view']) ?>"><?= $arView ?></a>
                                <? endforeach; ?>
                            </div>
                        </div>
                    <? endif; ?>
                </div>
            <? else: ?>
                <span><?= Loc::getMessage("FAVORITES_NOT_FOUND") ?></span>
            <? endif; ?>


<? else: ?>
	<div class="<?= $arParams['CLASS_NAME'] ?> js-fav" data-id="<?= $arParams['ELEMENT_ID']; ?>" title="В избранное"></div>
<? endif; ?>