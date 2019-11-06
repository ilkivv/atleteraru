<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
global $APPLICATION;
if (!empty($arResult['ITEMS']))
{
    
    ?>
    <div class="carousel-container">
        <?/*<a class="carousel-prev j-carousel-prev-<?php echo $arParams['DATA_SUFFIX'];?>" href="#">Назад</a>
        <a class="carousel-next j-carousel-next-<?php echo $arParams['DATA_SUFFIX'];?>" href="#">Вперёд</a>*
        <div class="carousel j-carousel" data-suffix="<?php echo $arParams['DATA_SUFFIX'];?>">*/?>
        
        <div class="carousel products-carousel" >
    <?
    
	CJSCore::Init(array("popup"));
	
	$strElementEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT");
	$strElementDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE");
	$arElementDeleteParams = array("CONFIRM" => GetMessage('CT_BCT_ELEMENT_DELETE_CONFIRM'));

	$strFullPath = $_SERVER['DOCUMENT_ROOT'].$this->GetFolder();
	$templateData = array(
		'TEMPLATE_THEME' => $this->GetFolder().'/'.ToLower($arParams['VIEW_MODE']).'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css',
		'TEMPLATE_CLASS' => 'bx_'.$arParams['TEMPLATE_THEME']
	);

	include($strFullPath.'/banner/template.php');
	?>
	    </div>
	</div>
	<?php
}
?>