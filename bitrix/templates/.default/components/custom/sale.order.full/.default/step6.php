 <?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>



<?

//echo "<pre>"; var_dump($arResult); echo "</pre>";


if (!empty($arResult["ORDER"]['ID']) && $_SESSION['LAST_ORDER_ID'] != $arResult['ORDER']['ID'] )
{
	
	$_SESSION['LAST_ORDER_ID'] = $arResult['ORDER_ID'];	

	
	$products = array();
	
	$dbBasketItems = CSaleBasket::GetList(
         array(
               "NAME" => "ASC",
               "ID" => "ASC"			   
            ),
         array(
               "LID" => SITE_ID,
               "ORDER_ID" => $arResult['ORDER']['ID']
            ),
         false,
         false,
         array("PRODUCT_ID", "QUANTITY", 'PRICE' , 'NAME', 'ID')
      );   
   
		
   while($arItems = $dbBasketItems->Fetch())
	{	
		
		$productId = $arItems['PRODUCT_ID'];
		
		$mxResult = CCatalogSku::GetProductInfo(
			$productId 
		);
		
		if (is_array($mxResult))
		{			
			$productId = $mxResult['ID'];
		}
		
		$rsElement = CIBlockElement::GetList(array(), array('ID' => $productId), false, false, array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'PROPERTY_BREND'));
		if($arElement = $rsElement->GetNext())
		{
			
			
	       
		   $arItems['IBLOCK_SECTION_ID'] = $arElement['IBLOCK_SECTION_ID'];

		   if($arElement['PROPERTY_BREND_VALUE']){
			   $arItems['BRAND'] = $arElement['PROPERTY_BREND_VALUE'];
		   }
		   
		   if($arElement['IBLOCK_SECTION_ID'])
				$sectIds[] = $arElement['IBLOCK_SECTION_ID'];
		}		
		
		$products[] = $arItems;
		
		
	}	
		
		
	$sections = array();
	
	if($sectIds){
		
		$rsSect = CIBlockSection::GetList(array(), array('ID' =>$sectIds), false, array('ID', 'NAME'));
		while($arSect = $rsSect->GetNext())
		{
		   $sections[$arSect['ID']] = $arSect;
		}
		
	}	
	
	//$arOrderInfo['ID'] =  $arResult['ORDER_ID'];
	//$arOrderInfo['ITEMS'] = $products;
	//file_put_contents($_SERVER['DOCUMENT_ROOT']. '/log_orders.txt', serialize($arOrderInfo) . "\n", FILE_APPEND);
	
	
	?>
	
	<script>	
	dataLayer.push({
		"ecommerce": {
			"purchase": {
				"actionField": {
					"id" : "<?=$arResult["ORDER"]['ID']?>"
				},
				"products": [
				<?foreach ($products as $key => $item):
					$name = str_replace(array("\"","'"), '', $item["NAME"]);
					$category = str_replace(array("\"","'"), '', $sections[$item['IBLOCK_SECTION_ID']]['NAME']);
					$brand = str_replace(array("\"","'"), '', $item["BRAND"]);
				
				?>
					{
						"id": "<?=$item['PRODUCT_ID']?>",
						"name": "<?=$name?>",
						"price": <?=$item["PRICE"]?>,
						<?if($brand):?>
						"brand": "<?=$brand?>",
						<?endif?>
						<?if($category):?>
						"category": "<?=$category?>",
						<?endif?>
						"quantity": <?=ceil($item["QUANTITY"])?>
						
					}<?if($products[$key + 1]):?>,<?endif?>
				<?endforeach?>   
					
				]
			}
		}
	});
	</script>
	<?
	
}


?>


<?

if (!empty($arResult["ORDER"]))
{
    
	
	if($arResult["ORDER"]['PAY_SYSTEM_ID']==8/*COMEPAY*/) {
        ?>
        <h1>Ваш заказ успешно оформлен. Спасибо!</h1>

        <div class="ordering-successful">
        <div class="ordering-notice">
            Заказ отправлен на обработку. В личном кабинете вы всегда можете <a href="/personal/">посмотреть состояние
                вашего заказа.</a>
            <?
            if ($arResult["PAY_SYSTEM"]["NEW_WINDOW"] == "Y") {
                ?>
                <script language="JavaScript">
                    window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?=urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"]))?>');
                </script>
            <?= str_replace("#LINK#", $arParams["PATH_TO_PAYMENT"] . "?ORDER_ID=" . urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"])), GetMessage("STOF_ORDER_PAY_WIN")) ?>
            <?
            }
            else
            {
            if (strlen($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"]) > 0)
            {
            ?>
                <div id="formInclude">
                    <?php
                    include($arResult["PAY_SYSTEM"]["PATH_TO_ACTION"]);
                    ?>
                </div>
                <script>
                    window.onload = function () {
                        $('#formInclude form').submit();
                    }
                </script>
                <?
            }
            }
            ?>
        </div>
        <?
    }
    //print_r($arResult["ORDER"]);
    $db_props = CSaleOrderPropsValue::GetOrderProps($arResult["ORDER"]['ID']);

    while ($arProps = $db_props->Fetch())
    {
        if ($arProps['NAME'] == 'Город'){
            $city = $arProps['VALUE'];
        }
    }

    if (true)
    {
        if( in_array($arResult["ORDER"]['PAY_SYSTEM_ID'], array(4,5,9))) {
            ?>
            <h1>Ваш заказ успешно оформлен. Спасибо!</h1>

            <div class="ordering-successful">
                <div class="ordering-notice">
                    Заказ отправлен на обработку. В личном кабинете вы всегда можете <a href="/personal/">посмотреть состояние
                        вашего заказа.</a>
                </div>
            </div>

            <div class="payment-info">
                <p>
                    Для оплаты заказа вам необходимо произвести <span>100% предоплату заказа (сумма товаров+стоимость доставки)</span>.

                </p>
                <?php if( $arResult["ORDER"]['PAY_SYSTEM_ID']==5 ): ?>
                    <blockquote>
                        Перевод денег на карту <a href="https://online.sberbank.ru">Сбербанка</a>: <strong>4276 6400 1343 7233</strong> Получатель: <strong>Ирина Васильевна М.</strong> <br/>
                    </blockquote>
                <?php else: ?>
                    <p>Сейчас вы будете перенаправлены на оплату в Яндекс.Деньги, если этого не произошло, нажмите на кнопку "Оплатить".</p>

                    <form method="POST" action="https://money.yandex.ru/quickpay/confirm.xml" id="payYandex" target="_blank">
                        <input type="hidden" name="receiver" value="410011949574760">
                        <input type="hidden" name="label" value="<?= $arResult["ORDER"]['ID'] ?>">
                        <input type="hidden" name="quickpay-form" value="shop">
                        <input type="hidden" name="targets" value="<?= 'Заказ № ' . $arResult["ORDER"]['ID'] ?>">
                        <input type="hidden" name="sum" value="<?= $arResult["ORDER"]['PRICE'] ?>" data-type="number">
                        <input type="hidden" name="need-fio" value="false">
                        <input type="hidden" name="need-email" value="false">
                        <input type="hidden" name="need-phone" value="false">
                        <input type="hidden" name="need-address" value="false">
                        <input type="hidden" name="paymentType" value="PC">
                        <?php if ( $arResult["ORDER"]['PAY_SYSTEM_ID']==9 ): ?>
                            <input type="hidden" name="paymentType" value="AC">
                        <?php else: ?>
                            <input type="hidden" name="paymentType" value="PC">
                        <?php endif; ?>
                        <input type="submit" value="Оплатить">
                    </form>
                    <script>
                       document.getElementById('payYandex').submit();
                        //    $('#payYandex').submit();
                        

                    </script>
                <?php endif; ?>

            </div>
            <?php
            /*
             <!--<form id="sb" action="https://online.sberbank.ru/" target="_blank" method="post">
            </form>
            <script language="JavaScript">
                window.onload = function () {
                    $('#sb').submit();
                }
            </script>-->
             */
        } else {?>
        <h1>Ваш заказ успешно оформлен. Спасибо!</h1>

        <div class="ordering-successful">
            <div class="ordering-notice">
                Заказ отправлен на обработку. В личном кабинете вы всегда можете <a href="/personal/">посмотреть состояние
                    вашего заказа.</a>
            </div>
        </div>
        <?php }
    } else { ?>
        <h1>Ваш заказ успешно оформлен. Спасибо!</h1>

        <div class="ordering-successful">
            <div class="ordering-notice">
                Заказ отправлен на обработку. В личном кабинете вы всегда можете <a href="/personal/">посмотреть состояние
                    вашего заказа.</a>
            </div>
        </div>

    <?php } ?>
    </div>
    <?
}
else
{
    ?>
    <h1><?echo GetMessage("STOF_ERROR_ORDER_CREATE")?></h1>
    <div class="ordering-successful">
        <div class="ordering-notice">
            <?=str_replace("#ORDER_ID#", $arResult["ORDER_ID"], GetMessage("STOF_NO_ORDER"))?>
            <?=GetMessage("STOF_CONTACT_ADMIN")?>
        </div>
    </div>
    <?
}
?>