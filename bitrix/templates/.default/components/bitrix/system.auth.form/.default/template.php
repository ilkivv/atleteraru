<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (strlen($_POST['AUTH_FORM']) && $_POST['TYPE']=='AUTH') {
   $APPLICATION->RestartBuffer();
   if (!defined('PUBLIC_AJAX_MODE')) {
      define('PUBLIC_AJAX_MODE', true);
   }
   header('Content-type: application/json');
   if ($arResult['ERROR']) {
      echo json_encode(array(
         'type' => 'error',
      		'errors' => array (
         'USER_LOGIN' => strip_tags($arResult['ERROR_MESSAGE']['MESSAGE']),
      	 'USER_PASSWORD' => strip_tags($arResult['ERROR_MESSAGE']['MESSAGE'])),
      ));
   } else {
      echo json_encode(array('submitOn' => true,'reloadOn'=>true));
   }
   require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
   die();
}
?>
<?if($arResult["FORM_TYPE"] == "login"):?>
<div class="auth-block j-auth-block">
    <form name="system_auth_form<?=$arResult["RND"]?>" method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>" id="authPopupForm" class="ajaxform">
    <?if($arResult["BACKURL"] <> ''):?>
    	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
    <?endif?>
    <?foreach ($arResult["POST"] as $key => $value):?>
    	<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
    <?endforeach?>
    	<input type="hidden" name="AUTH_FORM" value="Y" />
    	<input type="hidden" name="TYPE" value="AUTH" />
        <table>
            <tr>
                <th><label for="login">Логин</label></th>
                <td><input type="text" name="USER_LOGIN" id="login" class="text-field" /></td>
            </tr>
            <tr>
                <th><label for="password">Пароль</label></th>
                <td>
                    <div class="password">
                        <input type="password" name="USER_PASSWORD" id="password" class="text-field" />
                        <a class="forget-pass j-show-recover" href="#">Забыли?</a>
                    </div>
                </td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <label class="checkbox">
                        <input type="checkbox" id="remember" name="USER_REMEMBER" value="Y" />
                        <span>Запомнить меня</span>
                    </label>
                </td>
            </tr>
            <tr>
                <th></th>
                <td>
                <input type="submit" style="display:none;" />
                    <a class="black-button" href="#" onclick="$('#authPopupForm').submit();">Войти</a>
                    <a class="reg-link" href="/registration/">Регистрация</a>
                </td>
            </tr>
        </table>
    </form>
</div>
<?
//if($arResult["FORM_TYPE"] == "login")
else:
/*
 * LOGOUT BLOCK
?>

<form action="<?=$arResult["AUTH_URL"]?>">
	<table width="95%">
		<tr>
			<td align="center">
				<?=$arResult["USER_NAME"]?><br />
				[<?=$arResult["USER_LOGIN"]?>]<br />
				<a href="<?=$arResult["PROFILE_URL"]?>" title="<?=GetMessage("AUTH_PROFILE")?>"><?=GetMessage("AUTH_PROFILE")?></a><br />
			</td>
		</tr>
		<tr>
			<td align="center">
			<?foreach ($arResult["GET"] as $key => $value):?>
				<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
			<?endforeach?>
			<input type="hidden" name="logout" value="yes" />
			<input type="submit" name="logout_butt" value="<?=GetMessage("AUTH_LOGOUT_BUTTON")?>" />
			</td>
		</tr>
	</table>
</form>
<?
*/
endif?>
