<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
global $arrFilter;
//$arrFilter['ID']=array(47978);
$sortType = array('asc'=>'ASC','desc'=>'DESC');
$sortBy = array('price'=> 'PROPERTY_MAX_PRICE','name'=>'NAME','id'=>'id');

CModule::IncludeModule("iblock");

$arFilter = Array("IBLOCK_ID"=>45, "ACTIVE"=>"Y" );
$res = CIBlockElement::GetList(Array("ID"=>"ASC"), $arFilter, array('PROPERTY_CML2_PRICEGROUP'),array("nPageSize"=>50000), array('IBLOCK_ID','PROPERTY_CML2_MANUFACTURER','ID'));
while($ob = $res->GetNext())
{
    $ids[] = $ob['PROPERTY_CML2_PRICEGROUP_ENUM_ID'];
}
$ids = array_unique($ids);
$property_enums = CIBlockPropertyEnum::GetList(Array("VALUE"=>"ASC"), Array("IBLOCK_ID"=>45, "CODE"=>"PROPERTY_CML2_PRICEGROUP"));

while($enum_fields = $property_enums->GetNext())
{
    if (in_array($enum_fields["ID"],$ids) && $enum_fields["ID"] == $_GET['MANUFACTURER_ID']) {
        echo '<h1>'.$enum_fields["VALUE"].'</h1>';
    }
    
   /* if (in_array($enum_fields["ID"],$ids)) {
    } else {
        echo '<span style="color:red;">'.$enum_fields["VALUE"].'</span><br />';
    }*/
}

?>
<?$APPLICATION->IncludeComponent(
		"bitrix:catalog.smart.filter",
		"brands",
		Array(
			"IBLOCK_TYPE" => "xmlcatalog",
			"IBLOCK_ID" => 45,
			"SECTION_ID" => "",
			"FILTER_NAME" => 'arrFilter',
			 "PRICE_CODE" => array($_GLOBALS['CURRENT_CITY']['PROPERTIES']['PRICETYPE']['VALUE']),
        "ALLOW_SALE" => $_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'],
		"STORIES_LIST" => $_GLOBALS["CURRENT_CITY"]["PROPERTIES"]["stories"]["VALUE"],
			"CACHE_TYPE" => "N",
			"CACHE_TIME" => "3600",
			"CACHE_GROUPS" => "Y",
			"SAVE_IN_SESSION" => "N",
			"XML_EXPORT" => "Y",
			"SECTION_TITLE" => "NAME",
			"SECTION_DESCRIPTION" => "DESCRIPTION",
			'HIDE_NOT_AVAILABLE' => "N",
			"TEMPLATE_THEME" => ""
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);?>
<?php 
if ($_GET['brand']) {
$arrFilter['SECTION_ID'] = $_GET['brand'];
}
$arrFilter['PROPERTY_CML2_PRICEGROUP']=1221;?>
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section",
	"",
	Array(
		"IBLOCK_TYPE" => "xmlcatalog",
		"IBLOCK_ID" => "45",
		"SECTION_ID" => "",
		"SECTION_CODE" => "",
		"SECTION_USER_FIELDS" => array("",""),
		"ELEMENT_SORT_FIELD" => ((array_key_exists($_GET['sort'],$sortBy))?$sortBy[$_GET['sort']]:"name"),
		"ELEMENT_SORT_ORDER" => ((array_key_exists($_GET['order'],$sortType))?$sortType[$_GET['order']]:"asc"),
		"ELEMENT_SORT_FIELD2" => "name",
		"ELEMENT_SORT_ORDER2" => "asc",
		"FILTER_NAME" => "arrFilter",
		"INCLUDE_SUBSECTIONS" => "Y",
		"SHOW_ALL_WO_SECTION" => "Y",
        "HIDE_EMPTY" => "Y",
		"HIDE_NOT_AVAILABLE" => "N",
		"PAGE_ELEMENT_COUNT" => "60",
		"LINE_ELEMENT_COUNT" => "3",
		"PROPERTY_CODE" => array("CML2_MANUFACTURER","CML2_TASTE","MAX_PRICE"),
		"OFFERS_LIMIT" => "50",
		"TEMPLATE_THEME" => "",
		"PRODUCT_SUBSCRIPTION" => "N",
		"SHOW_DISCOUNT_PERCENT" => "N",
		"SHOW_OLD_PRICE" => "N",
		"MESS_BTN_BUY" => "",
		"MESS_BTN_ADD_TO_BASKET" => "",
		"MESS_BTN_SUBSCRIBE" => "",
		"MESS_BTN_DETAIL" => "",
		"MESS_NOT_AVAILABLE" => "",
		"SECTION_URL" => "/kras/e-store/#SECTION_CODE_PATH#/",
		"DETAIL_URL" => "/kras/e-store/#SECTION_CODE_PATH#/#ELEMENT_CODE#/#catalog-brands",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "1800",
		"CACHE_GROUPS" => "Y",
		"SET_TITLE" => "Y",
		"SET_BROWSER_TITLE" => "Y",
		"BROWSER_TITLE" => "-",
		"SET_META_KEYWORDS" => "Y",
		"META_KEYWORDS" => "",
		"SET_META_DESCRIPTION" => "Y",
		"META_DESCRIPTION" => "",
		"ADD_SECTIONS_CHAIN" => "N",
		"DISPLAY_COMPARE" => "N",
		"SET_STATUS_404" => "N",
		"CACHE_FILTER" => "N",
		 "PRICE_CODE" => array($_GLOBALS['CURRENT_CITY']['PROPERTIES']['PRICETYPE']['VALUE']),
        "ALLOW_SALE" => $_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'],
		"STORIES_LIST" => $_GLOBALS["CURRENT_CITY"]["PROPERTIES"]["stories"]["VALUE"],
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"CONVERT_CURRENCY" => "N",
		"BASKET_URL" => "/personal/basket.php",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"USE_PRODUCT_QUANTITY" => "N",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"PRODUCT_PROPERTIES" => array(),
		"PAGER_TEMPLATE" => "",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Товары",
		"PAGER_SHOW_ALWAYS" => "Y",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "Y",
		"OFFERS_FIELD_CODE" => array("",""),
		"OFFERS_PROPERTY_CODE" => array("CML2_TASTE",""),
		"OFFERS_SORT_FIELD" => "",
		"OFFERS_SORT_ORDER" => "",
		"OFFERS_SORT_FIELD2" => "",
		"OFFERS_SORT_ORDER2" => "",
		"PRODUCT_DISPLAY_MODE" => "Y",
		"ADD_PICT_PROP" => "-",
		"LABEL_PROP" => "-",
		"OFFERS_CART_PROPERTIES" => array(),
    "HIDE_CHECK_AVAILABILITY" => $_GLOBALS['CURRENT_CITY']['HIDE_CHECK_AVAILABILITY'],
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>