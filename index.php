<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Купить спортивное питание в интернет-магазине Атлет по доступным ценам");
$APPLICATION->SetPageProperty("description", "Купите спортивное питание по доступным ценам. Интернет-магазин Атлет - это отличное качество и недорогие цены. Звоните по телефону");
$APPLICATION->SetTitle("Главная страница");
?><?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	"",
	Array(
		"AREA_FILE_SHOW" => "sect",
		"AREA_FILE_SUFFIX" => "inc".$_GLOBALS['CURRENT_CITY']['ID'],
		"AREA_FILE_RECURSIVE" => "Y",
		"EDIT_TEMPLATE" => ""
	)
);?> <?/*$APPLICATION->IncludeComponent("bitrix:main.include","",Array(
        "AREA_FILE_SHOW" => "sect", 
        "AREA_FILE_SUFFIX" => "inc_brs_".$_GLOBALS['CURRENT_CITY']['ID'], 
        "AREA_FILE_RECURSIVE" => "Y", 
        "EDIT_TEMPLATE" => "" 
    )
);*/?> <?php

    global $arBFilter;

    $arBFilter = array(
        'PROPERTY_CITY.ID' => $_GLOBALS['CURRENT_CITY']['ID']
    );

?> <?$APPLICATION->IncludeComponent(
	"bitrix:news.list", 
	"banners", 
	array(
		"IBLOCK_TYPE" => "news",
		"IBLOCK_ID" => "53",
		"NEWS_COUNT" => "10",
		"SORT_BY1" => "RAND",
		"SORT_ORDER1" => "DESC",
		"SORT_BY2" => "SORT",
		"SORT_ORDER2" => "ASC",
		"FILTER_NAME" => "arBFilter",
		"FIELD_CODE" => array(
			0 => "PREVIEW_PICTURE",
			1 => "",
		),
		"PROPERTY_CODE" => array(
			0 => "LINK",
			1 => "CITY",
			2 => "",
		),
		"CHECK_DATES" => "Y",
		"DETAIL_URL" => "",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"PREVIEW_TRUNCATE_LEN" => "",
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"SET_TITLE" => "Y",
		"SET_BROWSER_TITLE" => "Y",
		"SET_META_KEYWORDS" => "Y",
		"SET_META_DESCRIPTION" => "Y",
		"SET_STATUS_404" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"PARENT_SECTION" => "",
		"PARENT_SECTION_CODE" => "",
		"INCLUDE_SUBSECTIONS" => "Y",
		"PAGER_TEMPLATE" => ".default",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Новости",
		"PAGER_SHOW_ALWAYS" => "Y",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "Y",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>
<h2>Новинки</h2>
 <?$APPLICATION->IncludeComponent(
	"bitrix:catalog.top",
	"",
	Array(
		"VIEW_MODE" => "BANNER",
		"TEMPLATE_THEME" => "blue",
		"PRODUCT_DISPLAY_MODE" => "Y",
		"ADD_PICT_PROP" => "-",
		"LABEL_PROP" => "-",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_OLD_PRICE" => "Y",
		"ROTATE_TIMER" => "30",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_NOT_AVAILABLE" => "Нет в наличии",
		"IBLOCK_TYPE" => "xmlcatalog",
		"IBLOCK_ID" => "45",
		"DATA_SUFFIX" => "new",
		"ELEMENT_SORT_FIELD" => "timestamp_x",
		"ELEMENT_SORT_ORDER" => "desc",
		"ELEMENT_SORT_FIELD2" => "name",
		"ELEMENT_SORT_ORDER2" => "desc",
		"SECTION_URL" => "/e-store/#SECTION_CODE_PATH#/",
		"DETAIL_URL" => "/e-store/#SECTION_CODE_PATH#/#ELEMENT_CODE#/",
		"BASKET_URL" => "/personal/cart/",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "Y",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"DISPLAY_COMPARE" => "N",
		"ELEMENT_COUNT" => "9",
		"LINE_ELEMENT_COUNT" => "3",
		"PROPERTY_CODE" => array("CML2_ATTRIBUTES","CML2_TASTE","MAX_PRICE","CML2_PRICEGROUP"),
		"OFFERS_FIELD_CODE" => array("NAME",""),
		"OFFERS_PROPERTY_CODE" => array("CML2_TASTE","CML2_PRICEGROUP"),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_FIELD2" => "timestamp_x",
		"OFFERS_SORT_ORDER2" => "desc",
		"OFFERS_LIMIT" => "500",
		"PRICE_CODE" => array($_GLOBALS['CURRENT_CITY']['PROPERTIES']['PRICETYPE']['VALUE']),
		"ALLOW_SALE" => $_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'],
		"USE_PRICE_COUNT" => "Y",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "N",
		"PRODUCT_PROPERTIES" => array("CML2_TASTE"),
		"USE_PRODUCT_QUANTITY" => "Y",
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"HIDE_NOT_AVAILABLE" => "N",
		"OFFERS_CART_PROPERTIES" => array("CML2_TASTE"),
		"CONVERT_CURRENCY" => "Y",
		"CURRENCY_ID" => "RUB",
		"FILTER_NAME" => "",
		"CACHE_FILTER" => "N",
		"HIDE_CHECK_AVAILABILITY" => $_GLOBALS['CURRENT_CITY']['HIDE_CHECK_AVAILABILITY'],
	)
);?>
<h2>Акция</h2>
 <?
 $arrFilter = array('PROPERTY_CML2_PRICEGROUP'=>1223);
 $APPLICATION->IncludeComponent(
	"bitrix:catalog.top", 
	".default", 
	array(
		"VIEW_MODE" => "BANNER",
		"TEMPLATE_THEME" => "blue",
		"PRODUCT_DISPLAY_MODE" => "Y",
		"ADD_PICT_PROP" => "-",
		"LABEL_PROP" => "-",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_OLD_PRICE" => "Y",
		"ROTATE_TIMER" => "30",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_NOT_AVAILABLE" => "Нет в наличии",
		"IBLOCK_TYPE" => "xmlcatalog",
		"IBLOCK_ID" => "45",
		"DATA_SUFFIX" => "customers",
		"ELEMENT_SORT_FIELD" => "shows",
		"ELEMENT_SORT_ORDER" => "desc",
		"ELEMENT_SORT_FIELD2" => "name",
		"ELEMENT_SORT_ORDER2" => "desc",
		"SECTION_URL" => "/e-store/#SECTION_CODE_PATH#/",
		"DETAIL_URL" => "/e-store/#SECTION_CODE_PATH#/#ELEMENT_CODE#/",
		"BASKET_URL" => "/personal/cart/",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "Y",
        "ALLOW_SALE" => $_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'],
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"DISPLAY_COMPARE" => "N",
		"ELEMENT_COUNT" => "9",
		"LINE_ELEMENT_COUNT" => "3",
		"PROPERTY_CODE" => array(
			0 => "CML2_ATTRIBUTES",
			1 => "CML2_TASTE",
			2 => "MAX_PRICE",
		),
		"OFFERS_FIELD_CODE" => array(
			0 => "NAME",
			1 => "",
		),
		"OFFERS_PROPERTY_CODE" => array(
			0 => "CML2_TASTE",
			1 => "",
		),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_FIELD2" => "timestamp_x",
		"OFFERS_SORT_ORDER2" => "desc",
		"OFFERS_LIMIT" => "50",
		"PRICE_CODE" => array(
			0 => $_GLOBALS['CURRENT_CITY']['PROPERTIES']['PRICETYPE']['VALUE'],
		),
		"USE_PRICE_COUNT" => "Y",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "N",
		"PRODUCT_PROPERTIES" => array(
			0 => "CML2_ATTRIBUTES",
		),
		"USE_PRODUCT_QUANTITY" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "1800",
		"CACHE_GROUPS" => "Y",
		"HIDE_NOT_AVAILABLE" => "N",
		"OFFERS_CART_PROPERTIES" => array(
			0 => "CML2_TASTE",
		),
		"CONVERT_CURRENCY" => "Y",
		"CURRENCY_ID" => "RUB",
		"FILTER_NAME" => "arrFilter",
		"CACHE_FILTER" => "N",
    "HIDE_CHECK_AVAILABILITY" => $_GLOBALS['CURRENT_CITY']['HIDE_CHECK_AVAILABILITY'],
	),
	false
);?>
<h2>Распродажа</h2>
 <?php
 $arrFilter = array('PROPERTY_CML2_PRICEGROUP'=>1221);
 $APPLICATION->IncludeComponent(
	"bitrix:catalog.top",
	"",
	Array(
		"VIEW_MODE" => "BANNER",
		"TEMPLATE_THEME" => "blue",
		"PRODUCT_DISPLAY_MODE" => "Y",
		"ADD_PICT_PROP" => "-",
		"LABEL_PROP" => "-",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_OLD_PRICE" => "Y",
		"ROTATE_TIMER" => "30",
		"MESS_BTN_BUY" => "Купить",
		"MESS_BTN_ADD_TO_BASKET" => "В корзину",
		"MESS_BTN_DETAIL" => "Подробнее",
		"MESS_NOT_AVAILABLE" => "Нет в наличии",
		"IBLOCK_TYPE" => "xmlcatalog",
		"IBLOCK_ID" => "45",
		"DATA_SUFFIX" => "sale",
		"ELEMENT_SORT_FIELD" => "timestamp_x",
		"ELEMENT_SORT_ORDER" => "desc",
		"ELEMENT_SORT_FIELD2" => "name",
		"ELEMENT_SORT_ORDER2" => "desc",
		"SECTION_URL" => "/e-store/#SECTION_CODE_PATH#/",
		"DETAIL_URL" => "/e-store/#SECTION_CODE_PATH#/#ELEMENT_CODE#/",
		"BASKET_URL" => "/personal/cart/",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "Y",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"DISPLAY_COMPARE" => "N",
		"ELEMENT_COUNT" => "9",
		"LINE_ELEMENT_COUNT" => "3",
		"PROPERTY_CODE" => array("CML2_ATTRIBUTES","CML2_TASTE","MAX_PRICE"),
		"OFFERS_FIELD_CODE" => array("NAME",""),
		"OFFERS_PROPERTY_CODE" => array("CML2_TASTE",""),
		"OFFERS_SORT_FIELD" => "sort",
		"OFFERS_SORT_ORDER" => "asc",
		"OFFERS_SORT_FIELD2" => "timestamp_x",
		"OFFERS_SORT_ORDER2" => "desc",
		"OFFERS_LIMIT" => "50",
		"PRICE_CODE" => array($_GLOBALS['CURRENT_CITY']['PROPERTIES']['PRICETYPE']['VALUE']),
        "ALLOW_SALE" => $_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'],
		"USE_PRICE_COUNT" => "Y",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "N",
		"PRODUCT_PROPERTIES" => array("CML2_ATTRIBUTES","CML2_TASTE"),
		"USE_PRODUCT_QUANTITY" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "1800",
		"CACHE_GROUPS" => "Y",
		"HIDE_NOT_AVAILABLE" => "N",
		"OFFERS_CART_PROPERTIES" => array("CML2_TASTE"),
		"CONVERT_CURRENCY" => "Y",
		"CURRENCY_ID" => "RUB",
		"FILTER_NAME" => "arrFilter",
		"CACHE_FILTER" => "N",
    "HIDE_CHECK_AVAILABILITY" => $_GLOBALS['CURRENT_CITY']['HIDE_CHECK_AVAILABILITY'],
	)
);?>

<div class="profit-block">
	 <?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	".default",
	Array(
		"AREA_FILE_SHOW" => "page",
		"AREA_FILE_SUFFIX" => "incbottom",
		"AREA_FILE_RECURSIVE" => "Y",
		"EDIT_TEMPLATE" => "standard.php"
	)
);?>
</div>

<div class="main-description-block">

	 <?$APPLICATION->IncludeComponent(
		"bitrix:main.include",
		".default",
		Array(
			"AREA_FILE_SHOW" => "page",
			"AREA_FILE_SUFFIX" => "maindesc",
			"AREA_FILE_RECURSIVE" => "N",
			"EDIT_TEMPLATE" => "standard.php"
		)
	);?>

</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>