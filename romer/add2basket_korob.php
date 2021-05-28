<?
define("NO_KEEP_STATISTIC", true);
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;

use Bitrix\Sale;

$request = Context::getCurrent()->getRequest();
$method = $request->getRequestMethod();

\Bitrix\Main\Loader::IncludeModule("sale");
\Bitrix\Main\Loader::includeModule("catalog");
\Bitrix\Main\Loader::includeModule("iblock");


//добавление в корзину для оптовиков http://joxi.ru/J2b85L9CgVnw3r
//по номеру короба (из 1с выгружается как ТП) подтягиваются нужные размеры (ТП) и кладутся в корзину. Отображается в оформлении заказа, как 1 короб, а в заказе несколько ТП-размеров

if (trim($request->getPost("ids")) != '') {
    $arrToCart = json_decode(str_replace('|', '"', $request->getPost("ids")), true); //ids ТП приходят с кнопки покупки короба
    $resultProd = \Bitrix\Catalog\ProductTable::getList(['filter' => array('=ID' => array_keys($arrToCart))]);
    $arrAvail = [];
    while ($product = $resultProd->fetch()) {
        $arrAvail[$product['ID']] = $product['QUANTITY']; //доступные к покупке
    }

    $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
    $addIDS = array_keys($arrToCart);
    $sostav = helpers\getSostavKorob($request->getPost("korob"))['SOSTAV_KOROBA']; // в HL состав короба c указанием размеров и их количества {36:1,37:2,38-2,40-1,41-1}
    $finded = false;
    foreach ($basket as $basketItem) {
        $b_id = $basketItem->getId();
        $basketPropertyCollection = $basketItem->getPropertyCollection();
        $props = $basketPropertyCollection->getPropertyValues();
        if ($props['KOROB']['VALUE'] == $request->getPost("korob")) {
            $finded = array_search($basketItem->getProductId(), $addIDS); //проверяем чтоб был в наличии
            if ($finded !== false) {
                $q = $basketItem->getQuantity() + $sostav[$props['RAZMER']['VALUE']];
                if ($q > $arrAvail[$basketItem->getProductId()]) { //+по количеству в коробе
                    $basketPropertyCollection->setProperty(array(
                        array(
                            'NAME' => 'Предзаказ',
                            'CODE' => 'PREDZAKAZ',
                            'VALUE' => 'Да',
                            'SORT' => 10,
                        ),
                    ));
                    $basketPropertyCollection->save();
                } else {
                    foreach ($basketPropertyCollection as $propertyItem) {
                        if ($propertyItem->getField('CODE') == 'PREDZAKAZ') {
                            $propertyItem->delete();
                            break;
                        }
                    }
                    $basketPropertyCollection->save();
                }
                $basketItem->setField('QUANTITY', $basketItem->getQuantity() + $sostav[$props['RAZMER']['VALUE']]);
            }
        }

    }
    if ($finded !== false) {
        $basket->save();
    } else {

        $mxResult = CCatalogSku::getProductList($request->getPost("offer"), CATALOG_IBLOCK_TP);
        if (is_array($mxResult)) {
            $item = [];
            $item['MAIN_ID'] = current($mxResult)['ID'];
            $res = CIBlockElement::GetList(
                [],
                ['ID' => current($mxResult)['ID'], 'IBLOCK_ID' => CATALOG_IBLOCK_ID],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'DETAIL_PICTURE', 'PROPERTY_CML2_ARTICLE', 'PROPERTY_TSVET_PRODUKTSII', 'PROPERTY_MORE_PHOTO', 'PROPERTY_MORE_PHOTO_EXTERNAL']
            );

            if ($arRes = $res->GetNext()) {
                $pict = false;
                if ($arRes['DETAIL_PICTURE'] > 0) $pict = $arRes['DETAIL_PICTURE'];
                elseif ($arRes['PROPERTY_MORE_PHOTO_VALUE'] > 0) $pict = $arRes['PROPERTY_MORE_PHOTO_VALUE'];
                elseif ($arRes['PROPERTY_MORE_PHOTO_EXTERNAL_VALUE'] > 0) $pict = $arRes['PROPERTY_MORE_PHOTO_EXTERNAL_VALUE'];
                $item["PREVIEW_PICTURE_SRC"] = ($pict) ? CFile::GetPath($pict) : DEFAULT_IMG;
                $item['DETAIL_PAGE_URL'] = $arRes['DETAIL_PAGE_URL'];
                $item['ARTICLE'] = $arRes['PROPERTY_CML2_ARTICLE_VALUE'];
            }
        }
        $dbProps = CIBlockElement::GetProperty(CATALOG_IBLOCK_ID, current($mxResult)['ID'], array("sort" => "asc"), Array("CODE" => "NAIMENOVANIE_DLYA_SAYTA_2"));
        if ($arProps = $dbProps->Fetch())
            $item['NAME'] = ($arProps["VALUE_ENUM"] != '') ? $item['NAME'] = $arProps["VALUE_ENUM"] : '';

        $all_success = false;
        foreach ($arrToCart as $id => $offer) {


            $propsArr = [
                ['NAME' => 'Цвет', 'CODE' => 'CLR', 'VALUE' => $request->getPost("color")],
                ['NAME' => 'Размер', 'CODE' => 'RAZMER', 'VALUE' => $offer['RAZMER']],
                ['NAME' => 'Изображение', 'CODE' => 'PREVIEW_PICTURE_SRC', 'VALUE' => $item['PREVIEW_PICTURE_SRC']],
                ['NAME' => 'Артикул', 'CODE' => 'ARTICLE', 'VALUE' => $item['ARTICLE']],
                ['NAME' => 'Наименование', 'CODE' => 'NAMING', 'VALUE' => $item['NAME']],
                ['NAME' => 'Элемент', 'CODE' => 'MAIN_ELEMENT', 'VALUE' => $item['MAIN_ID']],
                ['NAME' => 'Короб', 'CODE' => 'KOROB', 'VALUE' => $request->getPost("korob")],
                ['NAME' => 'Страница', 'CODE' => 'DETAIL_PAGE_URL', 'VALUE' => $item['DETAIL_PAGE_URL']],
            ];
            if ($offer['QUANTITY'] > $arrAvail[$id]) {
                $propsArr[] = ['NAME' => 'Предзаказ', 'CODE' => 'PREDZAKAZ', 'VALUE' => 'Да'];
            }

            $fields = [
                'PRODUCT_ID' => IntVal($id),
                'QUANTITY' => $offer['QUANTITY'],
                'PROPS' => $propsArr,
            ];

            $r = Bitrix\Catalog\Product\Basket::addProduct($fields);
            if (!$r->isSuccess()) {
                print_r($r->getErrorMessages()[0]);
                $all_success = false;
                break;
            } else {
                $all_success = true;
            }


        }
        if ($all_success) { //обновляем корзину в шапке
            $APPLICATION->IncludeComponent("bitrix:sale.basket.basket.line", "", Array(
                    "PATH_TO_BASKET" => SITE_DIR . "personal/order/make/",
                    "PATH_TO_ORDER" => SITE_DIR . "personal/order/make/",
                    "PATH_TO_PERSONAL" => SITE_DIR . "personal/",
                    "PATH_TO_PROFILE" => SITE_DIR . "personal/",
                    "PATH_TO_REGISTER" => SITE_DIR . "auth/",
                )
            );
        }
    }

}



