<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
$arViewModeList = $arResult['VIEW_MODE_LIST'];
?><?
if (0 < $arResult["SECTIONS_COUNT"])
{
?>
<ul>
<li><a href="<?=($GLOBALS['CURRENT_CITY']['PROPERTIES']['FOLDER']['VALUE'])?>/e-store/promo/">Акция</a></li>
<li><a href="<?=($GLOBALS['CURRENT_CITY']['PROPERTIES']['FOLDER']['VALUE'])?>/e-store/sale/">Распродажа</a></li>
<?
	
	$intCurrentDepth = 1;
	$boolFirst = true;
	/*foreach ($arResult['SECTIONS'] as &$arSection)
	{
	    if ($arSection['NAME'] == 'Распродажа !!!') {
	        ?><li id=""><a href="<? echo $arSection["SECTION_PAGE_URL"]; ?>"><? echo $arSection["NAME"];?></a></li>
			<?
			}
		}*/
		
	foreach ($arResult['SECTIONS'] as &$arSection)
	{
	    if ($arSection['NAME'] != 'Номенклатура' && $arSection['NAME'] != 'Распродажа !!!') {
		?><li><a href="<? echo $arSection["SECTION_PAGE_URL"]; ?>"><? echo $arSection["NAME"];?></a></li>
		<?
		}
	}
?>
</ul>
<?
}
?>