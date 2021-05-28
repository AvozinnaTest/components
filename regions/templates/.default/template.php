<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}
$this->setFrameMode(true);
?>
<div class="header-city">
    Ваш регион: <a href="#" class="header-city-text" data-modal="js-citySelect"><?=$arResult['CURRENT_NAME']?></a> <span class="icon-arrow-toggle"></span>
    <div class="header-city-ask <?= ($arResult['USER_CITY_NOTSURE']) ? ' _showing' : ''?>">
        <div>Ваш город <?=$arResult['CURRENT_NAME']?>?</div>
        <div class="header-city-ask__buttons">
            <button class="button button-icon_green header-city-ask__button js-cityagree">да</button>
            <button class="button button-icon_green header-city-ask__button" data-modal="js-citySelect">нет, другой</button>
        </div>
    </div>
</div>

<div class="popup _city js-citySelect">
    <div class="popup__overlay js-watchPopup">
        <div class="popup__body" data-body-scroll-lock-ignore="">
            <div class="popup__wrap">
                <div class="popup__close js-closePopup">

                </div>
                <div class="popup__title">
                    Выберите город
                </div>
                <form action="" class="popup__form">
                    <label class="input popup__label _search">
                        <input type="text" class="input-field popup-regions-search" placeholder="Введите название города" value="" autocomplete="on">
                    </label>
                    <button class="popup-search__button icon-search"></button>
                </form>
                <nav class="popup-city__navigation">
                    <ul class="popup-city__items" data-simplebar data-simplebar-auto-hide="false">

                        <?$i = 0;
                        foreach ($arResult['CITIES'] as $id=>$city) {?>
                            <li class="popup-city__item">
                                <a href="#" class="popup-city__link <?= ($i < 3) ? '_bold' : ''?>" data-id="<?=$id?>">
                                    <?=$city?>
                                </a>
                            </li>
                        <? $i++;
                        }?>

                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
