<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if (strlen($_POST['send_account_info']) && $_POST['send_account_info']==GetMessage("AUTH_SEND")) {
    $APPLICATION->RestartBuffer();
    if (!defined('PUBLIC_AJAX_MODE')) {
        define('PUBLIC_AJAX_MODE', true);
    }
    
   $res = $APPLICATION->arAuthResult;
   
  // var_dump($res['TYPE'] == 'ERROR'); die();
   
   /* echo "<pre>";
   var_dump($res);
   echo "</pre>"; die(); */
    
   header('Content-type: application/json');
   
/*global $USER;
    $arResult = $USER->SendPassword('', $_POST['USER_EMAIL']);*/
    if ($arResult['ERROR']) {
        echo json_encode(array(
                'type' => 'errors',
                'errors' => array (
                        'USER_LOGIN' => strip_tags($arResult['ERROR_MESSAGE']['MESSAGE']),
                        'USER_PASSWORD' => strip_tags($arResult['ERROR_MESSAGE']['MESSAGE'])),
        ));
    }
    elseif($res['TYPE'] == 'ERROR' ){
        
      echo json_encode(array(
                'type' => 'errors',
                'errors' => array (
                   'USER_EMAIL' => strip_tags($res["MESSAGE"])
                ),
        ));  
        
    }
    else {
        echo json_encode(array('submitOn' => true,'result'=>$arResult,'msg'=>ShowMessage($arParams["~AUTH_RESULT"]), 'callFunc' => 'recoveryPassSuccess'));
    }
    require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
    die();
}

?>
<form name="bform" id='pForgotForm' method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>" class="ajaxform">
<?
if (strlen($arResult["BACKURL"]) > 0)
{
?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?
}
?>
    <input type="hidden" name="USER_LOGIN" value="" />
    <input type="submit" name="send_account_info" id='pForgotFormSubmitBtn' value="<?=GetMessage("AUTH_SEND")?>" style="display:none;"/>
	<input type="hidden" name="AUTH_FORM" value="Y">
	<input type="hidden" name="TYPE" value="SEND_PWD">
<div class="recover-block j-recover-block">
    <p>Пожалуйста, введите ваш e-mail.<br /> Мы вышлем письмо со ссылкой для<br /> создания нового пароля.</p>
    
    
    <div class="j-err_pForgotForm_USER_EMAIL" class="error-message"></div>
    
    <input type="text" name="USER_EMAIL" id="email" class="text-field" placeholder="Ваш email" />
    <a class="black-button j-get-pass" href="#" onclick="$('#pForgotFormSubmitBtn').trigger('click');">Получить пароль</a>
    <a class="white-button j-cancel-recover" href="#">Отмена</a>
</div>
<div class="success-block j-success-block">
    <p>Мы выслали вам письмо с восстановлением<br />  пароля. Если письмо не пришло, проверьте<br /> раздел «Спам» в вашем ящике.</p>
    <a class="black-button j-success-recover" href="#">Хорошо</a>
</div>
</form>