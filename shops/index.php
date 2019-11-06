<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Контактная информация розничного магазина: Адрес, телефон");
$APPLICATION->SetPageProperty("description", "Контактная информация розничного магазина: Адрес, телефон");
$APPLICATION->SetTitle("Магазины");
?><?$APPLICATION->IncludeComponent(
	"bitrix:catalog.store.list",
	"",
	Array(
		"SEF_MODE" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"PHONE" => "Y",
		"SCHEDULE" => "Y",
		"SET_TITLE" => "Y",
		"TITLE" => "Магазины",
        "SHOPS_LIST"=>($_GLOBALS['CURRENT_CITY']['ID'] == 74934)?(array_merge($_GLOBALS['CURRENT_CITY']['PROPERTIES']['stories']['VALUE'],array(5,7))):($_GLOBALS['CURRENT_CITY']['PROPERTIES']['stories']['VALUE']),
		"MAP_TYPE" => "0"
	)
);?><br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>