<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc as Loc;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

class CityChange extends \CBitrixComponent
{
    /**
     * cache keys in arResult
     * @var array()
     */
    protected $cacheKeys = array();

    /**
     * add parameters from cache dependence
     * @var array
     */
    protected $cacheAddon = array();

    /**
     * pager navigation params
     * @var array
     */
    protected $navParams = array();

    /**
     * include lang files
     */
    public function onIncludeComponentLang()
    {
        $this->includeComponentLang(basename(__FILE__));
        Loc::loadMessages(__FILE__);
    }

    /**
     * prepare input params
     * @param array $params
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        $params = parent::onPrepareComponentParams($params);
        //$params['CACHE_TIME'] =  intval($params['CACHE_TIME']) > 0 ? intval($params['CACHE_TIME']) : 3600;

        return $params;
    }

    /**
     * read data from cache or not
     * @return bool
     */
    protected function readDataFromCache()
    {
        //if ($this->arParams['CACHE_TYPE'] == 'N') // no cache
        return false;
        //return !($this->StartResultCache(3600, $this->cacheAddon));
    }

    /**
     * cache arResult keys
     */
    protected function putDataToCache()
    {
        if (is_array($this->cacheKeys) && sizeof($this->cacheKeys) > 0)
        {
            $this->SetResultCacheKeys($this->cacheKeys);
        }
    }

    /**
     * abort cache process
     */
    protected function abortDataCache()
    {
        $this->AbortResultCache();
    }

    /**
     * check needed modules
     * @throws Main\LoaderException
     */
    protected function checkModules()
    {
        if (!Main\Loader::includeModule('iblock') || !Main\Loader::includeModule('catalog'))
            throw new Main\LoaderException(Loc::getMessage('STANDARD_ELEMENTS_LIST_CLASS_IBLOCK_MODULE_NOT_INSTALLED'));

    }

    /**
     * check required input params
     * @throws Main\ArgumentNullException
     */
    protected function checkParams()
    {


    }

    /**
     * some actions before cache
     */
    protected function executeProlog()
    {

    }

    /**
     * get Nearest City with magazine
     * @param $x1
     * @param $y1
     * @return bool
     */
    protected static function getNearestCity($x1, $y1) {
        $obCache = new \CPHPCache;
        $life_time = 360000;
        $cache_id = 'all_city_list';
        $allCities = array();
        if($obCache->InitCache($life_time, $cache_id, "/")):
            $vars = $obCache->GetVars();
            $allCities = $vars["allCitys"];
        else :
            if($obCache->StartDataCache($life_time, $cache_id, "/")):
                $res = CCatalogStore::GetList(
                    array(),
                    array('ACTIVE' => 'Y',
                        "!GPS_N"=>false,
                        "!GPS_S"=>false),
                    false,
                    false,
                    array("ID","TITLE","ACTIVE")
                );


                while($arFields = $res->GetNext()){
                    $allCities[] = $arFields;
                }

                $obCache->EndDataCache(array(
                    "allCitys" => $allCities
                ));
            endif;
        endif;

        $path = 0;
        $gid = false;
        foreach ($allCities as $arFields) {
            $x2 = $arFields['GPS_N'];
            $y2 = $arFields['GPS_S'];
            $a = abs($x1 - $x2);
            $b = abs($y1 - $y2);
            $c = sqrt($a*$a + $b*$b);
            if ($path == 0) {
                $gid = $arFields["TITLE"];
                $path = $c;
            } else if ((float)$c <= (float)$path) {
                $gid = $arFields["TITLE"];
                $path = $c;
            }
        }


        $obCache = new \CPHPCache;
        $life_time = 360000;
        $cache_id = 'detectedCity'.$gid;


        $cityName = false;
        if($obCache->InitCache($life_time, $cache_id, "/")):
            $vars = $obCache->GetVars();
            $cityName = $vars["cityName"];
        else :
            if($obCache->StartDataCache($life_time, $cache_id, "/")):
                $res = CIBlockSection::GetByID($gid);
                if($ar_res = $res->Fetch())
                    $cityName =  $ar_res['TITLE'];
                $obCache->EndDataCache(array(
                    "cityName" => $cityName
                ));
            endif;
        endif;

        return $cityName;

    }

    /**
     * @param $name
     * @param $value
     * @param int $time
     * @param string $path
     * @param string $domain
     * @return bool
     */
    protected static function setCookie($name, $value, $time = 3600000, $path = '/', $domain = '.santechsystemy.ru') {
        setcookie($name, rawurlencode($value), time()+$time, $path,  $domain);
        $_COOKIE["my_city"] = rawurlencode($value);
        return true;
    }

    /**
     * detect User city
     * @throws Main\LoaderException
     */
    protected static function detectCity() {
        $nearCity = false;
        if(Main\Loader::IncludeModule("olegpro.ipgeobase"))
        {
            $arData = \Olegpro\IpGeoBase\IpGeoBase::getInstance()->getRecord();
            $detectedCity = $arData['city'];

            self::setCookie('my_city', $detectedCity);

            //ближайший город с магазином
            $x1 = $arData["lat"];
            $y1 = $arData["lng"];

            $nearCity = $detectedCity; //self::getNearestCity($x1, $y1);
            self::setCookie('city', $nearCity);
        }

        return $nearCity;
    }

    /**
     * @param $arCity
     * @return bool|string
     * @throws Main\LoaderException
     */
    public static function getCity($arCity) {
        //домен из текущего url

        //если мы зашли не на конкретный поддомен
        if (!$arCity) {
            //город по умолчанию
            $city = "Москва";
            //проверяем выбрал ли пользователь город
            if(isset($_COOKIE["my_city"])){
                $city = rawurldecode($_COOKIE["my_city"]);
            }else {
                //если не выбрал получаем города
                $city = self::detectCity();
            }
        } else {
            //город берем для поддомена
            $city = $arCity;
        }

        return $city;
    }


    /**
     * get domainsList
     * @return array
     * @throws Main\ArgumentException
     */
    public static function getArCity()
    {
        $obCache = new \CPHPCache;
        $life_time = 360000;
        $cache_id = 'domainArr';

        $arAllDomains = array();
        if ($obCache->InitCache($life_time, $cache_id, "/")) {
            //if(false):
            $vars = $obCache->GetVars();
            $arAllDomains = $vars["arAllDomains"];
        } else {
            if($obCache->StartDataCache($life_time, $cache_id, "/")){
                $obDomain = \Bitrix\Catalog\StoreTable::getList(array(
                    'runtime' => array(
                        "SEO_CITY" => array(
                            "data_type" => Bitrix\Iblock\ElementTable::getEntity(),
                            "reference" => array('=this.TITLE' => 'ref.NAME', 'ref.IBLOCK_ID' => new Main\DB\SqlExpression('37')),
                        ),
                        "DOMAIN" => array(
                            "data_type" => \Bitrix\Iblock\DiElementPropertyTable::getEntity(),
                            "reference" => array('=this.SEO_CITY.ID' => 'ref.IBLOCK_ELEMENT_ID', 'ref.IBLOCK_PROPERTY_ID' => new Main\DB\SqlExpression('741')),
                        ),
                        "REGION" => array(
                            "data_type" => \Bitrix\Iblock\DiElementPropertyTable::getEntity(),
                            "reference" => array('=this.SEO_CITY.ID' => 'ref.IBLOCK_ELEMENT_ID', 'ref.IBLOCK_PROPERTY_ID' => new Main\DB\SqlExpression('1320')),
                        ),
                    ),
                    'filter' => array(
                        'ACTIVE' => 'Y',
                    ),
                    'order' => array('ID' => 'ASC'),
                    'select' => array('ID', 'TITLE', 'ADDRESS', 'DOMAIN.VALUE', 'REGION.VALUE')
                ));

                while ($arDomain = $obDomain->fetch()) {

                    $addr = explode(',', $arDomain['ADDRESS']);
                    $c = array_shift($addr);
                    $map ='<div width="600" height="300"><img style="border-radius:3px" alt="карта" src="/design/img/map.jpg">
				<div class="footerimg1" style="padding-bottom:145px;"><span class="footertxt">'.$arDomain['TITLE'].'</span>,'.implode(',',$addr).'</div>
				</div>';

                    if ($arDomain['CATALOG_STORE_DOMAIN_VALUE'] == '')
                        $arDomain['CATALOG_STORE_DOMAIN_VALUE'] = "default";
                    $arAllDomains['DOMAINS'][$arDomain['TITLE']] = $arDomain['CATALOG_STORE_DOMAIN_VALUE'];
                    $arAllDomains['MAPS'][$arDomain['TITLE']] = $map;
                    $arAllDomains['DESCRIPTION'][$arDomain['TITLE']] = $arDomain['ADDRESS'];
                    $arAllDomains['REGION'][$arDomain['TITLE']] = substr($arDomain['CATALOG_STORE_REGION_VALUE'],3);
                }

                $obCache->EndDataCache(array(
                    "arAllDomains" => $arAllDomains
                ));
            }
        }

        return $arAllDomains;
    }


    /**
     * @param $arCity
     * @return array
     */
    public static function findDomain($arCity) {

        $domain = parse_url($_SERVER['HTTP_HOST']);
        $domain = $domain['path'];
        $subDomain = false;
        $arSubDomain = array_flip($arCity);
        //проверяем на www
        if (strripos($domain, 'www.') !== false) {
            $domain = str_replace('www.', '', $domain);
        }

        $reg = explode(".", $domain);



        if (count($reg) > 2) {
            $subDomain = $reg[0];
            array_shift($reg);
        }
        $domain = implode('.', $reg);



        $currentPage = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        return array(
            'DOMAIN' => $domain,
            'SUBDOMAIN' => $subDomain,
            'SUBDOMAIN_NAME' => $arSubDomain[$subDomain],
            'CURPAGE' => $currentPage
        );

    }


    protected  function getMagazines($city = 'default') {
        global $APPLICATION;
        $obCache = new \CPHPCache;
        $life_time = 360000;
        $cache_id = 'magzineArr'.$city;
        if ($APPLICATION->GetCurDir() == '/about/contacts/all/')
            $cache_id = 'magzineArrAll';

        $arMagazine = array();
        if($obCache->InitCache($life_time, $cache_id, "/")):
            $vars = $obCache->GetVars();
            $arMagazine = $vars["arMagazine"];
        else :
            if($obCache->StartDataCache($life_time, $cache_id, "/")):

                $arSelect = Array(
                    "ID",
                    "TITLE",
                    "ACTIVE",
                    "GPS_N",
                    "GPS_S",
                    "ADDRESS",
                    "DESCRIPTION",
                    "SCHEDULE",
                    "UF_TYPE",
                    "UF_TEXT_HOW_GO",
                    "UF_HOW_TO_GO",
                    "UF_RECIEVE_CASH",
                    "UF_RECIEVE_CARD",
                );



                $arFilter = Array(
                    "ACTIVE_DATE"=>"Y",
                    "ACTIVE"=>"Y",
                    "!GPS_N"=>false,
                    "!GPS_S"=>false,
                );
                if ($this->arParams['GET_ONLY_CURRENT'] == 'Y') {
                    $arFilter['TITLE'] = rawurldecode($city);
                }

                //$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);


                $res = CCatalogStore::GetList(
                    array('TITLE'=>'ASC'),
                    $arFilter,
                    false,
                    false,
                    $arSelect
                );

                while($arFields = $res->Fetch()) {
                    $arMagazine[] = $arFields;
                }
                $obCache->EndDataCache(array(
                    "arMagazine" => $arMagazine
                ));
            endif;
        endif;

        return $arMagazine;
    }

    protected function getDeliveryDates($city = 'default') {
        $obCache = new \CPHPCache;
        $life_time = 360000;
        $cache_id = 'deliveryDates'.$city;

        $timeList = array();
        if($obCache->InitCache($life_time, $cache_id, "/")):
            $vars = $obCache->GetVars();
            $timeList = $vars["timeList"];
        else :
            if($obCache->StartDataCache($life_time, $cache_id, "/")):
                $days = [
                    'Воскресенье', 'Понедельник', 'Вторник', 'Среда',
                    'Четверг', 'Пятница', 'Суббота'
                ];

                $day = $days[ date("w" )];

                $sectionID = false;
                $bxSection = CIBlockSection::GetList(
                    array(),
                    array(
                        'IBLOCK_ID' => 50,
                        'ACTIVE' => 'Y',
                        'NAME' => $city
                    ),
                    false,
                    array('ID' ,'NAME')
                );
                while($arSection = $bxSection->Fetch()){
                    $sectionID = $arSection['ID'];
                }

                if ($sectionID > 0) {
                    $bxTime = CIBlockElement::GetList(
                        array(),
                        array('IBLOCK_ID' => 50, 'ACTIVE' => 'Y', 'NAME' => $day, 'SECTION_ID' => $sectionID),
                        false,
                        false,
                        array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID','PROPERTY_PARTNER', 'PROPERTY_SELF', 'PROPERTY_TK')
                    );

                    $timeList = array();
                    if($arTime = $bxTime->Fetch()){
                        $timeList = $arTime;
                    }
                }
                $obCache->EndDataCache(array(
                    "timeList" => $timeList
                ));
            endif;
        endif;

        return $timeList;

    }

    public static function getRegionEmail($region){
        $email = 'info@santechsystemy.ru';
        if ($region > 0) {
            $hlbl = 6; //GeoRegion
            $hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            $rsData = $entity_data_class::getList(array(
                "select" => array("*"),
                "order" => array("ID" => "ASC"),
                "filter" => array("ID"=>$region)
            ));
            if ($arData = $rsData->Fetch()) {
                if ($arData['UF_EMAIL']!='') $email = $arData['UF_EMAIL'];
            }
        }
        return $email;
    }

    public static function getRegionId($city){
        $reg_id = false;
        if ($city != '') {
            $hlbl = 7; //GeoCity
            $hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            $rsData = $entity_data_class::getList(array(
                "select" => array("*"),
                "order" => array("ID" => "ASC"),
                "filter" => array("UF_GEOC_NAME"=>$city)
            ));
            if ($arData = $rsData->Fetch()) {
                if ($arData['UF_GEOC_REGION']!='') $reg_id = $arData['UF_GEOC_REGION'];
            }
        }
        return $reg_id;
    }
    public static function getRegionInfo($region){
        $info = [];
        if ($region > 0) {
            $hlbl = 6; //GeoRegion
            $hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            $rsData = $entity_data_class::getList(array(
                "select" => array("*"),
                "order" => array("ID" => "ASC"),
                "filter" => array("ID"=>$region)
            ));
            if ($arData = $rsData->Fetch()) {
                $info = $arData;
            }
        }
        return $info;
    }

    /**
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     */
    protected function getResult()
    {
        $arCity = self::getArCity();
        $sub_city = self::findDomain($arCity['DOMAINS']);
        $default_no_mainfilial = 0;

        $city = self::getCity($sub_city['SUBDOMAIN_NAME']);
        if(isset($_COOKIE["my_city_town"])){
            $town = rawurldecode($_COOKIE["my_city_town"]);
        } else {
            $town = $city;
            self::setCookie('my_city', $city); //город филиала, напр. Тула
            self::setCookie('my_city_town', $city); // город для отображения, напр. Алексин
        }
        if (isset($_COOKIE["default_no_mainfilial"])) {
            $default_no_mainfilial = $_COOKIE["default_no_mainfilial"];
        }
        $magazine = array();
        if ($this->arParams['GET_MAGAZINE'] == 'Y') {
            $magazine = self::getMagazines($city);
        }
        $timeList = array();
        if ($this->arParams['GET_DELIVERY_DATE'] == 'Y') {
            $timeList = self::getDeliveryDates($city);
        }

        $email = '';
        if(isset($_COOKIE["my_region_id"]) && $_COOKIE["my_region_id"]>0){
            $email = self::getRegionEmail($_COOKIE["my_region_id"]);
        } else {
            if ($arCity['REGION'][$city] > 0) {
                $email = self::getRegionEmail($arCity['REGION'][$city]);
            } else {
                $gettingRegion = self::getRegionId($city);
                $email = self::getRegionEmail($gettingRegion);
            }

        }

        $this->arResult = array(
            'AR_CITY' => $arCity,
            'CITY' => $city,
            'default_no_mainfilial' => $default_no_mainfilial,
            'TOWN' => $town,
            'SUBDOMAIN' => $sub_city,
            'MAGAZINE' => $magazine,
            'DELIVERY_DATES' => $timeList,
            'EMAIL' => $email
        );




    }

    /**
     * some actions after component work
     */
    protected function executeEpilog()
    {

    }

    /**
     * @return mixed|void
     */
    public function executeComponent()
    {


        try
        {
            $this->checkModules();
            $this->checkParams();
            $this->executeProlog();
            if (!$this->readDataFromCache())
            {

            $this->getResult();
            $this->putDataToCache();
            $this->includeComponentTemplate();
            }
            $this->executeEpilog();
        }
        catch (Exception $e)
        {
            $this->abortDataCache();
            ShowError($e->getMessage());
        }
    }
}
