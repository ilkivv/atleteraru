<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Контактная информация и форма обратной связи интернет-магазина Атлет");
$APPLICATION->SetPageProperty("description", "Контактная информация и форма обратной связи интернет-магазина Атлет");
$APPLICATION->SetTitle("Контакты");
?><h1>Контакты</h1>
<?php include $_SERVER["DOCUMENT_ROOT"]."/contacts/".$_GLOBALS['CURRENT_CITY']['ID'].'.php';?>

<?require($_SERVER["DOCUMENT_ROOT"]."/contacts/form.php");?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>