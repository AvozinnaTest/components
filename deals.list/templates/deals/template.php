<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @var $arParams array
 * @var $arResult array
 */

$this->setFrameMode(true);?>


<div class="personal-listing__list">

    <?
    ?>
    <?foreach($arResult['ITEMS'] as $i=>$item) {
        if ($item['UF_ITEMS_COUNT'] == 0) continue;
        $item['UF_ID_BITRIX'] = preg_replace('/\D/', '', trim($item['UF_ID_BITRIX']));
        ?>
        <div class="personal-listing-item" data-deal-id="<?=$item['ID']?>">
            <div class="personal-listing-item__header">
                <div class="personal-listing-item__title"><span class="personal-listing-item__point <?if (trim($item['UF_ID_BITRIX'])==''){?> personal-listing-item__point_deact<?}?>"></span><?=$item['UF_ID_1C']?> <?if (trim($item['UF_ID_BITRIX'])!=''){?><span>по заявке</span> №<?=$item['UF_ID_BITRIX']?> <?}?></div>
                <div class="personal-listing-item__sum"><?=$item['UF_ITEMS_SUMM']?> руб.</div>
                <div class="personal-listing-item__quantity"><?=$item['UF_ITEMS_COUNT']?> тов.</div>
                <a class="personal-listing-item__history"  href="#/popside-deal-history-123456" data-popside-deal-history="deal-history" data-deal-hash="<?= base64_encode('deal'.$item['ID'])?>">История сделки</a>
            </div>
            <div class="personal-listing-item__info">
                <div class="personal-listing-item__dates">
                    <span>Создана <b><?=$item['UF_DATE']?></b></span>
                    <span>Обновлена <b><?=$item['UF_DATE_CHANGE']?></b></span>
                </div>
                <div class="personal-listing-item__status personal-listing-item__status_active">
                    <?if (!empty($item['SHIPMENTS'])) {
                        if ($item['IS_SHIPPED'] == 1) {
                            ?>
                            <span>Отгружено полностью</span>
                            <?
                        } else {?>
                            <span style="color: #0068b6">Отгружено частично</span>
                        <?}
                    } ?>
                </div>
            </div>
            <div class="js-personal-listing-hidden">

                <div class="personal-listing-item__icons">
                    <div class="personal-listing-item__icon <?= ($item['UF_STATUS']=='NEED_CHECK') ? '_wait' : ''?> <?= ($item['UF_STATUS']=='CHECKED' || $item['UF_STATUS']=='AGREED') ? '_checked' : ''?>">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512px" height="512px" class=""><g><g>
                                    <g>
                                        <g>
                                            <path d="M405.333,0H106.667C83.146,0,64,19.135,64,42.667v426.667C64,492.865,83.146,512,106.667,512h298.667     C428.854,512,448,492.865,448,469.333V42.667C448,19.135,428.854,0,405.333,0z M426.667,469.333     c0,11.76-9.563,21.333-21.333,21.333H106.667c-11.771,0-21.333-9.573-21.333-21.333V42.667c0-11.76,9.563-21.333,21.333-21.333     h298.667c11.771,0,21.333,9.573,21.333,21.333V469.333z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#7D7D7D"/>
                                            <path d="M394.667,42.667H117.333c-5.896,0-10.667,4.771-10.667,10.667v192c0,5.896,4.771,10.667,10.667,10.667h277.333     c5.896,0,10.667-4.771,10.667-10.667v-192C405.333,47.438,400.563,42.667,394.667,42.667z M384,234.667H128V64h256V234.667z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#7D7D7D"/>
                                            <path d="M288,277.333h-64c-5.896,0-10.667,4.771-10.667,10.667v64c0,5.896,4.771,10.667,10.667,10.667h64     c5.896,0,10.667-4.771,10.667-10.667v-64C298.667,282.104,293.896,277.333,288,277.333z M277.333,341.333h-42.667v-42.667h42.667     V341.333z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#7D7D7D"/>
                                            <path d="M181.333,277.333h-64c-5.896,0-10.667,4.771-10.667,10.667v64c0,5.896,4.771,10.667,10.667,10.667h64     c5.896,0,10.667-4.771,10.667-10.667v-64C192,282.104,187.229,277.333,181.333,277.333z M170.667,341.333H128v-42.667h42.667     V341.333z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#7D7D7D"/>
                                            <path d="M394.667,277.333h-64c-5.896,0-10.667,4.771-10.667,10.667v64c0,5.896,4.771,10.667,10.667,10.667h64     c5.896,0,10.667-4.771,10.667-10.667v-64C405.333,282.104,400.563,277.333,394.667,277.333z M384,341.333h-42.667v-42.667H384     V341.333z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#7D7D7D"/>
                                            <path d="M288,384h-64c-5.896,0-10.667,4.771-10.667,10.667v64c0,5.896,4.771,10.667,10.667,10.667h64     c5.896,0,10.667-4.771,10.667-10.667v-64C298.667,388.771,293.896,384,288,384z M277.333,448h-42.667v-42.667h42.667V448z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#7D7D7D"/>
                                            <path d="M181.333,384h-64c-5.896,0-10.667,4.771-10.667,10.667v64c0,5.896,4.771,10.667,10.667,10.667h64     c5.896,0,10.667-4.771,10.667-10.667v-64C192,388.771,187.229,384,181.333,384z M170.667,448H128v-42.667h42.667V448z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#7D7D7D"/>
                                            <path d="M394.667,384h-64c-5.896,0-10.667,4.771-10.667,10.667v64c0,5.896,4.771,10.667,10.667,10.667h64     c5.896,0,10.667-4.771,10.667-10.667v-64C405.333,388.771,400.563,384,394.667,384z M384,448h-42.667v-42.667H384V448z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#7D7D7D"/>
                                            <path d="M309.333,213.333h21.333c17.646,0,32-14.354,32-32v-64c0-17.646-14.354-32-32-32h-21.333c-17.646,0-32,14.354-32,32v64     C277.333,198.979,291.688,213.333,309.333,213.333z M341.333,181.333c0,5.885-4.792,10.667-10.667,10.667h-21.333     c-4.576,0-8.411-2.931-9.922-6.995l41.922-41.922V181.333z M298.667,117.333c0-5.885,4.792-10.667,10.667-10.667h21.333     c4.576,0,8.414,2.931,9.923,6.993l-41.923,41.923V117.333z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#7D7D7D"/>
                                        </g>
                                    </g>
                                </g></g>
                                    </svg>
                        <div class="personal-listing-item__icontext">
                            <?= ($item['UF_STATUS']=='CHECKED') ? 'Обработано менеджером' :  'Обработка менеджером';?>
                            <span><?= ($item['UF_STATUS']=='NEED_CHECK') ? '' : date('d.m.Y H:i',strtotime($item['UF_DATE_CHANGE']))?></span>
                        </div>
                    </div>
                    <div class="personal-listing-item__arrow"></div>
                    <div class="personal-listing-item__icon <?= ($item['UF_STATUS']=='CHECKED' || $item['UF_STATUS']=='AGREED') ? '_checked' : ''?>">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512px" height="512px"><g><g>
                                    <g>
                                        <g>
                                            <path d="M444.875,109.792L338.208,3.125c-2-2-4.708-3.125-7.542-3.125h-224C83.146,0,64,19.135,64,42.667v426.667     C64,492.865,83.146,512,106.667,512h298.667C428.854,512,448,492.865,448,469.333v-352     C448,114.5,446.875,111.792,444.875,109.792z M341.333,36.417l70.25,70.25h-48.917c-11.771,0-21.333-9.573-21.333-21.333V36.417z      M426.667,469.333c0,11.76-9.563,21.333-21.333,21.333H106.667c-11.771,0-21.333-9.573-21.333-21.333V42.667     c0-11.76,9.563-21.333,21.333-21.333H320v64C320,108.865,339.146,128,362.667,128h64V469.333z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M373.333,298.667H224c-5.896,0-10.667,4.771-10.667,10.667c0,5.896,4.771,10.667,10.667,10.667h149.333     c5.896,0,10.667-4.771,10.667-10.667C384,303.438,379.229,298.667,373.333,298.667z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M138.667,128h128c5.896,0,10.667-4.771,10.667-10.667c0-5.896-4.771-10.667-10.667-10.667h-128     c-5.896,0-10.667,4.771-10.667,10.667C128,123.229,132.771,128,138.667,128z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M373.333,234.667H138.667c-5.896,0-10.667,4.771-10.667,10.667c0,5.896,4.771,10.667,10.667,10.667h234.667     c5.896,0,10.667-4.771,10.667-10.667C384,239.438,379.229,234.667,373.333,234.667z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M128,181.333c0,5.896,4.771,10.667,10.667,10.667h234.667c5.896,0,10.667-4.771,10.667-10.667     c0-5.896-4.771-10.667-10.667-10.667H138.667C132.771,170.667,128,175.438,128,181.333z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M280.521,387.063c-15.688,15.438-35.458,32.833-43.292,38.125c-1.854-4.198-3.958-11.219-5.229-15.479     C227.667,395.344,224.271,384,213.333,384c-9.354,0-13.563,8.552-19.938,21.49c-3.125,6.333-9.604,19.521-11.958,21.177     c-2.313-0.24-6.271-2.938-10.625-7C182.563,404.49,192,386.917,192,373.333c0-23.313-19.625-32-32-32     c-12.583,0-32,18.323-32,42.667c0,10.792,5.979,24.125,14.458,35.823c-3.375,3.375-6.813,6.438-10.188,8.979     c-4.708,3.531-5.667,10.219-2.146,14.927c2.104,2.802,5.313,4.271,8.542,4.271c2.229,0,4.479-0.698,6.396-2.135     c3.688-2.76,7.646-6.229,11.625-10.188c8.354,7.438,17.229,12.323,24.646,12.323c14.417,0,22.979-16.406,30.417-31.5     c4.917,16.292,9.667,31.5,22.917,31.5c3.563,0,14.375,0,60.813-45.729c4.208-4.135,4.25-10.885,0.125-15.083     C291.479,382.969,284.729,382.948,280.521,387.063z M156.75,402.917c-4.333-6.604-7.417-13.458-7.417-18.917     c0-13.01,9.542-20.625,10.667-21.333c1.771,0,10.667,0.51,10.667,10.667C170.667,380.135,164.917,391.615,156.75,402.917z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                        </g>
                                    </g>
                                </g></g> </svg>
                        <div class="personal-listing-item__icontext">

                            <?if ($item['UF_STATUS']=='NEED_CHECK') {?>Ждем информацию<?} else {?>Счет сформирован<br><a href="/personal/sdelki/pdf.php?DEAL_CODE=<?=$item['UF_DEAL_CODE']?>" target="_blank">Скачать</a><?}?>
                        </div>
                    </div>
                    <div class="personal-listing-item__arrow"></div>
                    <div class="personal-listing-item__icon <?= ($item['UF_STATUS']=='CHECKED') ? '_wait' : ''?> <?= ($item['UF_STATUS']=='AGREED') ? '_checked' : ''?>">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512px" height="512px" class=""><g><g>
                                    <g>
                                        <g>
                                            <path d="M405.333,42.667h-44.632c-4.418-12.389-16.147-21.333-30.035-21.333h-32.229C288.417,8.042,272.667,0,256,0     s-32.417,8.042-42.438,21.333h-32.229c-13.888,0-25.617,8.944-30.035,21.333h-44.631C83.146,42.667,64,61.802,64,85.333v384     C64,492.865,83.146,512,106.667,512h298.667C428.854,512,448,492.865,448,469.333v-384C448,61.802,428.854,42.667,405.333,42.667     z M170.667,53.333c0-5.885,4.792-10.667,10.667-10.667h37.917c3.792,0,7.313-2.021,9.208-5.302     c5.854-10.042,16.146-16.031,27.542-16.031s21.688,5.99,27.542,16.031c1.896,3.281,5.417,5.302,9.208,5.302h37.917     c5.875,0,10.667,4.781,10.667,10.667V64c0,11.76-9.563,21.333-21.333,21.333H192c-11.771,0-21.333-9.573-21.333-21.333V53.333z      M426.667,469.333c0,11.76-9.563,21.333-21.333,21.333H106.667c-11.771,0-21.333-9.573-21.333-21.333v-384     c0-11.76,9.563-21.333,21.333-21.333h42.667c0,23.531,19.146,42.667,42.667,42.667h128c23.521,0,42.667-19.135,42.667-42.667     h42.667c11.771,0,21.333,9.573,21.333,21.333V469.333z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M160,170.667c-17.646,0-32,14.354-32,32c0,17.646,14.354,32,32,32s32-14.354,32-32     C192,185.021,177.646,170.667,160,170.667z M160,213.333c-5.875,0-10.667-4.781-10.667-10.667     c0-5.885,4.792-10.667,10.667-10.667s10.667,4.781,10.667,10.667C170.667,208.552,165.875,213.333,160,213.333z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M160,277.333c-17.646,0-32,14.354-32,32c0,17.646,14.354,32,32,32s32-14.354,32-32     C192,291.688,177.646,277.333,160,277.333z M160,320c-5.875,0-10.667-4.781-10.667-10.667c0-5.885,4.792-10.667,10.667-10.667     s10.667,4.781,10.667,10.667C170.667,315.219,165.875,320,160,320z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M160,384c-17.646,0-32,14.354-32,32c0,17.646,14.354,32,32,32s32-14.354,32-32C192,398.354,177.646,384,160,384z      M160,426.667c-5.875,0-10.667-4.781-10.667-10.667c0-5.885,4.792-10.667,10.667-10.667s10.667,4.781,10.667,10.667     C170.667,421.885,165.875,426.667,160,426.667z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M373.333,192h-128c-5.896,0-10.667,4.771-10.667,10.667c0,5.896,4.771,10.667,10.667,10.667h128     c5.896,0,10.667-4.771,10.667-10.667C384,196.771,379.229,192,373.333,192z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M373.333,298.667h-128c-5.896,0-10.667,4.771-10.667,10.667c0,5.896,4.771,10.667,10.667,10.667h128     c5.896,0,10.667-4.771,10.667-10.667C384,303.438,379.229,298.667,373.333,298.667z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M373.333,405.333h-128c-5.896,0-10.667,4.771-10.667,10.667c0,5.896,4.771,10.667,10.667,10.667h128     c5.896,0,10.667-4.771,10.667-10.667C384,410.104,379.229,405.333,373.333,405.333z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                        </g>
                                    </g>
                                </g></g> </svg>
                        <div class="personal-listing-item__icontext">
                            <?if ($item['UF_STATUS']=='NEED_CHECK') {?>Ждем информацию<?} else { if ($item['UF_STATUS']=='AGREED') {?>Согласование подтверждено<?} else {?>Необходимо согласование<?}}?>
                        </div>
                    </div>
                    <div class="personal-listing-item__arrow"></div>
                    <div class="personal-listing-item__icon <?= ($item['UF_STATUS']=='AGREED') ? '_checked' :  '';?>">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 512.009 512.009" style="enable-background:new 0 0 512.009 512.009;" xml:space="preserve" width="512px" height="512px" class=""><g><g>
                                    <g>
                                        <g>
                                            <path d="M506.275,171.877l-245.333-128c-3.104-1.604-6.771-1.604-9.875,0l-245.333,128c-4.354,2.271-6.594,7.229-5.427,12     c1.177,4.771,5.448,8.125,10.365,8.125h10.667v266.667c0,5.896,4.771,10.667,10.667,10.667h64     c5.896,0,10.667-4.771,10.667-10.667V213.335h298.667v245.333c0,5.896,4.771,10.667,10.667,10.667h64     c5.896,0,10.667-4.771,10.667-10.667V192.002h10.667c4.917,0,9.188-3.354,10.365-8.125     C512.869,179.106,510.629,174.148,506.275,171.877z M469.338,181.335v266.667h-42.667V202.669     c0-5.896-4.771-10.667-10.667-10.667h-320c-5.896,0-10.667,4.771-10.667,10.667v245.333H42.671V181.335     c0-1.5-0.313-2.917-0.865-4.208l214.198-111.76l214.198,111.76C469.65,178.419,469.338,179.835,469.338,181.335z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                            <path d="M373.338,320.002h-128c-5.896,0-10.667,4.771-10.667,10.667v10.667h-96c-5.896,0-10.667,4.771-10.667,10.667v106.667     c0,5.896,4.771,10.667,10.667,10.667h234.667c5.896,0,10.667-4.771,10.667-10.667v-128     C384.004,324.773,379.234,320.002,373.338,320.002z M234.671,448.002h-85.333v-85.333h32v21.333     c0,5.896,4.771,10.667,10.667,10.667s10.667-4.771,10.667-10.667v-21.333h32V448.002z M362.671,448.002H256.004v-96v-10.667     h42.667v32c0,5.896,4.771,10.667,10.667,10.667c5.896,0,10.667-4.771,10.667-10.667v-32h42.667V448.002z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#AFBDC7"/>
                                        </g>
                                    </g>
                                </g></g> </svg>
                        <div class="personal-listing-item__icontext">
                            <?= ($item['UF_STATUS']=='AGREED') ? 'Передано в работу' :  'Передача в работу';?>

                        </div>
                    </div>
                    <div class="personal-listing-item__dots"><i></i><i></i><i></i><i></i><i></i><i></i></div>
                    <div class="personal-listing-item__icon <?= ($item['UF_STATUS']=='CANCEL') ? '_cancel' :  '';?>">
                        <svg xmlns="http://www.w3.org/2000/svg" height="512px" viewBox="-40 0 427 427.00131" width="512px" class=""><g><path d="m232.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0" data-original="#000000" class="" data-old_color="#000000" fill="#AFBDC7"/><path d="m114.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0" data-original="#000000" class="" data-old_color="#000000" fill="#AFBDC7"/><path d="m28.398438 127.121094v246.378906c0 14.5625 5.339843 28.238281 14.667968 38.050781 9.285156 9.839844 22.207032 15.425781 35.730469 15.449219h189.203125c13.527344-.023438 26.449219-5.609375 35.730469-15.449219 9.328125-9.8125 14.667969-23.488281 14.667969-38.050781v-246.378906c18.542968-4.921875 30.558593-22.835938 28.078124-41.863282-2.484374-19.023437-18.691406-33.253906-37.878906-33.257812h-51.199218v-12.5c.058593-10.511719-4.097657-20.605469-11.539063-28.03125-7.441406-7.421875-17.550781-11.5546875-28.0625-11.46875h-88.796875c-10.511719-.0859375-20.621094 4.046875-28.0625 11.46875-7.441406 7.425781-11.597656 17.519531-11.539062 28.03125v12.5h-51.199219c-19.1875.003906-35.394531 14.234375-37.878907 33.257812-2.480468 19.027344 9.535157 36.941407 28.078126 41.863282zm239.601562 279.878906h-189.203125c-17.097656 0-30.398437-14.6875-30.398437-33.5v-245.5h250v245.5c0 18.8125-13.300782 33.5-30.398438 33.5zm-158.601562-367.5c-.066407-5.207031 1.980468-10.21875 5.675781-13.894531 3.691406-3.675781 8.714843-5.695313 13.925781-5.605469h88.796875c5.210937-.089844 10.234375 1.929688 13.925781 5.605469 3.695313 3.671875 5.742188 8.6875 5.675782 13.894531v12.5h-128zm-71.199219 32.5h270.398437c9.941406 0 18 8.058594 18 18s-8.058594 18-18 18h-270.398437c-9.941407 0-18-8.058594-18-18s8.058593-18 18-18zm0 0" data-original="#000000" class="" data-old_color="#000000" fill="#AFBDC7"/><path d="m173.398438 154.703125c-5.523438 0-10 4.476563-10 10v189c0 5.519531 4.476562 10 10 10 5.523437 0 10-4.480469 10-10v-189c0-5.523437-4.476563-10-10-10zm0 0" data-original="#000000" class="" data-old_color="#000000" fill="#AFBDC7"/></g> </svg>
                        <div class="personal-listing-item__icontext">
                            Отмена сделки
                        </div>
                    </div>

                </div>
                <div class="personal-listing-item__shipment">
                    <?foreach ($item['SHIPMENTS'] as $shipment) {?>
                        <div class="personal-listing-item__shipment_item"><?=$shipment['UF_WB_DATE']?> К сделке привязана реализация <a href="/personal/otgruzki/?UF_ID_1C=<?=$shipment['UF_ID_1C']?>"><?=$shipment['UF_ID_1C']?> - <?=$shipment['SUMM']?> руб.</a> - <?=$shipment['UF_WB_STATUS']?></div>
                    <?}?>
                </div>
                <div class="personal-listing-item__buttons">
                    <?if (strtolower($item['UF_LOCK']) !='да' && $item['UF_STATUS']!='NEED_CHECK' && $item['UF_STATUS']!='AGREED' && $item['UF_STATUS']!='CANCEL') {?>
                        <div class="personal-listing-preloader hidden-block js-personal-listing-preloader"></div>

                        <div class="personal-listing-item-viewing viewing-block">
                            <button class="personal-listing-item__edit js-deals-edit">Редактировать</button>
                            <button class="personal-listing-item__conform js-deals-conform">Согласовать</button>
                            <button class="personal-listing-item__cancel js-deals-cancel">Отменить сделку</button>
                        </div>
                        <div class="personal-listing-item-editing edition-block">
                            <!--button class="personal-listing-item__save js-deals-save">Сохранить и отправить </button-->
                            <button class="personal-listing-item__save js-deals-save button-loading-save">
                                <span class="button-loading-submit">Сохранить и отправить</span>
                                <span class="loading"><i class="fa fa-refresh"></i></span>
                                <span class="check"><i class="fa fa-check"></i></span>
                            </button>
                            <button class="personal-listing-item__notsave js-deals-notsave">Отменить правки</button>

                        </div>
                    <? } ?>
                </div>


                <div class="personal-listing-item__table">
                    <div class="personal-listing-item__th">
                        <div class="personal-listing-item__npp"></div>
                        <div class="personal-listing-item__name">Наименование</div>
                        <div class="personal-listing-item__quantity">Кол-во</div>
                        <div>Цена, руб.</div>
                        <div>Сумма, руб.</div>
                        <div>Поставка</div>
                        <div class="personal-listing-item__tdbadge"></div>
                        <div class="personal-listing-item__tddel edition-block"></div>
                    </div>
                    <?$kn = 1;
                    $all_weight = 0;
                    $all_volume = 0;
                    foreach ( $item['ITEMS_ARRAY'] as $good) {
                        $good['MULT'] = $arResult['ITEMS'][$i]['FROM_CATALOG'][$good['НомерСтроки']]['PROPERTY_PRODUCT_MULTIPLICITY_VALUE'];
                        $good['WEIGHT'] = $arResult['ITEMS'][$i]['FROM_CATALOG'][$good['НомерСтроки']]['PROPERTY_PRODUCT_WEIGHT_VALUE'];
                        $good['VOLUME'] = $arResult['ITEMS'][$i]['FROM_CATALOG'][$good['НомерСтроки']]['PROPERTY_PRODUCT_VOLUME_VALUE'];
                        $q_multiplicity = ($good['MULT']>0) ? $good['MULT'] : 1;
                        $all_weight += floatval(str_replace(',','.',$good['WEIGHT']))*$good['Количество'];
                        $all_volume += floatval(str_replace(',','.',$good['VOLUME']))*$good['Количество'];

                        $good['DETAIL_PAGE_URL'] = (isset($arResult['ITEMS'][$i]['FROM_CATALOG'][$good['НомерСтроки']])) ? $arResult['ITEMS'][$i]['FROM_CATALOG'][$good['НомерСтроки']]['DETAIL_PAGE_URL'] : '';
                        $good['POSTAVKA'] = (isset($arResult['ITEMS'][$i]['FROM_CATALOG'][$good['НомерСтроки']])) ? 'Склад' : 'Под заказ';
                        $good['STICKERS'] = $arResult['ITEMS'][$i]['FROM_CATALOG'][$good['НомерСтроки']]['PROPERTY_STICKERS_VALUE'];
                        if (is_array($good['STICKERS']) && !empty($good['STICKERS'])) {
                            foreach($good['STICKERS'] as $sticker){
                                if ($sticker == 'Под заказ') {
                                    $good['POSTAVKA'] = 'Под заказ';
                                }
                            }
                        }
                        ?>
                        <div class="personal-listing-item__tr" data-good-id="<?=$good['НомерСтроки']?>">
                            <div class="personal-listing-item__npp"><?=$kn?>.</div><?$kn++;?>
                            <div class="personal-listing-item__name"><a href="<?=$good['DETAIL_PAGE_URL']?>"><?=$good['Номенклатура']?></a><span><?=$good['НоменклатураКод']?></span></div>
                            <div class="personal-listing-item__quantity personal-listing-item__unit">
                                <span>Кол-во</span>
                                <div class="viewing-block">
                                    <span ><b><?=$good['Количество']?></b> <?=$good['ЕдиницаИзмерения']?></span>
                                    <?if (isset($arResult['ITEMS'][$i]['FROM_ORDER'][$good['НомерСтроки']]['QUANTITY'])) {
                                        $diffQ =  $arResult['ITEMS'][$i]['FROM_ORDER'][$good['НомерСтроки']]['QUANTITY'] - $good['Количество'];?>
                                        <span class="personal-listing-item__triangle <?= ($diffQ>0) ? ' _less' : '';?>"></span>
                                        <span class="personal-listing-item__quantity _old"><?=$arResult['ITEMS'][$i]['FROM_ORDER'][$good['НомерСтроки']]['QUANTITY']?> <?=$good['ЕдиницаИзмерения']?></span>
                                    <?}?>
                                </div>
                                <div class="edition-block">
                                    <div class="numeric _multiply">
                                        <button class="products-list-item-quantity-decrease numeric-decrease"><span>-<?= ($q_multiplicity>1) ? $q_multiplicity : ''?></span></button>

                                        <input class="numeric-ease _not_used" data-multiplicity="<?=$q_multiplicity?>" data-product-reserve="<?=$good['Количество']?>" type="text" name="quantity" value="<?=$good['Количество']?>" disabled="disabled">

                                        <button class="products-list-item-quantity-increase numeric-increase _not_used"><span>+<?= ($q_multiplicity>1) ? $q_multiplicity : ''?></span></button>
                                    </div>
                                </div>

                            </div>

                            <div class="personal-listing-item__price personal-listing-item__unit">
                                <span>Цена, руб.</span>
                                <span><?=$good['Цена']?></span>
                                <?if (isset($arResult['ITEMS'][$i]['FROM_ORDER'][$good['НомерСтроки']]['PRICE'])) {
                                    $diffP = $arResult['ITEMS'][$i]['FROM_ORDER'][$good['НомерСтроки']]['PRICE'] - $good['Цена'];
                                    $diffP = floatval($arResult['ITEMS'][$i]['FROM_ORDER'][$good['НомерСтроки']]['PRICE']) - floatval(str_replace(',','.',str_replace(' ','', $good['Цена'])));
                                    if ($diffP !=0) {?>
                                        <span class="personal-listing-item__triangle <?= ($diffP>0) ? ' _less' : '';?> viewing-block"></span>
                                        <span class="personal-listing-item__price _old viewing-block"><?=$arResult['ITEMS'][$i]['FROM_ORDER'][$good['НомерСтроки']]['PRICE']?></span>
                                    <?}?>
                                <?}?>
                            </div>
                            <div class="personal-listing-item__summ personal-listing-item__unit"><span>Сумма, руб.</span><span><?=$good['Сумма']?></span></div>
                            <div class="personal-listing-item__postavka<?= ($item['UF_STATUS']=='NEED_CHECK') ? ' _wait' : ''?>">
                                <?= ($item['UF_STATUS']=='NEED_CHECK') ? 'Уточняем' : $good['POSTAVKA']?>
                            </div>
                            <div class="personal-listing-item__tdbadge">
                                <?if (!empty($item['SHIPMENTS'])) {?>
                                    <?if ($item['FROM_SHIPMENT'][$good['НомерСтроки']]) {?>
                                        <img src="/local/templates/elfgroup/assets/img/icon_sh_ok.png" alt="" title="Отгружено полностью">
                                        <?} else {?>
                                        <img src="/local/templates/elfgroup/assets/img/icon_sh_not.png" alt="" title="Отгружено частично или не отгружено">
                                        <?}?>
                                <?}?>
                                <?if ($good['Коммент']!='') {?>
                                <div class="personal-listing-item__comment" title="<?=$good['Коммент']?>">
                                </div>
                                <?}?>
                            </div>
                            <div class="personal-listing-item__tddel edition-block js-flexing"><div class="personal-listing-item__delete edition-block"></div></div>
                            <div class="personal-listing-item__recover">
                                Товар удален из сделки. <span class="js-tem-recover">Восстановить <svg xmlns="http://www.w3.org/2000/svg" id="Capa_1" enable-background="new 0 0 515.556 515.556" height="512px" viewBox="0 0 515.556 515.556" width="512px"><g><path d="m386.667 96.667h-225.556v64.444h225.556c35.542 0 64.444 28.902 64.444 64.444s-28.902 64.444-64.444 64.444h-290v-64.444l-96.667 96.667 96.667 96.667v-64.444h290c71.068 0 128.889-57.821 128.889-128.889s-57.821-128.889-128.889-128.889z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#ABABAB"/></g> </svg></span>
                                <!--<svg xmlns="http://www.w3.org/2000/svg" id="Capa_1" enable-background="new 0 0 551.13 551.13" height="512px" viewBox="0 0 551.13 551.13" width="512px"><g><path d="m413.348 103.337h-241.12v34.446h241.119c56.983 0 103.337 46.353 103.337 103.337s-46.354 103.337-103.337 103.337h-327.233v-68.891l-86.114 86.113 86.114 86.114v-68.891h327.234c75.972 0 137.783-61.81 137.783-137.783s-61.811-137.782-137.783-137.782z" data-original="#000000" class="active-path" data-old_color="#000000" fill="#ABABAB"/></g> </svg>-->
                            </div>
                        </div>
                    <? } ?>
                    <?/*TODO временно скрыто
                    <div class="personal-listing-item-search edition-block">
                        <div class="personal-listing-item-search__query">
                            <input class="personal-listing-item-search__input" type="text" value="" placeholder="Добавить по Коду товара">
                            <button class="personal-listing-item-search__button"></button>
                        </div>
                        <div class="personal-listing-item-search__result"><span class="errortext">Не найдено</span></div>
                    </div>*/?>
                    <div class="personal-listing-item__itog">
                        <div class="personal-listing-item__td _itogo">Итого</div>
                        <div class="personal-listing-item__sumtext"><span><?=$item['UF_ITEMS_COUNT']?> тов.</span><span><?=$all_weight?> кг</span> <span><?=$all_volume?> м<sup>3</sup></span> <span> <?=$item['UF_ITEMS_SUMM']?> руб.</span></div>
                    </div>
                </div>
            </div>
            <div class="personal-listing-item__bottom">
                <div class="personal-listing-preloader hidden-block js-personal-listing-preloader"></div>
                <a class="personal-listing-item__more js-personal-listing-item-more" href="">Подробнее</a>
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



