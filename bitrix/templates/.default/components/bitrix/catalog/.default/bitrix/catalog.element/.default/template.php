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
$templateData = array(
	'TEMPLATE_THEME' => $this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css',
	'TEMPLATE_CLASS' => 'bx_'.$arParams['TEMPLATE_THEME']
);

$strMainID = $this->GetEditAreaId($arResult['ID']);
$arItemIDs = array(
	'ID' => $strMainID,
	'PICT' => $strMainID.'_pict',
	'DISCOUNT_PICT_ID' => $strMainID.'_dsc_pict',
	'STICKER_ID' => $strMainID.'_sticker',
	'BIG_SLIDER_ID' => $strMainID.'_big_slider',
	'BIG_IMG_CONT_ID' => $strMainID.'_bigimg_cont',
	'SLIDER_CONT_ID' => $strMainID.'_slider_cont',
	'SLIDER_LIST' => $strMainID.'_slider_list',
	'SLIDER_LEFT' => $strMainID.'_slider_left',
	'SLIDER_RIGHT' => $strMainID.'_slider_right',
	'OLD_PRICE' => $strMainID.'_old_price',
	'PRICE' => $strMainID.'_price',
	'DISCOUNT_PRICE' => $strMainID.'_price_discount',
	'SLIDER_CONT_OF_ID' => $strMainID.'_slider_cont_',
	'SLIDER_LIST_OF_ID' => $strMainID.'_slider_list_',
	'SLIDER_LEFT_OF_ID' => $strMainID.'_slider_left_',
	'SLIDER_RIGHT_OF_ID' => $strMainID.'_slider_right_',
	'QUANTITY' => $strMainID.'_quantity',
	'QUANTITY_DOWN' => $strMainID.'_quant_down',
	'QUANTITY_UP' => $strMainID.'_quant_up',
	'QUANTITY_MEASURE' => $strMainID.'_quant_measure',
	'QUANTITY_LIMIT' => $strMainID.'_quant_limit',
	'BUY_LINK' => $strMainID.'_buy_link',
	'ADD_BASKET_LINK' => $strMainID.'_add_basket_link',
	'COMPARE_LINK' => $strMainID.'_compare_link',
	'PROP' => $strMainID.'_prop_',
	'PROP_DIV' => $strMainID.'_skudiv',
	'DISPLAY_PROP_DIV' => $strMainID.'_sku_prop',
	'OFFER_GROUP' => $strMainID.'_set_group_',
	'BASKET_PROP_DIV' => $strMainID.'_basket_prop',
);
$strObName = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainID);
$templateData['JS_OBJ'] = $strObName;

$strTitle = (
	isset($arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"]) && '' != $arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"]
	? $arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"]
	: $arResult['NAME']
);
$strAlt = (
	isset($arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"]) && '' != $arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"]
	? $arResult["IPROPERTY_VALUES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"]
	: $arResult['NAME']
);

reset($arResult['MORE_PHOTO']);
$arFirstPhoto = current($arResult['MORE_PHOTO']);
if ($arFirstPhoto['ID']) {
    $arWaterMark = Array(
            array(
                    "name" => "watermark",
                    "position" => "bottomleft", // Положение
                    "type" => "image",
                    "size" => "real",
                    "file" => $_SERVER["DOCUMENT_ROOT"].'/upload/watermark.png', // Путь к картинке
                    "fill" => "exact",
            )
    );
    $arFileTmp = CFile::ResizeImageGet(
            $arFirstPhoto['ID'],
            array("width" => 288, "height" => 288),
            BX_RESIZE_IMAGE_EXACT,
            true,
            $arWaterMark
    );
	
	 //$arFileTmp['src'] = urlencode($arFirstPhoto['SRC']);
	//echo "<pre>"; var_dump($arFirstPhoto); echo "<pre>";
}
?>

<?


//echo "<pre>"; var_dump($arResult['PROPERTIES']['CML2_MANUFACTURER']['VALUE']); echo "</pre>";

$brand = str_replace(array("\"","'"), '', $arResult['PROPERTIES']['CML2_MANUFACTURER']['VALUE']);


if($arResult['IBLOCK_SECTION_ID']){
		
	$rsSect = CIBlockSection::GetList(array(), array('ID' =>$arResult['IBLOCK_SECTION_ID']), false, array('ID', 'NAME'));
	if($arSect = $rsSect->GetNext())
	{
	   $category =  str_replace(array("\"","'"), '', $arSect['NAME']);
	}
	
}



if($arResult['OFFERS']){
	
	$countOffer = count($arResult['OFFERS']);
	
	$counter = 0;	
	
		?>
		
		<script type="text/javascript">		  
				dataLayer.push({
				"ecommerce": {
					"detail": {
						"products": [
							<?
							foreach($arResult['OFFERS'] as $offer){
								$counter++;
							?>
							{								
								"id": "<?=$offer['ID']?>",
								"name" : "<?=str_replace(array("\"","'"), '', $offer['NAME']); ?>",								
								<?if($brand):?>
								"brand": "<?=$brand?>",
								<?endif?>
								<?if($category):?>
								"category": "<?=$category?>",
								<?endif?>
								"price": <?=$offer["MIN_PRICE"]["DISCOUNT_VALUE"]?>
							   
							}<?if($counter < $countOffer){?>,<?}?>
							<?}?>
						   
						]
					}
				}
			});
		</script>
		
		
		<?
	
}else{
	?>
	
	<script type="text/javascript">		  
			dataLayer.push({
			"ecommerce": {
				"detail": {
					"products": [
						{

							"id": "<?=$arResult['ID']?>",
							"name" : "<?=str_replace(array("\"","'"), '', $arResult['NAME']); ?>",								
							<?if($brand):?>
							"brand": "<?=$brand?>",
							<?endif?>
							<?if($category):?>
							"category": "<?=$category?>",
							<?endif?>
							"price": <?=$arResult["MIN_PRICE"]["DISCOUNT_VALUE"]?>
						   
						},
					   
					]
				}
			}
		});
	</script>
	
	<?
}

?>

<div itemscope itemtype="http://schema.org/Product">
<div itemprop="aggregateRating" itemscope="" itemtype="https://schema.org/AggregateRating">
<meta itemprop="ratingValue" content="4.5">
<meta itemprop="reviewCount" content="27">
</div>
<div class="j-catalog-item js-product-container product clearfix" id="<? echo $arItemIDs['ID']; ?>" 
		data-value="<?=$arResult['IBLOCK_SECTION_ID'];?>" 
		rel="<?=$arResult['ID'];?>"
		
			data-id="<?=$arResult['ID']?>" 
			data-name="<?=str_replace(array("\"","'"), '', $arResult['NAME']);?>"
			data-category="<?=$category?>"
			data-price="<?=$arResult['MIN_PRICE']["DISCOUNT_VALUE"]?>"
			data-brand="<?=$brand?>"
		
		>
    <div class="product-img">
       <?php if ($arResult['LABEL'])
            {
            ?>
             <em id="<? echo $arItemIDs['STICKER_ID'] ?>"><? echo $arResult['LABEL_VALUE']; ?></em>
            <?
            }elseif ($arResult['PROPERTIES']['CML2_PRICEGROUP']['VALUE']) {
			?><em id=""><?=$arResult['PROPERTIES']['CML2_PRICEGROUP']['VALUE']?></em><?php 
			}
            ?>
        <img itemprop="image" id="<? echo $arItemIDs['PICT']; ?>" src="<? echo ($arFileTmp['src'])?$arFileTmp['src']:$arFirstPhoto['SRC']; ?>" alt="<? echo $strAlt; ?>" title="<? echo $strTitle; ?>" />
    </div>
    <div class="product-body">
    <?
    if ('Y' == $arParams['DISPLAY_NAME'])
    {
    ?>
        <h1 itemprop="name"><? echo (
		isset($arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"]) && '' != $arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"]
		? $arResult["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"]
		: $arResult["NAME"]
		); ?></h1>
		<?
    }
   $PRODUCT_NAME =$arResult["NAME"]; 
    ?>
        <div class="products-cat"><a href="/e-store/<?=$arResult['IBLOCK_SECTION_ID'];?>/"><?=$arResult['SECTION']['NAME']?></a></div>
        <div class="product-control-container">
        <?php 
            if (isset($arResult['OFFERS']) && !empty($arResult['OFFERS']))
            {
                   /* echo "<!--11";
					print_r($arResult['OFFERS']);
					echo "-->";*/
					
                foreach ($arResult['OFFERS'] as $k => $v) {
	                $SKUIDS[] = $v['ID'];
	                $SKU[$v['ID']] = $v;
	                $GLOBALS['SKU'][$v['ID']] = $v;
                    if ($v['AMOUNT'] > 0) {
	                $prices[] = array('VALUE'=>$v['MIN_PRICE']['VALUE'],'PRINT_VALUE'=>$v['MIN_PRICE']['PRINT_VALUE']);
	                }
                }
				
				
				//echo "<pre>"; var_dump($arResult['OFFERS_BUY_PROPS']); echo "</pre>";
				
				//echo "<pre>"; var_dump($arResult['OFFERS_BUY_PROPS']); echo "</pre>";
				
				
				
                ?>
				
				
				
            <label>Вариант</label> 
                <div class="choose-taste big-choose-taste j-choose-taste <?if (!$arResult['TOTAL_AMOUNT']){echo "disabled";}elseif( count($arResult['OFFERS_BUY_PROPS']) <2){echo "no-arrow";}?>">
                    <div data-value="<?=$arResult['OFFERS_BUY_PROPS'][0]['SKU_ID']?>" class="choose-taste-link j-choose-taste-link" id="<?=$strMainID?>_prop" data-id="<?=$arResult['OFFERS_BUY_PROPS'][0]['ID']?>"><span><?=$arResult['OFFERS_BUY_PROPS'][0]['VALUE']?> <?php /*?>(<?php if ($arResult['OFFERS_BUY_PROPS'][0]['AMOUNT'] >10){echo ">10";}else{echo $arResult['OFFERS_BUY_PROPS'][0]['AMOUNT']*1;}?>)<?php */?></span> <em></em></div>
                    <div class="choose-taste-list-container">
                        <div class="choose-taste-list j-choose-taste-list">
                        <?php 
            				foreach ($arResult['OFFERS_BUY_PROPS'] as $code => $property)
            				{
            				   ?><div class="item" data-value="<?=$property['SKU_ID']?>"><?=$property['VALUE']?> <?php /*?>(<?php if ($property['AMOUNT'] >10){echo ">10";}else{echo $property['AMOUNT']*1;}?>)<?*/?></div><?php 
            				}
                        ?>
                        </div>
                    </div>
                </div>
                <?
			}
        ?>
        </div>
<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
        
                <?
                if ($arResult['TOTAL_AMOUNT'] )
                {
                	if($arParams['ALLOW_SALE'] == 'Y'){
                ?>
            	<div class="product-control-container last">
                    <label for="<? echo $arItemIDs['QUANTITY']; ?>" type="text"">Количество</label>
                    <input id="<? echo $arItemIDs['QUANTITY']; ?>" type="text" value="<? echo (isset($arResult['OFFERS']) && !empty($arResult['OFFERS']) ? 1 : $arResult['CATALOG_MEASURE_RATIO']); ?>" name="count" data-big="true" class="j-discrete-input" />
            	</div>
                <?php }?>

                <div class="product-price">
                <?php if($arResult['OFFERS_BUY_PROPS'][0]['OLD_PRICE']['VALUE'] != $arResult['OFFERS_BUY_PROPS'][0]['PRICE']['VALUE']) {
                ?> <s style="margin:10px;"><span class="old-price"><? echo $arResult['OFFERS_BUY_PROPS'][0]['OLD_PRICE']['PRINT_VALUE']; ?></span></s> <?
					}?>
                    <strong itemprop="price"><? echo $arResult['OFFERS_BUY_PROPS'][0]['PRICE']['PRINT_VALUE']; ?></strong>
                <?php 
            	if ('Y' == $arParams['SHOW_MAX_QUANTITY'])
            	{
            		if (isset($arResult['OFFERS']) && !empty($arResult['OFFERS']))
            		{
            		    $hideLink = true;
        		    ?>
                    <span class="exists">Товар в наличии</span>
            		<?php 
            		}
            		else
            		{
            			if ('Y' == $arResult['CATALOG_QUANTITY_TRACE'] && 'N' == $arResult['CATALOG_CAN_BUY_ZERO'])
            			{
                        ?>
                        	<p id="<? echo $arItemIDs['QUANTITY_LIMIT']; ?>"><? echo GetMessage('OSTATOK'); ?>: <span><? echo $arResult['CATALOG_QUANTITY']; ?></span></p>
                        <?
            			}
            		}
            	}
                ?>
<meta itemprop="priceCurrency" content="RUB">
<link itemprop="availability" href="http://schema.org/InStock">
                </div>
  </div>
                <?php if ($arResult['MIN_PRICE']['PRINT_DISCOUNT_VALUE'] && $arParams['ALLOW_SALE'] == 'Y') {?>
                <a class="screw-button to-basket" href="javascript:void(0);" id="<? echo $arItemIDs['BUY_LINK']; ?>"><span>Добавить в корзину</span></a>
                <?php }?>
                <div style="padding-top:5px;">
                <div class="few-products j-error"></div>
                <div class="in-basket" ></div>
                </div>
    
                <?} elseif($arParams['ALLOW_SALE'] == 'Y') {?>
                <div class="product-control-container last">
                            <label class="disabled" for="productCount">Количество</label>
                            <input id="productCount" type="text" value="1" name="count" data-big="true" class="j-discrete-input disabled" />
                        </div>
                        
                        <div class="product-price">
                        <?php if($arResult['OFFERS_BUY_PROPS'][0]['OLD_PRICE']['VALUE'] != $arResult['OFFERS_BUY_PROPS'][0]['PRICE']['VALUE']) {
                        ?> <s style="margin:10px;"><span class="old-price"><? echo $arResult['OFFERS_BUY_PROPS'][0]['OLD_PRICE']['PRINT_VALUE']; ?></span></s> <?
                        }?>
                            <strong><? echo $arResult['OFFERS_BUY_PROPS'][0]['PRICE']['PRINT_VALUE']; ?></strong>
                            <span class="empty">Товара нет на складе</span>
                        </div>

                        <div class="product-tip">Но, вы можете купить его в одном из наших розничных магазинов</div>
                <?php 
                $hideLink = true;
                }else {
                    $hideLink = true;
                    ?>
                        <div class="product-price">
                            <strong><? echo $arResult['OFFERS_BUY_PROPS'][0]['PRICE']['PRINT_VALUE']; ?></strong>
                            <span class="empty">Товара нет в наличии</span>
                        </div>
                                    <?php
                } ?>
    </div>
	   <div class="j-flash-message-container" style="margin:20px;"></div>
</div>
<?php if (isset($arResult['OFFERS']) && !empty($arResult['OFFERS']))
{?>
<script type="text/javascript">
var <? echo $strObName; ?> = <? echo CUtil::PhpToJSObject($arResult['JS_OFFERS'], false, true); ?>;
</script>
 <?php }?>
<div class="j-tabs">

    <div class="product-tabs j-tabs-links">
        <ul>
            <?php if ('' != $arResult['DETAIL_TEXT'] || '' != $arResult['PREVIEW_TEXT']) {?>
            <li><a class="active" rel="description-product-tab" href="#"><span>Описание</span></a></li>
            <?php }?>
            
            <?php if ($arParams["USE_STORE"] == "Y" && \Bitrix\Main\ModuleManager::isModuleInstalled("catalog") && !($hideLink && $arParams['HIDE_EMPTY'] && !$arParams['ALLOW_SALE'])) {?>
            <li><a rel="shops-product-tab" href="#"><span>Наличие в розничных магазинах</span></a></li>
            <?php }?>
            
          	<?php if ('Y' == $arParams['USE_COMMENTS']) { ?>
            <li><a rel="comments-product-tab" href="#"><span>Отзывы (<span class='j-comments-cnt'></span>)</span></a></li>
            <?php } ?>
        </ul>
    </div>
	
        <? if ('' != $arResult['DETAIL_TEXT']) { ?>
		
		
        	<div id="description-product-tab" class="j-tabs-body product-tabs-body text" style="display: block;" itemprop="description">
						
			<? if ('html' == $arResult['DETAIL_TEXT_TYPE']) {
        		echo $arResult['DETAIL_TEXT'];
        	} else { ?>
				<p><? echo $arResult['DETAIL_TEXT']; ?></p>
			<? } ?>
			
        	</div>
			
			<?/*</div>*/?>
			
        <? }elseif ('' != $arResult['PREVIEW_TEXT']) { ?>
        	
			<div id="description-product-tab" class="j-tabs-body product-tabs-body text" style="display: block;" itemprop="description">
				
				<? if ('html' == $arResult['PREVIEW_TEXT_TYPE']) {
					echo $arResult['PREVIEW_TEXT'];
				} else { ?>
					<p><? echo $arResult['PREVIEW_TEXT']; ?></p>
				<? } ?>
				
        	</div>
			
			<?/*</div>*/?>
			<? }
    
    
    