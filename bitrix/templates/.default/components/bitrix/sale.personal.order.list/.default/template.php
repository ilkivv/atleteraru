<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if(!empty($arResult['ERRORS']['FATAL'])):?>

	<?foreach($arResult['ERRORS']['FATAL'] as $error):?>
		<?=ShowError($error)?>
	<?endforeach?>

<?else:?>

	<?if(!empty($arResult['ERRORS']['NONFATAL'])):?>

		<?foreach($arResult['ERRORS']['NONFATAL'] as $error):?>
			<?=ShowError($error)?>
		<?endforeach?>

	<?endif?>

<script type="text/javascript">
    $(document).ready(function(){
        $('.pay_systems').change(function(value){
            var key = 0;
            $.ajax({
                type: 'POST',
                url: '/ajax/update_payment_system.php',
                data: 'order=' + $(this).attr('rel') + '&payment_id=' + $(this).val(),
                success: function(data){}
            });
            //console.log("val: " + $(this).val() + " id: " + $(this).attr('rel'));
            if($(this).val() == 9 || $(this).val() == 4){
                $('.j-basket-buttons-childs[rel="' + $(this).attr('rel') + '"]').show();
                if($(this).val() == 9){
                    $('.j-paymentType-' + $(this).attr('rel')).val('AC');
                }else{
                    $('.j-paymentType-' + $(this).attr('rel')).val('PC');
                }
            }else{
                $('.j-basket-buttons-childs[rel="' + $(this).attr('rel') + '"]').hide();
            }
            if ($(this).val() == 5){
                //console.log("cber");
                $('blockquote[rel="' + $(this).attr('rel') + '"]').css('display','block');

            }else{
                //console.log("out");
                $('blockquote[rel="' + $(this).attr('rel') + '"]').css('display','none');
            }
        });
    });
</script>

	<?if(!empty($arResult['ORDERS'])):?>

		<?foreach($arResult["ORDER_BY_STATUS"] as $key => $group):?>

			<?foreach($group as $k => $order):?>
<?php //var_dump($arResult); ?>
				<div class="spoiler j-spoiler">
                        <div class="spoiler-link j-spoiler-link waiting <?=$arResult["INFO"]["STATUS"][$key]['COLOR']?>">
                            <span>Заказ № <?=$order["ORDER"]["ACCOUNT_NUMBER"]?>  от <?=$order["ORDER"]["DATE_INSERT_FORMATED"];?></span>
                            <em><?php if ($order['ORDER']['CANCELED'] == 'Y'){?>Отменен<?php } else {?><? echo $arResult["INFO"]["STATUS"][$key]["NAME"];}?></em>
                        </div>
                        <div class="spoiler-body j-spoiler-body ">

                            <table class="orders-total">
                                <tr>
                                    <td>
                                    <?if($order['HAS_DELIVERY']):?>

                                        <strong>Способ доставки</strong>

                                        <span>
										<?if(intval($order["ORDER"]["DELIVERY_ID"])):?>
										
											<?=$arResult["INFO"]["DELIVERY"][$order["ORDER"]["DELIVERY_ID"]]["NAME"]?> <br />
										
										<?elseif(strpos($order["ORDER"]["DELIVERY_ID"], ":") !== false):?>
										
											<?$arId = explode(":", $order["ORDER"]["DELIVERY_ID"])?>
											<?=$arResult["INFO"]["DELIVERY_HANDLERS"][$arId[0]]["NAME"]?> (<?=$arResult["INFO"]["DELIVERY_HANDLERS"][$arId[0]]["PROFILES"][$arId[1]]["TITLE"]?>) <br />

										<?endif?>
                                        </span>

									<?endif?>
                                    </td>
                                    <td>
                                        <strong>Оплата</strong>
                                        <div class="results"></div>
                                        <?php if ( ($order["ORDER"]["CANCELED"] != "Y") &&
                                        ($order["ORDER"]["PAYED"] != "Y")): ?>
                                        <select class="pay_systems choose-taste-link j-choose-taste-link" rel="<?= $order['ORDER']['ID'] ?>">
                                            <?php foreach($arResult['INFO']['PAY_SYSTEM'] as $pay): ?>
                                                <?php if ($pay['ACTIVE'] === 'Y'): ?>
                                                    <?php if ($arResult["INFO"]["PAY_SYSTEM"][$order["ORDER"]["PAY_SYSTEM_ID"]]["NAME"] === $pay['NAME']): ?>
                                                        <option value="<?= $pay['ID'] ?>" selected><?= $pay['NAME'] ?></option>
                                                    <?php else: ?>
                                                        <option value="<?= $pay['ID'] ?>"><?= $pay['NAME'] ?></option>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                        <?php endforeach; ?>
                                        </select>
                                        <?php else: ?>
                                        <span><?=$arResult["INFO"]["PAY_SYSTEM"][$order["ORDER"]["PAY_SYSTEM_ID"]]["NAME"]?></span>
                                        <?php endif; ?>
                                        <?php if ($order["ORDER"]["PAY_SYSTEM_ID"] == 5): ?>
                                            <blockquote rel="<?= $order['ORDER']['ID'] ?>" style="font-size: 12px;">
                                        <?php else: ?>
                                            <blockquote rel="<?= $order['ORDER']['ID'] ?>" style="font-size: 12px;display: none">
                                        <?php endif; ?>
                                                Перевод денег на карту <a href="https://online.sberbank.ru">Сбербанка</a>: Ирина Васильевна М. <strong>4276 6400 1343 7233</strong><br/>
                                            </blockquote>

                                    </td>
                                    <td>
                                        <strong>Сумма заказа с учетом доставки</strong>
                                        <span><?=$order["ORDER"]["FORMATED_PRICE"]?></span>
                                    </td>
                                </tr>
                            </table>

                            <h5 class="orders-title"><span>Состав заказа</span></h5>

                            <table class="orders-list">
                            <?foreach ($order["BASKET_ITEMS"] as $item):
                            $product = (CCatalogSku::GetProductInfo($item['PRODUCT_ID']));
                            $renderImage = null;
                            if ($params = CIBlockElement::GetByID($product['ID'])->GetNext()) {
                               $props = CIBlockElement::GetProperty(
                                    46,
                                    $item['PRODUCT_ID'],
                                    array(),
                                    Array('CODE'=>'CML2_TASTE')
                                    );
                                $prop= $props->Fetch();
                                if ($params["PREVIEW_PICTURE"]) {
                                    $renderImage = CFile::ResizeImageGet($params["PREVIEW_PICTURE"], Array("width" => 50, "height" => 50));
                                    $renderImage = $renderImage['src'];
                                }
                            }
                            if (!$renderImage) {
                                $renderImage = $templateFolder."/images/no_photo.png";
                            }
                            ?>
	                                <tr>
	                                    <td>
	                                        <?if(strlen($item["DETAIL_PAGE_URL"])):?><a href="<?=$item["DETAIL_PAGE_URL"]?>" target="_blank"><?endif?>
	                                        <img src="<?=$renderImage?>" alt="" width="50"/>
	                                        <span><?=$item['NAME']?></span><?if(strlen($item["DETAIL_PAGE_URL"])):?></a><?endif?>
										</td>
	                                    <td>
	                                        <em>
	                                            Количество: <?=$item['QUANTITY']?> <?=(isset($item["MEASURE_NAME"]) ? $item["MEASURE_NAME"] : GetMessage('SPOL_SHT'))?>
	                                        </em>
	                                    </td>
	                                    <td>
	                                        <strong><?php echo $item['PRICE'];?> р.</strong>
	                                    </td>
	                                </tr>
							<?endforeach?>
                                
                            </table>

                            <div class="basket-buttons">
                                <?php if ( ($order["ORDER"]["CANCELED"] != "Y") &&
                                    ($order["ORDER"]["PAYED"] != "Y")): ?>
                                <?php if (in_array($order["ORDER"]['PAY_SYSTEM_ID'], array(9, 4))): ?>
                                    <div class="basket-buttons-childs j-basket-buttons-childs" rel="<?= $order['ORDER']['ID'] ?>">
                                <?php else: ?>
                                        <div class="basket-buttons-childs j-basket-buttons-childs" style="display: none" rel="<?= $order['ORDER']['ID'] ?>">
                                <?php endif; ?>
                                        <form method="POST" action="https://money.yandex.ru/quickpay/confirm.xml" id="payYandex" target="_blank">
                                            <input type="hidden" name="receiver" value="410011949574760">
                                            <input type="hidden" name="label" value="<?= $order["ORDER"]['ID'] ?>">
                                            <input type="hidden" name="quickpay-form" value="shop">
                                            <input type="hidden" name="targets" value="<?= 'Заказ № ' . $order["ORDER"]['ID'] ?>">
                                            <input type="hidden" name="sum" value="<?= $order["ORDER"]['PRICE'] ?>" data-type="number">
                                            <input type="hidden" name="need-fio" value="false">
                                            <input type="hidden" name="need-email" value="false">
                                            <input type="hidden" name="need-phone" value="false">
                                            <input type="hidden" name="need-address" value="false">
                                            <?php if ($order["ORDER"]['PAY_SYSTEM_ID'] == 9): ?>
                                                <input class="j-paymentType-<?= $order["ORDER"]['ID']; ?>" type="hidden" name="paymentType" value="AC">
                                            <?php else: ?>
                                                <input class="j-paymentType-<?= $order["ORDER"]['ID']; ?>" type="hidden" name="paymentType" value="PC">
                                            <?php endif; ?>
                                            <input class="basket-pay-button" type="submit" value="Оплатить">
                                        </form>
                                    </div>
                                <?php endif; ?>
                                <?if($order["ORDER"]["CANCELED"] != "Y"):?>
                                <div class="basket-buttons-childs j-basket-buttons-childs" rel="<?= $order['ORDER']['ID'] ?>">

                                    <a class="black-button" href="/personal/?cancelOrder=Y&orderId=<?=$order['ORDER']['ID']?>">Отменить заказ</a>
                                    <?if($order["ORDER"]["PAY_SYSTEM_ID"] == 8 && $order["ORDER"]["PAYED"]=='N'):?>
                                        <a class="black-button comepay" data-id="<?=$order['ORDER']['ID']?>" href="#">Оплатить заказ</a>
                                        <form id="comepay<?=$order['ORDER']['ID']?>" action="/bitrix/tools/comepay_create.php" method="post">
                                            <input type="hidden" name="card" value="1">
                                            <input type="hidden" name="id" value="<?
                                            global  $DB;
                                            $arrResult = $DB->Query('SELECT BILL_ID, SUM, ORDER_ID FROM comepay_payment WHERE ORDER_ID='.$order['ORDER']['ID'].'');
                                            $orders = array();
                                            while($data = $arrResult->Fetch()) {
                                                echo $data['BILL_ID'];
                                            }
                                            ?>">
                                        </form>
                                    <?endif?>
                                </div>
                                <?endif?>
                            </div>

                        </div>

                    </div>

			<?endforeach?>

		<?endforeach?>

		<?if(strlen($arResult['NAV_STRING'])):?>
			<?=$arResult['NAV_STRING']?>
		<?endif?>

	<?else:?>
		<?=GetMessage('SPOL_NO_ORDERS')?>
	<?endif?>

<?endif?>
<script>
    window.onload = function () {
        $('.comepay').on("click", function (e) {
            var id = $(this).data('id');
            $('#comepay'+id).submit();
        });
    }
</script>
