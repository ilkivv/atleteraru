<div class="header-wrap">
	<header id="header" class="header-1">
		<div class="header">
		
			<div class="col-logo">
			
				<a href="/" class="logo">
					<img src="/bitrix/templates/atlet/img/atletera.png?23" alt="Атлетера" />
				</a>	
			
			</div>
			
		
		  <div class="col-right">
      
      
      
    
			<div class="contacts-header">
			
				<?php /*if($_GLOBALS['CURRENT_CITY']['ID'] == 74935){?>				
								
					
					<div class="header-phone header-phone-1">+7 (391) 251-89-98</div>
					<div class="header-phone header-phone-2">+7-913-534-89-98</div>
				
				
				<?php }elseif($_GLOBALS['CURRENT_CITY']['ID'] == 76341){
					?>						
					<div class="header-phone header-phone-1">+7 (983) 596-63-20</div>
					
				<?php }else{?>					
					
					<div class="header-phone header-phone-1">+7 (3822) 57-68-20</div>
					<div class="header-phone header-phone-2">+7-913-827-6820</div>
					
				<?php }*/?>
			<div class="header-phone header-phone-1"><a href="tel:88002221340">8 800 222 13 40</a></div>
			
			<?$full_version = isset($_SESSION['VERSION_SITE']) && $_SESSION['VERSION_SITE'] == 'full';?>	
					
			<?if($full_version):?>		  
			  <?$url = $APPLICATION->GetCurPageParam("version=adaptive", array("version")); ?>
			  <a class="version-site header-version-site mobile-change" style="display: block !important;" href="<?=$url?>">Мобильная версия сайта</a>
			<?endif?>
				
				
			</div>
      
      <div class="header-cities">
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
      
        </div>
		
		<div class="mobile-toggle-search">
		
		<div class="mobile-search-block">
		<?$APPLICATION->IncludeComponent(
			"bitrix:search.form",
			"search-mobile",
			Array(
				"PAGE" => "#SITE_DIR#search/",
				"USE_SUGGEST" => "N"
			)
		);?>
		</div>
		
		</div>
		
		
				<?
				//if ($_SERVER['REMOTE_ADDR'] == '5.77.5.203') print_r($_GLOBALS['CURRENT_CITY']);
				
				global $USER;
				if ($_GLOBALS['CURRENT_CITY']['PROPERTIES']['SALEALLOW']['VALUE_XML_ID'] == 'Y' || true){
				if ($USER->IsAuthorized() && !$_POST['AUTH_FORM'])
				{ 
					$rsUser = CUser::GetByID($USER->GetID());
					$arUser = $rsUser->Fetch();
					?>
					<div class="header-auth-reg">
					<div class="welcome">Здравствуйте, <a href="/personal/"><?=$arUser['NAME']?></a></div>
					
					<div class="personal-block"><a href="/personal/">Личный кабинет</a> <a href="/?logout=yes">выйти</a></div></div>
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
						<?$APPLICATION->IncludeComponent("bitrix:system.auth.forgotpasswd","",Array("SHOW_ERRORS" => "Y"));?>
						
					</div>
				</div> 
				<?php 
				}
			}?>
			   
			   
          <div class="basket-top" id='smallbasket'>
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
		</div>
	  
	  
		
		<?/*
		<div class="header-row-mobile">
		
			
			
			
			<div class="col-phone">
				<a href="tel:88002221340" class="phone">8 800 222 13 40</a>		
			</div>
			
		
		
		</div>
					
		*/?>

		
		<div class="header-nav">
		
			<div class="toggle-container">

				<div class="mobile-menu-btn toggle-content-btn">
				
					<button class="cmn-toggle-switch cmn-toggle-switch__htx">
						<span>Меню</span>
					</button>

				</div>
				
				
				<div class="toggle-content">
				
					<?$full_version = isset($_SESSION['VERSION_SITE']) && $_SESSION['VERSION_SITE'] == 'full';?>	
					
					
					<?if(!$full_version):?>
					  <?$url = $APPLICATION->GetCurPageParam("version=full", array("version")); ?>
					  <a class="version-site header-version-site" href="<?=$url?>">Полная версия сайта</a>
					
					<?endif?>
					
					

					<nav id="menu">
					
						<div class="mobile-catalog-menu">
							
							<div class="menu-title">Категории</div>
							
							<div class="menu-content">
								
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
						
						
						</div>
						
						<div class="mobile-catalog-menu">
							
							<div class="menu-title">Бренды</div>
							
							<div class="menu-content">
								
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
						

						<?$APPLICATION->IncludeComponent("bitrix:menu",".default",Array(
								"ROOT_MENU_TYPE" => "top_".$_GLOBALS['CURRENT_CITY']['ID'], 
								"MAX_LEVEL" => "1", 
								"CHILD_MENU_TYPE" => "top", 
								"USE_EXT" => "Y",
								"DELAY" => "N",
								"ALLOW_MULTI_SELECT" => "Y",
								"MENU_CACHE_TYPE" => "N", 
								"MENU_CACHE_TIME" => "360000", 
								"MENU_CACHE_USE_GROUPS" => "Y", 
								"MENU_CACHE_GET_VARS" => "" 
							)
						);?>

					</nav>
				
				</div>
				
				
			</div>	
		
		</div>
		
		
		
		<button type="button" class="up-button j-up-button" style="display: none;">Вверх</button>
		
	</header>

</div>