<?
global $MESS;
$PathInstall = str_replace('\\', '/', __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall)-strlen('/index.php'));
IncludeModuleLangFile($PathInstall.'/install.php');
include($PathInstall.'/version.php');

if (class_exists('zyter_smtp')) return;

class zyter_smtp extends CModule
{
	var $MODULE_ID = "zyter.smtp";
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $PARTNER_NAME;
	public $PARTNER_URI;
	public $MODULE_GROUP_RIGHTS = 'N';

	public function __construct()
	{
		$arModuleVersion = array();

		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->PARTNER_NAME = GetMessage('ZYTER_PARTNER_NAME');
		$this->PARTNER_URI = GetMessage('ZYTER_PARTNER_URI');

		$this->MODULE_NAME = GetMessage('ZYTER_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('ZYTER_MODULE_DESCRIPTION');
	}

	
	
	function DoInstall()
	{
		global $APPLICATION;
		
		if(function_exists("custom_mail")){
			$APPLICATION->throwException(GetMessage('ZYTER_ERROR'));			
			return false;
		}
		
		if (!IsModuleInstalled("zyter.smtp"))
		{
			$this->InstallDB();
			$this->InstallEvents();
			$this->InstallFiles();
			
		}
		
		return true;
	}

	function DoUninstall()
	{
		$this->UnInstallFiles();
		$this->UnInstallDB();
		$this->UnInstallEvents();
		
		
		return true;
	}
	
	
		function InstallDB() {

		
		RegisterModule("zyter.smtp");	
		return true;
	
			
	}
	
	function UnInstallDB()
	{
		
		UnRegisterModule("zyter.smtp");
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
	
	if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface'))
		{
				
			$str = '<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zyter.smtp/classes/general/cmodulezytersmtp.php");?'.'>';
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					
					$text=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php');
					if($text){
						$pos=strpos($text,'?>');
						if($pos===FALSE){
							$str = ' require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zyter.smtp/classes/general/cmodulezytersmtp.php");';
						}				
					}	
					file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php',
					$str, FILE_APPEND);
					break;
				}
				closedir($dir);
			}
		}
	return true;
	}
	
	function UnInstallFiles()
	{	
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php'))
			{
				$file = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php', 'r');
				$text = fread($file, filesize($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php'));
				fclose($file);
				$file = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/init.php', 'w');
				fwrite($file, str_replace('<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zyter.smtp/classes/general/cmodulezytersmtp.php");?>', '', $text));
				fclose($file);
			}
		return true;
	}
	
	

}
?>