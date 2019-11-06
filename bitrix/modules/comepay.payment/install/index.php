<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));

include(GetLangFileName($strPath2Lang."/lang/", "/install.php"));


Class comepay_payment extends CModule
{
	var $MODULE_ID = "comepay.payment";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";
	var $PARTNER_NAME;
	var $PARTNER_URI;

	function comepay_payment()
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

		$this->PARTNER_URI  = "http://comepay.ru";
		$this->PARTNER_NAME = GetMessage("COMEPAY.PAYMENT_PARTNER_NAME");
		$this->MODULE_NAME = GetMessage("COMEPAY.PAYMENT_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("COMEPAY.PAYMENT_INSTALL_DESCRIPTION");
	}

	function DoInstall()
	{
		global $APPLICATION, $step;
		$GLOBALS["errors"] = false;
			$this->InstallFiles();
			$this->InstallDB();
			$GLOBALS["errors"] = $this->errors;
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();

			$GLOBALS["errors"] = $this->errors;
	}

	function InstallDB()
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;
		RegisterModule("comepay.payment");
		RegisterModuleDependences("sale", "OnSaleCancelOrder", "comepay.payment", "CComepayPayment", "OnSaleCancelOrderHandler");
		if(!$DB->Query("SELECT 'x' FROM comepay_payment WHERE 1=0", true)){
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/comepay.payment/install/db/".$DBType."/install.sql");
		}
		if($this->errors !== false){
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;
		$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/comepay.payment/install/db/".$DBType."/uninstall.sql");
		UnRegisterModule("comepay.payment");
		UnRegisterModuleDependences("sale", "OnSaleCancelOrder", "comepay.payment", "CComepayPayment", "OnSaleCancelOrderHandler");
		if($this->errors !== false){
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		return true;
	}


	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/comepay.payment/install/sale_payment/comepay.payment/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_payment/comepay.payment/");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/comepay.payment/install/tools/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/comepay.payment/install/result_pages/", $_SERVER["DOCUMENT_ROOT"]."/");
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFilesEx("/bitrix/php_interface/include/sale_payment/comepay.payment");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/comepay.payment/install/tools/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/comepay.payment/install/result_pages/", $_SERVER["DOCUMENT_ROOT"]."/");
		return true;
	}

}
?>