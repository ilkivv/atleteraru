<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);

?>

<div class="callback-form">
	<div class="title-b">
		<?=$arResult["arForm"]["NAME"]?>	
	</div>
	<div class="info-b">
    <?php if ($arResult['isFormNote'] == 'Y') { ?>
    <div class="alert alert-success form-success form-description"><?=$arResult['FORM_NOTE']?></div>
    <?php } else { ?>
    <?php if ($arResult["isFormErrors"] == "Y") { ?>
    <div class="alert alert-danger form-errors">
    <?=$arResult["FORM_ERRORS"];?>
    </div>
    <?php } ?>
    <?=$arResult["FORM_HEADER"]?>

    <p class="form-description"><?=$arResult["FORM_DESCRIPTION"]?></p>
    <? foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion) { ?>
	<?if($FIELD_SID == 'AGREEMENT'){continue;}?>
    <div class="form-group <?=strtolower(str_replace('_', '-', $FIELD_SID))?>">
        <?if (is_array($arResult["FORM_ERRORS"]) && array_key_exists($FIELD_SID, $arResult['FORM_ERRORS'])):?>
        <?endif;?>
        <?=str_replace(array('/>', '<textarea'), array('placeholder="'.$arQuestion["CAPTION"].'"/>', '<textarea placeholder="'.$arQuestion["CAPTION"].'" '), $arQuestion["HTML_CODE"])?>
    </div>   
    <? } ?>
		
    <? if($arResult["isUseCaptcha"] == "Y") { ?>
    <div class="form-group captcha">
        <input type="hidden" name="captcha_sid" value="<?=htmlspecialcharsbx($arResult["CAPTCHACode"]);?>" />
        <div class="label"><?=GetMessage("FORM_CAPTCHA_FIELD_TITLE")?> <?=$arResult["REQUIRED_SIGN"];?></div>
        <input type="text" name="captcha_word" size="30" maxlength="50" value="" class="inputtext" />
        <img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult["CAPTCHACode"]);?>" width="180" height="40" />
    </div>
    <? } ?>
	
	
	
	<div class="form-group agreement-group">
		<div class="col-md-12">
		
			<label class="styled-checkbox agreement">
				<input type="checkbox" name="form_checkbox_AGREEMENT[]" value="<?=$arResult["QUESTIONS"]["AGREEMENT"]["STRUCTURE"][0]['ID']?>" 
					<?if(in_array($arResult["QUESTIONS"]["AGREEMENT"]["STRUCTURE"][0]['ID'], $arResult["arrVALUES"]["form_checkbox_AGREEMENT"])):?>
					checked
					<?endif?>
				>
				<span class="caption">			
					Я согласен с условиями <a target="_blank" onclick="privateAgreement()" class="privacy"  href="javascript:void(0)">политики конфиденциальности</a>
					<?//Нажимая кнопку Заказать, я даю своё <a rel="nofollow" target="_blank" href="/politika-konfedentsialnosti/">согласие на обработку моих персональных данных</a>, в соответствии с Федеральным законом от 27.07.2006 года №152-ФЗ «О персональных данных», на условиях и для целей, определенных <a href="javascript:void(0)" onclick="privateAgreement()" class="privacy">Политикой конфиденциальности</a>?>			
				</span>
			</label>		
			
		</div>
	</div>	
	
	
        <input <?=(intval($arResult["F_RIGHT"]) < 10 ? "disabled=\"disabled\"" : "");?> type="submit" class="form-submit" name="web_form_submit" value="<?=htmlspecialcharsbx(strlen(trim($arResult["arForm"]["BUTTON"])) <= 0 ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"]);?>" />
    <?=$arResult["FORM_FOOTER"]?>
    <?php } ?>
	</div>
</div>