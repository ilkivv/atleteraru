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

if (!empty($arResult['ITEMS']))
{
	$arSkuTemplate = array();
	$strElementEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT");
	$strElementDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE");
	$arElementDeleteParams = array("CONFIRM" => GetMessage('CT_BCS_TPL_ELEMENT_DELETE_CONFIRM'));
?>

<? echo $arResult["NAV_STRING"]; ?>
<div class="catalog-products-list">
<?

foreach ($arResult['ITEMS'] as $key => $arItem)
{
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $strElementEdit);
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $strElementDelete, $arElementDeleteParams);
	$strMainID = $this->GetEditAreaId($arItem['ID']);

	$arItemIDs = array(
		'ID' => $strMainID,
		'PICT' => $strMainID.'_pict',
		'SECOND_PICT' => $strMainID.'_secondpict',
		'STICKER_ID' => $strMainID.'_sticker',
		'SECOND_STICKER_ID' => $strMainID.'_secondsticker',
		'QUANTITY' => $strMainID.'_quantity',
		'QUANTITY_DOWN' => $strMainID.'_quant_down',
		'QUANTITY_UP' => $strMainID.'_quant_up',
		'QUANTITY_MEASURE' => $strMainID.'_quant_measure',
		'BUY_LINK' => $strMainID.'_buy_link',
		'SUBSCRIBE_LINK' => $strMainID.'_subscribe',

		'PRICE' => $strMainID.'_price',
		'DSC_PERC' => $strMainID.'_dsc_perc',
		'SECOND_DSC_PERC' => $strMainID.'_second_dsc_perc',

		'PROP_DIV' => $strMainID.'_sku_tree',
		'PROP' => $strMainID.'_prop_',
		'DISPLAY_PROP_DIV' => $strMainID.'_sku_prop',
		'BASKET_PROP_DIV' => $strMainID.'_basket_prop',
	);

	$strObName = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainID);

	$productTitle = (
		isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])&& $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] != ''
		? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
		: $arItem['NAME']
	);
	$imgTitle = (
		isset($arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] != ''
		? $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE']
		: $arItem['NAME']
	);
	?>
	<div class="product-item">
	    <div class="j-catalog-item catalog-item"  id="<? echo $strMainID; ?>" rel="<?=$arItem['ID']?>" data-value="<?=$arItem['IBLOCK_SECTION_ID'];?>" >
		<a href="<? echo $arItem['DETAIL_PAGE_URL']; ?>" class="catalog-item-block" title="<? echo $imgTitle; ?>">
    		<?
    		$ar_new_groups = array();
    		$groups = CIBlockElement::GetElementGroups($arItem['ID'], true);
    		while($group = $groups->Fetch()) {
    			$ar_new_groups[] =  '<a href="/e-store/'.$group['LIST_PAGE_URL'].$group['ID'].'/">'.$group['NAME'].'</a>';
    			if ($group['NAME'] == 'Распродажа !!!') {
    				$arItem['SECTION']['NAME'] = 'Распродажа';
    			}
    		}
            	if ('Y' == $arParams['SHOW_DISCOUNT_PERCENT'] && false)
            	{
            	?>
        			<div id="<? echo $arItemIDs['DSC_PERC']; ?>" class="bx_stick_disc right bottom" style="display:<? echo (0 < $arItem['MIN_PRICE']['DISCOUNT_DIFF_PERCENT'] ? '' : 'none'); ?>;">-<? echo $arItem['MIN_PRICE']['DISCOUNT_DIFF_PERCENT']; ?>%</div>
            	<?
            	}
            	if ($arItem['LABEL'])
            	{
            	?>
            	<em id="<? echo $arItemIDs['STICKER_ID']; ?>" ><? echo $arItem['LABEL_VALUE']; ?></em>
            	<?
            	}elseif ($arItem['PROPERTIES']['CML2_PRICEGROUP']['VALUE']) {
			?><em id=""><?=$arItem['PROPERTIES']['CML2_PRICEGROUP']['VALUE']?></em><?php 
			}
	            ?>
    		<img src="<? echo $arItem['PREVIEW_PICTURE']['SRC']; ?>" alt="" />
    		<span class="catalog-item-title"><? echo $productTitle; ?></span>
		</a>
		<span class="catalog-item-cats">
            <?php
                echo join(", ",$ar_new_groups);
                ?>
        </span>
        <?
	if (isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
	{
		if ('Y' == $arParams['PRODUCT_DISPLAY_MODE'])
		{
			?>
			<?
			if ('Y' == $arParams['USE_PRODUCT_QUANTITY'])
			{
			?>
			<input type="hidden" class="bx_col_input" id="<? echo $arItemIDs['QUANTITY']; ?>" name="<? echo $arParams["PRODUCT_QUANTITY_VARIABLE"]; ?>" value="<? echo $arItem['CATALOG_MEASURE_RATIO']; ?>">
			<?
			}
			
			$basketLink = '<a id="'.$strMainID .'" class="to-basket" href="#"><span>В корзину</span></a>';
		}
		
		$boolShowOfferProps = ('Y' == $arParams['PRODUCT_DISPLAY_MODE'] && $arItem['OFFERS_PROPS_DISPLAY']);
		$boolShowProductProps = (isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES']));
		
		if ('Y' == $arParams['PRODUCT_DISPLAY_MODE'])
		{
			if (!empty($arItem['OFFERS_PROP']) && $arItem['OFFERS_BUY_PROPS'])
			{
                 ?>                
                <div class="choose-taste j-choose-taste <?php if (count($arItem['OFFERS_BUY_PROPS'])<2){echo "no-arrow";}?>">
                    <div data-value="<?=$arItem['OFFERS_BUY_PROPS'][0]['SKU_ID']?>" class="choose-taste-link j-choose-taste-link" id="<?=$strMainID?>_prop" data-id="<?=$arItem['OFFERS_BUY_PROPS'][0]['ID']?>"><span><?=$arItem['OFFERS_BUY_PROPS'][0]['VALUE']?>  <?php /*?>(<?php if ($arItem['OFFERS_BUY_PROPS'][0]['AMOUNT'] >10){echo ">10";}else{echo $arItem['OFFERS_BUY_PROPS'][0]['AMOUNT']*1;}?>)<?*/?></span> <em></em></div>
                    <div class="choose-taste-list-container">
                        <div class="choose-taste-list j-choose-taste-list">
                        <?php 
            				foreach ($arItem['OFFERS_BUY_PROPS'] as $code => $property)
            				{
            				    ?><div class="item" data-value="<?=$property['SKU_ID']?>"><?=$property['VALUE']?><?php /*?> (<?php if ($property['AMOUNT'] >10){echo ">10";}else{echo $property['AMOUNT']*1;}?>)<?*/?></div><?php 
            				}
                        ?>
                        </div>
                    </div>
                    <input type="hidden" name="taste" class="j-choose-taste-input" />
                </div>
                <?
			}
		} 
	}?>
        <div class="catalog-item-price"<?php if (empty($arItem['MIN_PRICE'])){echo "style='height:70px;'";}?>>
                  <div id="<? echo $arItemIDs['PRICE']; ?>">
                    <?
                	if (!empty($arItem['MIN_PRICE']))
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
                	} elseif($arParams['ALLOW_SALE'] == 'N'){
                	    ?><span class="is-empty">Нет в наличии</span>
                	    </div>
                	    <div><span class="new-price"><?php if ($arItem['DISPLAY_PROPERTIES']['MAX_PRICE']['VALUE']){echo $arItem['DISPLAY_PROPERTIES']['MAX_PRICE']['VALUE'] . ' р.';};?></span>
                	    <?php
                	}else {
            $basketLink = '';
                	    ?><span class="is-empty">Только в розничных магазинах</span>
                	    </div>
                	    <div><span class="new-price"><?php if ($arItem['DISPLAY_PROPERTIES']['MAX_PRICE']['VALUE']){echo $arItem['DISPLAY_PROPERTIES']['MAX_PRICE']['VALUE'] . ' р.';};?></span>
                	    <?php 
                	}
                	?></div>
            	<?php if ($arParams['ALLOW_SALE'] == 'Y'):?><?=$basketLink;	?><?php endif;?>
            </div>
     <div class="few-products j-error"></div>
    <?php if ($arParams['ALLOW_SALE'] == 'Y'):?><div class="in-basket" <?php if ($arItem['OFFERS_BUY_PROPS'][0]['IN_BASKET']){echo "style='display:block;'";}?>><?php if ($arItem['OFFERS_BUY_PROPS'][0]['IN_BASKET']){?><a href="/personal/basket/">В корзине (<?=$arItem['OFFERS_BUY_PROPS'][0]['IN_BASKET']?>)</a> <span class="j-remove-item">Убрать 1</span><?php }?></div><?php endif;?>
     <?php if (!stristr( $arItem['NAME'],'Подарочный сертификат')){?><a class="quantity j-quantity" data-id="1" href="#">Узнать наличие в магазинах</a><?php }?>
    <?php if (isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
    {?>
    <script type="text/javascript">
    var <? echo $strObName; ?> = <? echo CUtil::PhpToJSObject($arItem['JS_OFFERS'], false, true); ?>;
    </script>
     <?php }?>
    </div></div><?
    }
    ?>
</div>

<?
	if ($arParams["DISPLAY_BOTTOM_PAGER"])
	{
		?><? echo $arResult["NAV_STRING"]; ?><?
	}
}
?>