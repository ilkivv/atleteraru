<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponentTemplate $this
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
    die();
if (strlen($_POST['register_submit_button'])) {
    if ($arResult['POST']['new_city']) {
        $arFields = array(
            "SORT" => 100,
            "COUNTRY_ID" => 19,
            "WITHOUT_CITY" => "N",
            'REGION_ID'=>$arResult['POST']['REGION_ORDER_PROP_2'],
        );
        $arCity = array(
            "NAME" => $arResult['POST']['new_city'],
            "SHORT_NAME" => $arResult['POST']['new_city'],
            "ru" => array(
                "LID" => "ru",
                "NAME" => $arResult['POST']['new_city'],
                "SHORT_NAME" => $arResult['POST']['new_city']
            ),
            "en" => array(
                "LID" => "en",
                "NAME" => $arResult['POST']['new_city'],
                "SHORT_NAME" => $arResult['POST']['new_city']
            )
        );

        $arFields["CITY"] = $arCity;
        $ID = CSaleLocation::Add($arFields);
        $arResult['VALUES']['PERSONAL_CITY'] = $ID;
        $_POST['REGISTER']['PERSONAL_CITY'] = $ID;
        $arResult['VALUES']['PERSONAL_CITY'] = $ID;
    }

    if (!in_array($arResult['VALUES']['PERSONAL_CITY'],array(2310,2312))) {
        if (!$_POST['UF_HOUSE']) {
            $errors['UF_HOUSE'] = 'Поле "Дом" не заполнено';
        }
        if (!$_POST['UF_FLAT']) {
            $errors['UF_FLAT'] = 'Поле "Квартира" не заполнено';
        }
        if (!$_POST['REGISTER']['PERSONAL_STREET']) {
            $errors['REGISTER[PERSONAL_STREET]'] = 'Поле "Улица" не заполнено';
        }
        if (!$_POST['REGISTER']['SECOND_NAME']) {
            $errors['REGISTER[SECOND_NAME]'] = 'Поле "Отчество" не заполнено';
        }
        if (!$_POST['REGISTER']['PERSONAL_ZIP']) {
            $errors['REGISTER[PERSONAL_ZIP]'] = 'Поле "Почтовый индекс" не заполнено';
        }
        if (!$_POST['REGISTER']['EMAIL']) {
            $errors['REGISTER[EMAIL]'] = 'Поле "Email" не заполнено';
        }
    }
    if (!$arResult['VALUES']['PERSONAL_CITY']) {
        $errors['REGISTER[PERSONAL_CITY]'] = 'Поле "Город" не заполнено';
    }

    if (count($arResult["ERRORS"]) > 0 || $errors) {
        foreach ($arResult["ERRORS"] as $key => $error) {
            if (is_int($key)) {
                $otherErrors[] = GetMessage("REGISTER_FIELD_".$key) . $error;
            } else {
                $errors['REGISTER['.$key.']'] = '<label>'.GetMessage("REGISTER_FIELD_".$key) . $error.'</label>';
            }
        }
    }
}
?>

<?if($USER->IsAuthorized()):?>

    <p><?echo GetMessage("MAIN_REGISTER_AUTH")?></p>

<?else:?>
    <?php if ($arResult['ERRORS']['SYSTEM']) {
        ?><span style="color:red;"><?=$arResult['ERRORS']['SYSTEM']?></span><?php
    }?>
    <form method="post" action="<?=POST_FORM_ACTION_URI?>" name="regform" enctype="multipart/form-data" class="" id="regUserForm">
        <?
        if($arResult["BACKURL"] <> ''):
            ?>
            <input type="hidden" name="backurl" value="/registration/success.php" />
            <?
        endif;
        ?>
        <div class="profile-form-container">
            <?if ($otherErrors) {?><div class="error" style="color:red;"><?php echo join("<br />",$otherErrors);?><br /></div><?php }?>
            <p class="required-fields"><span> — поля обязательные для заполнения</span></p>
            <table class="registration-table">
                <tr>
                    <td>
                        <div class="text-field-container <?php if ($errors['REGISTER[NAME]'] || !$_POST){?> error<?php }?>">
                            <label for="regName" class="label">Имя</label>
                            <input type="text" id="regName" name="REGISTER[NAME]" class="text-field" value="<?=$arResult['VALUES']['NAME']?>"/>
                        </div>
                    </td>
                    <td>
                        <div class="text-field-container j-other-required <?php if ($errors['REGISTER[PERSONAL_STREET]']){?> error<?php }?>">
                            <label for="regStreet" class="label">Улица</label>
                            <input style="width: 150px;" type="text" id="regStreet" name="REGISTER[PERSONAL_STREET]" class="text-field" value="<?=$arResult['VALUES']['PERSONAL_STREET']?>"/>
                        </div>
                        <div class="text-field-container j-other-required <?php if ($errors['UF_HOUSE']){?> error<?php }?>">
                            <label for="regHouse" class="label">Дом</label>
                            <input style="width: 35px;" type="text" id="regHouse" name="UF_HOUSE" class="text-field" value="<?=mysql_escape_string($_POST['UF_HOUSE'])?>"/>
                        </div>
                        <div class="text-field-container j-other-required <?php if ($errors['UF_FLAT']){?> error<?php }?>">
                            <label for="regFlat" class="label">Квартира</label>
                            <input style="width: 24px;" type="text" id="regFlat" name="UF_FLAT" class="text-field" value="<?=mysql_escape_string($_POST['UF_FLAT'])?>"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="text-field-container j-other-required <?php if ($errors['REGISTER[SECOND_NAME]']){?> error<?php }?>">
                            <label for="regSecondName" class="label">Отчество</label>
                            <input type="text" id="regSecondName" name="REGISTER[SECOND_NAME]" class="text-field" value="<?=$arResult['VALUES']['SECOND_NAME']?>"/>
                        </div>
                    </td>
                    <td>
                        <div class="text-field-container <?php if ($errors['REGISTER[PERSONAL_MOBILE]'] || !$_POST){?> error<?php }?>">
                            <label for="regPhoneNumber" class="label">Номер телефона</label>
                            <input style="width: 124px;" type="text" id="regPhoneNumber" name="REGISTER[PERSONAL_MOBILE]" class="text-field" value="<?=$arResult['VALUES']['PERSONAL_MOBILE']?>"/>
                        </div>
                        <div class="text-field-container j-other-required <?php if ($errors['REGISTER[PERSONAL_ZIP]']){?> error<?php }?>">
                            <label for="regZip" class="label">Почтовый индекс</label>
                            <input style="width: 124px;" type="text" id="regZip" name="REGISTER[PERSONAL_ZIP]" class="text-field" value="<?=$arResult['VALUES']['PERSONAL_ZIP']?>"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="text-field-container <?php if ($errors['REGISTER[LAST_NAME]'] || !$_POST){?> error<?php }?>">
                            <label for="regSurname" class="label">Фамилия</label>
                            <input type="text" id="regSurname" name="REGISTER[LAST_NAME]" class="text-field" value="<?=$arResult['VALUES']['LAST_NAME']?>"/>
                        </div>
                    </td>
                    <td>
                        <?php
                        $db_vars = CSaleLocation::GetList(
                            array(
                                "SORT" => "ASC",
                                "COUNTRY_NAME_LANG" => "ASC",
                                "CITY_NAME_LANG" => "ASC"
                            ),
                            array("LID" => LANGUAGE_ID),
                            false,
                            false,
                            array()
                        );
                        while ($vars = $db_vars->Fetch()){
                            $arResult['CITY']['VARIANTS'][] = $vars;
                        }
                        ?>

                        <div class="text-field-container <?php if ($errors['REGISTER[PERSONAL_CITY]']){?> error<?php }?>">
                            <label for="orderingCity" class="label">Ваш город</label>
                            <?php
                            $selected = false;
                            $first_city = array();
                            foreach ($arResult['CITY']['VARIANTS'] as $k => $v) {
                                if (!$first_city && ($v['CITY_NAME'] == 'Томск' || $v['CITY_NAME'] == 'Северск')) {
                                    if ($v['CITY_NAME'] == 'Томск')
                                        $first_city = $v;
                                }
                                if (($v['CITY_NAME'] == 'Томск' || $v['CITY_NAME'] == 'Северск') && ($v['ID'] == $arResult['VALUES']['PERSONAL_CITY'])) {

                                    $selected = $v;
                                    $selector = 'main';
                                }
                            }
                            if (!$selected && isset($arResult['VALUES']['PERSONAL_CITY'])) {
                                $selected = array('ID'=>"",'CITY_NAME'=>'Другой город');
                                $selector = 'other';
                            } elseif (!$selected) {
                                $selected = $first_city;
                                $selector = 'main';
                            }
                            ?>
                            <div class="choose-taste big-choose-taste j-choose-taste" style="width:184px;">
                                <div data-value="<?php echo $selected['ID'];?>" class="choose-taste-link j-choose-taste-link" id='city_selector'><span><?php echo $selected['CITY_NAME'];?></span> <em></em></div>
                                <div class="choose-taste-list-container">
                                    <div class="choose-taste-list j-choose-taste-list">
                                        <?php foreach ($arResult['CITY']['VARIANTS'] as $k => $v) {
                                            if ($v['CITY_NAME'] == 'Томск' || $v['CITY_NAME'] == 'Северск' ) {
                                                ?><div class="item" data-value="<?=$v['ID']?>" ><?=$v['CITY_NAME']?></div>
                                                <?php
                                            }
                                        }?>
                                        <div class="item" data-value="">другой город</div>
                                    </div>
                                </div>
                                <input type="hidden" class="j-choose-taste-input" id='main_city_selector' <?php if ($selector == 'main'){?>name="REGISTER[PERSONAL_CITY]" <?php }?> value="<?php echo $selected['ID'];?>"/>
                            </div>
                        </div>
                        <?php /*?>
                                   <select  <?php if ($selector == 'other'){?>name="REGISTER[PERSONAL_CITY]"  style="position:relative;z-index:5;"<?php }else {?>style="display:none;position:relative;z-index:5;"<?php }?> id='second_city_selector'>
                                        <option value="">--выберите город--</option>
                                        <?php foreach ($arResult['CITY']['VARIANTS'] as $k => $v) {
                                            if (!($v['CITY_NAME'] == 'Томск' || $v['CITY_NAME'] == 'Северск')) {
                                            ?><option value="<?=$v['CITY_NAME']?>" <?php if ($v['CITY_NAME'] == $arResult['VALUES']['PERSONAL_CITY'] ){echo "selected";}?>><?=$v['CITY_NAME']?></option><?php 
                                            } 
                                        }?>
                                    </select>
                                    */
                        ?>
                        <div id='second_city_selector' <?php if ($selector == 'other'){?> style="position:relative;z-index:5;"<?php }else {?>style="display:none;position:relative;z-index:5;"<?php }?> >
                            <div style="margin-bottom:10px;">
                                <?php
                                $GLOBALS["APPLICATION"]->IncludeComponent(
                                    "bitrix:sale.ajax.locations",
                                    ".default",
                                    array(
                                        "AJAX_CALL" => "Y",
                                        "COUNTRY_INPUT_NAME" => "COUNTRY_",
                                        "REGION_INPUT_NAME" => "REGION_",
                                        "CITY_INPUT_NAME" => "REGISTER[PERSONAL_CITY]",
                                        "CITY_OUT_LOCATION" => "Y",
                                        "LOCATION_VALUE" => $arResult['VALUES']['PERSONAL_CITY'],
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
                        <div class="text-field-container <?php if ($errors['REGISTER[LOGIN]'] || !$_POST){?> error<?php }?>">
                            <label for="regLogin" class="label">Логин</label>
                            <input type="text" id="regLogin" name="REGISTER[LOGIN]" class="text-field" value="<?=$arResult['VALUES']['LOGIN']?>"/>
                        </div>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <div class="text-field-container <?php if ($errors['REGISTER[EMAIL]'] || !$_POST){?> error<?php }?>">
                            <label for="regEmail" class="label">Email</label>
                            <input type="text" id="regEmail" name="REGISTER[EMAIL]" class="text-field" value="<?=$arResult['VALUES']['EMAIL']?>"/>
                        </div>
                    </td>
                    <td>
                        <div class="text-field-container <?php if ($errors['REGISTER[PASSWORD]'] || !$_POST){?> error<?php }?>">
                            <label for="regPass" class="label ">Пароль</label>
                            <input style="width: 124px;" type="password" id="regPass" name="REGISTER[PASSWORD]" class="text-field" />
                        </div>
                        <div class="text-field-container <?php if ($errors['REGISTER[CONFIRM_PASSWORD]'] || !$_POST){?> error<?php }?>">
                            <label for="regPassRepeat" class="label">Повторите пароль</label>
                            <input style="width: 124px;" type="password" id="regPassRepeat" name="REGISTER[CONFIRM_PASSWORD]" class="text-field" />
                        </div>
                    </td>
                </tr>
                <?
                /* CAPTCHA */
                if ($arResult["USE_CAPTCHA"] == "Y")
                {
                    ?>
                    <tr>
                        <td>
                            <div class="text-field-container">
                                <label for="captcha" class="label required"><?=GetMessage("REGISTER_CAPTCHA_PROMT")?></label>
                                <input style="width: 124px;" type="text" id="captcha" name="captcha_word" class="text-field" />
                                <input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
                                <div class="text-field-container" >
                                    <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" height="40" alt="CAPTCHA" class="captcha_pic"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?
                }
                /* !CAPTCHA */
                ?>

            </table>
        </div>

        <div class="profile-sidebar">

            <div class="bonus-card">

                <label for="cardNumber">Номер вашей<br /> накопительной карты:</label>
                <input type="text" class="text-field" id="cardNumber" name="cardNumber" value="<?php echo $_POST['cardNumber'];?>"/>
                <a class="get-card" href="/bonuce/">Как получить карту?</a>

            </div>

        </div>

        <div class="registration-button">
            <a href="#" class="big-black-button" onclick="$('#submitReg').trigger('click');">Зарегистрироваться</a>
            <input type="submit" class="hidden-input" value="Регистрация" id="submitReg" name="register_submit_button" />
        </div>
    </form>

    <script>/*
$(document).ready(function(){
	$('.j-choose-taste-input').on('change',function(){
		console.log($(this).val());
		   if ($(this).val() == 0) {
			   $(this).attr('name','');
			   $('#second_city_selector').attr('name','REGISTER[PERSONAL_CITY]').show();
		   } else {
			   $(this).attr('name','REGISTER[PERSONAL_CITY]');
			   $('#second_city_selector').attr('name','').hide();
		   }
		});
});*/
    </script>





    <script>
        function checkCityChange(obj)
        {
            var allow = true;
            if (obj.val() == '(выберите город)') {
                allow = false;
                arParams = {'COUNTRY_INPUT_NAME':'COUNTRY_','REGION_INPUT_NAME':'REGION_','CITY_INPUT_NAME':'REGISTER[PERSONAL_CITY]','CITY_OUT_LOCATION':'Y','ALLOW_EMPTY_CITY':'Y','ONCITYCHANGE':'checkCityChange($(this))'};
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

            property_id = 'ORDER_PROP_2';

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
            if ($('.j-choose-taste-input').val() == 0) {
                $('#cartRequest').hide();
            } else {
                $('#cartRequest').show();
            }
            $('.j-choose-taste-input').on('change',function(){
                $('.j-other-required').removeClass('error');
                if ($(this).val() == '') {
                    $('.j-other-required').removeClass('error').addClass('error');
                }
                if ($(this).val() == 0) {
                    $(this).attr('name','');
                    $('#second_city_selector')/*.attr('name','ORDER_PROP_2')*/.show();
                    $('#ORDER_PROP_2').attr('name','REGISTER[PERSONAL_CITY]');
                    $('#cartRequest').hide();
                } else {
                    $(this).attr('name','REGISTER[PERSONAL_CITY]');
                    $('#ORDER_PROP_2').attr('name','');
                    $('#second_city_selector')/*.attr('name','')*/.hide();
                    $('#cartRequest').show();
                }

            });
        });
    </script>
<?endif?>
