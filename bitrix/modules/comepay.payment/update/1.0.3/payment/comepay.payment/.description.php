<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
IncludeModuleLangFile(__FILE__);

$psTitle = GetMessage("COMEPAY.PAYMENT_TITLE");
$psDescription = GetMessage("COMEPAY.PAYMENT_DESCRIPTION");

$arPSCorrespondence = array(
		"SHOP_LOGIN" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_SHOP_LOGIN"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_PASS" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_SHOP_PASS"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"PRV_ID" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_PRV_ID"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_NOTIFY_PASS" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_SHOP_NOTIFY_PASS"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		"LIFETIME" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_BILL_LIFETIME"),
				"DESCR" => GetMessage("COMEPAY.PAYMENT_BILL_LIFETIME_DESCR"),
				"VALUE" => "240",
				"TYPE" => ""
			),
		"SHOP_NOTIFY_PASS" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_SHOP_NOTIFY_PASS"),
				"DESCR" => "",
				"VALUE" => "",
				"TYPE" => ""
			),
		/*
		"TEST" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_TEST"),
				"DESCR" => "",
				"VALUE" => "1",
				"TYPE" => "",
			),*/
		"CLIENT_PHONE" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_CLIENT_PHONE"),
				"DESCR" => "",
				"VALUE" => "PHONE",
				"TYPE" => "PROPERTY"
			),
		"ORDER_ID" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_ORDER_ID"),
				"DESCR" => "",
				"VALUE" => "ID",
				"TYPE" => "ORDER"
			),
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_SHOULD_PAY"),
				"DESCR" => "",
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER"
			),
		"CURRENCY" => array(
				"NAME" => GetMessage("COMEPAY.PAYMENT_CURRENCY"),
				"DESCR" => GetMessage("COMEPAY.PAYMENT_CURRENCY_DESCR"),
				"VALUE" => "CURRENCY",
				"TYPE" => "ORDER"
			),
	);
?>