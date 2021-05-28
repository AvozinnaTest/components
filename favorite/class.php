<?php defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Application;
use Bitrix\Main\Web\Cookie;

class DialFavorite extends CBitrixComponent
{
	/**
	 * @return array|mixed
	 */
	public function executeComponent()
	{
		if ($this->arParams['LIST_PAGE'] === 'Y') {
			$this->arResult = self::getFavoriteItems($this->arParams);
			$this->includeComponentTemplate();
			return $this->arResult;
		}
		$this->includeComponentTemplate();
	}


	public static function getUserFavorite()
	{
		global $USER;
		try {
			if ($USER->IsAuthorized()) {
				$idUser = $USER->GetID();
				$rsUser = CUser::GetByID($idUser);
				$arUser = $rsUser->Fetch();
				$currentFav = $arUser['UF_FAVORITES'];
			} else {
				$currentFav = Application::getInstance()->getContext()->getRequest()->getCookie("favorites");
			}
			return unserialize($currentFav);
		} catch (Exception $e) {
			AddMessage2Log($e->getMessage());
		}

	}

    public static function getFavoriteItems($params = array())
    {
        global $USER;
        try {
            $favItems = self::getUserFavorite();
            $dateSort = $favItems;
            if ($favItems && CModule::IncludeModule("iblock")) {
                $arFilter = ['ID' => $favItems];
                if ($params['SEARCH_TEXT'] !== '') {
                    $arFilter['?SEARCHABLE_CONTENT'] = $params['SEARCH_TEXT'];
                }

                $res = CIBlockElement::GetList(
                    [
                        'SORT' => 'ID',
                        'ID'   => 'DESC'
                    ],
                    $arFilter,
                    false,
                    false,
                    ['*']
                );
                $arItems = [];
                while ($ob = $res->GetNextElement(true, false)) {
                    $arRes = $ob->GetFields();
                    $arRes['PROPERTIES'] = $ob->GetProperties();
                    $arRes['PREVIEW_PICTURE'] = CFile::GetFileArray($arRes['PREVIEW_PICTURE']);
                    $arRes['DETAIL_PICTURE'] = CFile::GetFileArray($arRes['DETAIL_PICTURE']);
                    $offers = CCatalogSKU::getOffersList($arRes['ID']);
                    if ($offers[$arRes['ID']]) {
                        $arRes['actualOffer'] =  array_shift($offers[$arRes['ID']])['ID'];
                    }
                    $arRes['PRICENUM'] = CPrice::GetBasePrice($arRes['actualOffer'])['PRICE'];
                    $arItems[] = $arRes;
                }
                $order = ($params['SORT_BY']!='') ? $params['ORDER_BY'] : 'ASC';
                if ($params['SORT_BY'] == 'price' ) {
                    usort($arItems, function ($item1, $item2) {
                        return $item1['PRICENUM'] <=> $item2['PRICENUM'];
                    });
                }
                else {
                    $sortbyDate = array_flip($dateSort);
                    usort($arItems, function($a,$b) use($sortbyDate){
                        return $sortbyDate[$a['ID']] - $sortbyDate[$b['ID']];
                    });
                }

                if ($order == 'DESC') krsort($arItems);
                $favItems['ITEMS'] = $arItems;

            }
            return $favItems;
        } catch (Exception $e) {
            AddMessage2Log($e->getMessage());
        }

    }

	public static function setUserFavorite($favorite)
	{
		global $USER;
		try {
			if (intval($favorite) > 0) {

				if (!$USER->IsAuthorized()) {
					$application = Application::getInstance();
					$context = $application->getContext();
					$request = $context->getRequest();
					$currentFav = $request->getCookie("favorites");
					$arElements = unserialize($currentFav);
					if (!in_array($favorite, $arElements))
						$arElements[] = $favorite;
					$cookie = new Cookie("favorites", serialize($arElements), time() + 60 * 60 * 24 * 60);
					$cookie->setDomain($context->getServer()->getHttpHost());
					$cookie->setHttpOnly(false);
					$context->getResponse()->addCookie($cookie);
					$context->getResponse()->flush("");
				} else {
					$idUser = $USER->GetID();
					$rsUser = CUser::GetByID($idUser);
					$arUser = $rsUser->Fetch();
					if (!array_key_exists('UF_FAVORITES', $arUser)) {
						$arFields = [
							"ENTITY_ID" => "USER",
							"FIELD_NAME" => "UF_FAVORITES",
							"USER_TYPE_ID" => "string",
							"EDIT_FORM_LABEL" => ["ru" => "Избранное", "en" => "Favorite"]
						];
						$obUserField = new CUserTypeEntity;
						$obUserField->Add($arFields);
						$arUser['UF_FAVORITES'] = '';
					}
					$arElements = unserialize($arUser['UF_FAVORITES']);

					if (!in_array($favorite, $arElements))
						$arElements[] = $favorite;

					$USER->Update($idUser, ["UF_FAVORITES" => serialize($arElements)]);
				}
				return [
					'ACTION' => 'ADD',
					'result' => 'success',
					'count'  => count($arElements)
				];
			}
		} catch (Exception $e) {
			AddMessage2Log($e->getMessage());
		}

	}

	public static function removeUserFavorite($favorite)
	{
		global $USER;
		try {
			if (intval($favorite) > 0) {

				if (!$USER->IsAuthorized()) {
					$application = Application::getInstance();
					$context = $application->getContext();
					$request = $context->getRequest();
					$currentFav = $request->getCookie("favorites");
					$arElements = unserialize($currentFav);
					if (($key = array_search($favorite, $arElements)) !== false) {
						unset($arElements[$key]);
					}
					$cookie = new Cookie("favorites", serialize($arElements), time() + 60 * 60 * 24 * 60);
					$cookie->setDomain($context->getServer()->getHttpHost());
					$cookie->setHttpOnly(false);
					$context->getResponse()->addCookie($cookie);
					$context->getResponse()->flush("");
				} else {
					$idUser = $USER->GetID();
					$rsUser = CUser::GetByID($idUser);
					$arUser = $rsUser->Fetch();
					if (!array_key_exists('UF_FAVORITES', $arUser)) {
						$arFields = [
							"ENTITY_ID" => "USER",
							"FIELD_NAME" => "UF_FAVORITES",
							"USER_TYPE_ID" => "string",
							"EDIT_FORM_LABEL" => ["ru" => "Избранное", "en" => "Favorite"]
						];
						$obUserField = new CUserTypeEntity;
						$obUserField->Add($arFields);
						$arUser['UF_FAVORITES'] = '';
					}
					$arElements = unserialize($arUser['UF_FAVORITES']);

					if (($key = array_search($favorite, $arElements)) !== false) {
						unset($arElements[$key]);
					}

					$USER->Update($idUser, ["UF_FAVORITES" => serialize($arElements)]);
				}
				return [
					'ACTION' => 'DELETE',
					'result' => 'success',
					'count'  => count($arElements)
				];
			}
		} catch (Exception $e) {
			AddMessage2Log($e->getMessage());
		}

	}

    public static function searchFavorite($text){
        try {
            $favItems = self::getUserFavorite();
            if ($favItems && CModule::IncludeModule("iblock")) {
                $res = CIBlockElement::GetList(
                    [
                        'SORT' => 'ASC',
                        'ID'   => 'DESC'
                    ],
                    [
                        'ID' => $favItems,
                        '?NAME' => $text
                    ],
                    false,
                    false,
                    ['*']
                );

                while ($ob = $res->GetNextElement(true, false)) {
                    $arRes = $ob->GetFields();
                    $arRes['PROPERTIES'] = $ob->GetProperties();
                    $arRes['PREVIEW_PICTURE'] = CFile::GetFileArray($arRes['PREVIEW_PICTURE']);
                    $arRes['DETAIL_PICTURE'] = CFile::GetFileArray($arRes['DETAIL_PICTURE']);
                    $arRes['MIN_PRICE']['VALUE'] = $arRes['PROPERTIES']['MINIMUM_PRICE_' . PRICE_ID]['VALUE'];
                    $arRes['PRICE'] = CurrencyFormat(CPrice::GetBasePrice($arRes['ID'])['PRICE'], 'RUB');
                    $offers = CCatalogSKU::getOffersList($arRes['ID']);
                    if ($offers[$arRes['ID']]) {
                        $arRes['actualOffer'] =  array_shift($offers[$arRes['ID']])['ID'];
                    }
                    $favItems['ITEMS'][] = $arRes;
                }
            }
            return $favItems['ITEMS'];
        } catch (Exception $e) {
            AddMessage2Log($e->getMessage());
        }
    }

	public static function OnAfterUserAuthorizeHandler(&$arFields)
	{
		try {
			if ($arFields["user_fields"]['ID'] > 0) {
				$application = Application::getInstance();
				$context = $application->getContext();
				$request = $context->getRequest();

				$currentFav = $request->getCookie("favorites");
				$currentFav = unserialize($currentFav);
				global $USER;
				$idUser = $arFields["user_fields"]['ID'];
				$rsUser = \CUser::GetByID($idUser);
				$arUser = $rsUser->Fetch();
				$arElements = unserialize($arUser['UF_FAVORITES']);
				if ($currentFav) {
					foreach ($currentFav as $id) {
						if (!in_array($id, $arElements))
							$arElements[] = $id;
					}
					$USER->Update($idUser, Array("UF_FAVORITES" => serialize($arElements)));
				}
				$cookie = new Cookie("favorites", '', time() - 60);
				$cookie->setDomain($context->getServer()->getHttpHost());
				$cookie->setHttpOnly(false);

				$context->getResponse()->addCookie($cookie);
				$context->getResponse()->flush("");
			}
		} catch (Exception $e) {
			AddMessage2Log($e->getMessage());
		}
	}
}