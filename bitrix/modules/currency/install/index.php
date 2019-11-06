<?
use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);

class currency extends CModule
{
	var $MODULE_ID = "currency";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function currency()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = CURRENCY_VERSION;
			$this->MODULE_VERSION_DATE = CURRENCY_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("CURRENCY_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("CURRENCY_INSTALL_DESCRIPTION");
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		$this->InstallEvents();
		$GLOBALS["errors"] = $this->errors;

		$APPLICATION->IncludeAdminFile(GetMessage("CURRENCY_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/step1.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("CURRENCY_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("CURRENCY_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/unstep2.php");
		}
	}

	function InstallDB()
	{
		global $DB, $APPLICATION;
		global $stackCacheManager;
		global $CACHE_MANAGER;

		$this->errors = false;

		if (!$DB->Query("SELECT COUNT(CURRENCY) FROM b_catalog_currency", true)):
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/db/".strtolower($DB->type)."/install.sql");
		endif;

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}
		RegisterModule("currency");
		$stackCacheManager->Clear("currency_currency_lang");
		$CACHE_MANAGER->Clean("currency_currency_list");
		$CACHE_MANAGER->Clean("currency_base_currency");
		$stackCacheManager->Clear("currency_rate");

		if (\Bitrix\Main\Loader::includeModule("currency"))
		{
			$by = "sort";
			$order = "asc";
			$dbCurrency = CCurrency::GetList($by, $order);
			if (!$dbCurrency->Fetch())
			{
				$languageID = '';

				$by = 'sort';
				$order = 'desc';
				$dbSites = CSite::GetList($by, $order, array('DEF' => 'Y', 'ACTIVE' => 'Y'));
				$defaultSite = is_object($dbSites) ? $dbSites->Fetch() : null;
				if(is_array($defaultSite))
				{
					$languageID = $defaultSite['LANGUAGE_ID'];
				}

				$currencyList = array();
				$currencySetID = '';
				switch ($languageID)
				{
					case 'ua':
					case 'de':
					case 'en':
						$currencySetID = $languageID;
						break;
					case 'ru':
						$rsLang = CLanguage::GetByID('kz');
						if ($arLang = $rsLang->Fetch())
						{
							$currencySetID = 'kz';
						}
						if ($currencySetID == '')
						{
							$rsLang = CLanguage::GetByID('ua');
							if ($arLang = $rsLang->Fetch())
							{
								$currencySetID = 'ua';
							}
						}
						if ($currencySetID == '')
						{
							$currencySetID = $languageID;
						}
						break;
					default:
						$currencySetID = 'en';
						break;
				}
				switch ($currencySetID)
				{
					case 'kz':
						$arFields = array('CURRENCY' => 'KZT', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'RUB', 'AMOUNT' => 1, 'AMOUNT_CNT' => 4.72, 'SORT' => 200);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'USD', 'AMOUNT' => 1, 'AMOUNT_CNT' => 154.52, 'SORT' => 300);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'EUR', 'AMOUNT' => 1, 'AMOUNT_CNT' => 212.73, 'SORT' => 400);
						CCurrency::Add($arFields);

						$currencyList = array('KZT', 'RUB', 'USD', 'EUR');
						break;
					case 'ua':
						$arFields = array('CURRENCY' => 'UAH', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'RUB', 'AMOUNT' => 2.54, 'AMOUNT_CNT' => 10, 'SORT' => 200);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'USD', 'AMOUNT' => 799.3, 'AMOUNT_CNT' => 100, 'SORT' => 300);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'EUR', 'AMOUNT' => 1083.37, 'AMOUNT_CNT' => 100, 'SORT' => 400);
						CCurrency::Add($arFields);

						$currencyList = array("UAH", "RUB", "USD", "EUR");
						break;
					case 'ru':
						$arFields = array('CURRENCY' => 'RUB', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'USD', 'AMOUNT' => 32.30, 'AMOUNT_CNT' => 1, 'SORT' => 200);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'EUR', 'AMOUNT' => 43.80, 'AMOUNT_CNT' => 1, 'SORT' => 300);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'UAH', 'AMOUNT' => 39.41, 'AMOUNT_CNT' => 10, 'SORT' => 400);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'BYR', 'AMOUNT' => 36.72, 'AMOUNT_CNT' => 10000, 'SORT' => 500);
						CCurrency::Add($arFields);

						$currencyList = array('RUB', 'USD', 'EUR', 'UAH', 'BYR');
						break;
					case 'de':
						$arFields = array('CURRENCY' => 'EUR', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'USD', 'AMOUNT' => 0.74, 'AMOUNT_CNT' => 1, 'SORT' => 200);
						CCurrency::Add($arFields);

						$currencyList = array('EUR', 'USD');
						break;
					case 'en':
						$arFields = array('CURRENCY' => 'USD', 'AMOUNT' => 1, 'AMOUNT_CNT' => 1, 'SORT' => 100);
						CCurrency::Add($arFields);

						$arFields = array('CURRENCY' => 'EUR', 'AMOUNT' => 1.36, 'AMOUNT_CNT' => 1,
							'SORT' => 200
						);
						CCurrency::Add($arFields);
						$currencyList = array('USD', 'EUR');
						break;
				}

				if (!empty($currencyList))
				{
					Option::set('currency', 'installed_currencies', implode(',', $currencyList));
					$b = "sort";
					$o = "asc";
					$dbLangs = CLanguage::GetList($b, $o, array("ACTIVE" => "Y"));
					while ($arLangs = $dbLangs->Fetch())
					{
						$CACHE_MANAGER->Clean("currency_currency_list_".$arLangs["LID"]);

						IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install_lang.php", $arLangs["LID"]);
						foreach($currencyList as $val)
						{
							$arFields = array();
							switch ($val)
							{
								case "USD":
									$arFields = array(
										"LID" => $arLangs["LID"],
										"CURRENCY" => "USD",
										"FORMAT_STRING" => GetMessage("CUR_INSTALL_USD_FORMAT_STRING"),
										"FULL_NAME" => GetMessage("CUR_INSTALL_USD_FULL_NAME"),
										"DEC_POINT" => GetMessage("CUR_INSTALL_USD_DEC_POINT"),
										"THOUSANDS_VARIANT" => GetMessage("CUR_INSTALL_USD_THOUSANDS_SEP"),
										"THOUSANDS_SEP" => false,
										"DECIMALS" => 2,
										"HIDE_ZERO" => "Y"
									);
									break;
								case "EUR":
									$arFields = array(
										"LID" => $arLangs["LID"],
										"CURRENCY" => "EUR",
										"FORMAT_STRING" => GetMessage("CUR_INSTALL_EUR_FORMAT_STRING"),
										"FULL_NAME" => GetMessage("CUR_INSTALL_EUR_FULL_NAME"),
										"DEC_POINT" => GetMessage("CUR_INSTALL_EUR_DEC_POINT"),
										"THOUSANDS_VARIANT" => GetMessage("CUR_INSTALL_EUR_THOUSANDS_SEP"),
										"THOUSANDS_SEP" => false,
										"DECIMALS" => 2,
										"HIDE_ZERO" => "Y"
									);
									break;
								case "RUB":
									$arFields = array(
										"LID" => $arLangs["LID"],
										"CURRENCY" => "RUB",
										"FORMAT_STRING" => GetMessage("CUR_INSTALL_RUB_FORMAT_STRING"),
										"FULL_NAME" => GetMessage("CUR_INSTALL_RUB_FULL_NAME"),
										"DEC_POINT" => GetMessage("CUR_INSTALL_RUB_DEC_POINT"),
										"THOUSANDS_VARIANT" => GetMessage("CUR_INSTALL_RUB_THOUSANDS_SEP"),
										"THOUSANDS_SEP" => false,
										"DECIMALS" => 2,
										"HIDE_ZERO" => "Y"
									);
									break;
								case "UAH":
									$arFields = array(
										"LID" => $arLangs["LID"],
										"CURRENCY" => "UAH",
										"FORMAT_STRING" => GetMessage("CUR_INSTALL_UAH_FORMAT_STRING"),
										"FULL_NAME" => GetMessage("CUR_INSTALL_UAH_FULL_NAME"),
										"DEC_POINT" => GetMessage("CUR_INSTALL_UAH_DEC_POINT"),
										"THOUSANDS_VARIANT" => GetMessage("CUR_INSTALL_UAH_THOUSANDS_SEP"),
										"THOUSANDS_SEP" => false,
										"DECIMALS" => 2,
										"HIDE_ZERO" => "Y"
									);
									break;
								case "KZT":
									$arFields = array(
										"LID" => $arLangs["LID"],
										"CURRENCY" => "KZT",
										"FORMAT_STRING" => GetMessage("CUR_INSTALL_KZT_FORMAT_STRING"),
										"FULL_NAME" => GetMessage("CUR_INSTALL_KZT_FULL_NAME"),
										"DEC_POINT" => GetMessage("CUR_INSTALL_KZT_DEC_POINT"),
										"THOUSANDS_VARIANT" => GetMessage("CUR_INSTALL_KZT_THOUSANDS_SEP"),
										"THOUSANDS_SEP" => false,
										"DECIMALS" => 2,
										"HIDE_ZERO" => "Y"
									);
									break;
								case "BYR":
									$arFields = array(
										"LID" => $arLangs["LID"],
										"CURRENCY" => "BYR",
										"FORMAT_STRING" => GetMessage("CUR_INSTALL_BYR_FORMAT_STRING"),
										"FULL_NAME" => GetMessage("CUR_INSTALL_BYR_FULL_NAME"),
										"DEC_POINT" => GetMessage("CUR_INSTALL_BYR_DEC_POINT"),
										"THOUSANDS_VARIANT" => GetMessage("CUR_INSTALL_BYR_THOUSANDS_SEP"),
										"THOUSANDS_SEP" => false,
										"DECIMALS" => 2,
										"HIDE_ZERO" => "Y"
									);
							}
							if (!empty($arFields))
								CCurrencyLang::Add($arFields);
						}
					}
				}
			}
		}
		$stackCacheManager->Clear("currency_currency_lang");
		$CACHE_MANAGER->Clean("currency_currency_list");
		$CACHE_MANAGER->Clean("currency_base_currency");
		$stackCacheManager->Clear("currency_rate");

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;
		if (!isset($arParams["savedata"]) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/db/".strtolower($DB->type)."/uninstall.sql");
			if($this->errors !== false)
			{
				$APPLICATION->ThrowException(implode("", $this->errors));
				return false;
			}
		}

		UnRegisterModule("currency");

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/currency", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFilesEx("/bitrix/themes/.default/icons/currency/");
		DeleteDirFilesEx("/bitrix/images/currency/");
		DeleteDirFilesEx("/bitrix/js/currency/");

		return true;
	}
}