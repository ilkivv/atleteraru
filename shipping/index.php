<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Информация об оплате и доставке товара - Спортивное питание Атлет");
$APPLICATION->SetPageProperty("title", "Информация об оплате и доставке товара - Спортивное питание Атлет");
$APPLICATION->SetTitle("Доставка/оплата");
?><h1>Доставка и оплата</h1>
<?php include $_SERVER["DOCUMENT_ROOT"]."/shipping/".$_GLOBALS['CURRENT_CITY']['ID'].'.php';?>
 </strong><? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>