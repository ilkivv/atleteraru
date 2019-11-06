<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arUser = array();
if ($USER->IsAuthorized()) //Если пользователь авторизован
 {
  $rsUser = CUser::GetByID($USER->GetID()); //$USER->GetID() - получаем ID авторизованного пользователя  и сразу же - его поля
  $arUser = $rsUser->Fetch();
 }
?>

<div class="ordering-step" style="display:none;">Оформление заказа: шаг 1 из 3</div>

<h1>Контактные данные и адрес доставки</h1>

<div class="ordering-info">
    <span><strong>Ваш заказ:</strong> <?php echo count($arResult['BASKET_ITEMS']).' '. declension(count($arResult['BASKET_ITEMS']), array('товар',"товара","товаров"));?> на сумму: <?=round($arResult['ORDER_DISCOUNT_PRICE'],2)?></span>
    <span><strong>Доставка:</strong> не выбрано</span>
    <em><strong>Итого:</strong> <?=round($arResult['ORDER_DISCOUNT_PRICE'],2)?> р.</em>
</div>
<form action="#" method="post">
<input type="hidden" name="PROFILE_ID" value="0">
<div id="sof-prof-div">                    
    <div class="profile-form-container">
        <p class="required-fields"><span>*</span> — поля обязательные для заполнения</p>

        <table class="registration-table">
            <tr>
                <td>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['NAME']){echo "error";}?>">
                        <label for="orderingName" class="label required j-required j-other-required j-tomsk-required ">Имя</label>
                        <input type="text" id="orderingName" name="CONTACT_PERSON[NAME]" class="text-field" value="<?=($arResult['POST']['CONTACT_PERSON']['NAME'])?$arResult['POST']['CONTACT_PERSON']['NAME']:$USER->GetFirstName()?>"/>
                    </div>
                </td>
                <td>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_5']){echo "error";}?>">
                        <label for="orderingStreet" class="label j-required  j-other-required ">Улица</label>
                        <input style="width: 156px;" type="text" id="orderingStreet" name="ORDER_PROP_5" value="<?=($arResult['POST']['~ORDER_PROP_5'])?$arResult['POST']['~ORDER_PROP_5']:$arUser['PERSONAL_STREET']?>" class="text-field" />
                    </div>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_8']){echo "error";}?>">
                        <label for="orderingHouse" class="label j-required  j-other-required ">Дом</label>
                        <input style="width: 45px;" type="text" id="orderingHouse" name="ORDER_PROP_8" value="<?=($arResult['POST']['~ORDER_PROP_8'])?$arResult['POST']['~ORDER_PROP_8']:$arUser['UF_HOUSE']?>" class="text-field" />
                    </div>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_9']){echo "error";}?>">
                        <label for="orderingFlat" class="label j-required  j-other-required ">Квартира</label>
                        <input style="width: 30px;" type="text" id="orderingFlat" name="ORDER_PROP_9" value="<?=($arResult['POST']['~ORDER_PROP_9'])?$arResult['POST']['~ORDER_PROP_9']:$arUser['UF_FLAT']?>" class="text-field" />
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['LASTNAME']){echo "error";}?>">
                        <label for="orderingSurname" class="label  j-required j-other-required ">Фамилия</label>
                        <input type="text" id="orderingSurname" name="CONTACT_PERSON[LASTNAME]" class="text-field"  value="<?=($arResult['POST']['CONTACT_PERSON']['LASTNAME'])?$arResult['POST']['CONTACT_PERSON']['LASTNAME']:$USER->GetLastName()?>"/>
                    </div>
                </td>
                <td>
                    <div class="text-field-container">
                        <label for="orderingCity" class="label required j-required j-other-required j-tomsk-required  <?php if ($arResult['arERRORS']['ORDER_PROP_2']){echo "error";}?>">Ваш город</label>
                        <?php 
                            $selected = false;
                            $first_city = array();
                        foreach ($arResult["PRINT_PROPS_FORM"]['USER_PROPS_Y'][2]['VARIANTS'] as $k => $v) {
                                if (!$first_city && ($v['CITY_NAME'] == 'Томск' || $v['CITY_NAME'] == 'Северск')) {
                                    if ($v['CITY_NAME'] == 'Томск')
                                    $first_city = $v;
                                }
                                if (
                                        ($v['CITY_NAME'] == 'Томск' || $v['CITY_NAME'] == 'Северск') 
                                        && (
                                                $v['ID'] == (($arResult['POST']['~ORDER_PROP_2'])?$arResult['POST']['~ORDER_PROP_2']:$arUser['PERSONAL_CITY']) 
                                            ) 
                                        && (($arResult['POST']['~ORDER_PROP_2'])?$arResult['POST']['~ORDER_PROP_2']:$arUser['PERSONAL_CITY']) >0) {
                                    $selected = $v;
                                    $selector = 'main';
                                }
                        }
                        
                        if (!$selected && (isset($arResult['POST']['~ORDER_PROP_2']) || $arUser['PERSONAL_CITY'])) {
                            $selected = array('ID'=>0,'CITY_NAME'=>'Другой город');
                            $selector = 'other';
                        } elseif (!$selected) {
                            $selected = $first_city;
                            $selector = 'main';
                        }
                        ?>
                            <div class="choose-taste big-choose-taste j-choose-taste j-city-selector" style="width:184px;">
                                <div data-value="<?php echo $selected['ID'];?>" class="choose-taste-link j-choose-taste-link" id='city_selector'><span><?php echo $selected['CITY_NAME'];?></span> <em></em></div>
                                <div class="choose-taste-list-container">
                                    <div class="choose-taste-list j-choose-taste-list">
                                    <?php foreach ($arResult["PRINT_PROPS_FORM"]['USER_PROPS_Y'][2]['VARIANTS'] as $k => $v) {
                                        if ($v['CITY_NAME'] == 'Томск' || $v['CITY_NAME'] == 'Северск' ) {
                                        ?><div class="item" data-value="<?=$v['ID']?>" ><?=$v['CITY_NAME']?></div>
                                        <?php 
                                        }
                                    }?>
                                    <div class="item" data-value="0">другой город</div>
                                    </div>
                                </div>
                                <input type="hidden" class="j-choose-taste-input" id='main_city_selector' <?php if ($selector == 'main'){?>name="ORDER_PROP_2" <?php }?> value="<?php echo $selected['ID'];?>"/>
                            </div>
                    </div>
                     <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_15']){echo "error";}?>" style="margin-left:5px;">
                        <label for="orderingHouse" class="label">Подъезд</label>
                        <input style="width: 24px;" type="text" id="orderingHouse" name="ORDER_PROP_15" value="<?=($arResult['POST']['~ORDER_PROP_15'])?$arResult['POST']['~ORDER_PROP_15']:$arUser['UF_PORCH']?>" class="text-field" />
                    </div>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_16']){echo "error";}?>">
                        <label for="orderingFlat" class="label">Этаж</label>
                        <input style="width: 24px;" type="text" id="orderingFlat" name="ORDER_PROP_16" value="<?=($arResult['POST']['~ORDER_PROP_16'])?$arResult['POST']['~ORDER_PROP_16']:$arUser['UF_FLOOR']?>" class="text-field" />
                    </div>
                        <?php /*?><select  <?php if ($selector == 'other'){?>name="ORDER_PROP_2"  style="position:relative;z-index:5;"<?php }else {?>style="display:none;position:relative;z-index:5;"<?php }?> id='second_city_selector'>
                        <option value="0">--выберите город--</option>
                        <?php foreach ($arResult["PRINT_PROPS_FORM"]['USER_PROPS_Y'][2]['VARIANTS'] as $k => $v) {
                            if (!($v['CITY_NAME'] == 'Томск' || $v['CITY_NAME'] == 'Северск')) {
                            ?><option value="<?=$v['ID']?>" <?php if ($v['ID'] == $arResult['POST']['~ORDER_PROP_2'] || strtoupper(trim($v['CITY_NAME'])) == strtoupper(trim($arUser['PERSONAL_CITY']))){echo "selected";}?>><?=$v['CITY_NAME']?></option><?php 
                            } 
                        }?>
                        </select>
                        */?>
                        <div  id='second_city_selector' <?php if ($selector == 'other'){?> style="position:relative;z-index:5;"<?php }else {?>style="display:none;position:relative;z-index:5;"<?php }?> >
                        <div style="margin-bottom:10px;">
                        <?php $GLOBALS["APPLICATION"]->IncludeComponent(
									"bitrix:sale.ajax.locations",
									".default",
									array(
										"AJAX_CALL" => "Y",
										"COUNTRY_INPUT_NAME" => "COUNTRY_",
										"REGION_INPUT_NAME" => "REGION_",
										"CITY_INPUT_NAME" => "ORDER_PROP_2",
										"CITY_OUT_LOCATION" => "Y",
										"LOCATION_VALUE" => ($arResult['POST']['~ORDER_PROP_2'])?$arResult['POST']['~ORDER_PROP_2']:$arUser['PERSONAL_CITY'],
										"ORDER_PROPS_ID" => 2,
										"ONCITYCHANGE" => 'checkCityChange($(this))',
									),
									null,
									array('HIDE_ICONS' => 'Y')
							);?></div>
							<div id='LOCATION_ORDER_PROP_2'></div>
                        </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['SECONDNAME']){echo "error";}?>">
                        <label for="orderingSecondName" class="label j-required j-other-required">Отчество</label>
                        <input type="text" id="orderingSecondName" name="CONTACT_PERSON[SECONDNAME]" value="<?=($arResult["POST"]['CONTACT_PERSON']['SECONDNAME'])?$arResult["POST"]['CONTACT_PERSON']['SECONDNAME']:$arUser['SECOND_NAME']?>"  class="text-field" />
                    </div>
                </td>
                <td>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_10']){echo "error";}?>">
                        <label for="orderingPhoneNumber" class="label required j-required j-other-required j-tomsk-required">Номер телефона</label>
                        <input style="width: 124px;" type="text" id="orderingPhoneNumber" name="ORDER_PROP_10" value="<?=($arResult['POST']['~ORDER_PROP_10'])?$arResult['POST']['~ORDER_PROP_10']:$arUser['PERSONAL_MOBILE']?>" class="text-field" />
                    </div>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_4']){echo "error";}?>">
                        <label for="orderingZip" class="label j-required j-other-required">Почтовый индекс</label>
                        <input style="width: 124px;" type="text" id="orderingZip" name="ORDER_PROP_4" value="<?=($arResult['POST']['~ORDER_PROP_4'] !='')?$arResult['POST']['~ORDER_PROP_4']:$arUser['PERSONAL_ZIP']?>" class="text-field" />
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_6']){echo "error";}?>">
                        <label for="orderingEmail" class="label j-required j-other-required">Email</label>
                        <input type="text" id="orderingEmail" name="ORDER_PROP_6" class="text-field"   value="<?=($arResult['POST']['~ORDER_PROP_6'])?$arResult['POST']['~ORDER_PROP_6']:((false === strpos($USER->GetEmail(),'@server.loc'))?$USER->GetEmail():'')?>"/>
                    </div>
                </td>
                <td>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_12']){echo "error";}?>">
                        <label for="orderingDay" class="label">День доставки</label>
                        <input style="width: 124px;" type="text" id="orderingDay" name="ORDER_PROP_12" value="<?=$arResult['POST']['~ORDER_PROP_12']?>"  class="text-field" />
                    </div>
                    <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_11']){echo "error";}?>">
                        <label for="orderingTime" class="label">Время доставки</label>
                        <input style="width: 124px;" type="text" id="orderingTime" name="ORDER_PROP_11" value="<?=$arResult['POST']['~ORDER_PROP_11']?>" class="text-field" />
                    </div>
                </td>
            </tr>
            <tr>
                <!-- td>
                    <div class="text-field-container">
                        <label for="orderingLogin" class="label required">Логин</label>
                        <input type="text" id="orderingLogin" name="login" class="text-field" />
                    </div>
                </td-->
                <td></td>
            </tr>
        </table>
    </div>

    <div class="ordering-sidebar">

        <div class="text-field-container <?php if ($arResult['arERRORS']['ORDER_PROP_13']){echo "error";}?>">
            <label for="orderingComment" class="label">Комментарий к заказу</label>
            <textarea id="orderingComment" name="ORDER_PROP_13" class="textarea"><?=$arResult['POST']['~ORDER_PROP_13']?></textarea>
            
        </div>
        <?php 
        //echo strpos($arUser['LOGIN'],'buyer');
        if (!$arUser['UF_COUPON'] && $arResult['POST']['~IS_NEW'] && (false == strpos($arUser['LOGIN'],'buyer'))) {?>
        <div class="text-field-container" id="cartRequest"><br />
        <label for="ORDER_PROP_14" class="label"><input type="checkbox" id='ORDER_PROP_14' name='ORDER_PROP_14' value="Y" <? if($arResult['POST']['~ORDER_PROP_14']){echo "checked";}?>> <strong style="color:#000;font-size:20px;">заказать<br /> дисконтную карту</strong></label>
        </div><?php }?>
    </div>
</div>
    <div class="ordering-button">
        <a href="#" class="screw-button" onclick="$('#ordForm').submit();"><span>Далее</span></a>
        <!-- input type="submit" class="hidden-input" value="Выбрать способ доставки" id="submitReg" name="contButton" /-->
    </div>

</form>
<script>
function checkCityChange(obj)
{
	var allow = true;
	if (obj.val() == '(выберите город)') {
		allow = false;
		arParams = {'COUNTRY_INPUT_NAME':'COUNTRY_','REGION_INPUT_NAME':'REGION_','CITY_INPUT_NAME':'ORDER_PROP_2','CITY_OUT_LOCATION':'Y','ALLOW_EMPTY_CITY':'Y','ONCITYCHANGE':'checkCityChange($(this))'};
		arParams.COUNTRY = parseInt(19);
		arParams.REGION = parseInt(0);
		arParams.SITE_ID = 's1';
		var url = '/bitrix/templates/.default/components/bitrix/sale.ajax.locations/.default/ajax.php';
		BX.ajax.post(url, arParams, function(res){$('#second_city_selector').html('<div style="margin-bottom:10px;">'+res+'</div><div id="LOCATION_ORDER_PROP_2"></div>');})
	}
    if (obj.val() == 0) {
        $('#newCity').parent().remove();
    	obj.parent().append(
    	    	'<div style="margin-top:10px;">'
                +'<label for="newCity" class="label j-required j-other-required">Ваш город</label>'
                +'<input style="width: 124px;" type="text" id="newCity" name="new_city" value="" class="text-field" />'
                +'</div>'
        );
    	allow = false;
    } else {
    	allow = true;
    	$('#newCity').parent().remove();
    }

    if (allow) {
        $('input[name="ORDER_PROP_2"]').val(obj.val);
    }
        
}
function getLocation(country_id, region_id, city_id, arParams, site_id)
{
	BX.showWait();
	
	property_id = arParams.CITY_INPUT_NAME;
	
	function getLocationResult(res)
	{
		BX.closeWait();
		
		/*var obContainer = document.getElementById('LOCATION_' + property_id);
		if (obContainer)
		{
			obContainer.innerHTML = res;
		}*/
		$('#LOCATION_' + property_id).html(res);
	}

	arParams.COUNTRY = parseInt(country_id);
	arParams.REGION = parseInt(region_id);
	arParams.SITE_ID = site_id;

	var url = '/bitrix/templates/.default/components/bitrix/sale.ajax.locations/.default/ajax.php';
	BX.ajax.post(url, arParams, getLocationResult)
}
$(document).ready(function(){
	/*if ($('.j-choose-taste-input').val() == 0) {
		 $('#cartRequest').hide();
	} else {
		$('#cartRequest').show();
		}*/
	$('.j-choose-taste-input').on('change',function(){
		   if ($(this).val() == 0) {
			   $(this).attr('name','');
			   $('#second_city_selector')/*.attr('name','ORDER_PROP_2')*/.show();
			   $('#ORDER_PROP_2').attr('name','ORDER_PROP_2');
			   $('#cartRequest').hide();
		   } else {
			   $(this).attr('name','ORDER_PROP_2');
			   $('#ORDER_PROP_2').attr('name','');
			   $('#second_city_selector')/*.attr('name','')*/.hide();
			   $('#cartRequest').show();
		   }
		});
});
</script>