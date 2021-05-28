<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

//print_r($arResult['AR_CITY']['DOMAINS'] );

?>



<? $domain = $arResult['SUBDOMAIN']['DOMAIN'];
//print_r($domain);
?>

<div class="regionBox">


 <p class="region">


 	<?


 	if ($arResult['TOWN'] && !isset($_COOKIE['my_city_init']) && strlen(trim($arResult['SUBDOMAIN']['SUBDOMAIN_NAME'])) == 0 ) {
 		echo "Ваш город: " . $arResult['TOWN'] . "?";
 	} elseif ($arResult['TOWN'] && (isset($_COOKIE['my_city_init']) || $arResult['SUBDOMAIN']['SUBDOMAIN_NAME'])) {
 		echo "Ваш город : <a href='#' class='city'><span>" . rawurldecode($arResult['TOWN']) . "</span></a>";
 	} else {
 		echo "<a href='#' class='city'><span>Город не определен</span></a>";
 	}
 	?>
 </p>
 <?
 if ($arResult['CITY'] && !isset($_COOKIE['my_city_init']) && !$arResult['SUBDOMAIN']['SUBDOMAIN_NAME']) {
 	if (isset($arResult['DOMAINS'][$arResult['CITY']]) && $arResult['DOMAINS'][$arResult['CITY']] != 'default') {
 		$link .= 'https://' . $arResult['DOMAINS'][$arResult['CITY']] . '.' . $domain . $_SERVER['REQUEST_URI'];
 	} else {
 		$link = 'https://www.' . $domain . $_SERVER['REQUEST_URI'];
 	}
 	echo "<span class='first_init_city'><a href='" . $link . "' class='submit_city' data-city='" . $arResult['CITY'] . "'>Да</a> <span class='change_city'>Другой город</span></span>";
 }
 ?>



</div>