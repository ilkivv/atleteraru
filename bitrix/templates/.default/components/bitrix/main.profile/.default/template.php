<?
/**
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

if ($_POST['new_city']) {
    $arFields = array(
        "SORT" => 100,
        "COUNTRY_ID" => 19,
        "WITHOUT_CITY" => "N",
        'REGION_ID'=>$_POST['REGION_PERSONAL_CITY'],
    );
    $arCity = array(
        "NAME" => $_POST['new_city'],
        "SHORT_NAME" => $_POST['new_city'],
        "ru" => array(
            "LID" => "ru",
            "NAME" => $_POST['new_city'],
            "SHORT_NAME" => $_POST['new_city']
        ),
        "en" => array(
            "LID" => "en",
            "NAME" => $_POST['new_city'],
            "SHORT_NAME" => $_POST['new_city']
        )
    );

    $arFields["CITY"] = $arCity;
    $ID = CSaleLocation::Add($arFields);
    $arResult['arUser']['PERSONAL_CITY'] = $ID;
    $_POST['PERSONAL_CITY'] = $ID;
    $user = new CUser;
    $user->Update($USER->GetID(),array('PERSONAL_CITY'=>$_POST['PERSONAL_CITY']));
    CUser::SetParam('UF_COUPON',$_POST['PERSONAL_CITY']);
}

if (strlen($_POST['coupon']) && $_POST['coupon'] !='') {
    $APPLICATION->RestartBuffer();
    if (!defined('PUBLIC_AJAX_MODE')) {
        define('PUBLIC_AJAX_MODE', true);
    }
    header('Content-type: application/json');
    if (strpos($_POST['coupon'],'i') !== false || strlen($_POST['coupon']) != 5) {
        $error = 'Неверный номер карты';
    } else {
        $arUser=CUser::GetByID($USER->GetID())->GetNext();
        if ($arUser['UF_COUPON'] != '' && strpos($arUser['UF_COUPON'],'i')=== false ) {
            $error = 'Вы не можете заменить дисконтную карту.';
        } elseif (CCatalogDiscountCoupon::IsExistCoupon($_POST['coupon'])) {
            $arFilter = array (
                'COUPON' => $_POST['coupon']
            );
            $dbCoupon = CCatalogDiscountCoupon::GetList (array (), $arFilter);
            $arCoupon = $dbCoupon->Fetch ();
            $card_fio = explode(' ',$arCoupon['DESCRIPTION']);
            if (mb_strtoupper(trim($card_fio[0]) . ' ' . trim($card_fio[1])) != mb_strtoupper(trim($arResult["arUser"]["LAST_NAME"]) . ' ' . trim($arResult["arUser"]["NAME"]))) {
                $error = 'Вы не может привязать чужую карту. Проверьте ФИО.';
            } else {
                $rsUsers = CUser::GetList (($by = "id"), ($order = "desc"), Array (
                    "UF_COUPON" => $_POST[ 'coupon' ]
                ), array ());
                if ($rsUsers->GetNext ()) {
                    $error = 'Пользователь с такой дисконтной картой уже существует ';
                } else {
                    $user = new CUser;
                    $user->Update($USER->GetID(),array('UF_COUPON'=>$_POST['coupon']));
                    CUser::SetParam('UF_COUPON',$_POST['coupon']);
                }
            }
        } else {
            $error = 'Неверный номер дисконтной карты';
        }
    }
    if ($error) {
        echo json_encode(array(
            'type' => 'error',
            'message' => $error,
        ));
    } else {
        echo json_encode(array('submitOn' => true,'message'=>'Дисконтная карта успешно привязана'));
    }
    require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
    die();
}

?>
<?ShowError($arResult["strProfileError"]);
if ($arResult['DATA_SAVED'] == 'Y')
    ShowNote(GetMessage('PROFILE_DATA_SAVED'));
?>
<script type="text/javascript">
    function activateCoupon()
    {
        $.post('/personal/',{'coupon':$('#cardNumber').val()},function(resp){
            if (resp.submitOn) {
                $('#cardNumber').hide();
                $('#cardNum').html($('#cardNumber').val());
                $('.j-bonus-activating').hide();
                FlashMessage.Show('Бонусная карта успешно привязана');
            } else {
                FlashMessage.Show(resp.message);
            }
        });
    }

    <!--
    var opened_sections = [<?
        $arResult["opened"] = $_COOKIE[$arResult["COOKIE_PREFIX"]."_user_profile_open"];
        $arResult["opened"] = preg_replace("/[^a-z0-9_,]/i", "", $arResult["opened"]);
        if (strlen($arResult["opened"]) > 0)
        {
            echo "'".implode("', '", explode(",", $arResult["opened"]))."'";
        }
        else
        {
            $arResult["opened"] = "reg";
            echo "'reg'";
        }
        ?>];
    //-->

    var cookie_prefix = '<?=$arResult["COOKIE_PREFIX"]?>';
</script>
<form method="post" name="form1" action="<?=$arResult["FORM_TARGET"]?>" enctype="multipart/form-data" id="profileForm">
    <?=$arResult["BX_SESSION_CHECK"]?>
    <input type="hidden" name="lang" value="<?=LANG?>" />
    <input type="hidden" name="ID" value=<?=$arResult["ID"]?> />

    <div class="profile-form-container">
        <p class="required-fields"><span>*</span> — поля обязательные для заполнения</p>

        <table class="registration-table">
            <tr>
                <td>
                    <div class="text-field-container">
                        <label for="regName" class="label required">Имя</label>
                        <input type="text" id="regName" name="NAME" maxlength="50" value="<?=$arResult["arUser"]["NAME"]?>" class="text-field j-profile-field" />
                    </div>
                </td>
                <td>
                    <div class="text-field-container">
                        <label for="regStreet" class="label required">Улица</label>
                        <input style="width: 150px;" type="text" id="regStreet"  name="PERSONAL_STREET" maxlength="255" class="text-field j-profile-field" value="<?=$arResult["arUser"]["PERSONAL_STREET"]?>"/>
                    </div>
                    <div class="text-field-container">
                        <label for="regHouse" class="label required">Дом</label>
                        <input style="width: 45px;" type="text" id="regHouse" name="UF_HOUSE" class="text-field j-profile-field" value="<?=$arResult["arUser"]["UF_HOUSE"]?>" />
                    </div>
                    <div class="text-field-container">
                        <label for="regFlat" class="label required">Квартира</label>
                        <input style="width: 30px;" type="text" id="regFlat"  name="UF_FLAT" class="text-field j-profile-field" value="<?=$arResult["arUser"]["UF_FLAT"]?>" value="16" />
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="text-field-container">
                        <label for="regSurname" class="label required">Фамилия</label>
                        <input type="text" id="regSurname"  name="LAST_NAME" maxlength="50" value="<?=$arResult["arUser"]["LAST_NAME"]?>" class="text-field j-profile-field" />
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
                            if (($v['CITY_NAME'] == 'Томск' || $v['CITY_NAME'] == 'Северск') && ($v['ID'] == $arResult['arUser']['PERSONAL_CITY'])) {

                                $selected = $v;
                                $selector = 'main';
                            }
                        }
                        if (!$selected && isset($arResult['arUser']['PERSONAL_CITY'])) {
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
                            <input type="hidden" class="j-choose-taste-input" id='main_city_selector' <?php if ($selector == 'main'){?>name="PERSONAL_CITY" <?php }?> value="<?php echo $selected['ID'];?>"/>
                        </div>
                    </div>
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
                                    "CITY_INPUT_NAME" => "PERSONAL_CITY",
                                    "CITY_OUT_LOCATION" => "Y",
                                    "LOCATION_VALUE" => $arResult['arUser']['PERSONAL_CITY'],
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
                    <div class="text-field-container">
                        <label for="regSecondName" class="label required">Отчество</label>
                        <input type="text" id="regSecondName"  name="SECOND_NAME" maxlength="50" value="<?=$arResult["arUser"]["SECOND_NAME"]?>"  class="text-field j-profile-field" />
                    </div>
                </td>
                <td>
                    <div class="text-field-container">
                        <label for="regPhoneNumber" class="label">Номер телефона</label>
                        <input style="width: 124px;" type="text" id="regPhoneNumber"  name="PERSONAL_MOBILE" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_MOBILE"]?>"  class="text-field j-profile-field" />
                    </div>
                    <div class="text-field-container">
                        <label for="regZip" class="label">Почтовый индекс</label>
                        <input style="width: 124px;" type="text" id="regZip"  name="PERSONAL_ZIP" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_ZIP"]?>" class="text-field j-profile-field" />
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="text-field-container">
                        <label for="regLogin" class="label required">Логин</label>
                        <input type="text" id="regLogin"  name="LOGIN" maxlength="50" value="<? echo $arResult["arUser"]["LOGIN"]?>" class="text-field j-profile-field" />
                    </div>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="text-field-container">
                        <label for="regEmail" class="label required">Email</label>
                        <input type="text" id="regEmail"  name="EMAIL" maxlength="50" value="<? echo $arResult["arUser"]["EMAIL"]?>" class="text-field j-profile-field" />
                    </div>
                </td>
                <td>
                    <div class="change-password-container">
                        <a class="j-change-pass" href="#">Сменить пароль</a>
                        <div class="change-password-popup j-change-password-popup">
                            <div class="text-field-container">
                                <label for="regOldPass" class="label">Новый пароль</label>
                                <input type="password" id="regOldPass" name="NEW_PASSWORD"  class="text-field" />
                            </div>
                            <div class="text-field-container">
                                <label for="regNewPass" class="label">Подтвердите пароль</label>
                                <input type="password" id="regNewPass" name="NEW_PASSWORD_CONFIRM" class="text-field" />
                            </div>
                            <a class="j-save-pass black-button" href="#" onclick="$('#sbmtRegBtn').trigger('click');">Изменить</a>
                            <a class="j-cancel-pass white-button" href="#">Отмена</a>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="profile-sidebar">
        <?php if ($arResult["arUser"]["EMAIL"]) {?>
            <div class="bonus-card">

                <label for="cardNumber">Номер вашей<br /> накопительной карты:</label>
                <?php
                //$arUser=CUser::GetByID($USER->GetID())->GetNext();
                if (strpos($arResult["arUser"]['UF_COUPON'],'i') !== false || !$arResult["arUser"]['UF_COUPON']) {
                    ?>
                    <input type="text" class="text-field j-bonus-field" id="cardNumber" name="cardNumber" value="<?=$arResult["arUser"]['UF_COUPON']?>"/>
                    <div id="cardNum"></div>
                    <?php
                } else {
                    ?>
                    <div id="cardNum"><?=$arResult["arUser"]['UF_COUPON']?></div>
                    <?php
                }
                ?>
                <div class="get-button j-bonus-activating">
                    <a href="#" onclick="activateCoupon();">Активировать номер</a>
                </div>
                <a class="get-card" href="/bonuce/">Как получить карту?</a>

            </div>
        <?php }?>
    </div>
    <?php ?>
    <div class="save-profile-panel">
        <a class="black-button" href="#"  id='sbmtRegBtn' onclick="$('#submitReg').trigger('click');">Сохранить изменения</a>
        <a class="white-button j-cancel-profile" onclick="$('#profileForm')[0].reset(); $(this).removeClass('vis'); return false;" href="#">Отмена</a>
        <input type="submit" class="hidden-input" value="Зарегистрироваться" id="submitReg" name="save" />
        <?php /*?>                      <a class="delete-profile j-delete-profile" href="#">Удалить мой профиль</a>
                            <div class="delete-profile-popup j-delete-profile-popup">
                                <p>Вы действительно хотите удалить свой профиль?</p>
                                <a class="j-cancel-profile black-button" href="#">Нет</a>
                                <a class="j-del-profile white-button" href="#">Да</a>
                            </div>

                        */?>
    </div>

</form>








<script>
    function checkCityChange(obj)
    {
        var allow = true;
        if (obj.val() == '(выберите город)') {
            allow = false;
            arParams = {'COUNTRY_INPUT_NAME':'COUNTRY_','REGION_INPUT_NAME':'REGION_','CITY_INPUT_NAME':'PERSONAL_CITY','CITY_OUT_LOCATION':'Y','ALLOW_EMPTY_CITY':'Y','ONCITYCHANGE':'checkCityChange($(this))'};
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
            if ($(this).val() == 0) {
                $(this).attr('name','');
                $('#second_city_selector')/*.attr('name','ORDER_PROP_2')*/.show();
                $('#PERSONAL_CITY').attr('name','PERSONAL_CITY');
                /* $('#cartRequest').hide();*/
            } else {
                $(this).attr('name','PERSONAL_CITY');
                $('#PERSONAL_CITY').attr('name','');
                $('#second_city_selector')/*.attr('name','')*/.hide();
                $('#cartRequest').show();
            }
        });
    });
</script>













<?php return;?>


<div class="bx-auth-profile">


    <div class="profile-link profile-user-div-link"><a title="<?=GetMessage("REG_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('reg')"><?=GetMessage("REG_SHOW_HIDE")?></a></div>
    <div class="profile-block-<?=strpos($arResult["opened"], "reg") === false ? "hidden" : "shown"?>" id="user_div_reg">
        <table class="profile-table data-table">
            <thead>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            </thead>
            <tbody>
            <?
            if($arResult["ID"]>0)
            {
                ?>
                <?
                if (strlen($arResult["arUser"]["TIMESTAMP_X"])>0)
                {
                    ?>
                    <tr>
                        <td><?=GetMessage('LAST_UPDATE')?></td>
                        <td><?=$arResult["arUser"]["TIMESTAMP_X"]?></td>
                    </tr>
                    <?
                }
                ?>
                <?
                if (strlen($arResult["arUser"]["LAST_LOGIN"])>0)
                {
                    ?>
                    <tr>
                        <td><?=GetMessage('LAST_LOGIN')?></td>
                        <td><?=$arResult["arUser"]["LAST_LOGIN"]?></td>
                    </tr>
                    <?
                }
                ?>
                <?
            }
            ?>
            <tr>
                <td><?=GetMessage('NAME')?></td>
                <td><input type="text"  /></td>
            </tr>
            <tr>
                <td><?=GetMessage('LAST_NAME')?></td>
                <td><input type="text" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('SECOND_NAME')?></font></td>
                <td><input type="text"/></td>
            </tr>
            <tr>
                <td><?=GetMessage('EMAIL')?><span class="starrequired">*</span></td>
                <td><input type="text" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('LOGIN')?><span class="starrequired">*</span></td>
                <td><input type="text" /></td>
            </tr>
            <?if($arResult["arUser"]["EXTERNAL_AUTH_ID"] == ''):?>
                <tr>
                <td><?=GetMessage('NEW_PASSWORD_REQ')?></td>
                <td><input type="password" maxlength="50" value="" autocomplete="off" class="bx-auth-input" />
                <?if($arResult["SECURE_AUTH"]):?>
                    <span class="bx-auth-secure" id="bx_auth_secure" title="<?echo GetMessage("AUTH_SECURE_NOTE")?>" style="display:none">
					<div class="bx-auth-secure-icon"></div>
				</span>
                    <noscript>
				<span class="bx-auth-secure" title="<?echo GetMessage("AUTH_NONSECURE_NOTE")?>">
					<div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
				</span>
                    </noscript>
                    <script type="text/javascript">
                        document.getElementById('bx_auth_secure').style.display = 'inline-block';
                    </script>
                    </td>
                    </tr>
                <?endif?>
                <tr>
                    <td><?=GetMessage('NEW_PASSWORD_CONFIRM')?></td>
                    <td><input type="password" maxlength="50" value="" autocomplete="off" /></td>
                </tr>
            <?endif?>
            <?if($arResult["TIME_ZONE_ENABLED"] == true):?>
                <tr>
                    <td colspan="2" class="profile-header"><?echo GetMessage("main_profile_time_zones")?></td>
                </tr>
                <tr>
                    <td><?echo GetMessage("main_profile_time_zones_auto")?></td>
                    <td>
                        <select name="AUTO_TIME_ZONE" onchange="this.form.TIME_ZONE.disabled=(this.value != 'N')">
                            <option value=""><?echo GetMessage("main_profile_time_zones_auto_def")?></option>
                            <option value="Y"<?=($arResult["arUser"]["AUTO_TIME_ZONE"] == "Y"? ' SELECTED="SELECTED"' : '')?>><?echo GetMessage("main_profile_time_zones_auto_yes")?></option>
                            <option value="N"<?=($arResult["arUser"]["AUTO_TIME_ZONE"] == "N"? ' SELECTED="SELECTED"' : '')?>><?echo GetMessage("main_profile_time_zones_auto_no")?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?echo GetMessage("main_profile_time_zones_zones")?></td>
                    <td>
                        <select name="TIME_ZONE"<?if($arResult["arUser"]["AUTO_TIME_ZONE"] <> "N") echo ' disabled="disabled"'?>>
                            <?foreach($arResult["TIME_ZONE_LIST"] as $tz=>$tz_name):?>
                                <option value="<?=htmlspecialcharsbx($tz)?>"<?=($arResult["arUser"]["TIME_ZONE"] == $tz? ' SELECTED="SELECTED"' : '')?>><?=htmlspecialcharsbx($tz_name)?></option>
                            <?endforeach?>
                        </select>
                    </td>
                </tr>
            <?endif?>
            </tbody>
        </table>
    </div>
    <div class="profile-link profile-user-div-link"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('personal')"><?=GetMessage("USER_PERSONAL_INFO")?></a></div>
    <div id="user_div_personal" class="profile-block-<?=strpos($arResult["opened"], "personal") === false ? "hidden" : "shown"?>">
        <table class="data-table profile-table">
            <thead>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?=GetMessage('USER_PROFESSION')?></td>
                <td><input type="text" name="PERSONAL_PROFESSION" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_PROFESSION"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_WWW')?></td>
                <td><input type="text" name="PERSONAL_WWW" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_WWW"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_ICQ')?></td>
                <td><input type="text" name="PERSONAL_ICQ" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_ICQ"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_GENDER')?></td>
                <td><select name="PERSONAL_GENDER">
                        <option value=""><?=GetMessage("USER_DONT_KNOW")?></option>
                        <option value="M"<?=$arResult["arUser"]["PERSONAL_GENDER"] == "M" ? " SELECTED=\"SELECTED\"" : ""?>><?=GetMessage("USER_MALE")?></option>
                        <option value="F"<?=$arResult["arUser"]["PERSONAL_GENDER"] == "F" ? " SELECTED=\"SELECTED\"" : ""?>><?=GetMessage("USER_FEMALE")?></option>
                    </select></td>
            </tr>
            <tr>
                <td><?=GetMessage("USER_BIRTHDAY_DT")?> (<?=$arResult["DATE_FORMAT"]?>):</td>
                <td><?
                    $APPLICATION->IncludeComponent(
                        'bitrix:main.calendar',
                        '',
                        array(
                            'SHOW_INPUT' => 'Y',
                            'FORM_NAME' => 'form1',
                            'INPUT_NAME' => 'PERSONAL_BIRTHDAY',
                            'INPUT_VALUE' => $arResult["arUser"]["PERSONAL_BIRTHDAY"],
                            'SHOW_TIME' => 'N'
                        ),
                        null,
                        array('HIDE_ICONS' => 'Y')
                    );

                    //=CalendarDate("PERSONAL_BIRTHDAY", $arResult["arUser"]["PERSONAL_BIRTHDAY"], "form1", "15")
                    ?></td>
            </tr>
            <tr>
                <td><?=GetMessage("USER_PHOTO")?></td>
                <td>
                    <?=$arResult["arUser"]["PERSONAL_PHOTO_INPUT"]?>
                    <?
                    if (strlen($arResult["arUser"]["PERSONAL_PHOTO"])>0)
                    {
                        ?>
                        <br />
                        <?=$arResult["arUser"]["PERSONAL_PHOTO_HTML"]?>
                        <?
                    }
                    ?></td>
            <tr>
                <td colspan="2" class="profile-header"><?=GetMessage("USER_PHONES")?></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_PHONE')?></td>
                <td><input type="text" name="PERSONAL_PHONE" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_PHONE"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_FAX')?></td>
                <td><input type="text" name="PERSONAL_FAX" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_FAX"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_MOBILE')?></td>
                <td><input type="text"/></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_PAGER')?></td>
                <td><input type="text" name="PERSONAL_PAGER" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_PAGER"]?>" /></td>
            </tr>
            <tr>
                <td colspan="2" class="profile-header"><?=GetMessage("USER_POST_ADDRESS")?></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_COUNTRY')?></td>
                <td><?=$arResult["COUNTRY_SELECT"]?></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_STATE')?></td>
                <td><input type="text" name="PERSONAL_STATE" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_STATE"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_CITY')?></td>
                <td><input type="text" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_ZIP')?></td>
                <td><input type="text"/></td>
            </tr>
            <tr>
                <td><?=GetMessage("USER_STREET")?></td>
                <td><textarea cols="30" rows="5" name="PERSONAL_STREET"><?=$arResult["arUser"]["PERSONAL_STREET"]?></textarea></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_MAILBOX')?></td>
                <td><input type="text" name="PERSONAL_MAILBOX" maxlength="255" value="<?=$arResult["arUser"]["PERSONAL_MAILBOX"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage("USER_NOTES")?></td>
                <td><textarea cols="30" rows="5" name="PERSONAL_NOTES"><?=$arResult["arUser"]["PERSONAL_NOTES"]?></textarea></td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="profile-link profile-user-div-link"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('work')"><?=GetMessage("USER_WORK_INFO")?></a></div>
    <div id="user_div_work" class="profile-block-<?=strpos($arResult["opened"], "work") === false ? "hidden" : "shown"?>">
        <table class="data-table profile-table">
            <thead>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?=GetMessage('USER_COMPANY')?></td>
                <td><input type="text" name="WORK_COMPANY" maxlength="255" value="<?=$arResult["arUser"]["WORK_COMPANY"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_WWW')?></td>
                <td><input type="text" name="WORK_WWW" maxlength="255" value="<?=$arResult["arUser"]["WORK_WWW"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_DEPARTMENT')?></td>
                <td><input type="text" name="WORK_DEPARTMENT" maxlength="255" value="<?=$arResult["arUser"]["WORK_DEPARTMENT"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_POSITION')?></td>
                <td><input type="text" name="WORK_POSITION" maxlength="255" value="<?=$arResult["arUser"]["WORK_POSITION"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage("USER_WORK_PROFILE")?></td>
                <td><textarea cols="30" rows="5" name="WORK_PROFILE"><?=$arResult["arUser"]["WORK_PROFILE"]?></textarea></td>
            </tr>
            <tr>
                <td><?=GetMessage("USER_LOGO")?></td>
                <td>
                    <?=$arResult["arUser"]["WORK_LOGO_INPUT"]?>
                    <?
                    if (strlen($arResult["arUser"]["WORK_LOGO"])>0)
                    {
                        ?>
                        <br /><?=$arResult["arUser"]["WORK_LOGO_HTML"]?>
                        <?
                    }
                    ?></td>
            </tr>
            <tr>
                <td colspan="2" class="profile-header"><?=GetMessage("USER_PHONES")?></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_PHONE')?></td>
                <td><input type="text" name="WORK_PHONE" maxlength="255" value="<?=$arResult["arUser"]["WORK_PHONE"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_FAX')?></font></td>
                <td><input type="text" name="WORK_FAX" maxlength="255" value="<?=$arResult["arUser"]["WORK_FAX"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_PAGER')?></font></td>
                <td><input type="text" name="WORK_PAGER" maxlength="255" value="<?=$arResult["arUser"]["WORK_PAGER"]?>" /></td>
            </tr>
            <tr>
                <td colspan="2" class="profile-header"><?=GetMessage("USER_POST_ADDRESS")?></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_COUNTRY')?></td>
                <td><?=$arResult["COUNTRY_SELECT_WORK"]?></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_STATE')?></td>
                <td><input type="text" name="WORK_STATE" maxlength="255" value="<?=$arResult["arUser"]["WORK_STATE"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_CITY')?></td>
                <td><input type="text" name="WORK_CITY" maxlength="255" value="<?=$arResult["arUser"]["WORK_CITY"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_ZIP')?></td>
                <td><input type="text" name="WORK_ZIP" maxlength="255" value="<?=$arResult["arUser"]["WORK_ZIP"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage("USER_STREET")?></td>
                <td><textarea cols="30" rows="5" name="WORK_STREET"><?=$arResult["arUser"]["WORK_STREET"]?></textarea></td>
            </tr>
            <tr>
                <td><?=GetMessage('USER_MAILBOX')?></td>
                <td><input type="text" name="WORK_MAILBOX" maxlength="255" value="<?=$arResult["arUser"]["WORK_MAILBOX"]?>" /></td>
            </tr>
            <tr>
                <td><?=GetMessage("USER_NOTES")?></td>
                <td><textarea cols="30" rows="5" name="WORK_NOTES"><?=$arResult["arUser"]["WORK_NOTES"]?></textarea></td>
            </tr>
            </tbody>
        </table>
    </div>
    <?
    if ($arResult["INCLUDE_FORUM"] == "Y")
    {
        ?>

        <div class="profile-link profile-user-div-link"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('forum')"><?=GetMessage("forum_INFO")?></a></div>
        <div id="user_div_forum" class="profile-block-<?=strpos($arResult["opened"], "forum") === false ? "hidden" : "shown"?>">
            <table class="data-table profile-table">
                <thead>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?=GetMessage("forum_SHOW_NAME")?></td>
                    <td><input type="checkbox" name="forum_SHOW_NAME" value="Y" <?if ($arResult["arForumUser"]["SHOW_NAME"]=="Y") echo "checked=\"checked\"";?> /></td>
                </tr>
                <tr>
                    <td><?=GetMessage('forum_DESCRIPTION')?></td>
                    <td><input type="text" name="forum_DESCRIPTION" maxlength="255" value="<?=$arResult["arForumUser"]["DESCRIPTION"]?>" /></td>
                </tr>
                <tr>
                    <td><?=GetMessage('forum_INTERESTS')?></td>
                    <td><textarea cols="30" rows="5" name="forum_INTERESTS"><?=$arResult["arForumUser"]["INTERESTS"]; ?></textarea></td>
                </tr>
                <tr>
                    <td><?=GetMessage("forum_SIGNATURE")?></td>
                    <td><textarea cols="30" rows="5" name="forum_SIGNATURE"><?=$arResult["arForumUser"]["SIGNATURE"]; ?></textarea></td>
                </tr>
                <tr>
                    <td><?=GetMessage("forum_AVATAR")?></td>
                    <td><?=$arResult["arForumUser"]["AVATAR_INPUT"]?>
                        <?
                        if (strlen($arResult["arForumUser"]["AVATAR"])>0)
                        {
                            ?>
                            <br /><?=$arResult["arForumUser"]["AVATAR_HTML"]?>
                            <?
                        }
                        ?></td>
                </tr>
                </tbody>
            </table>
        </div>

        <?
    }
    ?>
    <?
    if ($arResult["INCLUDE_BLOG"] == "Y")
    {
        ?>
        <div class="profile-link profile-user-div-link"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('blog')"><?=GetMessage("blog_INFO")?></a></div>
        <div id="user_div_blog" class="profile-block-<?=strpos($arResult["opened"], "blog") === false ? "hidden" : "shown"?>">
            <table class="data-table profile-table">
                <thead>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?=GetMessage('blog_ALIAS')?></td>
                    <td><input class="typeinput" type="text" name="blog_ALIAS" maxlength="255" value="<?=$arResult["arBlogUser"]["ALIAS"]?>" /></td>
                </tr>
                <tr>
                    <td><?=GetMessage('blog_DESCRIPTION')?></td>
                    <td><input class="typeinput" type="text" name="blog_DESCRIPTION" maxlength="255" value="<?=$arResult["arBlogUser"]["DESCRIPTION"]?>" /></td>
                </tr>
                <tr>
                    <td><?=GetMessage('blog_INTERESTS')?></td>
                    <td><textarea cols="30" rows="5" class="typearea" name="blog_INTERESTS"><?echo $arResult["arBlogUser"]["INTERESTS"]; ?></textarea></td>
                </tr>
                <tr>
                    <td><?=GetMessage("blog_AVATAR")?></td>
                    <td><?=$arResult["arBlogUser"]["AVATAR_INPUT"]?>
                        <?
                        if (strlen($arResult["arBlogUser"]["AVATAR"])>0)
                        {
                            ?>
                            <br /><?=$arResult["arBlogUser"]["AVATAR_HTML"]?>
                            <?
                        }
                        ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <?
    }
    ?>
    <?if ($arResult["INCLUDE_LEARNING"] == "Y"):?>
        <div class="profile-link profile-user-div-link"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('learning')"><?=GetMessage("learning_INFO")?></a></div>
        <div id="user_div_learning" class="profile-block-<?=strpos($arResult["opened"], "learning") === false ? "hidden" : "shown"?>">
            <table class="data-table profile-table">
                <thead>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?=GetMessage("learning_PUBLIC_PROFILE");?>:</td>
                    <td><input type="checkbox" name="student_PUBLIC_PROFILE" value="Y" <?if ($arResult["arStudent"]["PUBLIC_PROFILE"]=="Y") echo "checked=\"checked\"";?> /></td>
                </tr>
                <tr>
                    <td><?=GetMessage("learning_RESUME");?>:</td>
                    <td><textarea cols="30" rows="5" name="student_RESUME"><?=$arResult["arStudent"]["RESUME"]; ?></textarea></td>
                </tr>

                <tr>
                    <td><?=GetMessage("learning_TRANSCRIPT");?>:</td>
                    <td><?=$arResult["arStudent"]["TRANSCRIPT"];?>-<?=$arResult["ID"]?></td>
                </tr>
                </tbody>
            </table>
        </div>
    <?endif;?>
    <?if($arResult["IS_ADMIN"]):?>
        <div class="profile-link profile-user-div-link"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('admin')"><?=GetMessage("USER_ADMIN_NOTES")?></a></div>
        <div id="user_div_admin" class="profile-block-<?=strpos($arResult["opened"], "admin") === false ? "hidden" : "shown"?>">
            <table class="data-table profile-table">
                <thead>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?=GetMessage("USER_ADMIN_NOTES")?>:</td>
                    <td><textarea cols="30" rows="5" name="ADMIN_NOTES"><?=$arResult["arUser"]["ADMIN_NOTES"]?></textarea></td>
                </tr>
                </tbody>
            </table>
        </div>
    <?endif;?>
    <?// ********************* User properties ***************************************************?>
    <?if($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):?>
        <div class="profile-link profile-user-div-link"><a title="<?=GetMessage("USER_SHOW_HIDE")?>" href="javascript:void(0)" onclick="SectionClick('user_properties')"><?=strlen(trim($arParams["USER_PROPERTY_NAME"])) > 0 ? $arParams["USER_PROPERTY_NAME"] : GetMessage("USER_TYPE_EDIT_TAB")?></a></div>
        <div id="user_div_user_properties" class="profile-block-<?=strpos($arResult["opened"], "user_properties") === false ? "hidden" : "shown"?>">
            <table class="data-table profile-table">
                <thead>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                </thead>
                <tbody>
                <?$first = true;?>
                <?foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):?>
                    <tr><td class="field-name">
                            <?if ($arUserField["MANDATORY"]=="Y"):?>
                                <span class="starrequired">*</span>
                            <?endif;?>
                            <?=$arUserField["EDIT_FORM_LABEL"]?>:</td><td class="field-value">
                            <?$APPLICATION->IncludeComponent(
                                "bitrix:system.field.edit",
                                $arUserField["USER_TYPE"]["USER_TYPE_ID"],
                                array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField), null, array("HIDE_ICONS"=>"Y"));?></td></tr>
                <?endforeach;?>
                </tbody>
            </table>
        </div>
    <?endif;?>
    <?// ******************** /User properties ***************************************************?>
    <p><?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></p>
    <p><input type="submit" name="save" value="<?=(($arResult["ID"]>0) ? GetMessage("MAIN_SAVE") : GetMessage("MAIN_ADD"))?>">&nbsp;&nbsp;<input type="reset" value="<?=GetMessage('MAIN_RESET');?>"></p>
    </form>
    <?
    if($arResult["SOCSERV_ENABLED"])
    {
        $APPLICATION->IncludeComponent("bitrix:socserv.auth.split", ".default", array(
            "SHOW_PROFILES" => "Y",
            "ALLOW_DELETE" => "Y"
        ),
            false
        );
    }
    ?>
</div>