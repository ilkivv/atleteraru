<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Оформление заказа");
?><br>
<?$APPLICATION->IncludeComponent(
	"custom:sale.order.full",
	"",
	Array(
		"ALLOW_PAY_FROM_ACCOUNT" => "N",
		"SHOW_MENU" => "N",
		"CITY_OUT_LOCATION" => "N",
		"COUNT_DELIVERY_TAX" => "N",
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
		"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
		"SEND_NEW_USER_NOTIFY" => "N",
		"DELIVERY_NO_SESSION" => "Y",
		"PROP_1" => array(),
		"PROP_2" => array("3","8","9","10","11","12","13","14","15","16"),
		"PATH_TO_BASKET" => "/personal/cart/",
		"PATH_TO_PERSONAL" => "/personal/",
		"PATH_TO_AUTH" => "/auth.php",
		"PATH_TO_PAYMENT" => "/personal/order/payment/",
		"USE_AJAX_LOCATIONS" => "Y",
		"SHOW_AJAX_DELIVERY_LINK" => "S",
		"SET_TITLE" => "Y",
		"PRICE_VAT_INCLUDE" => "N",
		"PRICE_VAT_SHOW_VALUE" => "N"
	)
);?><br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>