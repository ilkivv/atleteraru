<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Контакты");
?><h1>Контакты</h1>
<div class="contacts-block clearfix">
	<div class="contacts-address-block clearfix">
		<div class="contacts-shop-title">
			Интернет-магазин спортивного питания «Атлет»<br /><br />
			<a href="/shops/">Посмотреть адреса и телефоны <br />розничных магазинов</a>
		</div>

		<div class="contacts-shop-address">
			 +7 (391) 251-89-98 <br /> +7-913-534-89-98
		</div>
	</div>
	
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/contacts/form.php");?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>