<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/zyter.smtp/classes/general/phpmailer/PHPMailerAutoload.php');

if (COption::GetOptionString("zyter.smtp","smtp_use") == "Y"){
	function custom_mail($to, $subject, $message, $additional_headers, $additional_parameters){
	

		$mass_header = decode_header($additional_headers);
		if(isset($mass_header['x-bitrix-posting']) && COption::GetOptionString("zyter.smtp","smtp_posting")){
			if($additional_parameters!="")
				return @mail($to, $subject, $message, $additional_headers, $additional_parameters);

			return @mail($to, $subject, $message, $additional_headers);			
		} 	
		
		$type 		= $ctype = $mass_header['content-type'];
		$ctype 		= split(";",$ctype);
		$types 		= split("/",$ctype[0]);
		$maintype	= trim(strtolower($types[0])); // text или multipart
		$subtype 	= trim(strtolower($types[1])); // а это подтип(plain, html, mixed)
		if(isset($mass_header['bcc']))
			$bcc = explode(",", $mass_header['bcc']);		

		if($maintype=="text")
		{
			if($ctype[0] == "text/plain" ) {
				$isHTML = false;
			}
			// если это html
			elseif($ctype[0] == "text/html") {
				$isHTML = true;
			}
			$bodyText = compile_body($message,$mass_header["content-transfer-encoding"],$mass_header["content-type"]);		
		}

		elseif($maintype=="multipart" and ereg($subtype,"signed,mixed,related"))//-----------------------------------с вложение--------------------------------------------------
		{		
			$boundary=get_boundary($mass_header['content-type']);
			$part = split_parts($boundary,$message);					
			$arFiles = array(); 
			for($i=0;$i<count($part);$i++) {
				$email = fetch_structure($part[$i]);
				$header = $email["header"];
				$body = $email["body"];
				$headers = decode_header($header);
				$ctype = $headers["content-type"];
				$cid = $headers["content-id"];				
				$Actype = split(";",$headers["content-type"]);	
				$types = split("/",$Actype[0]);					
				$rctype = strtolower($Actype[0]);
				$CharSet = str_replace('charset=', '', $Actype[1]);	
				$is_download = (ereg("name=",$headers["content-disposition"].$headers["content-type"]) || $headers["content-id"] != "" || $rctype == "message/rfc822");
				
				if($rctype == "text/plain" && !$is_download) {
					$bodyText = compile_body($body,$headers["content-transfer-encoding"],$headers["content-type"]);
					$isHTML = false;
				}
				
				// если это html
				elseif($rctype == "text/html" && !$is_download) {
					$bodyText = compile_body($body,$headers["content-transfer-encoding"],$headers["content-type"]);
					$isHTML = true;
				}
				
				elseif($is_download) {
					$filename = '';
					$cdisp = $headers["content-disposition"];
					$ctype = $headers["content-type"];
					$ctype2 = explode(";",$ctype);
					$ctype2 = $ctype2[0];
					$Atype = split("/",$ctype);
					$Acdisp = split(";",$cdisp);
					$fname = $Acdisp[1];
					if(ereg("filename=(.*)",$fname,$regs))
					$filename = $regs[1];
					if($filename == "" && ereg("name=(.*)",$ctype,$regs))
					$filename = $regs[1];
					$filename = str_replace('"', '', $filename);
					$filename = trim(decode_mime_string($filename));
					//$filename = imap_mime_header_decode($filename);
					//$filename = $filename[0]->text;
					$body = compile_body($body,$headers["content-transfer-encoding"],$ctype);
					$arFiles[] = array(
						"name" => $filename,
						"body" => $body,
					);
				}
				
			}
		}
				
		$smtpServerHost         = COption::GetOptionString("zyter.smtp","smtp_host");
		$smtpServerHostTypeEnc 	= COption::GetOptionString("zyter.smtp","smtp_type_encryption");
		$smtpServerHostPort     = COption::GetOptionString("zyter.smtp","smtp_port");
		$smtpServerUser         = COption::GetOptionString("zyter.smtp","smtp_user");
		$smtpServerUserPassword = COption::GetOptionString("zyter.smtp","smtp_password"); 
		$smtp_log				= COption::GetOptionString("zyter.smtp","smtp_log"); 
		$Timeout				= COption::GetOptionString("zyter.smtp","smtp_host_timeout"); 
		$smtp_nameMail         	= COption::GetOptionString("zyter.smtp","smtp_nameMail");
		$FromName 				= COption::GetOptionString("main","site_name");
		

		if($CharSet == ''){
			$charset      = strripos($additional_headers, 'charset=UTF-8');
			if($charset === false){
				$CharSet = 'windows-1251';
			}else
				$CharSet = 'UTF-8';				
		}
				
		
		$mail = new PHPMailer;
		$mail->Timeout  =   $Timeout;
		$mail->SMTPDebug = false;
		$mail->isSMTP();
		$mail->CharSet  = $CharSet;
		$mail->setLanguage(LANGUAGE_ID,$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/zyter.smtp/lang/ru/language/');
		// Set mailer to use SMTP
		$mail->Host = $smtpServerHost;  					// Specify main and backup SMTP servers
		$mail->SMTPAuth = true; 							// Enable SMTP authentication
		$mail->Username = $smtpServerUser; 					// SMTP username
		$mail->Password = $smtpServerUserPassword; 			// SMTP password
		$mail->SMTPSecure = $smtpServerHostTypeEnc; 		// Enable TLS encryption, `ssl` also accepted			
		$mail->Port = $smtpServerHostPort;


		$mail->From = $smtp_nameMail;
		$mail->FromName = $FromName;
				
		if(count($arFiles)>0) 								// Добавить вложения
			foreach($arFiles as $arItems){
				$mail->addStringAttachment($arItems['body'], $arItems['name']);	
			}
		
		$mail->isHTML($isHTML);
		$mail->Subject = $subject;
		$mail->Body    = $bodyText;
		$mail->addAddress($to);
		if(isset($bcc))
			foreach($bcc as $key => $arMail)
				$mail->AddBCC($arMail);			


		if(!$mail->send()) {
			if($smtp_log){
				$f=fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zyter.smtp/log.txt", "a+");
				fputcsv($f, array(date("d.m.Y H:i:s"),$mail->ErrorInfo));
				fclose($f);
			}
			
			return false;
		} else {
			return true;
		}
		$mail->clearAddresses();
		$mail->ClearCustomHeaders();
	}	
}
	

	function decode_header($header) {
		$headers = explode("\n",$header);
		$decodedheaders = Array();
		for($i=0;$i<count($headers);$i++) {
			$thisheader = trim($headers[$i]);
			if(!empty($thisheader))
			if(!ereg("^[A-Z0-9a-z_-]+:",$thisheader))
			$decodedheaders[$lasthead] .= " $thisheader";
			else {
				$dbpoint = strpos($thisheader,":");
				$headname = strtolower(substr($thisheader,0,$dbpoint));
				$headvalue = trim(substr($thisheader,$dbpoint+1));
				if($decodedheaders[$headname] != "") $decodedheaders[$headname] .= "; $headvalue";
				else $decodedheaders[$headname] = $headvalue;
				$lasthead = $headname;
			}
		}
		return $decodedheaders;
	}

	function get_boundary($ctype){
		if(preg_match('/boundary[ ]?=[ ]?(["]?.*)/i',$ctype,$regs)) {
			//$boundary = preg_replace('/^\"(.*)\"$/', "\1", $regs[1]);
			$str = str_replace('"', '', $regs[1]);
			$boundary = $str;
			return trim("$boundary");
		}
	}

	function split_parts($boundary,$body) {
		$startpos = strpos($body,$boundary)+strlen($boundary)+1;
		$lenbody = strpos($body,"\n$boundary--") - $startpos;
		$body = substr($body,$startpos,$lenbody);
		return explode($boundary."\n",$body);
	}
	function fetch_structure($email) {
		$ARemail = Array();
		$separador = "\n\n";
		$header = trim(substr($email,0,strpos($email,$separador)));
		$bodypos = strlen($header)+strlen($separador);
		$body = substr($email,$bodypos,strlen($email)-$bodypos);
		$ARemail["header"] = $header;
		$ARemail["body"] = $body;
		return $ARemail;
	}
	function compile_body($body,$enctype,$ctype) {
		$enctype = explode(" ",$enctype); $enctype = $enctype[0];
		if(strtolower($enctype) == "base64")
		$body = base64_decode($body);
		elseif(strtolower($enctype) == "quoted-printable")
		$body = quoted_printable_decode($body);
		if(ereg("koi8", $ctype)) $body = convert_cyr_string($body, "k", "w");
		return $body;
	}
	function decode_mime_string($subject) {
		$string = $subject;
		if(($pos = strpos($string,"=?")) === false) return $string;
		while(!($pos === false)) {
			$newresult .= substr($string,0,$pos);
			$string = substr($string,$pos+2,strlen($string));
			$intpos = strpos($string,"?");
			$charset = substr($string,0,$intpos);
			$enctype = strtolower(substr($string,$intpos+1,1));
			$string = substr($string,$intpos+3,strlen($string));
			$endpos = strpos($string,"?=");
			$mystring = substr($string,0,$endpos);
			$string = substr($string,$endpos+2,strlen($string));
			if($enctype == "q") $mystring = quoted_printable_decode(ereg_replace("_"," ",$mystring));
			else if ($enctype == "b") $mystring = base64_decode($mystring);
			$newresult .= $mystring;
			$pos = strpos($string,"=?");
		}

		$result = $newresult.$string;
		if(ereg("koi8", $subject)) $result = convert_cyr_string($result, "k", "w");
		if(ereg("KOI8", $subject)) $result = convert_cyr_string($result, "k", "w");
		return $result;
	}
?>