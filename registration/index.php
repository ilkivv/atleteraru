<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Страница регистрации на сайте. Больше скидок авторизованным пользователям");
$APPLICATION->SetPageProperty("description", "Страница регистрации на сайте. Больше скидок авторизованным пользователям");
$APPLICATION->SetTitle("Регистрация");
?><h1>Регистрация</h1>
<?$APPLICATION->IncludeComponent(
	"bitrix:main.register",
	"custom",
	Array(
		"USER_PROPERTY_NAME" => "",
		"SEF_MODE" => "N",
		"SHOW_FIELDS" => array("NAME","SECOND_NAME","LAST_NAME","PERSONAL_MOBILE","PERSONAL_STREET","PERSONAL_CITY","PERSONAL_ZIP","EMAIL"),
		"REQUIRED_FIELDS" => array("EMAIL","NAME","LAST_NAME","PERSONAL_MOBILE","PERSONAL_CITY"),
		"AUTH" => "Y",
		"USE_BACKURL" => "Y",
		"SUCCESS_PAGE" => $APPLICATION->GetCurPageParam('',array('backurl')),
		"SET_TITLE" => "N",
		"USER_PROPERTY" => array("UF_HOUSE","UF_FLAT")
	)
);?><br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>