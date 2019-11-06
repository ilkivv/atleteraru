<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$add_title = (isset($_GET['PAGEN_2']) && $_GET['PAGEN_2'] > 1) ? ' - Страница ' . (int)$_GET['PAGEN_2'] : '';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
	<meta charset="utf-8" />

	<?include_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/include/version-site.php');?>
	<?$APPLICATION->ShowHead();?>
	<title><?$APPLICATION->ShowTitle()?><?=$add_title?></title>
	<link rel="stylesheet" href="/bitrix/templates/atlet/css/styles.css?v=1" />
	<link rel="stylesheet" href="/bitrix/templates/atlet/libs/magnific-popup/magnific-popup.css" />
	<link rel="stylesheet" href="/bitrix/templates/.default/libs/stacktable/stacktable.css" />
	
	<link rel="stylesheet" href="/bitrix/templates/atlet/css/custom.css?v=4" />
	<link rel="stylesheet" href="/bitrix/templates/.default/css/adaptive.css?v=10" />
	<script type="text/javascript" src="/bitrix/templates/atlet/js/jquery.js"></script>
	
	<script type="text/javascript" src="/bitrix/templates/.default/libs/stacktable/stacktable.js"></script>
	
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
	 
	 <div class="catalog-block-link">
	 
		<div class="categories-types">
			<a href="/" ><span>Категории</span></a>
			<a href="/#catalog-brands"><span>Бренды</span></a>
		</div>
		
	 </div>
	 
	
	
	<?/*
        <header id="header" class="header-1">
            <div class="header">
                <?php if($_GLOBALS['CURRENT_CITY']['ID'] == 74935){?>
                <a href="/" class="logo"><img src="/bitrix/templates/atlet/img/atletera.png?23" alt="Атлетера" /></a>
                <div class="header-phone header-phone-1">+7 (391) 251-89-98</div>
                <div class="header-phone header-phone-2">+7-913-534-89-98</div>
                <?php }elseif($_GLOBALS['CURRENT_CITY']['ID'] == 76341){
                	?>
                	<a href="/" class="logo"><img src="/bitrix/templates/atlet/img/atletera.png?23" alt="Атлетера" /></a>
                	<div class="header-phone header-phone-1">+7 (983) 596-63-20</div>
                <?php }else{?>
                <a href="/" class="logo"><img src="/bitrix/templates/atlet/img/atletera.png?23" alt="Атлетера" /></a>
                <div class="header-phone header-phone-1">+7 (3822) 57-68-20</div>
                <div class="header-phone header-phone-2">+7-913-827-6820</div>
                <?php }?>
					<!--
					<div class="callback-btn j-callback-btn">
						<a class="ajax-popup-form" href="/ajax/callback.php">Заказать звонок</a>
					</div>
					-->
				
                 <?
                    global $USER;
                    if ($_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'] == 'Y'){
                    if ($USER->IsAuthorized() && !$_POST['AUTH_FORM'])
                    { 
	                    $rsUser = CUser::GetByID($USER->GetID());
						$arUser = $rsUser->Fetch();
	                    ?>
	                    <div class="header-auth-reg">Здравствуйте, <a href="/personal/"><?=$arUser['NAME']?></a>
	                    <br /><div style='text-align:left;'><a href="/personal/">Личный кабинет</a> <a href="/?logout=yes">выйти</a></div></div>
	                    <?php 
                    } else {
                    ?>
	                <div class="header-auth-reg j-auth-container">
	                    <a class="auth j-auth" href="#">Вход</a> или <a href="/registration/">регистрация</a>
	                    <div class="auth-popup j-auth-popup">
	                        <?$APPLICATION->IncludeComponent("bitrix:system.auth.form","",Array(
	                             "REGISTER_URL" => "/auth/?register=yes",
	                             "FORGOT_PASSWORD_URL" => "",
	                             "PROFILE_URL" => "/personal/",
	                             "SHOW_ERRORS" => "Y",
	                             )
	                        );?>
	                        <?$APPLICATION->IncludeComponent("bitrix:system.auth.forgotpasswd","",Array());?>
	                    </div>
	                </div> 
                    <?php 
                    }
                    }?>
                    <?$APPLICATION->IncludeComponent(
						"bitrix:news.list", 
						"cities", 
						array(
							"IBLOCK_ID" => "51",
							"LIST_PROPERTY_CODE" => array(
								0 => "domain",
							),
							"SORT_BY" => "NAME",
							"SORT_TYPE" => "ASC",
							"CURRENT" =>$_GLOBALS['CURRENT_CITY']['ID'],
							"CACHE_TYPE" => "A",
							"IBLOCK_TYPE" => "news",
							"NEWS_COUNT" => "20",
							"SORT_BY1" => "SORT",
							"SORT_ORDER1" => "ASC",
							"SORT_BY2" => "NAME",
							"SORT_ORDER2" => "ASC",
							"FILTER_NAME" => "",
							"FIELD_CODE" => array(
								0 => "NAME",
								1 => "",
							),
							"PROPERTY_CODE" => array(
								0 => "domain",
								1 => "",
							),
							"CHECK_DATES" => "Y",
							"DETAIL_URL" => "",
							"AJAX_MODE" => "N",
							"AJAX_OPTION_JUMP" => "N",
							"AJAX_OPTION_STYLE" => "Y",
							"AJAX_OPTION_HISTORY" => "N",
							"CACHE_TIME" => "36000000",
							"CACHE_FILTER" => "N",
							"CACHE_GROUPS" => "Y",
							"PREVIEW_TRUNCATE_LEN" => "",
							"ACTIVE_DATE_FORMAT" => "d.m.Y",
							"SET_TITLE" => "N",
							"SET_BROWSER_TITLE" => "N",
							"SET_META_KEYWORDS" => "N",
							"SET_META_DESCRIPTION" => "N",
							"SET_STATUS_404" => "N",
							"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
							"ADD_SECTIONS_CHAIN" => "N",
							"HIDE_LINK_WHEN_NO_DETAIL" => "N",
							"PARENT_SECTION" => "",
							"PARENT_SECTION_CODE" => "",
							"INCLUDE_SUBSECTIONS" => "N",
							"PAGER_TEMPLATE" => ".default",
							"DISPLAY_TOP_PAGER" => "N",
							"DISPLAY_BOTTOM_PAGER" => "N",
							"PAGER_TITLE" => "Новости",
							"PAGER_SHOW_ALWAYS" => "N",
							"PAGER_DESC_NUMBERING" => "N",
							"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
							"PAGER_SHOW_ALL" => "Y",
							"AJAX_OPTION_ADDITIONAL" => ""
						),
						false
					);?>
                <div id='smallbasket'>
                <?$APPLICATION->IncludeComponent(
					"bitrix:sale.basket.basket.small",
					"",
					Array(
						"PATH_TO_BASKET" => "/personal/cart/",
						"PATH_TO_ORDER" => "/personal/",
						"SHOW_DELAY" => "N",
						"SHOW_NOTAVAIL" => "N",
						"SHOW_SUBSCRIBE" => "N",
                        'ALLOW_SALE'=>$_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID']
					)
				);?>
                </div>
            
                
                
            </div>

            <nav id="menu">

                <?$APPLICATION->IncludeComponent("bitrix:menu",".default",Array(
                        "ROOT_MENU_TYPE" => "top", 
                        "MAX_LEVEL" => "1", 
                        "CHILD_MENU_TYPE" => "top", 
                        "USE_EXT" => "Y",
                        "DELAY" => "N",
                        "ALLOW_MULTI_SELECT" => "Y",
                        "MENU_CACHE_TYPE" => "N", 
                        "MENU_CACHE_TIME" => "3600", 
                        "MENU_CACHE_USE_GROUPS" => "Y", 
                        "MENU_CACHE_GET_VARS" => "" 
                    )
                );?>

            </nav>
			<button type="button" class="up-button j-up-button" style="display: block;">Вверх</button>
        </header>
		
	*/?>	

       <section id="main" class="clearfix">

            <section class="content wide-content clearfix">