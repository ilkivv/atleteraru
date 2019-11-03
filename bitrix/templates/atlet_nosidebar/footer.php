<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
</section>

        </section>

        <div class="clearfooter"></div>
    </div>
	
	<?include_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/include/footer.php')?>
	
	<?/*
    <footer class="main-footer">
	
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
				
				<div class="right-part">
				
					<a rel="nofollow" class="vk" href="http://vk.com/club59890209" target="new">
						<span class="vk-icon social-icon">
						</span>
					</a>
						<a rel="nofollow" class="instagram" href="https://www.instagram.com/atletera/" target="new">
						<span class="instagram-icon social-icon">
						</span>
					</a>
					<!--
					<div class="callback-btn">
						<a class="ajax-popup-form" href="/ajax/callback.php">Заказать звонок</a>
					</div>
					-->
					<div class="callback-btn j-callback-btn">
						<a class="ajax-popup-form" href="/ajax/callback.php">Заказать звонок</a>
					</div>
				
				</div>
			
			
			</div>
			
			<div class="footer2">
			
			
				<div class="left-part">
				
					<div class="phones-block">
						<a href="tel:">+7(3822) 57-68-20</a><br>
						<a href="tel:">+7(913) 827-68-20</a><br>
					</div>
					
					<div class="location">
					
						<div class="city-name"><?=$GLOBALS['CURRENT_CITY']["NAME"]?></div>
						<a href="/shops/">Адреса розничных магазинов</a>
					
					</div>
				
				</div>
				
				
				<div class="right-part">
				
					<div class="copyright1">©  Интернет-магазин спортивного питания «Атлет»  <?=date('Y')?></div>
					<div class="copyright2">			
					
							<a target="_blank" href="https://itb-company.com/">Поддержка сайта ITB</a>					
					
					</div>
				
				</div>
			
			
			</div>
		
			
		
		</div>
		
	</footer>
	
	*/?>
	
<script>
var CURRENT_CITY_URL = 'https://<?=$_GLOBALS['CURRENT_CITY']['PROPERTIES']['domain']['VALUE']?>';
</script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/jquery.form.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/enquire.min.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/scrollpane.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/carousel.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/functions.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/up-button.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/libs/magnific-popup/jquery.magnific-popup.min.js"></script>
	<script type="text/javascript" src="/bitrix/templates/.default/js/custom.js?v=6"></script>
	<!-- Yandex.Metrika counter -->
<script type="text/javascript">
(function (d, w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter28148988 = new Ya.Metrika({id:28148988,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
					webvisor: true,
					triggerEvent: true,
					ecommerce: "dataLayer"
										});
        } catch(e) { }
    });

    var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
    s.type = "text/javascript";
    s.async = true;
    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

    if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f, false);
    } else { f(); }
})(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="//mc.yandex.ru/watch/28148988" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
</body>
</html>