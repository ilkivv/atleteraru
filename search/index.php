<?

require ($_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/header.php");
$APPLICATION->SetTitle ("Поиск");
?>

<?

$APPLICATION->IncludeComponent ("bitrix:catalog.search", "", Array (
    "AJAX_MODE" => "N", 
    "IBLOCK_TYPE" => "xmlcatalog", 
    "IBLOCK_ID" => "45", 
    "ELEMENT_SORT_FIELD" => "sort", 
    "ELEMENT_SORT_ORDER" => "asc", 
    "ELEMENT_SORT_FIELD2" => "id", 
    "ELEMENT_SORT_ORDER2" => "desc", 
    "SECTION_URL" => "/e-store/#SECTION_ID#/", 
    "DETAIL_URL" => "/e-store/#SECTION_ID#/#ELEMENT_ID#/", 
    "BASKET_URL" => "/personal/cart/", 
    "ACTION_VARIABLE" => "action", 
    "PRODUCT_ID_VARIABLE" => "id", 
    "PRODUCT_QUANTITY_VARIABLE" => "quantity", 
    "PRODUCT_PROPS_VARIABLE" => "prop", 
    "SECTION_ID_VARIABLE" => "SECTION_ID", 
    "DISPLAY_COMPARE" => "Y", 
    "PAGE_ELEMENT_COUNT" => "30", 
    "LINE_ELEMENT_COUNT" => "3", 
    "PROPERTY_CODE" => array ("MAX_PRICE"), 
    "OFFERS_FIELD_CODE" => array (), 
    "OFFERS_PROPERTY_CODE" => array ('CML2_TASTE'), 
    "OFFERS_SORT_FIELD" => "sort", 
    "OFFERS_SORT_ORDER" => "asc", 
    "OFFERS_SORT_FIELD2" => "id", 
    "OFFERS_SORT_ORDER2" => "desc", 
    //"OFFERS_LIMIT" => "15", 
    "PRICE_CODE" => array($_GLOBALS['CURRENT_CITY']['PROPERTIES']['PRICETYPE']['VALUE']),
    "ALLOW_SALE" => $_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'],
	"STORIES_LIST" => $_GLOBALS["CURRENT_CITY"]["PROPERTIES"]["stories"]["VALUE"],
    "USE_PRICE_COUNT" => "N", 
    "SHOW_PRICE_COUNT" => "1", 
    "PRICE_VAT_INCLUDE" => "N", 
    "USE_PRODUCT_QUANTITY" => "Y", 
    "CACHE_TYPE" => "A", 
    "CACHE_TIME" => "36000000", 
    "RESTART" => "Y", 
    "NO_WORD_LOGIC" => "Y", 
    "USE_LANGUAGE_GUESS" => "Y", 
    "CHECK_DATES" => "Y", 
    "DISPLAY_TOP_PAGER" => "Y", 
    "DISPLAY_BOTTOM_PAGER" => "Y", 
    "PAGER_TITLE" => "Товары", 
    "PAGER_SHOW_ALWAYS" => "Y", 
    "PAGER_TEMPLATE" => "", 
    "PAGER_DESC_NUMBERING" => "Y", 
    "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000", 
    "PAGER_SHOW_ALL" => "Y", 
    "HIDE_NOT_AVAILABLE" => "N", 
    "CONVERT_CURRENCY" => "N", 
    "CURRENCY_ID" => "RUB", 
    "OFFERS_CART_PROPERTIES" => array (), 
    "AJAX_OPTION_JUMP" => "Y", 
    "AJAX_OPTION_STYLE" => "Y", 
    "AJAX_OPTION_HISTORY" => "Y"
));
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>