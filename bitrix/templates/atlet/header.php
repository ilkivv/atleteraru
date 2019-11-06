<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


$add_title = (isset($_GET['PAGEN_2']) && $_GET['PAGEN_2'] > 1) ? ' - Страница ' . (int)$_GET['PAGEN_2'] : '';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
	<meta charset="utf-8" />
	<meta name="yandex-verification" content="1cf9d46c8b587c68" />
<meta name="yandex-verification" content="b22a6ee97897daea" />
	
	<?include_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/include/version-site.php');?>
	
	<?$APPLICATION->ShowHead();?>
	<title><?$APPLICATION->ShowTitle()?><?=$add_title?></title>
	<link rel="stylesheet" href="/bitrix/templates/atlet/css/styles.css?v=1" />
	<link rel="stylesheet" href="/bitrix/templates/atlet/libs/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="/bitrix/templates/.default/libs/stacktable/stacktable.css" />
	
	<link rel="stylesheet" href="/bitrix/templates/.default/libs/slick/slick.css" />
	
	
	<link rel="stylesheet" href="/bitrix/templates/atlet/css/styles404.css" />
	<link rel="stylesheet" href="/bitrix/templates/atlet/css/custom.css?v=4" />
	
	<link rel="stylesheet" href="/bitrix/templates/.default/css/adaptive.css?v=10" />
	
	<script type="text/javascript">
	window.dataLayer = window.dataLayer || [];
	</script>
<script type="text/javascript" src="/seo.js" async></script>

	<script src="/bitrix/templates/.default/js/privacy.js"></script>
	<script type="text/javascript">
		var privateAgreement = privacy({company: 'ИП Шарапов К. М.', date: '«22»  ноября 2018 г.'});
	</script>

</head>
<body>
<?$APPLICATION->ShowPanel()?>
<div style="display: none;" id="tmp">
    <div class="product-residue">
        
    </div>
</div>

    <div id="wrap">

        <?include_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/include/header.php')?>

        <section id="main" class="clearfix">

            <aside class="sidebar">

                <div class="sidebar-catalog j-tabs">
					<?php if (strpos($APPLICATION->GetCurPage(),"manufacturer")) {$isManufacturer=true;}?> 
                    <div class="j-tabs-links tabs-links">
                        <a  rel="catalog-products" href="#catalog-products" class="j-tab-brands <?php if (!$isManufacturer){?>active<?php }?>">Категории</a><!--
                        --><a  rel="catalog-brands" href="#catalog-brands" class="j-tab-brands <?php if ($isManufacturer){?>active<?php }?>">Бренды</a>
                    </div>

                    <div class="catalog-search">
                        <form action="<?=($GLOBALS['CURRENT_CITY']['PROPERTIES']['FOLDER']['VALUE'])?>/search/" method="get" id='sForm'>
                        <input type="hidden" name="how" value="r">
                            <input placeholder="Поиск по каталогу" type="text" id="search" name="q" class="search-text-field" />
                            <input class="hidden-input" type="submit" value="Найти" />
                            <a href="#" class="search-submit" onclick="$('#sForm').submit();">Найти</a>
                        </form>
                    </div>

                    <div id="catalog-products"  <?php if (!$isManufacturer){?>style="display: block;"<?php }?> class="j-tabs-body tabs-body">
                        <?$APPLICATION->IncludeComponent(
                        	"bitrix:catalog.section.list",
                        	"",
                        	Array(
                        		"IBLOCK_TYPE" => "xmlcatalog",
                        		"IBLOCK_ID" => "45",
                                "ELEMENT_SORT_FIELD" => "name",
                                "ELEMENT_SORT_ORDER" => "ASC",
                                "SECTION_SORT_FIELD"    =>      "name",
                                "SECTION_SORT_ORDER"    =>      "desc",
                        		"SECTION_ID" => "",
                                "TOP_DEPTH" => "1",
                        		"SECTION_URL" => "/e-store/#SECTION_CODE_PATH#/",
                        		"COUNT_ELEMENTS" => "Y",
                                'CNT_ACTIVE'=>true,
                        		"CACHE_TYPE" => "N",
                        		"CACHE_TIME" => "3600"
                        	)
                        );?>
                    </div>

                    <div id="catalog-brands"  <?php if ($isManufacturer){?>style="display: block;"<?php }?> class="j-tabs-body tabs-body">
                        <ul>
                        <?
                            CModule::IncludeModule("iblock");
                            
                            $arFilter = Array("IBLOCK_ID"=>45, "ACTIVE"=>"Y" );
                            $res = CIBlockElement::GetList(Array("ID"=>"ASC"), $arFilter, array('PROPERTY_CML2_MANUFACTURER'),array("nPageSize"=>50000), array('IBLOCK_ID','PROPERTY_CML2_MANUFACTURER','ID'));
                            while($ob = $res->GetNext())
                            {
                            	$ids[] = $ob['PROPERTY_CML2_MANUFACTURER_ENUM_ID'];
                            }
                            $ids = array_unique($ids);
                            $property_enums = CIBlockPropertyEnum::GetList(Array("VALUE"=>"ASC"), Array("IBLOCK_ID"=>45, "CODE"=>"CML2_MANUFACTURER"));
                        
                            while($enum_fields = $property_enums->GetNext())
                            {
                            	if (in_array($enum_fields["ID"],$ids)) {
                                	echo '<li><a href="/e-store/manufacturer/'.$enum_fields["ID"].'/">'.$enum_fields["VALUE"].'</a></li>';
                            	}
                            }
                        ?>
                            
                        </ul>
                    </div>

                </div>

                <div class="discount-card">
                    <h2>Скидки<br /> по карте!</h2>
                    <p>Покупайте товары,<br /> получайте скидки на следующие покупки.</p>
                    <p><a href="<?=($GLOBALS['CURRENT_CITY']['PROPERTIES']['FOLDER']['VALUE'])?>/bonuce/"><strong>Узнайте как получить накопительную карту</strong></a></p>
                </div>
                
                <?php
                
                $topfltr = array("PROPERTY_DAYSALE"=>1);
                  $APPLICATION->IncludeComponent(
                    "bitrix:catalog.top", 
                    "daysale", 
                    array(
                      "VIEW_MODE" => "BANNER",
                      "TEMPLATE_THEME" => "blue",
                      "PRODUCT_DISPLAY_MODE" => "Y",
                      "ADD_PICT_PROP" => "-",
                      "LABEL_PROP" => "-",
                      "SHOW_DISCOUNT_PERCENT" => "N",
                      "SHOW_OLD_PRICE" => "Y",
                      "ROTATE_TIMER" => "30",
                      "MESS_BTN_BUY" => "Купить",
                      "MESS_BTN_ADD_TO_BASKET" => "В корзину",
                      "MESS_BTN_DETAIL" => "Подробнее",
                      "MESS_NOT_AVAILABLE" => "Нет в наличии",
                      "IBLOCK_TYPE" => "xmlcatalog",
                      "IBLOCK_ID" => "45",
                      "DATA_SUFFIX" => "customers",
                      "FILTER_NAME" => "topfltr",
                      "SECTION_URL" => ($GLOBALS['CURRENT_CITY']['PROPERTIES']['FOLDER']['VALUE'])."/e-store/#SECTION_ID#/",
                      "DETAIL_URL" => ($GLOBALS['CURRENT_CITY']['PROPERTIES']['FOLDER']['VALUE'])."/e-store/#SECTION_ID#/#ELEMENT_ID#/",
                      "BASKET_URL" => "/personal/cart/",
                      "ACTION_VARIABLE" => "action",
                      "PRODUCT_ID_VARIABLE" => "id",
                      "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                      "ADD_PROPERTIES_TO_BASKET" => "Y",
                      "PRODUCT_PROPS_VARIABLE" => "prop",
                      "PARTIAL_PRODUCT_PROPERTIES" => "Y",
                      "SECTION_ID_VARIABLE" => "SECTION_ID",
                      "DISPLAY_COMPARE" => "N",
                      "ELEMENT_COUNT" => "1",
                      "LINE_ELEMENT_COUNT" => "1",
                      "PROPERTY_CODE" => array(
                        0 => "DAYSALE",
                        1 => "CML2_ATTRIBUTES",
                        2 => "CML2_TASTE",
                      ),
                      "OFFERS_FIELD_CODE" => array(
                        0 => "NAME",
                        1 => "",
                      ),
                      "OFFERS_PROPERTY_CODE" => array(
                        0 => "CML2_TASTE",
                        1 => "",
                      ),
                      "OFFERS_SORT_FIELD" => "sort",
                      "OFFERS_SORT_ORDER" => "asc",
                      "OFFERS_SORT_FIELD2" => "timestamp_x",
                      "OFFERS_SORT_ORDER2" => "desc",
                      "OFFERS_LIMIT" => "15",
                      "PRICE_CODE" => array($_GLOBALS['CURRENT_CITY']['PROPERTIES']['PRICETYPE']['VALUE']),
                          'ALLOW_SALE'=>$_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'],
                      "USE_PRICE_COUNT" => "Y",
                      "SHOW_PRICE_COUNT" => "1",
                      "PRICE_VAT_INCLUDE" => "N",
                      "PRODUCT_PROPERTIES" => array(
                        0 => "CML2_ATTRIBUTES",
                      ),
                      "USE_PRODUCT_QUANTITY" => "N",
                      "CACHE_TYPE" => "N",
                      "CACHE_TIME" => "3600",
                      "CACHE_GROUPS" => "N",
                      "HIDE_NOT_AVAILABLE" => "N",
                      "OFFERS_CART_PROPERTIES" => array(
                        0 => "CML2_TASTE",
                      ),
                      "CONVERT_CURRENCY" => "Y",
                      "CURRENCY_ID" => "RUB",
                      "CACHE_FILTER" => "N"
                    ),
                    false
                  );?>
            </aside>

            <section class="content">