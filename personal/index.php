<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Персональный раздел");
?>
<div class="j-tabs">
				<div class="profile-panel j-tabs-links">
                    <ul>
                        <li rel="#profile">
                            <a class="active" rel="profile-tab" href="#"><span><?php global $USER; echo $USER->GetFullName();?></span></a>
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
                            ?>
                            <em>— ваша скидка <?=round($arDiscount['VALUE'],0)?>%</em>
                            <?php 
                            }
                             ?>
                        </li>
                        <li rel="#basket"><a rel="basket-tab" href="#"><span>Корзина</span></a></li>
                        <li rel="#orders"><a rel="orders-tab" href="#"><span>Мои заказы</span></a></li>
                    </ul>
                    <?php if ($USER->GetID() >0) {?><a class="logout" href="/?logout=YES">Выйти</a><?php }?>
                </div>
                <div class="j-flash-message-container"></div>
                <div id="profile-tab" class="j-tabs-body basket-tabs-body" style="display: block;">
					<?$APPLICATION->IncludeComponent(
						"bitrix:main.profile",
						"",
						Array(
						)
					);?>
                </div>

                <div id="basket-tab" class="j-tabs-body basket-tabs-body">
					<?$APPLICATION->IncludeComponent("bitrix:sale.basket.basket", ".default", Array(
						"COLUMNS_LIST"	=>	array(
							0	=>	"NAME",
							1	=>	"PRICE",
							2	=>	"QUANTITY",
							3	=>	"DELETE",
							4	=>	"DISCOUNT",
						),
						"PATH_TO_ORDER"	=>	"/personal/order/make/",
						"HIDE_COUPON"	=>	"N",
						"SET_TITLE"	=>	"Y"
						)
					);?>
                   
                </div>

                <div id="orders-tab" class="j-tabs-body basket-tabs-body">
					<?$APPLICATION->IncludeComponent("bitrix:sale.personal.order.list", ".default", Array(
						"SEF_MODE"	=>	"N",
						"HISTORIC_STATUSES" => "F",
				        'STATUS_COLOR_PSEUDO_CANCELLED' => 'black',
						"CACHE_TYPE" => "N",
						"ORDERS_PER_PAGE"	=>	"200",
						"PATH_TO_PAYMENT"	=>	"/personal/order/payment/",
						"PATH_TO_BASKET"	=>	"/personal/cart/",
						"SET_TITLE"	=>	"Y"
						)
					);?>
                </div>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>