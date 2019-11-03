<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(strlen($arResult["ERROR_MESSAGE"])>0)
	ShowError($arResult["ERROR_MESSAGE"]);
?>
<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<?$arPlacemarks=array();
?>
	<?foreach($arResult["STORES"] as $pid=>$arProperty):
	if (!in_array($arProperty['ID'],$arParams['SHOPS_LIST'])) {
    continue;
    }
	foreach ($arProperty as $k => $v) {
		if (!is_array($v))
		$arProperty[$k] = htmlspecialchars_decode($v);
	}
	?>
	<?php if ($arProperty['ID'] != 2) {?>
        <div class="shop-block" itemscope itemtype="http://schema.org/Organization">
<meta itemprop="name" content="Интернет-магазин Атлет">
            <div class="shop-address" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                <h3><span itemprop="streetAddress"><?php echo $arProperty['ADDRESS'];?></span></h3>
				<?php if ($arProperty["PHONE"]) {?><div class="shop-phones"><span itemprop="telephone"><?=$arProperty["PHONE"]?></span></div><?php }?>
                <?php if ($arProperty["SCHEDULE"]) {?><div class="shop-time">
            		<?=$arProperty["SCHEDULE"]?>
                </div><?php }?>
                <div class="shop-cash">Наличный расчет, VISA, Mastercard</div>
            </div><!--
            --><div style="background-image: url(<?php echo $arProperty['DETAIL_IMG']['src']?>);" class="shop-view"></div>
	            <?if ($arProperty['GPS_N']) {?>
	            <div id="shop-map-<?=$arProperty['ID']?>" class="shop-map">
	            </div>
	            <?php }?>
        </div>
	<?php }?>
	<?endforeach;?>
	
	
	<script type="text/javascript">

	<?foreach($arResult["STORES"] as $pid=>$arProperty):
	if (!in_array($arProperty['ID'],$arParams['SHOPS_LIST'])) {
	    continue;
	}
	?>
	<?php if ($arProperty['ID'] != 2 && $arProperty['GPS_N']) {?>
	    ymaps.ready(function() {
	 	var map_<?=$arProperty['ID']?>;
	 	map_<?=$arProperty['ID']?> = new ymaps.Map("shop-map-<?=$arProperty['ID']?>", {
	            center: [<?php echo $arProperty['GPS_S'];?>,<?php echo $arProperty['GPS_N'];?>], 
	            zoom: 16,
	            controls: [],
	        });
        	var points = [];
        	var markerCollection = new ymaps.GeoObjectCollection(null, {});
            	var myPlacemark = markerCollection.add(new ymaps.Placemark([<?php echo $arProperty['GPS_S'];?>,<?php echo $arProperty['GPS_N'];?>], {
                    hintContent: '',
                    balloonContent: '<strong><?php echo $arProperty['TITLE'];?></strong><br><?php echo $arProperty['ADDRESS'];?>'
                }));
            	map_<?=$arProperty['ID']?>.geoObjects.add(markerCollection);
            	map_<?=$arProperty['ID']?>.setBounds(markerCollection.getBounds());
            if (map_<?=$arProperty['ID']?>.getZoom() >16) {
            	map_<?=$arProperty['ID']?>.setZoom(16);
            }
	    });
        <?php }?>
        <?endforeach;?>
        
    </script>
	