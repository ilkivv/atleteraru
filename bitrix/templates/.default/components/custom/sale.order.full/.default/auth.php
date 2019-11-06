<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="breadcrumbs">
                    <a href="/personal/cart/">Назад</a>
                </div>
                <h1>Вы уже были зарегистрированы на сайте?</h1>
                    <table class="ordering-table">
                    <colgroup>
                            <col style="width: 400px;" />
                            <col />
                        </colgroup>
                        <tr>
                            <td>
                                <div class="radio">
                                    <input type="radio" name="regType" id="regType2" value=2 <?php if ($_POST['do_register'] == 'Y' || !$_POST['do_authorize']){echo "checked";}?> />
                                    <label for="regType2"><span>Заказать без регистрации</span></label>
                                </div>
                                <div class="auth-block">
                                <form method="post" action="<?= $arParams["PATH_TO_ORDER"]?>" name="order_reg_form" id='orderRegForm'>
								<?=bitrix_sessid_post()?>
                                <table>
                                        <tr>
                                            <td>
                                                <label for="regName">Имя</label>
                                                <input type="text" id='regName' name="NEW_NAME" value="<?=$arResult["POST"]["NEW_NAME"]?>" class="text-field" />
                                            </td>
                                        </tr>
                                        <!-- tr>
                                            <td>
	                                            <label for="regLastName">Фамилия</label>
	                                            <input type="text" name="NEW_LAST_NAME"  id="regLastName" value="<?=$arResult["POST"]["NEW_LAST_NAME"]?>" class="text-field" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
	                                            <label for="regEmail">Email</label>
	                                            <input type="text" id="regEmail" name="NEW_EMAIL" value="<?=$arResult["POST"]["NEW_EMAIL"]?>" class="text-field" />
                                            </td>
                                        </tr-->
                                    </table>
                                    <input type="hidden" id="NEW_GENERATE_Y" name="NEW_GENERATE" value="Y" >
                                    <input type="hidden" name="do_register" value="Y">
                                    </form>
                                    </div>
                                <div class="first-time-ordering">
                                    Сделать заказ товара без регистрации.
                                </div>
                                <br />
                                <div align="center"><strong><span>Или <a href="/registration/">зарегистрируйтесь</a></span></strong></div>
                            </td>
                            <td>
                                <div class="radio">
                                    <input type="radio" name="regType" id="regType1" value=1  <?php if ($_POST['do_authorize'] == "Y"){echo "checked";}?> />
                                    <label for="regType1"><span>Я уже зарегистрирован</span></label>
                                </div>

                                <div class="auth-block">
                                <form method="post" action="<?= $arParams["PATH_TO_ORDER"] ?>" name="order_auth_form" id='orderAuthForm'>
								<?=bitrix_sessid_post()?>
                                    <table>
                                        <tr>
                                            <td>
                                                <label for="loginPage">Логин</label>
                                                <input type="text" name="USER_LOGIN" id="loginPage" class="text-field"  value="<?=$arResult["USER_LOGIN"]?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label for="passwordPage">Пароль</label>
                                                <input type="password"  name="USER_PASSWORD" id="passwordPage" class="text-field" />
                                            </td>
                                        </tr>
                                    </table>
                                    <input type="hidden" name="do_authorize" value="Y">
                                    </form>
                                </div>

                            </td>
                        </tr>
                    </table>

                    <div class="ordering-button">
                        <a href="#" class="screw-button" onclick="return submitForm();"><span>Далее</span></a>
                        <input type="submit" class="hidden-input" value="Далее" id="submitReg" name="submitReg" />
                    </div>
                    <script>
function submitForm()
{
	if ($('#regType2').is(':checked')){
		$('#orderRegForm').submit();
		return false;
	}else {
		$('#orderAuthForm').submit();
		return false;
	}
}
                    </script>