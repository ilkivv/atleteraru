<footer class="main-footer container">
	
		<div class="footer-inner">
		
			<div class="footer1">
			
				<div class="left-part">
			
					<?$APPLICATION->IncludeComponent(
						"bitrix:menu",
						"bottom",
						Array(
							"ROOT_MENU_TYPE" => "bottom",
							"MENU_CACHE_TYPE" => "A",
							"MENU_CACHE_TIME" => "3600",
							"MENU_CACHE_USE_GROUPS" => "Y",
							"MENU_CACHE_GET_VARS" => array(0=>"",),
							"MAX_LEVEL" => "1",
							"CHILD_MENU_TYPE" => "",
							"USE_EXT" => "N",
							"DELAY" => "N",
							"ALLOW_MULTI_SELECT" => "N"
						)
					);?>
				
				</div>
				
				<?$full_version = isset($_SESSION['VERSION_SITE']) && $_SESSION['VERSION_SITE'] == 'full';?>
				
				<?if(!$full_version):?>
					<?$url = $APPLICATION->GetCurPageParam("version=full", array("version")); ?>
					<a class="version-site" href="<?=$url?>">Открыть полную версию сайта</a>
				<?else:?>
					<?$url = $APPLICATION->GetCurPageParam("version=adaptive", array("version")); ?>
					<a class="version-site" style="display: block !important;" href="<?=$url?>">Открыть мобильную версию сайта</a>
				<?endif?>
				
				<div class="right-part">


						<a rel="nofollow" class="vk" href="http://vk.com/club59890209" target="new">
						<span class="vk-icon social-icon">
						</span>
						</a>
							<a rel="nofollow" class="instagram" href="https://www.instagram.com/atletera/" target="new">
							<span class="instagram-icon social-icon">
							</span>
						</a>
						
					<!--<div class="callback-btn j-callback-btn">
						<a class="ajax-popup-form" href="/ajax/callback.php">Заказать звонок</a>
					</div>-->

				</div>


			</div>

			<div class="footer2">


				<div class="left-part">

					<div class="phones-block">
						<a href="tel:+73822576820">+7(3822) 57-68-20</a><br>
						<a href="tel:+79138276820">+7(913) 827-68-20</a><br>
					</div>

					<div class="location">

						<div class="city-name"><?=$GLOBALS['CURRENT_CITY']["NAME"]?></div>
						<a href="/shops/">Адреса розничных магазинов</a>

					</div>

				</div>


				<div class="right-part">

					<div class="copyright1">©  Интернет-магазин спортивного питания «Атлет»  <?=date('Y')?></div>
					<div class="copyright2">			

							<!-- <a target="_blank" href="https://itb-company.com/">Поддержка сайта ITB</a> -->					

					</div>
				
				</div>
			
			
			</div>
		
			
		
		</div>
		
	</footer>