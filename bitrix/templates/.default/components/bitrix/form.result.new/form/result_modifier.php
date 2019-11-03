<?php
/* CModule::IncludeModule('iblock');
foreach ($arResult["QUESTIONS"] as $FIELD_SID => &$arQuestion)
{
	if (in_array($FIELD_SID, ['SIMPLE_QUESTION_896'])) 
	{
		$res = CIBlockElement::GetList([], ['IBLOCK_ID' => 4]);
		while ($item = $res->GetNext()) $arResult['SERVICES'][] = $item; 
	}
	if (in_array($FIELD_SID, ['SIMPLE_QUESTION_910']))
	{
		if (isset($_GET['id'])) $specialist = CIBlockElement::GetByid($_GET['id'])->Fetch();
		$res = CIBlockElement::GetList([], ['IBLOCK_ID' => 7]);
		$arQuestion["HTML_CODE"] = '<select name="form_text_9" class="inputselect"><option value="">Выберите специалиста</option>';
		while ($item = $res->GetNext())
		{
			$arQuestion["HTML_CODE"] .= '<option value="'.$item['NAME'].'"'.($item['NAME'] == $_POST['form_text_9'] || $item['NAME'] == $specialist['NAME']?' selected':'').'>'.$item['NAME'].'</option>';
		}
		$arQuestion["HTML_CODE"] .= '</select>';
	} 
} */