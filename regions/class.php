<?php defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Application;
use Bitrix\Main\Web\Cookie;

class DialRegion extends CBitrixComponent
{
	/**
	 * @return array|mixed
	 */
	public function executeComponent()
	{
        $current = self::getUserCity();
        $allCities = self::getCitiesList();


        $application = Application::getInstance();
        $context = $application->getContext();
        $request = $context->getRequest();
        $userNotSure = $request->getCookie("USER_CITY_NOTSURE");
        //p($current);
        $this->arResult = [
            'CURRENT_ID' => $current['CITY_ID'],
            'CURRENT_NAME' => $current['CITY_NAME'],
            'CITIES' => $allCities,
            'USER_CITY_NOTSURE' => ($userNotSure == 1) ? true : false,
        ];
		$this->includeComponentTemplate();
        return $current['CITY_ID'];
	}

    public static function getCitiesList($params = array())
    {
        $cur_result = false;
        $obCache = new CPHPCache;
        $life_time = 3600*24;
        $cache_id = 'locationslist';
        if($obCache->InitCache($life_time, $cache_id)) {
            $vars = $obCache->GetVars();
            $cur_result = $vars["locations_list"];
        }
        else {
            try {
                $cities = [];
                \Bitrix\Main\Loader::includeModule('sale');
                $res = \Bitrix\Sale\Location\LocationTable::getList(array(
                    'order' => array('NAME_RU' => 'ASC'),
                    'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, 'TYPE_ID' => 5),
                    'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
                ));
                $defaultCities = ['Москва', 'Санкт-Петербург', 'Нижний Новгород'];
                $defaultCitiesArr = [];
                while ($item = $res->fetch()) {
                    //p($item);
                    if (!in_array($item['NAME_RU'], $defaultCities))
                        $cities[$item['ID']] = $item['NAME_RU'];
                    else
                        $defaultCitiesArr[$item['ID']] = $item['NAME_RU'];
                }
                $cities = $defaultCitiesArr + $cities;
                //p($cities);
                $cur_result = $cities;
                if($obCache->StartDataCache()) {
                    $obCache->EndDataCache(array(
                        "locations_list" => $cur_result
                    ));
                }
            } catch (Exception $e) {
                AddMessage2Log($e->getMessage());
            }
        }
        return $cur_result;
    }

	public static function getUserCity()
	{
		$userCity = false;
        $userCityCookie = 0;
        $application = Application::getInstance();
        $context = $application->getContext();
        $request = $context->getRequest();
        $userCityCookie = $request->getCookie("USER_CITY");
        $allCities = self::getCitiesList();
       // print_r($userCityCookie);
		try {
				if ($userCityCookie > 0 && !is_null($userCityCookie)) { //определить город
                    $userCity = $userCityCookie;
				}
				else {

                    $application = Application::getInstance();
                    $context = $application->getContext();
                    $request = $context->getRequest();
                    $ip = \Bitrix\Main\Service\GeoIp\Manager::getRealIp();
                    $geoIpData = \Bitrix\Main\Service\GeoIp\Manager::getDataResult($ip,LANGUAGE_ID);
                    $defaultCity = $geoIpData->getGeoData()->cityName;
                    if (is_null($defaultCity) || $defaultCity==''  || $ip == false) $defaultCity = 'Москва';
                    $userCity = array_search($defaultCity, $allCities);
                    $cookie = new Cookie("USER_CITY", $userCity, time() + 60 * 60 * 24 * 60);
                    $cookie->setDomain($context->getServer()->getHttpHost());
                    $cookie->setHttpOnly(false);
                    $context->getResponse()->addCookie($cookie);
                    $context->getResponse()->flush("");

                    $cookie = new Cookie("USER_CITY_NOTSURE", 1, time() + 60 * 60 * 24 );
                    $cookie->setDomain($context->getServer()->getHttpHost());
                    $cookie->setHttpOnly(false);
                    $context->getResponse()->addCookie($cookie);
                    $context->getResponse()->flush("");
                }
				return [
				    'CITY_ID' => $userCity,
				    'CITY_NAME' => $allCities[$userCity], //$userCity,
				];

		} catch (Exception $e) {
			AddMessage2Log($e->getMessage());
		}

	}

    public static function getUserCityCode()
    {
        $application = Application::getInstance();
        $context = $application->getContext();
        $request = $context->getRequest();
        $userCityCookie = $request->getCookie("USER_CITY");
        $allCities = self::getCitiesCodes();
        $code = '0000073738';
        if ($userCityCookie > 0 && !is_null($userCityCookie)) {
            $code = $allCities[$userCityCookie];
        }
        return $code;
    }

    public static function getCitiesCodes($params = array())
    {
        $cur_result = false;
        $obCache = new CPHPCache;
        $life_time = 3600*24;
        $cache_id = 'locationscodes';
        if($obCache->InitCache($life_time, $cache_id)) {
            $vars = $obCache->GetVars();
            $cur_result = $vars["locations_codes"];
        }
        else {
            try {
                $cities = [];
                \Bitrix\Main\Loader::includeModule('sale');
                $res = \Bitrix\Sale\Location\LocationTable::getList(array(
                    'order' => array('NAME_RU' => 'ASC'),
                    'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, 'TYPE_ID' => 5),
                    'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE')
                ));
                $defaultCitiesArr = [];
                while ($item = $res->fetch()) {
                    $cities[$item['ID']] = $item['CODE'];
                }
                $cities = $defaultCitiesArr + $cities;
                //p($cities);
                $cur_result = $cities;
                if($obCache->StartDataCache()) {
                    $obCache->EndDataCache(array(
                        "locations_codes" => $cur_result
                    ));
                }
            } catch (Exception $e) {
                AddMessage2Log($e->getMessage());
            }
        }
        return $cur_result;
    }

    public static function setUserCity($id)
    {
        try {
            if ($id > 0) {
                $application = Application::getInstance();
                $context = $application->getContext();
                $request = $context->getRequest();
                $cookie = new Cookie("USER_CITY", $id, time() + 60 * 60 * 24 * 60);
                $cookie->setDomain($context->getServer()->getHttpHost());
                $cookie->setHttpOnly(false);
                $context->getResponse()->addCookie($cookie);
                $context->getResponse()->flush("");
            }
            return [
                'ACTION' => 'CHANGE',
                'result' => 'success',
            ];

        } catch (Exception $e) {
            AddMessage2Log($e->getMessage());
        }

    }


    public static function searchCity($text){
        try {

            $allCities = self::getCitiesList();
            $searchedCities = preg_grep("/$text/i", $allCities);
            $searchResultStr = '';
            foreach ($searchedCities as $id=>$city) {
                $searchResultStr .= '<li class="popup-city__item"><a href="#" class="popup-city__link _bold" data-id="'.$id.'" onclick="var regions = new DialRegions(this); regions.setCity();">'.$city.'</a></li>';
            }

            return [
                'text' => $text,
                'searchedresult' => $searchedCities,
                'searchResultStr' => $searchResultStr,
                'JSCLASS' => '/local/components/dial/regions/templates/.default/script.js',
                'ACTION' => 'SEARCH',
                'result' => 'success',
            ];
        } catch (Exception $e) {
            AddMessage2Log($e->getMessage());
        }
    }

    public static function removeCookieCity(){
        try {
            $application = Application::getInstance();
            $context = $application->getContext();
            $cookie = new Cookie("USER_CITY_NOTSURE", 1, time() -100 );
            $cookie->setDomain($context->getServer()->getHttpHost());
            $cookie->setHttpOnly(false);
            $context->getResponse()->addCookie($cookie);
            $context->getResponse()->flush("");
            return [
                'ACTION' => 'ISSURE',
                'result' => 'success',
            ];

        } catch (Exception $e) {
            AddMessage2Log($e->getMessage());
        }
    }

}