<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if(!($arResult["SKIP_FIRST_STEP"] == "Y" && $arResult["SKIP_SECOND_STEP"] == "Y"))
	{
		?>
				<input type="submit" id="backBtn" name="backButton" value="&lt;&lt; <?echo GetMessage("SALE_BACK_BUTTON")?>" style="display: none;">
				<div class="breadcrumbs">
                    <a href="#" onclick="$('#backBtn').trigger('click');return false;">Назад</a>
                </div>
		<?
	}
	global $price;
	?>

<div class="ordering-step" style="display:none;">Оформление заказа: шаг 2 из 3</div>

<h1>Способ доставки</h1>

<div class="ordering-info">
    <span><strong>Ваш заказ:</strong> <?php echo count($arResult['BASKET_ITEMS']).' '. declension(count($arResult['BASKET_ITEMS']), array('товар',"товара","товаров"));?> на сумму: <?=round($arResult['ORDER_DISCOUNT_PRICE'],2)?></span>
    <span><strong>Доставка:</strong> не выбрано</span>
    <em><strong>Итого:</strong> <?=round($arResult['ORDER_DISCOUNT_PRICE'],2)?> р.</em>
</div>

                    <table class="ordering-table">
                    <?

                    if ($arResult["DELIVERY"]) {

						$first = true;
					foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
					{
						if ($delivery_id !== 0 && intval($delivery_id) <= 0):
					?><?
						foreach ($arDelivery["PROFILES"] as $profile_id => $arProfile)
						{
						    if ($first) {
						        $arProfile["CHECKED"] = 'Y';
						        $first = false;
						    } else {
                                $arProfile["CHECKED"] = 'N';
                            }
							?>
						<tr>
							<td>
							<div class="radio"><input type="radio" id="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>" name="<?=$arProfile["FIELD_NAME"]?>" value="<?=$delivery_id.":".$profile_id;?>" <?=$arProfile["CHECKED"] == "Y" ? "checked=\"checked\"" : "";?> />
								<label for="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>">
									<span><?=$arDelivery["TITLE"]?> <?=$arProfile["TITLE"]?></span>
								</label>
							</div>
							</td>
							<td>
							<?php //print_r($arResult['BASKET_ITEMS']);?>
							<?$APPLICATION->IncludeComponent('bitrix:sale.ajax.delivery.calculator', '', array(
									"NO_AJAX" => $arParams["SHOW_AJAX_DELIVERY_LINK"] == 'S' ? 'Y' : 'N',
									"DELIVERY" => $delivery_id,
									"PROFILE" => $profile_id,
							        'ITEMS'=>$arResult['BASKET_ITEMS'],
									"ORDER_WEIGHT" => $arResult["ORDER_WEIGHT"],
									"ORDER_PRICE" => $arResult["ORDER_PRICE"],
									"LOCATION_TO" => $arResult["DELIVERY_LOCATION"],
									"LOCATION_ZIP" => $arResult['POST']['~ORDER_PROP_4'],
									"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
								));
							?>
							<?if ($arParams["SHOW_AJAX_DELIVERY_LINK"] == 'N'):?>
							<script type="text/javascript">deliveryCalcProceed({STEP:1,DELIVERY:'<?=CUtil::JSEscape($delivery_id)?>',PROFILE:'<?=CUtil::JSEscape($profile_id)?>',WEIGHT:'<?=CUtil::JSEscape($arResult["ORDER_WEIGHT"])?>',PRICE:'<?=CUtil::JSEscape($arResult["ORDER_PRICE"])?>',LOCATION:'<?=intval($arResult["DELIVERY_LOCATION"])?>',CURRENCY:'<?=CUtil::JSEscape($arResult["BASE_LANG_CURRENCY"])?>'})</script>
							<?endif;?>
							<?if (strlen($arProfile["DESCRIPTION"]) > 0):?><div class="delivery-condition">
									<?=nl2br($arProfile["DESCRIPTION"])?></div><?endif;?>
							</td>
						</tr>
								<?
						} // endforeach
					?>


                        <?
						else:
						?>
					<tr>
						<td >
						<div class="radio">
							<input type="radio" id="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>" name="<?=$arDelivery["FIELD_NAME"]?>" value="<?= $arDelivery["ID"] ?>"<?if ($arDelivery["CHECKED"]=="Y") echo " checked";?>>
							<label for="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>">
							<span><?= $arDelivery["NAME"] ?></span></label>
							</label>
						</div>
						</td>
						<td>
						<?=GetMessage("SALE_DELIV_PRICE");?> <?=$arDelivery["PRICE_FORMATED"]?><br />
							<?
							if (strlen($arDelivery["DESCRIPTION"])>0 || strlen($arDelivery["PERIOD_TEXT"])>0)
							{
							?>
							<div class="delivery-condition">
                                    <?=$arDelivery["DESCRIPTION"]?>
                                    <?
							if (strlen($arDelivery["PERIOD_TEXT"])>0)
							{
								echo $arDelivery["PERIOD_TEXT"];
								?><?
							}
							?>
                                </div>
                            <?
							}
							?>
						</td>
					</tr>
                        <?php endif;?>
                        <?php }?>
                        <?php } else {?>
                        <tr>
						<td >
						<p>К сожалению, нет доступных способов доставки для Вашего населенного пункта</p>
						</td>
						</tr>
                        <?php }?>
                    </table>
                    <?php if ($arResult['DELIVERY']){?>
                    <div class="ordering-button">
                        <a href="#" class="screw-button" onclick="$('#ordForm').submit();"><span>Далее</span></a>
                        <input type="submit" class="hidden-input" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;" id="submitReg" name="contButton" />
                    </div>
                    <?php }?>







<?php return;?>
<table border="0" cellspacing="0" cellpadding="5">
<tr>
	<td valign="top" width="60%" align="right">
		<input type="submit" name="contButton" value="<?= GetMessage("SALE_CONTINUE")?> &gt;&gt;">
	</td>
	<td valign="top" width="5%" rowspan="3">&nbsp;</td>
	<td valign="top" width="35%" rowspan="3">
		
		<?echo GetMessage("STOF_DELIVERY_NOTES")?><br /><br />
		<?echo GetMessage("STOF_PRIVATE_NOTES")?>
		
	</td>
</tr>
<tr>
	<td valign="top" width="60%">
		<b><?echo GetMessage("STOF_DELIVERY_PROMT")?></b><br /><br />
		<table class="sale_order_full_table">
			<tr>
				<td colspan="2"><?echo GetMessage("STOF_SELECT_DELIVERY")?><br /><br /></td>
			</tr>
			<?
				foreach ($arResult["DELIVERY"] as $delivery_id => $arDelivery)
				{
					if ($delivery_id !== 0 && intval($delivery_id) <= 0):
				?>
				<tr>
					<td colspan="2">
						<b><?=$arDelivery["TITLE"]?></b><?if (strlen($arDelivery["DESCRIPTION"]) > 0):?><br />
						<?=nl2br($arDelivery["DESCRIPTION"])?><br /><?endif;?>
						----<table border="0" cellspacing="0" cellpadding="3">
						
					<?
						foreach ($arDelivery["PROFILES"] as $profile_id => $arProfile)
						{
							?>
					<tr>
						<td width="20" nowrap="nowrap">&nbsp;</td>
						<td width="0%" valign="top"><input type="radio" id="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>" name="<?=$arProfile["FIELD_NAME"]?>" value="<?=$delivery_id.":".$profile_id;?>" <?=$arProfile["CHECKED"] == "Y" ? "checked=\"checked\"" : "";?> /></td>
						<td width="50%" valign="top">
							<label for="ID_DELIVERY_<?=$delivery_id?>_<?=$profile_id?>">
								<small><b><?=$arProfile["TITLE"]?></b><?if (strlen($arProfile["DESCRIPTION"]) > 0):?><br />
								<?=nl2br($arProfile["DESCRIPTION"])?><?endif;?></small>
							</label>
						</td>
						<td width="50%" valign="top" align="right">
						<?
							$APPLICATION->IncludeComponent('bitrix:sale.ajax.delivery.calculator', '', array(
								"NO_AJAX" => $arParams["SHOW_AJAX_DELIVERY_LINK"] == 'S' ? 'Y' : 'N',
								"DELIVERY" => $delivery_id,
								"PROFILE" => $profile_id,
								"ORDER_WEIGHT" => $arResult["ORDER_WEIGHT"],
								"ORDER_PRICE" => $arResult["ORDER_PRICE"],
								"LOCATION_TO" => $arResult["DELIVERY_LOCATION"],
								"LOCATION_ZIP" => $arResult['DELIVERY_LOCATION_ZIP'],
								"CURRENCY" => $arResult["BASE_LANG_CURRENCY"],
							));
						?>
						<?if ($arParams["SHOW_AJAX_DELIVERY_LINK"] == 'N'):?>
						<script type="text/javascript">deliveryCalcProceed({STEP:1,DELIVERY:'<?=CUtil::JSEscape($delivery_id)?>',PROFILE:'<?=CUtil::JSEscape($profile_id)?>',WEIGHT:'<?=CUtil::JSEscape($arResult["ORDER_WEIGHT"])?>',PRICE:'<?=CUtil::JSEscape($arResult["ORDER_PRICE"])?>',LOCATION:'<?=intval($arResult["DELIVERY_LOCATION"])?>',CURRENCY:'<?=CUtil::JSEscape($arResult["BASE_LANG_CURRENCY"])?>'})</script>
						<?endif;?>
						</td>
					</tr>
							<?
						} // endforeach
					?>
						</table>

						
					</td>
				</tr>
				<?
					else:
?>
					<tr>
						<td valign="top" width="0%">
							<input type="radio" id="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>" name="<?=$arDelivery["FIELD_NAME"]?>" value="<?= $arDelivery["ID"] ?>"<?if ($arDelivery["CHECKED"]=="Y") echo " checked";?>>
						</td>
						<td valign="top" width="100%">
							<label for="ID_DELIVERY_ID_<?= $arDelivery["ID"] ?>">
							<b><?= $arDelivery["NAME"] ?></b><br />
							<?
							if (strlen($arDelivery["PERIOD_TEXT"])>0)
							{
								echo $arDelivery["PERIOD_TEXT"];
								?><br /><?
							}
							?>
							<?=GetMessage("SALE_DELIV_PRICE");?> <?=$arDelivery["PRICE_FORMATED"]?><br />
							<?
							if (strlen($arDelivery["DESCRIPTION"])>0)
							{
								?>
								<?=$arDelivery["DESCRIPTION"]?><br />
								<?
							}
							?>
							</label>
						</td>
					</tr>
					<?
					endif;
				
				} // endforeach
			?>
			<?
			//endif;
			?>
		</table>
	</td>
</tr>
<tr>
	<td valign="top" width="60%" align="right">
	
	</td>
</tr>
</table>