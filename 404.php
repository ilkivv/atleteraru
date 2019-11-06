<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Страница не найдена - Ошибка 404 - Интернет-магазин Атлет");
$APPLICATION->SetPageProperty("description", "Страница не найдена - Ошибка 404 - Интернет-магазин Атлет");
$APPLICATION->SetTitle("404 Not Found");
//include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

?>

<div class="wrap">
		<img src="/images/fit404.png" alt="">
		<div class="textwrap">
			<p class="text404">К сожалению такой страницы не существует.</p>
			<p class="text404">Вероятно она была удалена с сервера, либо ее здесь никогда не было.</p>
			<p class="text404">Можете попробовать перейти на страницу:</p>
			<a href="/" class="link-wrap"><div class="button404-wrap"><span class="button404">Главная страница</span></div></a>
		</div>
	</div>


<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>