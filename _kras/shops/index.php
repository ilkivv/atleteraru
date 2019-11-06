<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
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
        "SHOPS_LIST"=>$_GLOBALS['CURRENT_CITY']['PROPERTIES']['stories']['VALUE'],
		"MAP_TYPE" => "0"
	)
);?><br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>