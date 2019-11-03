<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($arResult["is_ajax_post"]) {
	
	//["MESSAGE"]
	
	//["COMMENT_ERROR"]
	
	
	if ($arResult['COMMENT_ERROR']) {       
			
			echo json_encode(array(
					'type' => 'errors',
					'errors' => array (                       
							'COMMENT' => strip_tags($arResult['COMMENT_ERROR'])),
					'captcha'=>$APPLICATION->CaptchaGetCode()		
			));
		
		
		
    }
    else {       
		
		 echo json_encode(array(
			'submitOn' => true,
			'result'=>$arResult,
			'msg'=>$arResult['MESSAGE'],
			'callFunc' => 'sendComment',
			'captcha'=>$APPLICATION->CaptchaGetCode()	
			
			
			)
			
		);
    }
	 
	die();
	
	//echo "<pre>"; var_dump($arResult); echo "</pre>";
	
    //die(json_encode(array('submitOn'=>true,'reloadOn'=>true)));
}
?>
<script>



$(document).ready(function(){
	// Comments
    if($('.j-comment-form-container').length > 0) {
        new Comments();
    }
	var a=0;
    $('.comment').each(function(i,elem){
		a++;
    });
	
	$('.j-comments-cnt').html(a);
		
	// Ajax form
	bindAjaxForm();
	
	
	
	
});
	
</script>


<?
if(strlen($arResult["MESSAGE"])>0)
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<?=$arResult["MESSAGE"]?>
		</div>
	</div>
	<?
}
if(strlen($arResult["ERROR_MESSAGE"])>0)
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text" id="blg-com-err">
			<?=$arResult["ERROR_MESSAGE"]?>
		</div>
	</div>
	<?
}
if(strlen($arResult["FATAL_MESSAGE"])>0)
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<?=$arResult["FATAL_MESSAGE"]?>
		</div>
	</div>
	<?
}
else
{
	
		if($arResult["is_ajax_post"] != "Y" && $arResult["CanUserComment"])
		{
			
			$ajaxPath = $templateFolder.'/ajax.php';
			?>
			<div class="j-comment-form-container">
				
				<div class="comment-form j-comment-form clearfix">
                   <form method="POST" name="form_comment" id="form_comment" action="<?=$ajaxPath; ?>" class="ajaxform">
    				<input type="hidden" name="parentId" id="parentId" value="">
    				<input type="hidden" name="edit_id" id="edit_id" value="">
    				<input type="hidden" name="act" id="act" value="add">
    				<input type="hidden" name="post" value="Y">
					
					
					<div class="j-err_form_comment_COMMENT" class="error-message"></div>
					
    				<?
    				if(isset($_REQUEST["IBLOCK_ID"]))
    				{
    					?><input type="hidden" name="IBLOCK_ID" value="<?=(int)$_REQUEST["IBLOCK_ID"]; ?>"><?
    				}
    				if(isset($_REQUEST["ELEMENT_ID"]))
    				{
    					?><input type="hidden" name="ELEMENT_ID" value="<?=(int)$_REQUEST["ELEMENT_ID"]; ?>"><?
    				}
    				if(isset($_REQUEST["SITE_ID"]))
    				{
    					?><input type="hidden" name="SITE_ID" value="<?=htmlspecialcharsbx($_REQUEST["SITE_ID"]); ?>"><?
    				}
    				echo makeInputsFromParams($arParams["PARENT_PARAMS"]);
    				echo bitrix_sessid_post();
    				?>
                        <textarea placeholder="Ваш комментарий" class="textarea" id="commentText" name="comment"></textarea>
                    
                        <div class="comment-form-right">
    
                            <div class="form-element">
                                <label for="user_email">Email</label>
                                <input class="text-field" type="text"  name="user_email" id="user_email" value="<?=htmlspecialcharsEx($_SESSION["blog_user_email"])?>" />
                                <em>На email будут приходить ответы на ваши комментарии</em>
                            </div>
    
                        </div>
    
                        <div class="comment-form-left">
    
                            <div class="form-element">
                                <label for="user_name">Имя</label>
                                <input class="text-field" type="text"  name="user_name" id="user_name" value="<?=htmlspecialcharsEx($_SESSION["blog_user_name"])?>"/>
                            </div>
    
                        <?
                    if($arResult["use_captcha"]===true)
                    {
                        ?>
                            
                        </div>
    					<script>
    						document.getElementById('captcha_code').value = '<?=$arResult["CaptchaCode"]?>';
    					</script>
                        <div class="comment-form-left">
                            <div class="form-element">
                                <label for="user_name">Код </label>
                                <input type="hidden" name="captcha_code" id="captcha_code" value="<?=$arResult["CaptchaCode"]?>">
								<input type="text" class="text-field" size="10" style="width:70px;" name="captcha_word" id="captcha_word" value=""  tabindex="7">
                                <img class="captcha_pic" src="/bitrix/tools/captcha.php?captcha_code=<?=$arResult["CaptchaCode"]?>" width="180" height="40" id="captcha" style="position:relative;top:14px;">
                            </div>
                            
        				<?
        			}?>
<a href="javascript:void(0);" onclick="$('#form_comment').submit();" class="red-button">Отправить комментарий</a>
                            <input class="hidden-input" type="submit" value="Отправить комментарий" name="commentSubmit" id="commentSubmit" />
        
                        </div>
                        <div class="hide-comment-form">
                            <a class="j-hide-comment-form" href="javascript:void(0);"  id="post-button" onclick="submitComment()">Свернуть</a>
                        </div>
                    <input type="hidden" name="blog_upload_cid" id="upload-cid" value="">
                    </form>
                </div>
			</div>
			<?php 
		}

		$prevTab = 0;
		function ShowComment($comment, $tabCount=0, $tabSize=2.5, $canModerate=false, $User=Array(), $use_captcha=false, $bCanUserComment=false, $errorComment=false, $arParams = array())
		{
			$comment["urlToAuthor"] = "";
			$comment["urlToBlog"] = "";

			if($comment["SHOW_AS_HIDDEN"] == "Y" || $comment["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_PUBLISH || $comment["SHOW_SCREENNED"] == "Y" || $comment["ID"] == "preview")
			{
				global $prevTab;
				$tabCount = IntVal($tabCount);
				if($tabCount <= 5)
					$paddingSize = 2.5 * $tabCount;
				elseif($tabCount > 5 && $tabCount <= 10)
					$paddingSize = 2.5 * 5 + ($tabCount - 5) * 1.5;
				elseif($tabCount > 10)
					$paddingSize = 2.5 * 5 + 1.5 * 5 + ($tabCount-10) * 1;

				if(($tabCount+1) <= 5)
					$paddingSizeNew = 2.5 * ($tabCount+1);
				elseif(($tabCount+1) > 5 && ($tabCount+1) <= 10)
					$paddingSizeNew = 2.5 * 5 + (($tabCount+1) - 5) * 1.5;
				elseif(($tabCount+1) > 10)
					$paddingSizeNew = 2.5 * 5 + 1.5 * 5 + (($tabCount+1)-10) * 1;
				$paddingSizeNew -= $paddingSize;

				if($prevTab > $tabCount)
					$prevTab = $tabCount;
				if($prevTab <= 5)
					$prevPaddingSize = 2.5 * $prevTab;
				elseif($prevTab > 5 && $prevTab <= 10)
					$prevPaddingSize = 2.5 * 5 + ($prevTab - 5) * 1.5;
				elseif($prevTab > 10)
					$prevPaddingSize = 2.5 * 5 + 1.5 * 5 + ($prevTab-10) * 1;

					$prevTab = $tabCount;
				?>
				
				<div class="comment <?php echo ($tabCount >0)?" reply-block":""?>">
		            <a name="<?=$comment["ID"]?>"></a>
                    <div class="comment-head">
                        <strong><?=$comment["AuthorName"]?></strong>
                        <span><?=$comment["DateFormated"]?></span>
                    </div>
                    <div class="comment-body" id="blg-comment-<?=$comment["ID"]?>">
                       <?=$comment["TextFormated"]?>
                    </div>
                    <div class="comment-reply">
                        <?php /*?><a class="j-comment-form-reply" data-id="<?=$comment["ID"]?>" href="#">Ответить</a>*/?>
                    </div>
				</div>
				<?
			}
		}

		function RecursiveComments($sArray, $key, $level=0, $first=false, $canModerate=false, $User, $use_captcha, $bCanUserComment, $errorComment, $arSumComments, $arParams)
		{
			if(!empty($sArray[$key]))
			{
				foreach($sArray[$key] as $comment)
				{
					if(!empty($arSumComments[$comment["ID"]]))
					{
						$comment["CAN_EDIT"] = $arSumComments[$comment["ID"]]["CAN_EDIT"];
						$comment["SHOW_AS_HIDDEN"] = $arSumComments[$comment["ID"]]["SHOW_AS_HIDDEN"];
						$comment["SHOW_SCREENNED"] = $arSumComments[$comment["ID"]]["SHOW_SCREENNED"];
						$comment["NEW"] = $arSumComments[$comment["ID"]]["NEW"];
					}
					ShowComment($comment, $level, 2.5, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams);
					if(!empty($sArray[$comment["ID"]]))
					{
						foreach($sArray[$comment["ID"]] as $key1)
						{
							if(!empty($arSumComments[$key1["ID"]]))
							{
								$key1["CAN_EDIT"] = $arSumComments[$key1["ID"]]["CAN_EDIT"];
								$key1["SHOW_AS_HIDDEN"] = $arSumComments[$key1["ID"]]["SHOW_AS_HIDDEN"];
								$key1["SHOW_SCREENNED"] = $arSumComments[$key1["ID"]]["SHOW_SCREENNED"];
								$key1["NEW"] = $arSumComments[$key1["ID"]]["NEW"];
							}
							ShowComment($key1, ($level+1), 2.5, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arParams);

							if(!empty($sArray[$key1["ID"]]))
							{
								RecursiveComments($sArray, $key1["ID"], ($level+2), false, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment, $arSumComments, $arParams);
							}
						}
					}
					if($first)
						$level=0;
				}
			}
		}
		?>
		<?
		if($arResult["is_ajax_post"] != "Y")
		{
			if($arResult["CanUserComment"])
			{
				$postTitle = "";
				if($arParams["NOT_USE_COMMENT_TITLE"] != "Y")
					$postTitle = "RE: ".CUtil::JSEscape($arResult["Post"]["TITLE"]);
				?>
				<a class="add-comment j-comment-form-link" href="#"><span>Добавить комментарий</span></a>
				<h3 class="h-comments">Комментарии (<span class='j-comments-cnt'></span>)</h3>
				<a name="0"></a>
				<?
				if(strlen($arResult["COMMENT_ERROR"]) > 0 && strlen($_POST["parentId"]) < 2
					&& IntVal($_POST["parentId"])==0 && IntVal($_POST["edit_id"]) <= 0)
				{
					?>
					<div class="blog-errors blog-note-box blog-note-error">
						<div class="blog-error-text"><?=$arResult["COMMENT_ERROR"]?></div>
					</div>
					<?
				}
			}

			if($arResult["NEED_NAV"] == "Y")
			{
				?>
				<div class="blog-comment-nav">
					<?=GetMessage("BPC_PAGE")?>&nbsp;<?
					for($i = 1; $i <= $arResult["PAGE_COUNT"]; $i++)
					{
						$style = "blog-comment-nav-item";
						if($i == $arResult["PAGE"])
							$style .= " blog-comment-nav-item-sel";
						?><a class="<?=$style?>" href="<?=$arResult["NEW_PAGES"][$i]?>" onclick="return bcNav('<?=$i?>', this)" id="blog-comment-nav-t<?=$i?>"><?=$i?></a>&nbsp;&nbsp;<?
					}
				?>
				</div>
				<?
			}
		}

		
		if($arResult["is_ajax_post"] == "Y")
			$arParams["is_ajax_post"] = "Y";

		if($arResult["is_ajax_post"] != "Y" && $arResult["NEED_NAV"] == "Y")
		{
			for($i = 1; $i <= $arResult["PAGE_COUNT"]; $i++)
			{
				$tmp = $arResult["CommentsResult"];
				$tmp[0] = $arResult["PagesComment"][$i];
				?>
					<div id="blog-comment-page-<?=$i?>"<?if($arResult["PAGE"] != $i) echo "style=\"display:none;\""?>><?RecursiveComments($tmp, $arResult["firstLevel"], 0, true, $arResult["canModerate"], $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"], $arResult["Comments"], $arParams);?></div>
				<?
			}
		}
		else
			RecursiveComments($arResult["CommentsResult"], $arResult["firstLevel"], 0, true, $arResult["canModerate"], $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"], $arResult["Comments"], $arParams);

		if($arResult["is_ajax_post"] != "Y")
		{
			if($arResult["NEED_NAV"] == "Y")
			{
				?>
				<div class="blog-comment-nav">
					<?=GetMessage("BPC_PAGE")?>&nbsp;<?
					for($i = 1; $i <= $arResult["PAGE_COUNT"]; $i++)
					{
						$style = "blog-comment-nav-item";
						if($i == $arResult["PAGE"])
							$style .= " blog-comment-nav-item-sel";
						?><a class="<?=$style?>" href="<?=$arResult["NEW_PAGES"][$i]?>" onclick="return bcNav('<?=$i?>', this)" id="blog-comment-nav-b<?=$i?>"><?=$i?></a>&nbsp;&nbsp;<?
					}
				?>
				</div>
				<?
			}
		}
}
?>
</div>
<?
if($arResult["is_ajax_post"] == "Y")
	die();

function makeInputsFromParams($arParams, $name="PARAMS")
{
	$result = "";

	if(is_array($arParams))
	{
		foreach ($arParams as $key => $value)
		{
			if(substr($key, 0, 1) != "~")
			{
				$inputName = $name.'['.$key.']';

				if(is_array($value))
					$result .= makeInputsFromParams($value, $inputName);
				else
					$result .= '<input type="hidden" name="'.$inputName.'" value="'.$value.'">'.PHP_EOL;
			}
		}
	}

	return $result;
}
?>