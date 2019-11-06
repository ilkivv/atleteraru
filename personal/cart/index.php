<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
?><div class="profile-panel j-tabs-links">
	<ul>
		 <?php if ($USER->GetID()) {?>
		<li rel="#profile"> <a class="" rel="profile-tab" href="/personal/#profile-tab"><?php global $USER; echo $USER->GetFullName();?></a>
		<?php 
          $arUser=CUser::GetByID($USER->GetID())->GetNext();
         if ($arUser['UF_COUPON']) {
             CModule::IncludeModule ("catalog");
            $arFilter = array (
                    'COUPON' => $arUser['UF_COUPON']
            );
            $dbCoupon = CCatalogDiscountCoupon::GetList (array (), $arFilter);
            $arCoupon = $dbCoupon->Fetch ();
           }
           if ($arCoupon) {
            $arDiscount = CCatalogDiscount::GetByID($arCoupon['DISCOUNT_ID']);
            ?> <em>— ваша скидка <?=round($arDiscount['VALUE'],0)?>%</em>
		<?php 
            }
             ?> </li>
		 <?php }?> 
		<li rel="#basket"><a class="active" rel="basket-tab" href="#"><span>Корзина</span></a></li>
		 <?php if ($USER->GetID()) {?>
		<li rel="#orders"><a rel="orders-tab" href="/personal/#orders-tab"><span>Мои заказы</span></a></li>
		 <?php }?>
	</ul>
	 <?php if (!$USER->GetID()) {?>
	<p class="j-auth-container">
 <a href="/registrtion/">Зарегистрируйтесь</a> или <a href="#" class="j-auth">войдите</a>, чтобы вести свою историю<br>
		 заказов и использовать скидку по карте.
	</p>
	 <?php }?>
</div>
<div id="basket-tab" class="j-tabs-body basket-tabs-body" style="display: block;">
	 <?$APPLICATION->IncludeComponent(
	"bitrix:sale.basket.basket",
	"",
	Array(
		"OFFERS_PROPS" => array(),
		"COLUMNS_LIST" => array("NAME","DISCOUNT","WEIGHT","DELETE","PRICE","QUANTITY","PROPERTY_CML2_TASTE"),
		"PATH_TO_ORDER" => "/personal/order/make/",
		"HIDE_COUPON" => "Y",
		"SET_TITLE" => "Y",
		"PRICE_VAT_SHOW_VALUE" => "N",
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
		"USE_PREPAYMENT" => "N",
		"QUANTITY_FLOAT" => "N",
		"ACTION_VARIABLE" => "action"
	)
);?>
</div>
<br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>