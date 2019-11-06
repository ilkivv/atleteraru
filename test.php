<?php 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->RestartBuffer();
/*global $topfltr; 
$topfltr = array("PROPERTY_DAYSALE"=>1);
echo ini_get('max_execution_time');
ini_set('max_execution_time',60);
echo ini_get('max_execution_time');*/
CModule::IncludeModule ("catalog");
CModule::IncludeModule ("sale");


$ID = 69382;

$arPrice = CCatalogProduct::GetMaxPrice ($ID, 1);
if ($arPrice[ 'PRICE' ][ 'PRICE' ] > 0) {
    echo $arPrice[ 'PRICE' ][ 'PRICE' ];
    $mxResult = CCatalogSku::GetProductInfo ($ID);
    $db_props = CIBlockElement::GetProperty (45,$mxResult[ 'ID' ],array(),array('CODE'=>'MAX_PRICE'));
    $ar_props = $db_props->Fetch();
    print_r($ar_props);
    if ($arPrice[ 'PRICE' ][ 'PRICE' ] > $ar_props['VALUE']) {
        CIBlockElement::SetPropertyValues ($mxResult[ 'ID' ], 45, $arPrice[ 'PRICE' ][ 'PRICE' ], 'MAX_PRICE');
    }
}



?>
