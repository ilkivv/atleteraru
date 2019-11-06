<?php
namespace Bitrix\Main\Analytics;

use Bitrix\Main\Config\Option;

class Counter
{
	protected static $data = array();

	public static function getInjectedJs($stripTags = false)
	{
		$accountId = static::getAccountId();
		$params = static::injectDataParams();
		$js = <<<JS
			var _ba = _ba || []; _ba.push(["aid", "{$accountId}"]);{$params}
			(function() {
				var ba = document.createElement("script"); ba.type = "text/javascript"; ba.async = true;
				ba.src = document.location.protocol + "//bitrix.info/ba.js";
				var s = document.getElementsByTagName("script")[0];
				s.parentNode.insertBefore(ba, s);
			})();
JS;

		$js = str_replace(array("\n", "\t"), "", $js);
		if ($stripTags === false)
		{
			return "<script>".$js."</script>";
		}
		else
		{
			return $js;
		}
	}

	public static function injectIntoPage()
	{
		global $APPLICATION;
		$APPLICATION->AddHeadString(static::getInjectedJs(), false, "AFTER_CSS");
	}

	public static function getAccountId()
	{
		if (defined("LICENSE_KEY"))
		{
			return md5("BITRIX".LICENSE_KEY."LICENCE");
		}
		else
		{
			return "";
		}
	}

	public static function getPrivateKey()
	{
		if (defined("LICENSE_KEY"))
		{
			return md5(LICENSE_KEY);
		}
		else
		{
			return "";
		}
	}

	public static function onBeforeEndBufferContent()
	{
		if (SiteSpeed::isOn() && (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true))
		{
			Counter::injectIntoPage();
		}
	}

	public static function sendData($arParams)
	{
		if (is_array($arParams))
		{
			static::$data[] = $arParams;
		}
	}

	private static function injectDataParams()
	{
		$result = "";
		foreach (static::$data as $index => $arItem)
		{
			foreach ($arItem as $key => $value)
			{
				$result .= '_ba.push(["ad['.$index.']['.\CUtil::JSEscape($key).']", "'.\CUtil::JSEscape($value).'"]);';
			}
		}

		return $result;
	}
} 