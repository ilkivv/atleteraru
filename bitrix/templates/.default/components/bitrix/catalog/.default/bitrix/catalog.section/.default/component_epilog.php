<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $templateData */
/** @var @global CMain $APPLICATION */
global $APPLICATION;
if (isset($templateData['TEMPLATE_THEME']))
{
	$APPLICATION->SetAdditionalCSS($templateData['TEMPLATE_THEME']);
}


$countPage = $arResult['NAV_RESULT']->NavPageCount;



global $APPLICATION;
$dir = $APPLICATION->GetCurDir();
$protocol = (CMain::IsHTTPS()) ? "https://" : "http://"; 

$dir = $protocol . $_SERVER['SERVER_NAME'].$dir;

$current = isset($_GET['PAGEN_2']) ? (int)$_GET['PAGEN_2'] : 1;

$current = $current < 1 ? 1 : $current;

if($current < $countPage){
	
	$next = '<link rel="next" href="'.$dir.'?PAGEN_2=' . ($current + 1) . '"/>';

	
	$APPLICATION->AddHeadString($next, true);
	
}


if($current > 2){

	$prev = '<link rel="prev" href="'.$dir.'?PAGEN_2=' . ($current - 1) . '"/>';

	$APPLICATION->AddHeadString($prev, true);
	
}

if($current == 2){
	
	$prev = '<link rel="prev" href="'.$dir.'"/>';
	
	
	$APPLICATION->AddHeadString($prev, true);
	
}



?>