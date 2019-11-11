<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->RestartBuffer();
if (!defined('PUBLIC_AJAX_MODE')) {
	define('PUBLIC_AJAX_MODE', true);
}
header('Content-type: application/json');
CModule::IncludeModule ("catalog");
$SKUID = intval($_POST['offer_prop_value']);
$arProduct = CCatalogProduct::GetByID($SKUID);
$QUANTITY = (intval($_POST['quantity']))?intval($_POST['quantity']):1;
$rsStore = CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID' =>$SKUID,'STORE_ID'=>2));
while($arStore = $rsStore->Fetch()) {
    $arProduct['QUANTITY'] = $arStore['AMOUNT'];
}

$dbBasketItems = CSaleBasket::GetList(
		array(
				"NAME" => "ASC",
				"ID" => "ASC"
		),
		array(
				"FUSER_ID" => CSaleBasket::GetBasketUserID(),
				"LID" => SITE_ID,
				"ORDER_ID" => "NULL",
				"PRODUCT_ID"=>$SKUID,
				'CAN_BUY'=>'Y'
		),
		false,
		false,
		array( "QUANTITY")
);
$arItem = $dbBasketItems->Fetch();
$arPrice = CPrice::GetBasePrice($SKUID);

    //print_r("//////////start");
    global $CITY;
    if($CITY == 'krasnoyarsk'){
        $location = array(5);
    }elseif ($CITY == 'kemerovo'){
        $location = array(7);
    }else{
        $location = array(1, 3, 4);
    }
    //print_r("/////////stop");
    if ($location){
        $catalogProductList = CCatalogStoreProduct::GetList(
            array(),
            array(
                'PRODUCT_ID' => $SKUID,
                'STORE_ID' => $location
            )
        );
        $listProduct = array();
        if (isset($catalogProductList)){
            while($productList = $catalogProductList->Fetch()){
                if($productList['AMOUNT'] > 0){
                    $listProduct[] = $productList;
                }
            }
        }

    }

if (($arProduct['QUANTITY'] -$arProduct['QUANTITY_RESERVED']- $QUANTITY-$arItem['QUANTITY']) >=0) {
	if ((!$arItem['ID'] && Add2Basket($arPrice['ID'],$QUANTITY,array("PRODUCT_PRICE_ID"=>$arPrice['ID'],"BASE_PRICE" => $arPrice["PRICE"],
        "PRICE" => $arPrice["PRICE"]))) || ($arItem['ID'] && CSaleBasket::Update($arItem['ID'], array('QUANTITY'=>($QUANTITY+$arItem['QUANTITY']))))) {
		die(json_encode(array('submitOn'=>true,'arPrice'=>$arPrice,'msg'=>'added','quantity'=>($QUANTITY+intval($arItem['QUANTITY'])))));
	} else {
		die(json_encode(array('submitOn'=>false,'msg'=>'err','text'=>'Ошибка системы','quantity'=>'')));
	}
} else {
    $ostatok = $arProduct['QUANTITY'] -$arProduct['QUANTITY_RESERVED'];
    if ($ostatok <=0) {
        die(json_encode(array('submitOn'=>false,'msg'=>'noquantity','text'=>'Недостаточно на складе','quantity'=>intval($arProduct['QUANTITY'] -$arProduct['QUANTITY_RESERVED']))));
    } else {
        die(json_encode(array('submitOn'=>false,'msg'=>'noquantity','text'=>'Нельзя заказать более '.intval($ostatok),'quantity'=>intval($arProduct['QUANTITY'] -$arProduct['QUANTITY_RESERVED']))));
    }
	
}

?>