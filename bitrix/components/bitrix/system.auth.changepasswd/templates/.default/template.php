<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="auth-form">
<?
ShowMessage($arParams["~AUTH_RESULT"]);
?>
<form method="post" action="<?=$arResult["AUTH_FORM"]?>" name="bform">
	<?if (strlen($arResult["BACKURL"]) > 0): ?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
	<? endif ?>
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="CHANGE_PWD">
	<h3><?=GetMessage("AUTH_CHANGE_PASSWORD")?></h3>
	<table class="registration-table">
		<tbody>
			<tr>
				<td>
				<div class="text-field-container ">
                    <label for="regName" class="label required"><?=GetMessage("AUTH_LOGIN")?></label>
                    <input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["LAST_LOGIN"]?>" class="text-field"/>
                </div>
				</td>
			</tr>
			<tr>
				<td>
				<div class="text-field-container ">
                    <label for="regName" class="label required"><?=GetMessage("AUTH_CHECKWORD")?></label>
    				<input type="text" name="USER_CHECKWORD" maxlength="50" value="<?=$arResult["USER_CHECKWORD"]?>" class="text-field" />
                </div>
				</td>
			</tr>
			<tr>
				<td>
				<div class="text-field-container ">
                    <label for="regName" class="label required"><?=GetMessage("AUTH_NEW_PASSWORD_REQ")?></label>
				    <input type="password" name="USER_PASSWORD" maxlength="50" value="<?=$arResult["USER_PASSWORD"]?>" class="text-field" />
                </div>
				</td>
			</tr>
			<tr>
				<td>
				<div class="text-field-container ">
                    <label for="regName" class="label required"><?=GetMessage("AUTH_NEW_PASSWORD_CONFIRM")?></label>
				    <input type="password" name="USER_CONFIRM_PASSWORD" maxlength="50" value="<?=$arResult["USER_CONFIRM_PASSWORD"]?>" class="text-field"  />
                </div>
				</td>
			</tr>
			<tr style="display:none;">
				<td><input type="submit" id='change_pwd' name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>" /></td>
			</tr>
		</tbody>
	</table>
<a href="#" class="big-black-button" onclick="$('#change_pwd').trigger('click');"><?=GetMessage("AUTH_CHANGE")?></a>

</form>

</div>