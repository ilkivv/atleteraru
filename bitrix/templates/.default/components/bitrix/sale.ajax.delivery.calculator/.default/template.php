<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (is_array($arResult["RESULT"]))
{
	if ($arResult["RESULT"]["RESULT"] == "NEXT_STEP")
		require("step.php");
	else
	{
		if ($arResult["RESULT"]["RESULT"] == "ERROR")
			echo ShowError($arResult["RESULT"]["TEXT"]);
		elseif ($arResult["RESULT"]["RESULT"] == "NOTE")
			echo ShowNote($arResult["RESULT"]["TEXT"]);
		elseif ($arResult["RESULT"]["RESULT"] == "OK")
		{
			echo "<strong>".(strlen($arResult["RESULT"]["VALUE_FORMATTED"]) > 0 ? $arResult["RESULT"]["VALUE_FORMATTED"] : number_format($arResult["RESULT"]["VALUE"], 2, ',', ' '))."</strong>";
			if ($arResult["RESULT"]["TRANSIT"] > 0)
			{
				echo '<br />';
				echo GetMessage('SALE_SADC_TRANSIT').': '.$arResult["RESULT"]["TRANSIT"].'';
			}

			if ($arResult["RESULT"]["PACKS_COUNT"] > 1)
			{
				echo '<br />';
				echo GetMessage('SALE_SADC_PACKS').': '.$arResult["RESULT"]["PACKS_COUNT"].'';
			}

		}
	}
}

if ($arParams["STEP"] == 0)
	require("start.php");
?>