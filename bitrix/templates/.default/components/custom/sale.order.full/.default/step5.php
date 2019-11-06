<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if(!($arResult["SKIP_FIRST_STEP"] == "Y" && $arResult["SKIP_SECOND_STEP"] == "Y" && $arResult["SKIP_THIRD_STEP"] == "Y" && $arResult["SKIP_FORTH_STEP"] == "Y"))
						{
							?>
							<input type="submit" id='backBtn' class="hidden-input"  name="backButton" value="&lt;&lt; <?echo GetMessage("SALE_BACK_BUTTON")?>">
							<div class="breadcrumbs">
			                    <a href="#" onclick="$('#backBtn').trigger('click');return false;">Назад</a>
			                </div>
							<?
						}
						?>

                <h1>Проверьте ваш заказ, пожалуйста</h1>

                <div class="ordering-confirm clearfix">
                    <div class="ordering-confirm-left">
                        <table>
                            <tr>
                                <th>Товар</th>
                                <td>
                                <?
								foreach($arResult["BASKET_ITEMS"] as $arBasketItems)
								{
									?>
										<?=$arBasketItems["NAME"]?>
											<?
											$s = array();
											foreach($arBasketItems["PROPS"] as $val)
											{
												$s[] = $val["VALUE"];
											}
											if ($s) {
												echo "(".join(',',$s).")";
											}
											?>
										 X <?=intval($arBasketItems["QUANTITY"]);?>
									<br />
									<?
								}
								?>
                                </td>
                            </tr>
                            <tr>
                                <th>Адрес</th>
                                <td>
                                <?
									foreach($arResult["ORDER_PROPS_PRINT"] as $k => $arProperties)
									{
										if(strLen($arProperties["VALUE_FORMATED"])>0)
										{
											$props[$arProperties['NAME']] = $arProperties["VALUE_FORMATED"];
										}
									}
									echo $props['Индекс'] . ' ' . $props['Город'] . ', ' . $props['Улица'] . ' ' . $props['Дом'].' подъезд '. $props['Подъезд']. ' этаж '.$props['Этаж'].' кв.' . $props['Квартира'] ;
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>Получатель</th>
                                <td><?
									foreach($arResult["ORDER_PROPS_PRINT"] as $k => $arProperties)
									{
										if(strLen($arProperties["VALUE_FORMATED"])>0 && $arProperties["NAME"] == 'Контактное лицо')
										{
											?>
											<?=$arProperties["VALUE_FORMATED"]?>
											<?
										}
									}
									?>
								</td>
                            </tr>
                            <tr>
                                <th>Доставка</th>
                                <td><?
									if (is_array($arResult["DELIVERY"]))
									{
										echo $arResult["DELIVERY"]["NAME"];
										if (is_array($arResult["DELIVERY_ID"]))
										{
											echo " (".$arResult["DELIVERY"]["PROFILES"][$arResult["DELIVERY_PROFILE"]]["TITLE"].")";
										}
									}
									elseif ($arResult["DELIVERY"]=="ERROR")
									{
										echo ShowError(GetMessage("SALE_ERROR_DELIVERY"));
									}
									else
									{
										echo GetMessage("SALE_NO_DELIVERY");
									}
									?>
								</td>
                            </tr>
                            <?if(is_array($arResult["PAY_SYSTEM"]) || $arResult["PAY_SYSTEM"]=="ERROR" || $arResult["PAYED_FROM_ACCOUNT"] == "Y")
							{
								?>
								<tr>
									<th>Оплата</td>
									<td>
										<?
										if($arResult["PAYED_FROM_ACCOUNT"] == "Y")
											echo " (".GetMessage("STOF_PAYED_FROM_ACCOUNT").")";
										elseif (is_array($arResult["PAY_SYSTEM"]))
										{
											echo $arResult["PAY_SYSTEM"]["PSA_NAME"];
										}
										elseif ($arResult["PAY_SYSTEM"]=="ERROR")
										{
											echo ShowError(GetMessage("SALE_ERROR_PAY_SYS"));
										}
										elseif($arResult["PAYED_FROM_ACCOUNT"] != "Y")
										{
											echo GetMessage("STOF_NOT_SET");
										}
										
										?>				
									</td>
								</tr>
								<?
							}
							?>
                        </table>
                    </div>

                    <div class="ordering-confirm-right">

                        <div class="ordering-confirm-sum">
                            Товаров на сумму: <strong><?=round($arResult["ORDER_DISCOUNT_PRICE"],2)?></strong><br />
                            <?php if (doubleval($arResult["DELIVERY_PRICE"]) > 0)
							{
								?>
										Доставка: <strong><?=$arResult["DELIVERY_PRICE_FORMATED"]?></strong> <?php /*if ($arResult['DELIVERY_ID'][0] == 'pecom') {?><!--Оплачивается при получении--><?php /*}*/?><br />
									
								<?
							}
							if (doubleval($arResult["DISCOUNT_PRICE_FORMATED"]) > 0)
							{
							?>
							Скидка:
								<?if (strLen($arResult["DISCOUNT_PERCENT_FORMATED"])>0):?>
									<strong><?echo $arResult["DISCOUNT_PERCENT_FORMATED"];?></strong>
									(<?echo $arResult["DISCOUNT_PRICE_FORMATED"]?>)
									<?php else:?>
									<strong><?echo $arResult["DISCOUNT_PRICE_FORMATED"]?></strong>
								<?endif;?>
							<?
							}
							?>
                        </div>

                        <div class="ordering-confirm-total">Итого: <strong><?=round($arResult["ORDER_DISCOUNT_PRICE"]+$arResult["DELIVERY_PRICE"],2)?></strong></div>

                        <a href="#" class="screw-button" onclick="$('#sbmt').trigger('click');return false;"><span>Подтвердить заказ</span></a>
						<input type="submit" class="hidden-input"  id='sbmt' name="contButton" value="<?= GetMessage("SALE_CONFIRM")?>">

                    </div>
                </div>