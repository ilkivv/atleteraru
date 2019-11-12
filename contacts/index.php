<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetAdditionalCSS('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
$APPLICATION->AddHeadScript('http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
$APPLICATION->SetPageProperty("title", "Контактная информация и форма обратной связи интернет-магазина Атлет");
$APPLICATION->SetPageProperty("description", "Контактная информация и форма обратной связи интернет-магазина Атлет");
$APPLICATION->SetTitle("Контакты");
?><h1>Контакты</h1>
<?php include $_SERVER["DOCUMENT_ROOT"]."/contacts/".$_GLOBALS['CURRENT_CITY']['ID'].'.php';?>

<?require($_SERVER["DOCUMENT_ROOT"]."/contacts/form.php");?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>