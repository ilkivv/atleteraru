<?
require ($_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/header.php");
$APPLICATION->RestartBuffer ();
if (! defined ('PUBLIC_AJAX_MODE')) {
    define ('PUBLIC_AJAX_MODE', true);
}
header ('Content-type: application/json');
CModule::IncludeModule ("catalog");
$SKUID = intval ($_POST[ 'offer_prop_value' ]);
$arProduct = CCatalogProduct::GetByID ($SKUID);
$QUANTITY = (intval ($_POST[ 'quantity' ])) ? intval ($_POST[ 'quantity' ]) : 1;

$dbBasketItems = CSaleBasket::GetList (array (
    "NAME" => "ASC", 
    "ID" => "ASC"
), array (
    "FUSER_ID" => CSaleBasket::GetBasketUserID (), 
    "LID" => SITE_ID, 
    "ORDER_ID" => "NULL", 
    "PRODUCT_ID" => $SKUID, 
    'CAN_BUY' => 'Y'
), false, false, array (
    
));
$arItem = $dbBasketItems->Fetch ();
if (($arItem[ 'QUANTITY' ]) >= 0) {
    CModule::IncludeModule ("sale");
    CSaleBasket::Update ($arItem['ID'], array (
        "PRODUCT_ID" => $SKUID,
        'QUANTITY' => ($arItem[ 'QUANTITY' ] - 1)
    ));
}
die (json_encode (array (
    'submitOn' => true, 
    'msg' => 'removed', 
    'quantity' => (intval ($arItem[ 'QUANTITY' ])-1)
)));

?>