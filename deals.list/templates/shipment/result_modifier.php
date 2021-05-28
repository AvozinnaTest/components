<?php
foreach ($arResult['ITEMS'] as $i=>&$item) {
    $item['ITEMS_ARRAY'] = CUtil::JsObjectToPhp($item['UF_ITEMS'],true);
    $item['UF_ID_BITRIX'] = preg_replace('/\D/', '', trim($item['UF_ID_BITRIX']));
    $uid_deal = $this->__component->getDeal1cCode($item['UF_DEAL_CODE']);
    $item['UF_DEAL_CODE_LINK'] = ($uid_deal !==false && $uid_deal!='') ? $uid_deal : '';
    $item['IS_SHIPPED'] = $this->__component->checkShipped($item['UF_W_CODE_1C']);
    $item['UF_SHIPPED'] = ($item['IS_SHIPPED'] !== false) ? $item['IS_SHIPPED']['UF_SHIPPED'] : 'нет';

    $item['ITEMS_SUMM'] = 0;
    $item['ITEMS_COUNT'] = 0;
    $item['ITEMS_WEIGHT'] = 0;
    $item['ITEMS_VOLUME'] = 0;
    $id_deal_uniq_code = false;
    foreach ( $item['ITEMS_ARRAY'] as $g=>&$good) {
        if ($good['Количество'] == 0) { unset($arResult['ITEMS'][$i]['ITEMS_ARRAY'][$g]); continue;}
        $item['ITEMS_SUMM'] += floatval($good['Сумма']);
        $good['Цена'] = number_format($good['Цена'], 2, ',', ' ');
        $good['Сумма'] = number_format($good['Сумма'], 2, ',', ' ');
        $good['Номенклатура']= str_replace('\\', '', $good['Номенклатура']);
        $item['ITEMS_COUNT']++;
        $good['DETAIL_PAGE_URL'] = $arResult['ARR_ITEMS_FROM_CATALOG'][$item['UF_DEAL_CODE']]['FROM_CATALOG'][$good['НомерСтроки']]['DETAIL_PAGE_URL'];
        $item['ITEMS_WEIGHT'] += floatval($good['Вес']);
        $item['ITEMS_VOLUME'] += floatval($good['Объем']);

    }
    $item['ITEMS_SUMM'] = number_format($item['ITEMS_SUMM'], 2, ',', ' ');
}
