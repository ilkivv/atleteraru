<?
$arUrlRewrite = array(
	array(
		"CONDITION" => "#^/kras/e-store/manufacturer/([0-9]{1,})/(.*)#",
		"RULE" => "MANUFACTURER_ID=\$1",
		"ID" => "bitrix:catalog.section",
		"PATH" => "/kras/e-store/manufacturer/index.php",
	),
	array(
		"CONDITION" => "#^/kem/e-store/manufacturer/([0-9]{1,})/(.*)#",
		"RULE" => "MANUFACTURER_ID=\$1",
		"ID" => "bitrix:catalog.section",
		"PATH" => "/kem/e-store/manufacturer/index.php",
	),
	array(
		"CONDITION" => "#^/e-store/manufacturer/([0-9]{1,})/(.*)#",
		"RULE" => "MANUFACTURER_ID=\$1",
		"ID" => "bitrix:catalog.section",
		"PATH" => "/e-store/manufacturer/index.php",
	),
	array(
		"CONDITION" => "#^/personal/lists/#",
		"RULE" => "",
		"ID" => "bitrix:lists",
		"PATH" => "/personal/lists/index.php",
	),
	array(
		"CONDITION" => "#^/kras/e-store/#",
		"RULE" => "",
		"ID" => "bitrix:catalog",
		"PATH" => "/kras/e-store/index.php",
	),
	array(
		"CONDITION" => "#^/content/news/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => "/content/news/index.php",
	),
	array(
		"CONDITION" => "#^/kem/e-store/#",
		"RULE" => "",
		"ID" => "bitrix:catalog",
		"PATH" => "/kem/e-store/index.php",
	),
	array(
		"CONDITION" => "#^/articles/#",
		"RULE" => "",
		"ID" => "bitrix:news",
		"PATH" => "/articles/index.php",
	),
	array(
		"CONDITION" => "#^/e-store/#",
		"RULE" => "",
		"ID" => "bitrix:catalog",
		"PATH" => "/e-store/index.php",
	),
);

?>