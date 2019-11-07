<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var string $strElementEdit */
/** @var string $strElementDelete */
/** @var array $arElementDeleteParams */
/** @var array $arSkuTemplate */
/** @var array $templateData */
$intCount = count($arResult['ITEMS']);
$strItemWidth = 100/$intCount;
$strAllWidth = 100*$intCount;
$arRowIDs = array();
$strContID = 'bx_catalog_slider_'.mt_rand(0, 1000000);
$boolFirst = true;
foreach ($arResult['ITEMS'] as $key => $arItem)
{
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $strElementEdit);
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $strElementDelete, $arElementDeleteParams);
	$strMainID = $this->GetEditAreaId($arItem['ID']);

	$arRowIDs[] = $strMainID;
	$arItemIDs = array(
		'ID' => $strMainID,
		'PICT' => $strMainID.'_pict',

		'QUANTITY' => $strMainID.'_quantity',
		'QUANTITY_DOWN' => $strMainID.'_quant_down',
		'QUANTITY_UP' => $strMainID.'_quant_up',
		'QUANTITY_MEASURE' => $strMainID.'_quant_measure',
		'BUY_LINK' => $strMainID.'_buy_link',

		'PRICE' => $strMainID.'_price',
		'OLD_PRICE' => $strMainID.'_old_price',
		'DSC_PERC' => $strMainID.'_dsc_perc',
		'BASKET_PROP_DIV' => $strMainID.'_basket_prop'
	);

	$strObName = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainID);
	$strTitle = (
		isset($arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]) && '' != isset($arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"])
		? $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]
		: $arItem['NAME']
	);
?>
    <div class="j-catalog-item item-of-the-day catalog-item" id="<? echo $strMainID; ?>" rel="<?=$arItem['ID']?>" data-value="<?=$arItem['IBLOCK_SECTION_ID'];?>" >
        <div class="item-of-the-day-title">Товар дня</div>
        <a class="catalog-item-block" href="<? echo $arItem['DETAIL_PAGE_URL']; ?>">
            <img src="<? echo ($arItem['PREVIEW_PICTURE']['SRC'])?$arItem['PREVIEW_PICTURE']['SRC']:"/bitrix/templates/.default/components/bitrix/catalog/.default/bitrix/catalog.element/.default/images/no_photo.png"; ?>" alt="<? echo $strTitle; ?>" />
            <span class="catalog-item-title"><? echo $arItem['NAME']; ?></span>
        </a>
        <span class="catalog-item-cats">
                <?php
                $ar_new_groups = array();
                $groups = CIBlockElement::GetElementGroups($arItem['ID'], true);
                while($group = $groups->Fetch())
                    $ar_new_groups[] =  '<a href="/e-store/'.$group['ID'].'/">'.$group['NAME'].'</a>';
                
                echo join(", ",$ar_new_groups);
                ?>
                
            </span>
             <?php 
            $can_buy = false;
             if (isset($arItem['OFFERS']) &&  !empty($arItem['OFFERS']) && $arItem['OFFERS_BUY_PROPS'] ) {?>
                <div class="choose-taste j-choose-taste <?php if (count($arItem['OFFERS_BUY_PROPS'])<2){echo "no-arrow";}?>">
                    <div data-value="<?=$arItem['OFFERS_BUY_PROPS'][0]['SKU_ID']?>" class="choose-taste-link j-choose-taste-link" id="<?=$strMainID?>_prop" data-id="<?=$arItem['OFFERS_BUY_PROPS'][0]['ID']?>"><span><?=$arItem['OFFERS_BUY_PROPS'][0]['VALUE']?> <?php /*?>(<?php if ($arItem['OFFERS_BUY_PROPS'][0]['AMOUNT'] >10){echo ">10";}else{echo $arItem['OFFERS_BUY_PROPS'][0]['AMOUNT'];}?>)<?*/?></span> <em></em></div>
                    <div class="choose-taste-list-container">
                        <div class="choose-taste-list j-choose-taste-list">
                        <?php 
            				foreach ($arItem['OFFERS_BUY_PROPS'] as $code => $property)
            				{
            				    ?><div class="item" data-value="<?=$property['SKU_ID']?>"><?=$property['VALUE']?> <?php /*?>(<?php if ($property['AMOUNT'] >10){echo ">10";}else{echo $property['AMOUNT'];}?>)<?php */?></div><?php 
            				}
                        ?>
                        </div>
                    </div>
                    <input type="hidden" name="taste" class="j-choose-taste-input" />
                </div>
            <?php }?>
            <div class="catalog-item-price"<?php if (empty($arItem['MIN_PRICE'])){echo "style='height:70px;'";}?>>
                  <div id="<? echo $arItemIDs['PRICE']; ?>">
                    <?
                	if (!empty($arItem['MIN_PRICE']) && !empty($arItem['OFFERS']) && $arItem['OFFERS_BUY_PROPS'][0]['PRICE']['PRINT_VALUE'])
                	{
                	    $basketLink = '<a id="'.$strMainID .'" class="to-basket" href="#"><span>В корзину</span></a>';
                		if($arItem['OFFERS_BUY_PROPS'][0]['OLD_PRICE']['VALUE'] != $arItem['OFFERS_BUY_PROPS'][0]['PRICE']['VALUE']) {
                        ?>  <span class="old-price"><? echo $arItem['OFFERS_BUY_PROPS'][0]['OLD_PRICE']['PRINT_VALUE']; ?></span> <?
                        }
                		?><span class="new-price"><?php
                		if ($arItem['OFFERS_BUY_PROPS'][0]['PRICE']['PRINT_VALUE'])
                		{
                			echo $arItem['OFFERS_BUY_PROPS'][0]['PRICE']['PRINT_VALUE'];
                			//print_r($arItem['OFFERS_BUY_PROPS'][0]);
                		}
                		?></span><?php
                		$hideLink = false;
                	} elseif($arParams['ALLOW_SALE'] == 'N'){
                	    $hideLink = true;
                    $basketLink = '';
                	    ?><span class="is-empty">Нет в наличии</span>
                	    <span class="new-price"><?php if ($arItem['DISPLAY_PROPERTIES']['MAX_PRICE']['VALUE']){echo $arItem['DISPLAY_PROPERTIES']['MAX_PRICE']['VALUE'] . ' р.';};?></span>
                	    <?php
                	}else {
                	    $hideLink = false;
                    $basketLink = '';
                	    ?><span class="is-empty">Только в розничных магазинах</span>
                	    <span class="new-price" style="width:100%;height:40px;"><?php if ($arItem['DISPLAY_PROPERTIES']['MAX_PRICE']['VALUE']){echo $arItem['DISPLAY_PROPERTIES']['MAX_PRICE']['VALUE'] . ' р.';};?></span>
                	    <?php
                	}
                	?></div>
            	<?php if ($arParams['ALLOW_SALE'] == 'Y'):?><?=$basketLink;	?><?php endif;echo $arParams['ALLOW_SALE'];?>
            </div>
             <div class="few-products j-error"></div>
            <?php if ($arParams['ALLOW_SALE'] == 'Y'):?><div class="in-basket" <?php if ($arItem['OFFERS_BUY_PROPS'][0]['IN_BASKET']){echo "style='display:block;'";}?>><?php if ($arItem['OFFERS_BUY_PROPS'][0]['IN_BASKET']){?><a href="/personal/basket/">В корзине (<?=$arItem['OFFERS_BUY_PROPS'][0]['IN_BASKET']?>)</a> <span class="j-remove-item">Убрать 1</span><?php }?></div><?php endif;?>
             <?php if (!stristr( $arItem['NAME'],'Подарочный сертификат')){
             if ($arParams['HIDE_EMPTY'] == 'Y' && $hideLink) { } else {
                 ?><a class="quantity j-quantity" data-id="1" href="#">Узнать наличие в магазинах</a><?php
             }
             }?>
            <div class="j-error"></div>
            <?php if (isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
{?>
<script type="text/javascript">
var <? echo $strObName; ?> = <? echo CUtil::PhpToJSObject($arItem['JS_OFFERS'], false, true); ?>;
</script>
 <?php }?>
    </div>
<?
}
?>
