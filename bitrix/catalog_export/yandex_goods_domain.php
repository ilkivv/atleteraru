<?

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
Loader::includeModule("iblock");


$arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM");
$arFilter = Array("IBLOCK_ID"=>IntVal($yvalue), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);

$rs = CIBlockElement::GetList(
   array(), 
   array(
	"IBLOCK_ID" => 54, 
	"ID" => 87919
   ),
   false, 
   false,
   array("ID", "IBLOCK_ID", "PROPERTY_PATH", "PROPERTY_DOMAIN")
);

if($ar = $rs->GetNext()) {

	header('Content-Type: text/xml; charset=windows-1251');

	$doman = $ar['PROPERTY_DOMAIN_VALUE'];
	
	
	$protocol = (CMain::IsHTTPS()) ? 'https' : 'http';
	$adress = $protocol.'://'.$_SERVER['SERVER_NAME'];	

	$yandex = file_get_contents(trim($ar['PROPERTY_PATH_VALUE']));
	$yandex = str_replace($doman, $adress ,$yandex);	

	echo $yandex;
	die();
	
}