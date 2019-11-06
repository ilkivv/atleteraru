<?
global $MESS;
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zyter.smtp/prolog.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

$module_id = "zyter.smtp";
CModule::IncludeModule($module_id);

$MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);

if($MOD_RIGHT>="R"):

	$arAllOptions = array(
		"home" => array(
			array("smtp_use", GetMessage("opt_smtp_check"),"N", array("checkbox", "Y")),
		),
		"main" => array(
			array("smtp_user", GetMessage("opt_smtp_user_login"), "", Array("text", 35)),
			array("smtp_password", GetMessage("opt_smtp_user_password"), "", Array("password", 35)),
			array("smtp_nameMail", GetMessage("opt_smtp_nameMail"), "", Array("text", 35)),
			array("smtp_log", GetMessage("opt_smtp_log"),"N", array("checkbox", "Y")),
			array("smtp_posting", GetMessage("opt_smtp_posting"),"N", array("checkbox", "Y")),
			Array("note"=>GetMessage("opt_smtp_posting_note")),
		),
		"connection_settings" => Array(
			array("smtp_host", GetMessage("opt_smtp_host"), "", Array("text", 35)),
			array("smtp_port", GetMessage("opt_smtp_host_port"), "25", array("text", 5)),
			array("smtp_host_timeout", GetMessage("opt_smtp_host_timeout"), "6", array("text", 5)),
			array("smtp_type_encryption", GetMessage("opt_smtp_type_encryption"), "", Array("selectbox", Array("ssl"=>GetMessage("opt_smtp_type_encryption_ssl"), "tls"=>GetMessage("opt_smtp_type_encryption_tls")))),	
		)
	);

	
	
//********************************
$message = null;
if($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["Update"])>0 && ($USER->CanDoOperation('edit_other_settings') && $USER->CanDoOperation('edit_groups')) && check_bitrix_sessid())
{
	COption::SetOptionString($module_id, "smtp_use", $_POST["smtp_use"]);
	COption::SetOptionString($module_id, "smtp_user", $_POST["smtp_user"]);
	COption::SetOptionString($module_id, "smtp_password", $_POST["smtp_password"]);
	COption::SetOptionString($module_id, "smtp_nameMail", $_POST["smtp_nameMail"]);	
	COption::SetOptionString($module_id, "smtp_log", $_POST["smtp_log"]);
	COption::SetOptionString($module_id, "smtp_posting", $_POST["smtp_posting"]);
	COption::SetOptionString($module_id, "smtp_host", $_POST["smtp_host"]);
	COption::SetOptionString($module_id, "smtp_port", $_POST["smtp_port"]);
	COption::SetOptionString($module_id, "smtp_type_encryption", $_POST["smtp_type_encryption"]);
	$smtp_host_timeout = trim($_POST["smtp_host_timeout"]);
	if(!isset($smtp_host_timeout) || $smtp_host_timeout == ''){
		COption::SetOptionString($module_id, "smtp_host_timeout",6);
		CAdminMessage::ShowMessage(GetMessage("opt_smtp_host_timeout_error"));
	}else
		COption::SetOptionString($module_id, "smtp_host_timeout", trim($_POST["smtp_host_timeout"]));
	
}
//**********************************	
	
	
	
	
if($MOD_RIGHT>="W"):
if($REQUEST_METHOD=="POST" && strlen($Update)>0) 
{ 
	for($i=0; $i<count($arAllOptions); $i++) { 
   	$name=$arAllOptions[$i][0]; 
   	$val=$$name; 

   	if($arAllOptions[$i][3][0]=="checkbox" && $val!="Y") $val="N"; 
   	COption::SetOptionString($module_id, $name, $val, $arAllOptions[$i][1]); 
   	} 
} 
endif; //if($MOD_RIGHT>="W"): 

function ShowParamsHTMLByArray($arParams)
{
	foreach($arParams as $Option)
	{
		__AdmSettingsDrawRow("zyter.smtp", $Option);
	}
}
?>

<?//иниациализация вкладок
$aTabs = array();
$aTabs[] = array("DIV" => "edit0", "TAB" => GetMessage("ZYTER_TAB_EXTRAMAIL_SETTINGSOUTSMTP"), "ICON" => "extramail_settings", "TITLE" => GetMessage("ZYTER_TAB_TITLE_EXTRAMAIL_SETTINGSOUTSMTP"));
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_TAB_RIGHTS"));
if(COption::GetOptionString("zyter.smtp","smtp_log"))
$aTabs[] = array("DIV" => "edit3", "TAB" => GetMessage("MAIN_TAB_LOG"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_TAB_LOG"));	


$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&lang=<?echo LANG?>"> 

<?$tabControl->BeginNextTab();?>
	 <? ShowParamsHTMLByArray($arAllOptions["home"]); ?> 
	<tr class="heading">
		<td colspan="2"><b><?=GetMessage("heading_smtp_main")?></b></td>
	</tr> 
	 <? ShowParamsHTMLByArray($arAllOptions["main"]); ?> 
 	<tr class="heading">
		<td colspan="2"><b><?=GetMessage("heading_smtp_connection_settings")?></b></td>
	</tr> 
	 <? ShowParamsHTMLByArray($arAllOptions["connection_settings"]); ?> 
	<tr>
		<td class="adm-detail-valign-top adm-detail-content-cell-l" width="50%"><?echo GetMessage("opt_smtp_connection_test")?></td>
		<td width="50%" class="adm-detail-content-cell-r">
			<?//echo GetMessage("opt_smtp_connection_default")?>

			
			<input type="button" name="" value="<?echo GetMessage("opt_smtp_connection_test_bott")?>" title="<?echo GetMessage("opt_smtp_connection_test_bott")?>" onclick="window.location='/bitrix/admin/settings.php?lang=<?=LANGUAGE_ID?>&mid=<?=urlencode($module_id)?>&smtp_connection_test=Y&<?=bitrix_sessid_get()?>'">
			<?
				if($_SERVER["REQUEST_METHOD"] == "GET"  && $_REQUEST["smtp_connection_test"] <> '' && check_bitrix_sessid()){
									
					require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/zyter.smtp/classes/general/phpmailer/PHPMailerAutoload.php');
					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Timeout  = 4 ;
					$mail->Host = COption::GetOptionString("zyter.smtp","smtp_host"); 
					$mail->SMTPAuth = true;
					$mail->Username = COption::GetOptionString("zyter.smtp","smtp_user");
					$mail->Password = COption::GetOptionString("zyter.smtp","smtp_password");
					$mail->SMTPSecure = COption::GetOptionString("zyter.smtp","smtp_type_encryption");
					$mail->Port = COption::GetOptionString("zyter.smtp","smtp_port");					
					$body = "Test connection";
					if(check_email(COption::GetOptionString("zyter.smtp","smtp_user"))){
						$AddAddress = COption::GetOptionString("zyter.smtp","smtp_user");
					}else 
						$AddAddress = COption::GetOptionString("main","email_from");
					$mail->SetFrom(COption::GetOptionString("zyter.smtp","smtp_user"), 'Test name');
					$mail->AddAddress($AddAddress, "Test name");
					$mail->setLanguage(LANGUAGE_ID,$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/zyter.smtp/lang/ru/language/');
					$mail->Subject = "Test connection";
					$mail->Body    = $body;
					if(!$mail->send()) {
						if(COption::GetOptionString("zyter.smtp","smtp_log")){
							$f=fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zyter.smtp/log.txt", "a+");
							fputcsv($f, array(date("d.m.Y H:i:s"),$mail->ErrorInfo));
							fclose($f);
						}
						CAdminMessage::ShowMessage(GetMessage("opt_smtp_connection_error").' '.$mail->ErrorInfo);
					} else {
						CAdminMessage::ShowNote(GetMessage("opt_smtp_connection_success"));
					}
				}
				
			?>
		</td>
	</tr>	 


<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?if(COption::GetOptionString("zyter.smtp","smtp_log")):?>
<?$tabControl->BeginNextTab();?>
<?
	$LOGPATH  = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zyter.smtp/log.txt";
	$log = file_get_contents($LOGPATH);

	if($log && $_REQUEST["smtp_log_clean"] == ''){

	
		?>
<div id="blockstat">		

<?

$sTableID = "BITPROFI_by_date";
$oSort = new CAdminSorting($sTableID);
$lAdmin = new CAdminList($sTableID, $oSort);

$arHeaders[]=
	array(	"id"	=>"count",
		"content"	=>'',
		"sort"		=>"count",
		"align"		=>"left",
		"default"	=>true
	);
$arHeaders[]=
	array(	"id"	=>"date",
		"content"	=>GetMessage('ZYTER_EXTRAMAIL_ADMIN_DATE'),
		"sort"		=>"date",
		"align"		=>"left",
		"default"	=>true
	);
$arHeaders[]=
	array(	"id"	=>"name",
		"content"	=>GetMessage('ZYTER_EXTRAMAIL_ADMIN_NAME'),
		"sort"		=>"name",
		"align"		=>"left",
		"default"	=>true
	);


$lAdmin->AddHeaders($arHeaders);
$rowI = 1;
if (($handle = fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zyter.smtp/log.txt", "r")) !== FALSE ) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

		$row =& $lAdmin->AddRow($rowI, $data);
		$row->AddViewField("count", $rowI);
		$row->AddViewField("date", $data[0]);
		$row->AddViewField("name", $data[1]);
		$rowI++;
    }
    fclose($handle);
}
?>
	<tr>
		<td colspan="2">
			<div class="smtp_log_list">
				<?
				$lAdmin->Display();
				?>
			</div>
		</td>
	</tr>		
</div><!-- id="blockstat" -->
		
	<tr>
		<td width="50%" class="adm-detail-content-cell-r">		
			<input type="button" name="" value="<?echo GetMessage("opt_smtp_log_clean_bott")?>" title="<?echo GetMessage("opt_smtp_log_clean_bott")?>" onclick="window.location='/bitrix/admin/settings.php?lang=<?=LANGUAGE_ID?>&mid=<?=urlencode($module_id)?>&smtp_log_clean=Y&<?=bitrix_sessid_get()?>'">
		</td>
	</tr>
	<?
				
	}else
		echo GetMessage("opt_smtp_log_message");
	
	if($log)
		if($_SERVER["REQUEST_METHOD"] == "GET"  && $_REQUEST["smtp_log_clean"] <> '' && check_bitrix_sessid() && file_get_contents($LOGPATH)){
			file_put_contents($LOGPATH, '');
		}		
		

?>
<?endif;?>	 
<?$tabControl->Buttons();?>
<input <?if (!$USER->CanDoOperation('edit_other_settings')) echo "disabled" ?> type="submit" name="Apply" value="<?echo GetMessage("MAIN_OPT_APPLY")?>" title="<?echo GetMessage("MAIN_OPT_APPLY_TITLE")?>"<?if($_REQUEST["back_url_settings"] == ""):?>  class="adm-btn-save"<?endif?>>
<!--<input type="submit" name="Update" <?if ($MOD_RIGHT<"W") echo "disabled" ?> value="<?echo GetMessage("MAIN_SAVE")?>">-->
<input type="reset" name="reset" value="<?echo GetMessage("MAIN_RESET")?>">
<input type="hidden" name="Update" value="Y">

<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form> 
<?endif;?>
