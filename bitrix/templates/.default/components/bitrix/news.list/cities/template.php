<div class="change-location j-change-location">
	<?/*<span class="change-location__label">Ваш город: </span>*/?>
	<div class="change-location__select">
		<span class="change-location__city j-change-location-city">
			<?php foreach ($arResult['ITEMS'] as $arItem):?>
			<?php if ($arParams['CURRENT'] == $arItem['ID']){echo $arItem['NAME'];}?>
			<?php endforeach;?>
		</span>
		<div class="change-location__dropdown j-change-location-dropdown">
			<?php foreach ($arResult['ITEMS'] as $arItem):?>
			<a href="http://<?=$arItem['PROPERTIES']['domain']['VALUE']?>" class="change-location__item"><?=$arItem['NAME']?></a>
			<?php endforeach;?>
		</div>
	</div>
</div>
