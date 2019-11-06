<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

 <h1><?=$arResult["NAME"]?></h1>

    <span class="article-date"><?=$arResult["DISPLAY_ACTIVE_FROM"]?></span>
    
    <div class="text">
    <?if(strlen($arResult["DETAIL_TEXT"])>0):?>
    	<?echo $arResult["DETAIL_TEXT"];?>
    <?else:?>
    	<?echo $arResult["PREVIEW_TEXT"];?>
    <?endif?>
    </div>
                
            