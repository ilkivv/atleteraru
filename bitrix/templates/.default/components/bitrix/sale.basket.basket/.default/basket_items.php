<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//echo ShowError($arResult["ERROR_MESSAGE"]);

$bDelayColumn  = false;
$bDeleteColumn = false;
$bWeightColumn = false;
$bPropsColumn  = false;
$bPriceType    = false;
if ($normalCount > 0):

foreach ($arResult["GRID"]["HEADERS"] as $id => $arHeader){
$arHeaders[] = $arHeader["id"];
}
foreach ($arResult["GRID"]["ROWS"] as $k => $arItem) {
$ids[] = $arItem['PRODUCT_ID'];
}
if ($ids) {
    $res = CIBlockElement::GetList(Array("SORT"=>"ASC"),
                                    Array('ID'=>$ids,'IBLOCK_ID'=>14),
                                     false,
                                    false,
                                    array('IBLOCK_SECTION_ID','ID')
                                    );
    $ids = array();
    while($ob = $res->GetNextElement())
    {
        $arFields = $ob->GetFields();
        
        $products[$arFields['ID']] = $arFields['IBLOCK_SECTION_ID'];
        $ids[] = $arFields['IBLOCK_SECTION_ID'];
    }
    $res= CIBlockSection::GetList(
                                Array("SORT"=>"ASC"),
                                Array('ID'=>$ids,'IBLOCK_ID'=>14),
                                false,
                                Array(),
                                false
                        );
    while($ob = $res->GetNextElement())
    {
        $arFields = $ob->GetFields();
        $sections[$arFields['ID']] = $arFields;
    }
}
?>
<div id="basket_items_list">
	<div class="bx_ordercart_order_table_container">
		<table class="basket-table" id="basket_items">
                        <colgroup>
                            <col style="width: 110px;" />
                            <col />
                            <col style="width: 210px;" />
                            <col style="width: 120px;" />
                            <col style="width: 35px;" />
                            
                        </colgroup>
                        <thead>
                            <tr>
                                <th></th>
								<th>Товар</th>
                                <th>Кол-во</th>
                                <th>Цена</th>
								<th></th>
                            </tr>
                        </thead>
                        <tbody>
					
				<?
				foreach ($arResult["GRID"]["ROWS"] as $k => $arItem):

					if ($arItem["DELAY"] == "N" && $arItem["CAN_BUY"] == "Y"):
				?>
				<?
				if (strlen($arItem["PREVIEW_PICTURE_SRC"]) > 0):
					$url = $arItem["PREVIEW_PICTURE_SRC"];
				elseif (strlen($arItem["DETAIL_PICTURE_SRC"]) > 0):
					$url = $arItem["DETAIL_PICTURE_SRC"];
				else:
					$url = $templateFolder."/images/no-photo-mini.png";
				endif;
				?>
							<tr id="<?=$arItem["ID"]?>" class="product-line" data-product-id="<?=$arItem['PRODUCT_ID']?>" data-item-name="<?=$arItem['NAME']?>" >
                                <td><?if (strlen($arItem["DETAIL_PAGE_URL"]) > 0):?><a href="<?=$arItem["DETAIL_PAGE_URL"] ?>"><?endif;?><img src="<?php echo $url;?>" alt="" /><?if (strlen($arItem["DETAIL_PAGE_URL"]) > 0):?></a><?endif;?></td>
                                <td>
                                    <div class="basket-product"><?if (strlen($arItem["DETAIL_PAGE_URL"]) > 0):?><a href="<?=$arItem["DETAIL_PAGE_URL"] ?>"><?endif;?><?=$arItem["NAME"]?><?if (strlen($arItem["DETAIL_PAGE_URL"]) > 0):?></a><?endif;?></div>
                                    <div class="basket-cats"><a href="/e-store/<?php echo $sections[$products[$arItem['PRODUCT_ID']]]['ID']; ?>/" target="blank"><?php echo $sections[$products[$arItem['PRODUCT_ID']]]['NAME'];?></a></div>
                                    <p class="error j-error j-error-<?php echo $arItem['PRODUCT_ID'];?>" style="position: relative; margin: 5px; color: #ed1e26; font-size: 13px; line-height: 16px;"></p>
                                </td>
                                <td>
                                <div style="display:none;">
                                    	<?
										$ratio = isset($arItem["MEASURE_RATIO"]) ? $arItem["MEASURE_RATIO"] : 0;
										$max = isset($arItem["AVAILABLE_QUANTITY"]) ? "max=\"".$arItem["AVAILABLE_QUANTITY"]."\"" : "";
										$useFloatQuantity = ($arParams["QUANTITY_FLOAT"] == "Y") ? true : false;
										$useFloatQuantityJS = ($useFloatQuantity ? "true" : "false");
										echo getMobileQuantityControl(
										        "QUANTITY_SELECT_".$arItem["ID"],
										        "QUANTITY_SELECT_".$arItem["ID"],
										        $arItem["QUANTITY"],
										        $arItem["AVAILABLE_QUANTITY"],
										        $useFloatQuantityJS,
										        $arItem["MEASURE_RATIO"],
										        $arItem["MEASURE_TEXT"]
										);
										?>
										</div>
										<input
										 	class="j-discrete-input" 
											type="text"
											size="3"
											id="QUANTITY_INPUT_<?=$arItem["ID"]?>"
											name="QUANTITY_INPUT_<?=$arItem["ID"]?>"
											size="2"
											maxlength="18"
											min="0"
											<?=$max?>
											step="<?=$ratio?>"
											value="<?=$arItem["QUANTITY"]?>"
											onchange="updateQuantity('QUANTITY_INPUT_<?=$arItem["ID"]?>', '<?=$arItem["ID"]?>', <?=$ratio?>, <?=$useFloatQuantityJS?>)"
										>
									<input type="hidden" id="QUANTITY_<?=$arItem['ID']?>" name="QUANTITY_<?=$arItem['ID']?>" value="<?=$arItem["QUANTITY"]?>" />
                                </td>
                                <td>
                                    <div>
											<strong id="current_price_<?=$arItem["ID"]?>"><?=$arItem["PRICE_FORMATED"]?></strong>
									</div>
									<?if (floatval($arItem["DISCOUNT_PRICE_PERCENT"]) > 0):?>
									<span id="old_price_<?=$arItem["ID"]?>" class="basket-old-price"><?=$arItem["FULL_PRICE_FORMATED"]?></span>
									<?endif;?>

									<?/*if ($bPriceType && strlen($arItem["NOTES"]) > 0):?>
										<div class="type_price"><?=GetMessage("SALE_TYPE")?></div>
										<div class="type_price_value"><?=$arItem["NOTES"]?></div>
									<?endif;*/?>
									 <strong style="display:none;" id="sum_<?=$arItem["ID"]?>"><?
									echo $arItem['SUM'];
									?></strong>
                                </td>
                                <td>
                                    <a class="basket-delete deleteitem j-basket-delete" href="<?=str_replace("#ID#", $arItem["ID"], $arUrls["delete"])?>" onclick="document.location='<?=str_replace("#ID#", $arItem["ID"], $arUrls["delete"])?>';">Удалить</a>
                                </td>
                            </tr>
				<?php 
					endif;
				endforeach;
				?>
			</tbody>
		</table>
	</div>
	<input type="hidden" id="column_headers" value="<?=CUtil::JSEscape(implode($arHeaders, ","))?>" />
	<input type="hidden" id="offers_props" value="<?=CUtil::JSEscape(implode($arParams["OFFERS_PROPS"], ","))?>" />
	<input type="hidden" id="action_var" value="<?=CUtil::JSEscape($arParams["ACTION_VARIABLE"])?>" />
	<input type="hidden" id="quantity_float" value="<?=$arParams["QUANTITY_FLOAT"]?>" />
	<input type="hidden" id="count_discount_4_all_quantity" value="<?=($arParams["COUNT_DISCOUNT_4_ALL_QUANTITY"] == "Y") ? "Y" : "N"?>" />
	<input type="hidden" id="price_vat_show_value" value="<?=($arParams["PRICE_VAT_SHOW_VALUE"] == "Y") ? "Y" : "N"?>" />
	<input type="hidden" id="hide_coupon" value="<?=($arParams["HIDE_COUPON"] == "Y") ? "Y" : "N"?>" />
	<input type="hidden" id="coupon_approved" value="N" />
	<input type="hidden" id="use_prepayment" value="<?=($arParams["USE_PREPAYMENT"] == "Y") ? "Y" : "N"?>" />

	<div class="basket-total">
        <div class="basket-total-price">Итого: 
            <span id="PRICE_WITHOUT_DISCOUNT"><?if (floatval($arResult["DISCOUNT_PRICE_ALL"]) > 0):?><?=$arResult["PRICE_WITHOUT_DISCOUNT"]?><?endif;?></span> 
            <strong id="allSum_FORMATED"><?=str_replace(" ", "&nbsp;", $arResult["allSum_FORMATED"])?></strong>
    	</div>
        <?
        if($arResult['DISCOUNT_PRICE_ALL'] >0 ):?><div class="basket-econom">Вы экономите <?php echo round(($arResult['DISCOUNT_PRICE_ALL']/($arResult['DISCOUNT_PRICE_ALL']+$arResult["allSum"]))*100,1)?>% (<?php echo $arResult['DISCOUNT_PRICE_ALL_FORMATED']; ?>)</div><?php endif?>
      <a class="screw-button" href="#" onclick="checkOut();" ><span>Оформить заказ</span></a>

    </div>
</div>
<?
else:
?>
<div id="basket_items_list">
	<table>
		<tbody>
			<tr>
				<td colspan="<?=$numCells?>" style="text-align:center">
					<div class=""><?=GetMessage("SALE_NO_ITEMS");?></div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<?
endif;
?>