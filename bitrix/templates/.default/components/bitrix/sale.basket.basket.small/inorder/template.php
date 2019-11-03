<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $price;
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
	if (count($arResult['ITEMS'])) {
		foreach ($arResult["ITEMS"] as &$v)
		{
			$price = $price +$v["PRICE"]*$v["QUANTITY"];
		}

	} 
}
?>
<?=count($arResult['ITEMS'])?> <?php echo declension(count($arResult['ITEMS']), array('товар',"товара","товаров"));?>  на сумму <?php echo round($price,2);?> р.
