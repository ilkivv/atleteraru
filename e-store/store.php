<?
require ($_SERVER[ "DOCUMENT_ROOT" ] . "/bitrix/header.php");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->RestartBuffer();
if (!defined('PUBLIC_AJAX_MODE')) {
    define('PUBLIC_AJAX_MODE', true);
}
$ID = $_GET['ID'];
if (CModule::IncludeModule ("catalog")) {
    $arInfo = CCatalogSKU::GetInfoByProductIBlock(45);
    $rsOffers = CIBlockElement::GetList (array (), array (
        'IBLOCK_ID' => 46,
        'PROPERTY_' . $arInfo[ 'SKU_PROPERTY_ID' ] => $ID
    ),false,false,array('CATALOG_PROPERTY_CML2_ATTRIBUTES','ID','IBLOCK_ID','NAME'));

    while ( $arOffer = $rsOffers->GetNext () ) {
        $res = CIBlockElement::GetProperty(46,$arOffer['ID'],array(),array('CODE'=>'CML2_ATTRIBUTES'))->Fetch();
        $arOffer['CML2_ATTRIBUTES'] = $res;
        $SKUIDS[] = $arOffer['ID'];
        $SKU[$arOffer['ID']] = $arOffer;
    }
}

$rsStore = CCatalogStore::GetList(
    array('SORT'=>'ASC'),
    array('ACTIVE'=>'Y','ISSUING_CENTER'=>(($_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'] == 'Y')?'N':'Y'),'SHIPPING_CENTER'=>(($_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'] == 'Y')?'N':'Y'),'ID'=>$_GLOBALS['CURRENT_CITY']['PROPERTIES']['stories']['VALUE'])
);

while ($arStore = $rsStore->Fetch()){
    $stories[$arStore['ID']] = $arStore;

}
$rsStore = CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID' =>$SKUIDS));
while ($arStore = $rsStore->Fetch()){
    $amount[$arStore['STORE_ID']][] = $arStore;

}
$rsProduct = CIBlockElement::GetList (array (), array (
    'IBLOCK_ID' => 45,
    'ID'  => $ID
));
$arProduct = $rsProduct->Fetch();
?>
<div class="product-residue">
    <div class="title">
        <?php if ($arProduct["PREVIEW_PICTURE"] || $arProduct["DETAIL_PICTURE"]){
            $renderImage = CFile::ResizeImageGet(($arProduct["PREVIEW_PICTURE"])?$arProduct["PREVIEW_PICTURE"]:$arProduct["DETAIL_PICTURE"], Array("width" => (85), "height" => (85)),BX_RESIZE_IMAGE_EXACT);
        } else {
            $renderImage['src'] = '/bitrix/templates/.default/components/bitrix/catalog/.default/bitrix/catalog.element/.default/images/no_photo.png';
        }
        ?>
        <img src="<?=$renderImage['src']?>" width="85">
        <h2><?php echo $arProduct['NAME']?></h2>
    </div>
    <div class="shops clearfix"><div class="shops-bg"></div>
        <a class="j-shop-prev shop-prev carousel-prev" href="#">Предыдущий магазин</a>
        <a class="j-shop-next shop-next carousel-next" href="#">Следующий магазин</a>
        <div class="shops-helper j-shops-slider">
            <?php foreach ($stories as $k => $v) {?>
                <div class="shop-item"><span></span>
                    <div class="shop">
                        <h4><?=$v['DESCRIPTION']?></h4>
                        <p><?=$v['ADDRESS']?> <?=$v['PHONE']?></p>
                    </div>
                    <ul>
                        <?php if ($amount[$v['ID']]) {
                            $amountCnt = 0;
                            //var_dump($v);


                            ?>
                            <?php foreach ($amount[$v['ID']] as $ak => $av) {

                                if ($av['AMOUNT']>0) {

                                    $arPrice = CPrice::GetList(array(), array(
                                        'PRODUCT_ID' => $av['PRODUCT_ID'],
                                        'CATALOG_GROUP_ID' => $arTypePriceToStore[$av['STORE_ID']]
                                    ));

                                    $discount = CCatalogDiscount::GetDiscountByProduct($av['PRODUCT_ID']);

                                    while($row = $arPrice->Fetch()){
                                        $priceItem = $row;
                                    }

                                    if (!empty($discount)){
                                        $discount = (int) $priceItem['PRICE'] / (int) ($discount[0]['VALUE']);
                                    }else{
                                        $discount = 0;
                                    }
                                    $amountCnt = 1;
                                    ?><li><?=$SKU[$av['PRODUCT_ID']]['CML2_ATTRIBUTES']['VALUE']?> (<?=$av['AMOUNT']?>) - <?= $priceItem['PRICE'] - $discount;?> р.</li><?php
                                }
                            }
                            if (!$amountCnt) {
                                ?><li>нет в наличии</li><?php
                            }
                            ?>
                        <?php } else {?><li>нет в наличии</li><?php }?>
                    </ul>
                </div>
            <?php }?>
        </div>
    </div>
</div>