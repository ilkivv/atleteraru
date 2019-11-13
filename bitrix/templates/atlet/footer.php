<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
</section>

        </section>

        <div class="clearfooter"></div>

    </div>

	 <?include_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/.default/include/footer.php')?>

	
<?//echo "<pre>"; var_dump($_SESSION); echo "</pre>";?>
	
	
<?/*	
    <div id="footer">
        <nav class="footer-menu">
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
        <div class="footer">

            <div class="copyrights">
                <p>Интернет-магазин спортивного питания «Атлет» © <?php echo date("Y");?></p>
                <p>Все права защищены.</p>
<!-- <div><span>Продвижение сайта</span> 
 <?php
   $home_or_not = ($_SERVER['REQUEST_URI'] == '/') ? TRUE : FALSE;
   ?>
   <a href="https://itb-company.com/" target="_blank" <?php if (!$home_or_not) {echo 'rel="nofollow"' ;} ?>>ITB-company.com</a></div> -->
            </div>

            <a rel="nofollow" class="vk" href="http://vk.com/club59890209" target="new">
                <img src="/bitrix/templates/atlet/img/vk.png" alt="" />
                <span>Мы Вконтакте</span>
            </a>

            <div class="feedback">
                <p><a href="/contacts/">Обратная связь</a></p>
            </div>

            <div class="phones">
                <p>+7 (3822) 57-68-20</p>
                <p>+7-913-827-6820</p>
            </div>

        </div>
    </div>
*/?>	
	
	
	
<script>
var CURRENT_CITY_URL = 'https://<?=$GLOBALS['CURRENT_CITY']['PROPERTIES']['domain']['VALUE']?>';
</script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/jquery.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/jquery.form.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/enquire.min.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/scrollpane.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/carousel.js"></script>

	<script type="text/javascript" src="/bitrix/templates/atlet/js/functions.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/js/up-button.js"></script>
	<script type="text/javascript" src="/bitrix/templates/atlet/libs/magnific-popup/jquery.magnific-popup.min.js"></script>
	<script type="text/javascript" src="/bitrix/templates/.default/libs/stacktable/stacktable.js"></script>
	<script type="text/javascript" src="/bitrix/templates/.default/libs/slick/slick.min.js"></script>
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
					webvisor:true,
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
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-112307419-25"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-112307419-25');
</script>
<!-- BEGIN JIVOSITE CODE {literal} -->
<script type='text/javascript'>
(function(){ var widget_id = 'I85W5RH2iV';var d=document;var w=window;function l(){
  var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true;
  s.src = '//code.jivosite.com/script/widget/'+widget_id
    ; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);}
  if(d.readyState=='complete'){l();}else{if(w.attachEvent){w.attachEvent('onload',l);}
  else{w.addEventListener('load',l,false);}}})();
</script>
<!-- {/literal} END JIVOSITE CODE -->



</body>
</html>