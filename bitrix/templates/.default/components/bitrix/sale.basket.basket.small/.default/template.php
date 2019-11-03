<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!function_exists('declension')) {
        function declension($count, $forms)
        {
                $mod100 = $count % 100;
                switch ($count%10) {
                        case 1:
                                if ($mod100 == 11)
                                        return $forms[2];
                                else
                                        return $forms[0];
                        case 2:
                        case 3:
                        case 4:
                                if (($mod100 > 10) && ($mod100 < 20))
                                        return $forms[2];
                                else
                                        return $forms[1];
                        case 5:
                        case 6:
                        case 7:
                        case 8:
                        case 9:
                        case 0:
                                return $forms[2];

                }

        }
}
if ($arResult["READY"]=="Y" || $arResult["DELAY"]=="Y" || $arResult["NOTAVAIL"]=="Y" || $arResult["SUBSCRIBE"]=="Y")
{
        if (!count($arResult['ITEMS'])) {
                ?>
                <a href="<?=$arParams["PATH_TO_BASKET"]?>" class="basket">
                    <em>Ваша корзина пуста</em>
                    <span class="basket-empty-bg"></span>
                    <span class="basket-bg"></span>
                    <span class="basket-hover-bg"></span>
                </a>
                <?php 
        }else {
                foreach ($arResult["ITEMS"] as &$v)
                {
                        $SKUIDS[$v['PRODUCT_ID']] = $v['QUANTITY'];
                        $price = $price +$v["PRICE"]*$v["QUANTITY"];
                }
?>
<script>
var SKU_IN_BASKET={<?php foreach ($SKUIDS as $id => $quantity){$parts[] = $id.':'.intval($quantity);}echo join(',',$parts);?>};
</script>
                                <a href="<?=$arParams["PATH_TO_BASKET"]?>" class="basket full">
                    <em class="count-products"><span class="count"><?=count($arResult['ITEMS'])?></span> <span class="caption"><?php echo declension(count($arResult['ITEMS']), array('товар',"товара","товаров"));?></span> (<?php echo round($price,2);?> р.)</em>
                    <span class="basket-empty-bg"></span>
                    <span class="basket-bg"></span>
                    <span class="basket-hover-bg"></span>
                </a>
<?php
        } 
} else {
?>
<script>
var SKU_IN_BASKET={};
</script>
                <a href="<?=$arParams["PATH_TO_BASKET"]?>" class="basket">
				
					
                    <em><span  class="count-empty">0</span><span class="caption-empty">Ваша корзина пуста</span></em>
                    <span class="basket-empty-bg"></span>
                    <span class="basket-bg"></span>
                    <span class="basket-hover-bg"></span>
                </a>
                <?php 
}
?>
