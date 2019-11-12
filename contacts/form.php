<?
if ($_POST && !$_GET['login']) {
$APPLICATION->RestartBuffer();
if (!defined('PUBLIC_AJAX_MODE')) {
	define('PUBLIC_AJAX_MODE', true);
}
if (!filter_var($_POST['feedbackEmail'],FILTER_VALIDATE_EMAIL)) {
	$errors['feedbackEmail'] = 'Укажите верный email';
}
if (!$_POST['message']) {
	$errors['message'] = 'Укажите сообщение';
}
if (!$_POST['name']) {
    $errors['name'] = 'Укажите имя'; 
}
if (!$APPLICATION->CaptchaCheckCode($_POST['captcha'], $_POST['captcha_sid'])) {
    $errors['captcha'] = 'Не верно указан код подтверждения';
}
header('Content-type: application/json');
if ($errors) {
    
	echo json_encode(array(
			'submitOn' => false,
			'errors'=>$errors,
	        'captcha'=>$APPLICATION->CaptchaGetCode()
	));
} else {
    $arEventFields = array(
            "NAME"           => $_POST['name'],
			"MESSAGE"       => $_POST['message'],
			"EMAIL"         => $_POST['feedbackEmail'],
            "SITE"          => 'atlet.tomsk.ru'
            
    );
	CEvent::Send("FEEDBACK_FORM", 's1', $arEventFields);
	echo json_encode(array('submitOn' => true,'callFunc'=>'showSuccessResult'));
}
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
die();
}
?>
				<div class="j-flash-message-container"></div>
                <div class="feedback-form">
					<form action="/contacts/" method="post" id="feedbackForm" class="ajaxform">
						<?php $code = $APPLICATION->CaptchaGetCode(); ?>
						  <input type="hidden" name="captcha_sid" value="<?=$code;?>" />
                        <h3>Пишите нам, если у вас есть вопросы или пожелания</h3>
                        <textarea placeholder="Ваше сообщение" class="textarea" id="message" name="message"></textarea>
                        <table>
                            <tr>
                                <td>
                                    <label for="name" class="label required">Имя</label>
                                    <input type="text" class="text-field" id="name" name="name" />
                                </td>
                                <td>
                                    <label for="captcha" class="label required">Введите число с картинки</label>
                                    <input type="text" class="text-field captcha" id="captcha" name="captcha" />

                                    <img class="captcha-img captcha_pic" src="/bitrix/tools/captcha.php?captcha_sid=<?=$code;?>" alt="" />
                                    <a class="changeUpdateImage" href="#"><i class="fa fa-refresh fa-2x" aria-hidden="true"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label for="feedbackEmail" class="label required">Email</label>
                                    <input type="text" class="text-field" id="feedbackEmail" name="feedbackEmail" />
                                </td>
                                <td>
                                    <a onclick="$('#feedbackForm').submit();return false;" class="red-button">Отправить сообщение</a>
                                    <input class="hidden-input" type="submit" value="Отправить сообщение" name="feedbackSubmit" id="feedbackSubmit" />
                                </td>
                            </tr>
                        </table>

                    </form>
                </div>
<script>
     function showSuccessResult(resp)
     {
    	 FlashMessage.Show('Данные успешно отправлены');
    	 $('#feedbackForm input,textarea').val('');
     }
</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('.changeUpdateImage').click(function () {
            $.ajax({
                url: "/ajax/callbackCaptcha.php",
                type:'POST',
                success: function(code){
                    $('input[name=captcha_sid]').val(code);
                    $('.captcha-img').attr('src', '/bitrix/tools/captcha.php?captcha_sid=' + code);
                }
            });

        });
    });
</script>