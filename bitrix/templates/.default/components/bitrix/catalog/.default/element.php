<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
global $SKUIDS,$SKU,$PRODUCT_NAME;
?>
<?$ElementID = $APPLICATION->IncludeComponent(
	"bitrix:catalog.element",
	"",
	array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "HIDE_EMPTY" => $arParams["HIDE_EMPTY"],
		"PROPERTY_CODE" => $arParams["DETAIL_PROPERTY_CODE"],
		"META_KEYWORDS" => $arParams["DETAIL_META_KEYWORDS"],
		"META_DESCRIPTION" => $arParams["DETAIL_META_DESCRIPTION"],
		"BROWSER_TITLE" => $arParams["DETAIL_BROWSER_TITLE"],
		"BASKET_URL" => $arParams["BASKET_URL"],
		"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
		"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
		"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
		"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
		"PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
        "ALLOW_SALE" => $arParams['ALLOW_SALE'],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],
		"PRICE_CODE" => $arParams["PRICE_CODE"],
		"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
		"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
		"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
		"PRICE_VAT_SHOW_VALUE" => $arParams["PRICE_VAT_SHOW_VALUE"],
		"USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
		"PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
		"ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
		"PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
		"LINK_IBLOCK_TYPE" => $arParams["LINK_IBLOCK_TYPE"],
		"LINK_IBLOCK_ID" => $arParams["LINK_IBLOCK_ID"],
		"LINK_PROPERTY_SID" => $arParams["LINK_PROPERTY_SID"],
		"LINK_ELEMENTS_URL" => $arParams["LINK_ELEMENTS_URL"],

		"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
		"OFFERS_FIELD_CODE" => $arParams["DETAIL_OFFERS_FIELD_CODE"],
		"OFFERS_PROPERTY_CODE" => $arParams["DETAIL_OFFERS_PROPERTY_CODE"],
		"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
		"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
		"OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
		"OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],

		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
		"ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
		"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
		'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
		'CURRENCY_ID' => $arParams['CURRENCY_ID'],
		'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
		'USE_ELEMENT_COUNTER' => $arParams['USE_ELEMENT_COUNTER'],
        'USE_STORE'=>$arParams["USE_STORE"],

		'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
		'LABEL_PROP' => $arParams['LABEL_PROP'],
		'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
		'OFFER_TREE_PROPS' => $arParams['OFFER_TREE_PROPS'],
		'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
		'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
		'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
		'SHOW_MAX_QUANTITY' => $arParams['DETAIL_SHOW_MAX_QUANTITY'],
		'MESS_BTN_BUY' => $arParams['MESS_BTN_BUY'],
		'MESS_BTN_ADD_TO_BASKET' => $arParams['MESS_BTN_ADD_TO_BASKET'],
		'MESS_BTN_SUBSCRIBE' => $arParams['MESS_BTN_SUBSCRIBE'],
		'MESS_BTN_COMPARE' => $arParams['MESS_BTN_COMPARE'],
		'MESS_NOT_AVAILABLE' => $arParams['MESS_NOT_AVAILABLE'],
		'USE_VOTE_RATING' => $arParams['DETAIL_USE_VOTE_RATING'],
		'VOTE_DISPLAY_AS_RATING' => (isset($arParams['DETAIL_VOTE_DISPLAY_AS_RATING']) ? $arParams['DETAIL_VOTE_DISPLAY_AS_RATING'] : ''),
		'USE_COMMENTS' => $arParams['DETAIL_USE_COMMENTS'],
		'BLOG_USE' => (isset($arParams['DETAIL_BLOG_USE']) ? $arParams['DETAIL_BLOG_USE'] : ''),
		'VK_USE' => (isset($arParams['DETAIL_VK_USE']) ? $arParams['DETAIL_VK_USE'] : ''),
		'VK_API_ID' => (isset($arParams['DETAIL_VK_API_ID']) ? $arParams['DETAIL_VK_API_ID'] : 'API_ID'),
		'FB_USE' => (isset($arParams['DETAIL_FB_USE']) ? $arParams['DETAIL_FB_USE'] : ''),
		'FB_APP_ID' => (isset($arParams['DETAIL_FB_APP_ID']) ? $arParams['DETAIL_FB_APP_ID'] : ''),
		'BRAND_USE' => (isset($arParams['DETAIL_BRAND_USE']) ? $arParams['DETAIL_BRAND_USE'] : 'N'),
		'BRAND_PROP_CODE' => (isset($arParams['DETAIL_BRAND_PROP_CODE']) ? $arParams['DETAIL_BRAND_PROP_CODE'] : ''),
		'DISPLAY_NAME' => (isset($arParams['DETAIL_DISPLAY_NAME']) ? $arParams['DETAIL_DISPLAY_NAME'] : ''),
		'ADD_DETAIL_TO_SLIDER' => (isset($arParams['DETAIL_ADD_DETAIL_TO_SLIDER']) ? $arParams['DETAIL_ADD_DETAIL_TO_SLIDER'] : ''),
		'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
		"ADD_SECTIONS_CHAIN" => (isset($arParams["ADD_SECTIONS_CHAIN"]) ? $arParams["ADD_SECTIONS_CHAIN"] : ''),
		"ADD_ELEMENT_CHAIN" => (isset($arParams["ADD_ELEMENT_CHAIN"]) ? $arParams["ADD_ELEMENT_CHAIN"] : ''),
		"DISPLAY_PREVIEW_TEXT_MODE" => (isset($arParams['DETAIL_DISPLAY_PREVIEW_TEXT_MODE']) ? $arParams['DETAIL_DISPLAY_PREVIEW_TEXT_MODE'] : ''),
		"DETAIL_PICTURE_MODE" => (isset($arParams['DETAIL_DETAIL_PICTURE_MODE']) ? $arParams['DETAIL_DETAIL_PICTURE_MODE'] : '')
	),
	$component
);?>
<?
if (0 < $ElementID)
{?>

<?php
	if($arParams["USE_STORE"] == "Y" && \Bitrix\Main\ModuleManager::isModuleInstalled("catalog"))
	{
	    $res = CIBlockElement::GetByID($ElementID);
        if($ar_res = $res->GetNext())
          $PRODUCT_NAME = $ar_res['NAME'];
		?>
		<div id="shops-product-tab" class="j-tabs-body product-tabs-body">
	            <? 
	            
	            $rsStore = CCatalogStore::GetList(
				        array("SORT"=>"ASC"),
				        
	                    array('ACTIVE'=>'Y','ISSUING_CENTER'=>(($arParams['ALLOW_SALE'] == 'Y' )?'N':'Y'),'SHIPPING_CENTER'=>(($arParams['ALLOW_SALE'] == 'Y')?'N':'Y'),'ID'=>$arParams['STORIES_LIST'])
				);
				while ($arStore = $rsStore->Fetch()){
				    $stories[$arStore['ID']] = $arStore;
				}
				$arInfo = CCatalogSKU::GetInfoByProductIBlock (45);

				$rsOffers = CIBlockElement::GetList (array (), array (
					'IBLOCK_ID' => 46,
					'PROPERTY_' . $arInfo[ 'SKU_PROPERTY_ID' ] => $ElementID
				), false, false, array (
					'CATALOG_PROPERTY_CML2_ATTRIBUTES',
					'ID',
					'IBLOCK_ID',
					'NAME',
					'PROPERTY_CML2_ATTRIBUTES'
				));

				$arOffers = array();
				while ($arOffer = $rsOffers->Fetch()){
					$arOffers[]=$arOffer["ID"];
					$SKU[$arOffer["ID"]] = $arOffer['PROPERTY_CML2_ATTRIBUTES_VALUE'];
				}

	    		$rsStore = CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID' =>/*$SKUIDS*/$arOffers));

				$amount = array();
	    		while ($arStore = $rsStore->Fetch()){
					$amount[$arStore['STORE_ID']][] = $arStore;
	    		}
	    		?>
        		<?php foreach ($stories as $k => $v) {?>
    			<div class="shops-block j-shops-block">

                    <div class="shops-info clearfix">

                        <div class="shops-info-left">
                            <h3><?=$v['DESCRIPTION']?></h3>
                            <span><?=$v['ADDRESS']?><br /> <?=$v['PHONE']?></span>
                        </div>

                    </div>
                    <div class="products-count clearfix">
                        <ul class="products-count-left"><?php
                        if ($amount[$v['ID']] && !stristr($PRODUCT_NAME,'сертификат')) {
                            $cnt=0;
                            foreach ($amount[$v['ID']] as $ak => $av) {
                                if ($av['AMOUNT'] > 0 && trim($SKU[$av['PRODUCT_ID']])) {
                                    $cnt++;
                                    ?><li><?=$SKU[$av['PRODUCT_ID']]?> (<?=($av['AMOUNT'])?>)</li><?php
                                }
                            }
                            if (!$cnt){
                            if (stristr($PRODUCT_NAME,'сертификат')){?><li>в наличии</li><?php }else{?><li>нет в наличии</li><?php }
                             }
                         } else { if (stristr($PRODUCT_NAME,'сертификат')){?><li>в наличии</li><?php }else{?><li>нет в наличии</li><?php }}?>
                        </ul>
                    </div>
                </div>
                <?php
	    		}?>
        </div>
		<?
	}
	?>

	<?
if ('Y' == $arParams['DETAIL_USE_COMMENTS'])
{
?>
	<div id="comments-product-tab" class="j-tabs-body product-tabs-body">
                <?$APPLICATION->IncludeComponent(
                	"bitrix:catalog.comments",
                	"",
                	array(
                		"ELEMENT_ID" => $ElementID,
                		"ELEMENT_CODE" => "",
                		"IBLOCK_ID" => $arParams['IBLOCK_ID'],
                		"URL_TO_COMMENT" => "",
                		"WIDTH" => "",
                		"COMMENTS_COUNT" => "45",
                		'BLOG_USE' => (isset($arParams['DETAIL_BLOG_USE']) ? $arParams['DETAIL_BLOG_USE'] : ''),
                		'VK_USE' => (isset($arParams['DETAIL_VK_USE']) ? $arParams['DETAIL_VK_USE'] : ''),
                		'VK_API_ID' => (isset($arParams['DETAIL_VK_API_ID']) ? $arParams['DETAIL_VK_API_ID'] : 'API_ID'),
                		'FB_USE' => (isset($arParams['DETAIL_FB_USE']) ? $arParams['DETAIL_FB_USE'] : ''),
                		'FB_APP_ID' => (isset($arParams['DETAIL_FB_APP_ID']) ? $arParams['DETAIL_FB_APP_ID'] : ''),
                		"CACHE_TYPE" => $arParams['CACHE_TYPE'],
                		"CACHE_TIME" => $arParams['CACHE_TIME'],
                		"BLOG_TITLE" => "",
                		"BLOG_URL" => "",
                		"PATH_TO_SMILE" => "/bitrix/images/blog/smile/",
                		"EMAIL_NOTIFY" => "Y",
                		"AJAX_POST" => "Y",
                		"SHOW_SPAM" => "N",
                		"SHOW_RATING" => "N",
                		"FB_TITLE" => "",
                		"FB_USER_ADMIN_ID" => "",
                		"FB_APP_ID" => $arParams['FB_APP_ID'],
                		"FB_COLORSCHEME" => "light",
                		"FB_ORDER_BY" => "reverse_time",
                		"VK_TITLE" => "",
                	),
                	$component,
                	array("HIDE_ICONS" => "Y")
                );


                ?>
            </div>
	<?
}
?>

	</div>
	<?php
}
?>