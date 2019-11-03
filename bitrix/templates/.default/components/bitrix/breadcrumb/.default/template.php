<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//delayed function must return a string
if(empty($arResult))
	return "";

//var_dump($GLOBALS['CURRENT_CITY']['PROPERTIES']['FOLDER']['VALUE']);

//echo "<pre>"; var_dump($arResult); echo "</pre>";

if($GLOBALS['CURRENT_CITY']['PROPERTIES']['FOLDER']['VALUE']){
	$arResult[0]['LINK'] = $GLOBALS['CURRENT_CITY']['PROPERTIES']['FOLDER']['VALUE'].'/';
}
	
	
$strReturn = '<div class="bx_breadcrumbs"><ul>';

$num_items = count($arResult);
for($index = 0, $itemSize = $num_items; $index < $itemSize; $index++)
{
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	
	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1)
		$strReturn .= '<li><a href="'.$arResult[$index]["LINK"].'" title="'.$title.'">'.$title.'</a></li>';
	else
		$strReturn .= '<li><span>'.$title.'</span></li>';
}

$strReturn .= '</ul></div>';

return $strReturn;
?>