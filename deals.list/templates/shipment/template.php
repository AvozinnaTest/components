<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @var $arParams array
 * @var $arResult array
 */

$this->setFrameMode(true);



?>


<div class="personal-listing__list personal-listing__list_otgruski">

        <?
        $prevdate = '';
        ?>
        <?foreach($arResult['ITEMS'] as $item) {?>

            <?
            $date = date("d.m.Y", $item['UF_TIMESTAMP']);
            if ($prevdate!=$date) {
                $prevdate = $date;
                ?>
                <div class="personal-listing-item__bydate"><span><b>Отгрузка - <?=$date?></b></span></div>
                <?
            }


            /******************************************************************************************/
            $textStatus = (strtolower($item['UF_SHIPPED']) == 'да') ? 'Отправлено' : $item['UF_WB_STATUS'];
            $textStatusTitle = $textStatus;
            if ($item['IS_SHIPPED'] !== false) { // есть  транспортная заявка
                $imgSrc = 'shipment_truck.svg';//truck
            } else { // есть  транспортной заявки
                $imgSrc = 'fast-delivery.svg';
                if (strtolower($item['UF_DELIVERY']) == 'да') { //доставка
                    $textStatusTitle = 'Ожидает отправки';
                    $imgSrc = 'shipment_forklift.svg';
                } else {
                    if (strtolower($item['UF_SELF']) == 'да') { //самовывоз
                        $textStatusTitle = 'Отгрузка самовывозом';
                        $imgSrc = 'shipment_warehouse.svg';
                    }
                    else { //неизвестно
                        $textStatusTitle = 'Собран на складе';
                        $imgSrc = 'shipment_box.svg';
                    }
                }
            }

            ?>


            <div class="personal-listing-item">
                <div class="personal-listing-item__header personal-listing-item__header_otgruski">
                    <div class="personal-listing-item__dateship"><?=$date?></div>
                    <div class="personal-listing-item__title"><?=$item['UF_ID_1C']?><?if ($item['UF_DEAL_CODE_LINK']!='') {?><br><a href="/personal/sdelki/?UF_ID_1C=<?=$item['UF_DEAL_CODE_LINK']?>"><?=$item['UF_DEAL_CODE_LINK']?></a><?}?></div>
                    <div class="personal-listing-item__quantity"><?=$item['ITEMS_COUNT']?> тов.</div>
                    <div class="personal-listing-item__allweight"><?=$item['ITEMS_WEIGHT']?> кг</div>
                    <div class="personal-listing-item__allsum"><?=$item['ITEMS_SUMM']?> руб.</div>
                    <div class="personal-listing-item__shipstatus"><?=$textStatus?></div>
                    <div class="personal-listing-item__car"><img src="/local/templates/elfgroup/assets/img/<?=$imgSrc?>" alt="<?=$textStatusTitle?>" title="<?=$textStatusTitle?>"></div>
                    <div class="personal-listing-item__pointer js-open-otgruz"></div>
                </div>
                <div class="js-personal-listing-hidden">
                    <div class="personal-listing-item__table">
                        <div class="personal-listing-item__th">
                            <div class="personal-listing-item__name">Наименование</div>
                            <div>Кол-во</div>
                            <div>Вес, кг</div>
                            <div>Объем, м<sup>3</sup></div>
                            <div>Цена, руб.</div>
                            <div>Сумма, руб.</div>
                        </div>
                        <? foreach ( $item['ITEMS_ARRAY'] as $good) {?>
                        <div class="personal-listing-item__tr">
                            <div class="personal-listing-item__name"><a href="#"><?=$good['Номенклатура']?></a><span><?=$good['НоменклатураКод']?></span></div>
                            <div class="personal-listing-item__quantity">
                                <span ><?=$good['Количество']?> шт.</span>
                            </div>
                            <div class="personal-listing-item__weight">
                                <span ><?=$good['Вес']?></span>
                            </div>
                            <div class="personal-listing-item__volume">
                                <span ><?=$good['Объем']?></span>
                            </div>
                            <div class="personal-listing-item__price">
                                <span><?=$good['Цена']?></span>
                            </div>
                            <div class="personal-listing-item__summ"><?=$good['Сумма']?></div>
                        </div>
                        <? }?>
                        <div class="personal-listing-item__itog">
                            <div class="personal-listing-item__td _itogo">Итого</div>
                            <div class="personal-listing-item__sumtext"><span><?=count($item['ITEMS_ARRAY'])?> тов.</span><span><?=$item['ITEMS_WEIGHT']?> кг</span> <span><?=$item['ITEMS_VOLUME']?> м<sup>3</sup></span> <span> <?=$item['ITEMS_SUMM']?> руб.</span></div>
                        </div>
<!--                        <div class="personal-listing-item__itog">-->
<!--                            <div class="personal-listing-item__td">Итого</div>-->
<!--                            <div class="personal-listing-item__sumtext"><span>--><?//=$item['ITEMS_WEIGHT']?><!-- кг</span><span>--><?//=$item['ITEMS_SUMM']?><!-- руб.</span></div>-->
<!--                        </div>-->
                    </div>
                </div>
            </div>

        <?}?>



    </div>
    <?php
    if($arParams['DISPLAY_BOTTOM_PAGER']) {
        echo $arResult["NAV_STRING"];
    }
    ?>
    <script>
        var componentParamsDeals = <?=CUtil::PhpToJSObject($arParams);?>;

    </script>



