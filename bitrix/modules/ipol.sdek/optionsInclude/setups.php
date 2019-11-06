<?
//платежные системы
$PayDefault = COption::GetOptionString($module_id,'paySystems','Y');
if($PayDefault != 'Y')
	$tmpPaySys=unserialize($PayDefault);

$paySysS=CSalePaySystem::GetList(array(),array('ACTIVE'=>'Y'));
$paySysHtml='<select name="paySystems[]" multiple size="5">';
while($paySys=$paySysS->Fetch()){
	$paySysHtml.='<option value="'.$paySys['ID'].'" ';
	if($PayDefault == 'Y') {
		$name = strtolower($paySys['NAME']);
		if( strpos($name, GetMessage('IPOLSDEK_cashe')) === false && 
			strpos($name, GetMessage('IPOLSDEK_cashe2')) === false && 
			strpos($name, GetMessage('IPOLSDEK_cashe3')) === false)
			$paySysHtml.='selected';
	}
	else {
		if(in_array($paySys['ID'],$tmpPaySys))
			$paySysHtml.='selected';
	}
	$paySysHtml.='>'.$paySys['NAME'].'</option>';
}
$paySysHtml.="</select>";
?>
<link href="/bitrix/js/<?=$module_id?>/jquery-ui.css?<?=mktime()?>" type="text/css"  rel="stylesheet" />
<link href="/bitrix/js/<?=$module_id?>/jquery-ui.structure.css?<?=mktime()?>" type="text/css"  rel="stylesheet" />
<script src='/bitrix/js/<?=$module_id?>/jquery-ui.js?<?=mktime()?>' type='text/javascript'></script>
<?=sdekdriver::getModuleExt('courierTimeCheck')?>
<style>
	.PropHint { 
		background: url("/bitrix/images/ipol.sdek/hint.gif") no-repeat transparent;
		display: inline-block;
		height: 12px;
		position: relative;
		width: 12px;
	}
	.b-popup { 
		background-color: #FEFEFE;
		border: 1px solid #9A9B9B;
		box-shadow: 0px 0px 10px #B9B9B9;
		display: none;
		font-size: 12px;
		padding: 19px 13px 15px;
		position: absolute;
		top: 38px;
		width: 300px;
		z-index: 50;
	}
	.b-popup .pop-text { 
		margin-bottom: 10px;
		color:#000;
	}
	.pop-text i {color:#AC12B1;}
	.b-popup .close { 
		background: url("/bitrix/images/ipol.sdek/popup_close.gif") no-repeat transparent;
		cursor: pointer;
		height: 10px;
		position: absolute;
		right: 4px;
		top: 4px;
		width: 10px;
	}
	.IPOLSDEK_clz{
		background:url(/bitrix/panel/main/images/bx-admin-sprite-small.png) 0px -2989px no-repeat; 
		width:18px; 
		height:18px;
		cursor: pointer;
		margin-left:100%;
	}
	.IPOLSDEK_clz:hover{
		background-position: -0px -3016px;
	}
	.errorText{
		color:red;
		font-size:11px;
	}
	.IPOLSDEK_sender{
		border: 1px dotted black !important;
		margin: 5px !important;
		float: left;
	}
	.subHeading td{
		padding: 8px 70px 10px !important;
		background-color: #EDF7F9;
		border-top: 11px solid #F5F9F9;
		border-bottom: 11px solid #F5F9F9;
		color: #4B6267;
		font-size: 14px;
		font-weight: bold;
		text-align: center !important;
		text-shadow: 0px 1px #FFF;
	}
	.IPOLSDEK_errTextCourier{
		font-size:10px;
		max-width:300px;
		margin:auto;
		color: red;
	}
	.IPOLSDEK_sepTable{
		width: 50%;
		float: left;
		text-align: center;
		font-weight: bold;
	}
</style>
<script>
	<?=sdekdriver::getModuleExt('mask_input')?>
	var IPOLSDEK_senderCities = [<?=$senderCitiesJS?>];

	function ipol_popup_virt(code, info){
		var offset = $(info).position().top;
		var LEFT = $(info).offset().left;		
		
		var obj;
		if(code == 'next') 	obj = $(info).next();
		else  				obj = $('#'+code);
		
		LEFT -= parseInt( parseInt(obj.css('width'))/2 );
		
		obj.css({
			top: (offset+15)+'px',
			left: LEFT,
			display: 'block'
		});	
		return false;
	}
	
	function IPOLSDEK_serverShow(){
		$('.IPOLSDEK_service').each(function(){
			$(this).css('display','table-row');
		});
		$('[onclick^="IPOLSDEK_serverShow("]').css('cursor','auto');
		$('[onclick^="IPOLSDEK_serverShow("]').css('textDecoration','none');
	}
	
	function IPOLSDEK_sbrosSchet(){
		if(confirm('<?=GetMessage('IPOLSDEK_OTHR_schet_ALERT')?>'))
			$.ajax({
				url:'/bitrix/js/<?=$module_id?>/ajax.php',
				type:'POST',
				data: 'action=killSchet',
				success: function(data){
					if(data=='1')
					{
						alert('<?=GetMessage("IPOLSDEK_OTHR_schet_DONE")?>');
						$("[onclick^='IPOLSDEK_sbrosSchet(']").parent().html('0');
					}
					else
						alert('<?=GetMessage("IPOLSDEK_OTHR_schet_NONE")?>'+data);
				}
			});
	}
	
	function IPOLSDEK_clrUpdt(){
		if(confirm('<?=GetMessage('IPOLSDEK_OPT_clrUpdt_ALERT')?>'))
		{
			$('.IPOLSDEK_clz').css('display','none');
			$.ajax({
				url:'/bitrix/js/<?=$module_id?>/ajax.php',
				type:'POST',
				data: 'action=killUpdt',
				success: function(data){
					if(data=='done')
						$("#IPOLSDEK_updtPlc").replaceWith('');
					else
					{
						$('.IPOLSDEK_clz').css('display','');
						alert('<?=GetMessage("IPOLSDEK_OPT_clrUpdt_ERR")?>');
					}
				}
			});
		}
	}

	function IPOLSDEK_syncList(params){
		var dataObj = {text:false,status:false};
		var reqObj  = {action: 'callUpdateList'};
		if(typeof(params) == 'undefined'){
			$("[onclick='IPOLSDEK_syncList()']").css('display','none');
			dataObj.text = '<?=GetMessage("IPOLSDEK_OTHR_lastModList_START")?>';
		}else{
			dataObj.text   = params.text;
			dataObj.status = params.result;
			if(params.result == 'end')
				reqObj['citiesDone'] = true;
		}

		if(dataObj.text){
			if($('#IPOLSDEK_syncInfo').length == 0)
				$("[onclick='IPOLSDEK_syncList()']").after('<span id="IPOLSDEK_syncInfo"></span>');
			$('#IPOLSDEK_syncInfo').html(dataObj.text);
			if(dataObj.status == 'error')
				$('#IPOLSDEK_syncInfo').css('color','red');
		}

		if(dataObj.status != 'error' && dataObj.status != 'done'){
			console.log("SEND",reqObj);
			$.ajax({
				url:"/bitrix/js/<?=$module_id?>/ajax.php",
				type:"POST",
				dataType: 'json',
				data:reqObj,
				success: IPOLSDEK_syncList,
				error: function(a,b,c){alert("sync "+b+" "+c);}
			});
		}
		else
			if(dataObj.status == 'done'){
				alert(dataObj.text);
				window.location.reload();
			}
	}
	
	function IPOLSDEK_syncOrdrs()
	{
		$('[onclick="IPOLSDEK_syncOrdrs()"]').css('display','none');
		$.post(
			"/bitrix/js/<?=$module_id?>/ajax.php",
			{'action':'callOrderStates'},
			function(data){
				$('#IPOLSDEK_SO').parent().html(data+"&nbsp;<input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_getOutLst_BUTTON')?>' id='IPOLSDEK_SO' onclick='IPOLSDEK_syncOrdrs()'/>");
				IPOLSDEK_table.getTable();
			}
		);
	}
	
	function IPOLSDEK_logoff(){
		$("[onclick='IPOLSDEK_logoff()']").attr('disabled','disabled');
		if(confirm('<?=GetMessage("IPOLSDEK_LBL_ISLOGOFF")?>'))
			$.post(
				"/bitrix/js/<?=$module_id?>/ajax.php",
				{'action':'logoff'},
				function(data){
					window.location.reload();
				}
			);
		else
			$("[onclick='IPOLSDEK_logoff()']").removeAttr('disabled');
	}
	
	function IPOLSDEK_addCityHold(){
		var maxCityCnt = parseInt('<?=count($IPOLSDEK_list['Region'])?>');
		var ttlCity    = $('[name="addHoldTerm[]"]').length;
		if(ttlCity>=maxCityCnt)
			return;
		
		$('[name="addHoldTerm[]"]:last').closest('tr').after('<tr><td class="adm-detail-content-cell-l"><?=$addHold?></td><td class="adm-detail-content-cell-r"><input type="text" name="addHoldTerm[]"></td></tr>');
		
		if(ttlCity+1>=maxCityCnt)
			$("[onclick='IPOLSDEK_addCityHold()']").css('display','none');
	}
	
	function IPOLSDEK_onTermChange(){
		var day = parseInt($('[name="termInc"]').val());
		if(isNaN(day))
			day = '';			
		$('[name="termInc"]').val(day);
	}
	
	function IPOLSDEK_clearCache(){
		$.post(
			"/bitrix/js/<?=$module_id?>/ajax.php",
			{'action':'clearCache'},
			function(data){
				alert("<?=GetMessage('IPOLSDEK_LBL_CACHEKILLED')?>")
			}
		);
	}
	function IPOLSDEK_rewriteCities(){
		if(confirm("<?=GetMessage('IPOLSDEK_LBL_SURETOREWRITE')?>")){
			$('#IPOLSDEK_REWRITECITIES').attr('disabled','disabled');
			$.post(
				"/bitrix/js/<?=$module_id?>/ajax.php",
				{'action':'goSlaughterCities'},
				function(data){
					if(data.indexOf('done')===-1)
						alert(data);
					else{
						alert('<?=GetMessage("IPOLSDEK_UPDT_DONE").date("d.m.Y H:i")?>');
						window.location.reload();
					}
				}
			);
		}
	}
	function IPOLSDEK_importCities(){
		$('#IPOLSDEK_IMPORTCITIES').attr('disabled','disabled');
		$.post(
			"/bitrix/js/<?=$module_id?>/ajax.php",
			{'action':'setImport'},
			function(data){
				window.location.reload();
			}
		);
	}
	// отправители
	function IPOLSDEK_actSender(wat){
		if(wat.attr('checked'))
			alert('<?=GetMessage("IPOLSDEK_LABEL_sendersWarning")?>');
	}
	<?
		$svdCt = sdekOption::senders();
	?>
	function IPOLSDEK_addSender(settings){
		if(typeof(settings) == 'undefined') settings = {senderName:"",cityName:"",courierCity:'',courierStreet:'',courierHouse:'',courierFlat:'',courierPhone:'',courierName:'',courierTimeBeg:'',courierTimeEnd:''};
		if(typeof(settings.courierComment) == 'undefined') settings.courierComment = '';
		var cnt = $('.IPOLSDEK_sender').length;
		var HTML = "<table class='IPOLSDEK_sender' id='IPOLSDEK_added'>";
			HTML += "<tr><td><?=GetMessage("IPOLSDEK_LBL_SENDER")?></td><td><input type='text' name='senders["+cnt+"][senderName]' value='"+settings.senderName+"'></td></tr>";
			HTML += "<tr><td><?=GetMessage("IPOLSDEK_LBL_COURIERTIME")?></td><td><input type='text' style='width:56px' name='senders["+cnt+"][courierTimeBeg]' value='"+settings.courierTimeBeg+"'> - <input type='text' style='width:56px' name='senders["+cnt+"][courierTimeEnd]' value='"+settings.courierTimeEnd+"'></td></tr>";
			HTML += "<tr><td><?=GetMessage("IPOLSDEK_JS_SOD_courierCity")?></td><td><input type='text' class='IPOLSDEK_senderCity' value='"+settings.cityName+"'/><input type='hidden' name='senders["+cnt+"][courierCity]' value='"+settings.courierCity+"'></td></tr>";
			HTML += "<tr><td><?=GetMessage("IPOLSDEK_JS_SOD_courierStreet")?></td><td><input type='text' name='senders["+cnt+"][courierStreet]' value='"+settings.courierStreet+"'></td></tr>";
			HTML += "<tr><td><?=GetMessage("IPOLSDEK_JS_SOD_courierHouse")?></td><td><input type='text' name='senders["+cnt+"][courierHouse]' value='"+settings.courierHouse+"'></td></tr>";
			HTML += "<tr><td><?=GetMessage("IPOLSDEK_JS_SOD_courierFlat")?></td><td><input type='text' name='senders["+cnt+"][courierFlat]' value='"+settings.courierFlat+"'></td></tr>";
			HTML += "<tr><td><?=GetMessage("IPOLSDEK_JS_SOD_courierPhone")?></td><td><input type='text' class='IPOLSDEK_phone' name='senders["+cnt+"][courierPhone]' value='"+settings.courierPhone+"'></td></tr>";
			HTML += "<tr><td><?=GetMessage("IPOLSDEK_JS_SOD_courierName")?></td><td><input type='text' name='senders["+cnt+"][courierName]' value='"+settings.courierName+"'></td></tr>";
			HTML += "<tr><td><?=GetMessage("IPOLSDEK_JS_SOD_courierComment")?></td><td><input type='text' name='senders["+cnt+"][courierComment]' value='"+settings.courierComment+"'></td></tr>";
			HTML += "</table>";
		$('#IPOLSDEK_sendersPlace').append(HTML);
		IPOLSDEK_courierSetEvents('#IPOLSDEK_added');
		$("#IPOLSDEK_added").removeAttr('id');
	}
	function IPOLSDEK_courierChangeCity(ev,ui){
		window.setTimeout(function(){
				$(arguments[0]).val(arguments[1]);
			},100,ev.target,ui.item.label);
		$(ev.target).siblings("[type='hidden']").val(ui.item.value);
	}
	function IPOLSDEK_courierSetEvents(mark){
		var chz = (typeof(mark.html) == 'undefined') ? $(mark) : mark;
		chz.find(".IPOLSDEK_senderCity").autocomplete({
		  source: IPOLSDEK_senderCities,
		  select: function(ev,ui){IPOLSDEK_courierChangeCity(ev,ui);}
		});
		chz.find(".IPOLSDEK_phone").mask("99999999999");
		chz.find("[name*='[courierTimeBeg]']").mask("29:59").on('change',IPOLSDEK_courierTimeChanged);
		chz.find("[name*='[courierTimeEnd]']").mask("29:59").on('change',IPOLSDEK_courierTimeChanged);
	}
	function IPOLSDEK_courierTimeChanged(link){
		var tr = $(link.delegateTarget).parents('tr:first');
		var check = IPOLSDEK_courierTimeCheck(tr.find("[name*='[courierTimeBeg]']").val(),tr.find("[name*='[courierTimeEnd]']").val());

		if(check === true || (!tr.find('[name*="[courierTimeBeg]"]').val() && !tr.find('[name*="[courierTimeEnd]"]').val())){
			tr.find('.IPOLSDEK_badInput').removeClass('IPOLSDEK_badInput');
			tr.parent().find('.IPOLSDEK_errTextCourier').html('');
		}else{
			if(check.error == 'start' || check.error == 'both')
				tr.find('[name*="[courierTimeBeg]"]').addClass('IPOLSDEK_badInput');
			if(check.error == 'end' || check.error == 'both')
				tr.find('[name*="[courierTimeEnd]"]').addClass('IPOLSDEK_badInput');
			tr.parent().find('.IPOLSDEK_errTextCourier').html(check.text);
		}
	}
	// конец отправители
	var IPOLSDEK_depature = {
		add: function(){
			$('#IPOLSDEK_addDeparturePlace').append("<div><input type='text' class='IPOLSDEK_addDeparture rescent'><input type='hidden' name='addDeparture[]'></div>");
			IPOLSDEK_depature.input($('.IPOLSDEK_addDeparture.rescent'));
			$('.IPOLSDEK_addDeparture.rescent').removeClass('rescent');
		},
		input: function(wat){
			wat.autocomplete({
			  source: IPOLSDEK_senderCities,
			  select: IPOLSDEK_depature.onSelect
			});
		},
		init: function(){
			$('.IPOLSDEK_addDeparture').each(function(){IPOLSDEK_depature.input($(this));});
		},
		onSelect: function(ev,ui){
			window.setTimeout(function(){
				$(arguments[0]).val(arguments[1]);
			},100,ev.target,ui.item.label);
			$(ev.target).siblings("[type='hidden']").val(ui.item.value);
		},
		delete: function(wat){
			wat.parent().replaceWith('');
		}
	};
	$(document).ready(function(){
		$('[name="termInc"]').on('keyup',IPOLSDEK_onTermChange);
		if($('.IPOLSDEK_sender').length)
			$('.IPOLSDEK_sender').each(function(){IPOLSDEK_courierSetEvents($(this))});
		IPOLSDEK_depature.init();
	});
</script>

<?
foreach(array("depature","showInOrders","realSeller","addDeparture","shipments","prntActOrdr","numberOfPrints","address","pvzPicker","hideNal","autoSelOne","profilesMode","cntExpress","AS","statusSTORE","statusTRANZT","statusCORIER","tarifs","dostTimeout","timeoutRollback","TURNOFF","TARSHOW","useOldServer") as $code)
	sdekOption::placeHint($code);

$deadServerCheck = COption::GetOptionString($module_id,'sdekDeadServer',false);
if($deadServerCheck && (mktime() - $deadServerCheck) < (COption::GetOptionString($module_id,'timeoutRollback',15) * 60)){?>
	<tr><td colspan='2'>
		<div class="adm-info-message-wrap adm-info-message-red">
		  <div class="adm-info-message">
			<div class="adm-info-message-title"><?=GetMessage('IPOLSDEK_DEAD_SERVER_HEADER')?></div>
				<?=GetMessage('IPOLSDEK_DEAD_SERVER_TITLE')?>&nbsp;<?=date('H:i:s d.m.Y',$deadServerCheck)?>.
			<div class="adm-info-message-icon"></div>
		  </div>
		</div>
	</td></tr>
<?}

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/errorLog.txt")){
	$errorStr=file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/errorLog.txt");
	if(strlen($errorStr)>0){?>
		<tr><td colspan='2'>
			<div class="adm-info-message-wrap adm-info-message-red">
			  <div class="adm-info-message">
				<div class="adm-info-message-title"><?=GetMessage('IPOLSDEK_FNDD_ERR_HEADER')?></div>
					<?=GetMessage('IPOLSDEK_FNDD_ERR_TITLE')?>
				<div class="adm-info-message-icon"></div>
			  </div>
			</div>
		</td></tr>
	<?}
}
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/hint.txt")){
	$updateStr=file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/hint.txt");
	if(strlen($updateStr)>0){?>
		<tr id='IPOLSDEK_updtPlc'><td colspan='2'>
			<div class="adm-info-message-wrap">
				<div class="adm-info-message" style='color: #000000'>
					<div class='IPOLSDEK_clz' onclick='IPOLSDEK_clrUpdt()'></div>
					<?=$updateStr?>
				</div>
			</div>
		</td></tr>
	<?}
}

$dost = sdekdriver::getDelivery();
if($dost){
	if($dost['ACTIVE'] != 'Y'){?>
	<tr><td colspan='2'>
		<div class="adm-info-message-wrap adm-info-message-red">
		  <div class="adm-info-message">
			<div class="adm-info-message-title"><?=GetMessage('IPOLSDEK_NO_ADOST_HEADER')?></div>
				<?=GetMessage('IPOLSDEK_NO_ADOST_TITLE')?>
			<div class="adm-info-message-icon"></div>
		  </div>
		</div>
	</td></tr>
	<?}
}else{?>
	<tr><td colspan='2'>
		<div class="adm-info-message-wrap adm-info-message-red">
		  <div class="adm-info-message">
			<?if($converted){?>
				<div class="adm-info-message-title"><?=GetMessage('IPOLSDEK_NOT_CRTD_HEADER')?></div>
					<?=GetMessage('IPOLSDEK_NOT_CRTD_TITLE')?>				
			<?}else{?>
				<div class="adm-info-message-title"><?=GetMessage('IPOLSDEK_NO_DOST_HEADER')?></div>
					<?=GetMessage('IPOLSDEK_NO_DOST_TITLE')?>
			<?}?>
			<div class="adm-info-message-icon"></div>
		  </div>
		</div>
	</td></tr>
<?}

foreach(array('pickup','courier') as $profile)
	if(!sdekHelper::checkTarifAvail($profile)){?>
		<tr><td colspan='2'>
			<div class="adm-info-message-wrap adm-info-message-red">
			  <div class="adm-info-message">
				<div class="adm-info-message-title"><?=GetMessage("IPOLSDEK_NO_PROFILE_HEADER_$profile")?></div>
					<?=GetMessage('IPOLSDEK_NO_PROFILE_TITLE')?>
				<div class="adm-info-message-icon"></div>
			  </div>
			</div>
		</td></tr>
	<?}
?>

<tr>
	<td align="center"><?=GetMessage("IPOLSDEK_LBL_YLOGIN")?>: <strong><?=COption::GetOptionString($module_id,'logSDEK','If you see this, something really bad have happend.')?></strong></td>
	<td align="center"><input type='button' onclick='IPOLSDEK_logoff()' value='<?=GetMessage('IPOLSDEK_LBL_DOLOGOFF')?>'></td>
</tr>
<tr><td></td><td align="center"><input type='button' onclick='IPOLSDEK_clearCache()' value='<?=GetMessage('IPOLSDEK_LBL_CLRCACHE')?>'></td></tr>

<?//Общие?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_common")?></td></tr>
<?ShowParamsHTMLByArray($arAllOptions["common"]);?>

<?//Печать?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_print")?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?sdekOption::placeFAQ('PRINT')?>
</td></tr>
<?ShowParamsHTMLByArray($arAllOptions["print"]);?>

<?//Габариты товаров по умолчанию?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_HDR_MEASUREMENT_DEF')?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?sdekOption::placeFAQ('GABS')?>
</td></tr>
<?ShowParamsHTMLByArray($arAllOptions["dimensionsDef"]);?>

<?//Свойства заказа?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_HDR_requestProps')?></td></tr>
<tr class="subHeading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_HDR_orderProps')?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?sdekOption::placeFAQ('PROPS')?>
</td></tr>
<?showOrderOptions();?>
<tr class="subHeading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_HDR_itemProps')?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?sdekOption::placeFAQ('IPROPS')?>
</td></tr>
<?ShowParamsHTMLByArray($arAllOptions["itemProps"]);?>
<?//Статусы заказа?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_status")?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?sdekOption::placeFAQ('STATUSES')?>
</td></tr>
<?
	sdekOption::placeStatuses($arAllOptions["status"]);
?>

<?//Виджет?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_vidjet")?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?sdekOption::placeFAQ('WIDGET')?>
</td></tr>
<?ShowParamsHTMLByArray($arAllOptions["vidjet"]);?>

<?//Оформление заказа?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_basket")?></td></tr>
<?ShowParamsHTMLByArray($arAllOptions["basket"]);?>

<?// Доставки?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_delivery")?></td></tr>
<tr><td colspan="2"><?=GetMessage("IPOLSDEK_FAQ_DELIVERY")?></td></tr>

<?//Платежные системы?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_OPT_paySystems")?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?sdekOption::placeFAQ('PAYSYS')?>
</td></tr>
<tr><td colspan="2" style='text-align:center'><?=$paySysHtml?></td></tr>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_addingService")?></td></tr>
<tr><td colspan="2" valign="top" align="center"><table>
	<?//Тарифы?>
	<tr><td colspan="4" valign="top" align="center"><strong><?=GetMessage("IPOLSDEK_OPT_tarifs")?></strong> <a href='#' class='PropHint' onclick='return ipol_popup_virt("pop-tarifs", this);'></a></td></tr>
	<?$arTarifs = sdekdriver::getExtraTarifs();?>
	<tr><th style="width:20px"></th><th><?=GetMessage("IPOLSDEK_TARIF_TABLE_NAME")?></th><th><?=GetMessage("IPOLSDEK_TARIF_TABLE_SHOW")?></th><th><?=GetMessage("IPOLSDEK_TARIF_TABLE_TURNOFF")?></th><th></th></tr>
	<?
	foreach($arTarifs as $tarifId => $tarifOption){?>
		<tr>
			<td style='text-align:center'><?if($tarifOption['DESC']){?><a href='#' class='PropHint' onclick='return ipol_popup_virt("pop-AS<?=$tarifId?>",this);'></a><?}?></td>
			<td><?=$tarifOption['NAME']?></td>
			<td align='center'><input type='checkbox' name='tarifs[<?=$tarifId?>][SHOW]' value='Y' <?=($tarifOption['SHOW']=='Y')?"checked":""?> /></td>
			<td align='center'><input type='checkbox' name='tarifs[<?=$tarifId?>][BLOCK]' value='Y' <?=($tarifOption['BLOCK']=='Y')?"checked":""?> /></td>
			<td>
				<? if($tarifOption['DESC']) {?>
				<div id="pop-AS<?=$tarifId?>" class="b-popup" style="display: none; ">
					<div class="pop-text"><?=$tarifOption['DESC']?></div>
					<div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
				</div>
				<?}?>
			</td>
		</tr>
	<?}?>
	<tr><td colspan='2'><br></td></tr>
</table></td></tr>
	<?//Дополнительные услуги?>
<tr><td colspan="2" valign="top" align="center"><table>
	<tr><td colspan="2" valign="top" align="center"><strong><?=GetMessage("IPOLSDEK_OPT_addingService")?></strong> <a href='#' class='PropHint' onclick='return ipol_popup_virt("pop-AS", this);'></a></td></tr>
	<?$arAddService = sdekdriver::getExtraOptions();?>
	<tr><th></th><th><?=GetMessage("IPOLSDEK_AS_TABLE_NAME")?></th><th><?=GetMessage("IPOLSDEK_AS_TABLE_SHOW")?></th><th><?=GetMessage("IPOLSDEK_AS_TABLE_DEF")?></th><th></th></tr>
	<?foreach($arAddService as $asId => $adOption){?>
		<tr>
			<td><a href='#' class='PropHint' onclick='return ipol_popup_virt("pop-AS<?=$asId?>",this);'></a></td>
			<td><?=$adOption['NAME']?></td>
			<td align='center'><input type='checkbox' name='addingService[<?=$asId?>][SHOW]' value='Y' <?=($adOption['SHOW']=='Y')?"checked":""?> /></td>
			<td align='center'><input type='checkbox' name='addingService[<?=$asId?>][DEF]' value='Y' <?=($adOption['DEF']=='Y')?"checked":""?> /></td>
			<td>
				<div id="pop-AS<?=$asId?>" class="b-popup" style="display: none; ">
					<div class="pop-text"><?=$adOption['DESC']?></div>
					<div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
				</div>
			</td>
		</tr>
	<?}?>
</table></td></tr>

<?// Отправители?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_senders")?></td>
</tr>
<tr>
	<td><label for="allowSenders"><?=GetMessage("IPOLSDEK_OPT_allowSenders")?></label></td>
	<td><input id="allowSenders" onchange='IPOLSDEK_actSender($(this))' name="allowSenders" value="Y" <?=(COption::GetOptionString($module_id,'allowSenders','N') == 'Y')?'checked':''?> type="checkbox"></td>
</tr>
<tr><td style="color:#555;" colspan="2">
	<?sdekOption::placeFAQ('SENDERS')?>
</td></tr>
<tr><td colspan="2" valign="top" align="center" id="IPOLSDEK_sendersPlace">
	<?if(is_array($svdCt) && count($svdCt))
		foreach($svdCt as $key => $val){?>
			<table class="IPOLSDEK_sender">
				<tr><td><?=GetMessage('IPOLSDEK_LBL_SENDER')?></td><td><input name="senders[<?=$key?>][senderName]" value='<?=$val['senderName']?>' type="text"></td></tr>
				<tr><td><?=GetMessage("IPOLSDEK_LBL_COURIERTIME")?></td><td><input type='text' style="width:56px" name='senders[<?=$key?>][courierTimeBeg]' value='<?=$val['courierTimeBeg']?>'> - <input type='text' name='senders[<?=$key?>][courierTimeEnd]' style="width:56px" value='<?=$val['courierTimeEnd']?>'></td></tr>
				<tr><td colspan='2'><div class='IPOLSDEK_errTextCourier'></div></td></tr>
				<tr><td><?=GetMessage('IPOLSDEK_JS_SOD_courierCity')?></td><td><input class="IPOLSDEK_senderCity" value='<?=$senderCities[$val['courierCity']]?>' type="text"><input name="senders[<?=$key?>][courierCity]" value='<?=$val['courierCity']?>' type="hidden"></td></tr>
				<tr><td><?=GetMessage('IPOLSDEK_JS_SOD_courierStreet')?></td><td><input name="senders[<?=$key?>][courierStreet]" value='<?=$val['courierStreet']?>' type="text"></td></tr>
				<tr><td><?=GetMessage('IPOLSDEK_JS_SOD_courierHouse')?></td><td><input name="senders[<?=$key?>][courierHouse]" value='<?=$val['courierHouse']?>' type="text"></td></tr>
				<tr><td><?=GetMessage('IPOLSDEK_JS_SOD_courierFlat')?></td><td><input name="senders[<?=$key?>][courierFlat]" value='<?=$val['courierFlat']?>' type="text"></td></tr>
				<tr><td><?=GetMessage('IPOLSDEK_JS_SOD_courierPhone')?></td><td><input class="IPOLSDEK_phone" name="senders[<?=$key?>][courierPhone]" value='<?=$val['courierPhone']?>' type="text"></td></tr>
				<tr><td><?=GetMessage('IPOLSDEK_JS_SOD_courierName')?></td><td><input name="senders[<?=$key?>][courierName]" value='<?=$val['courierName']?>' type="text"></td></tr>
				<tr><td><?=GetMessage('IPOLSDEK_JS_SOD_courierComment')?></td><td><input name="senders[<?=$key?>][courierComment]" value='<?=$val['courierComment']?>' type="text"></td></tr>
			</table>
	<?}?>
</td></tr>
<tr><td colspan="2" valign="top" align="center"><input type='button' value="<?=GetMessage("IPOLSDEK_LBL_ADDSENDER")?>" onclick='IPOLSDEK_addSender()'></td></tr>

<?// Сервисные свойства?>
<tr class="heading" onclick='IPOLSDEK_serverShow()' style='cursor:pointer;text-decoration:underline'>
	<td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_service")?></td>
</tr> 
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OTHR_schet')?></td>
	<td>
	<?
		$tmpVal=COption::GetOptionString($module_id,'schet',0);
		echo $tmpVal;
		if($tmpVal>0){
	?> <input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_schet_BUTTON')?>' onclick='IPOLSDEK_sbrosSchet()'/>
	<?}?>
	</td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OTHR_lastModList')?></td>
	<td>
		<? $ft = filemtime($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/list.php");?>
		<span id='IPOLSDEK_updtTime'><?=($ft)?date("d.m.Y H:i:s",$ft):GetMessage("IPOLSDEK_OTHR_NOTCOMMITED");?></span>
		<input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_lastModList_BUTTON')?>' onclick='IPOLSDEK_syncList()'/>
	</td>
</tr>		
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OPT_statCync')?></td>
	<td>
		<?	$optVal = COption::GetOptionString($module_id,'statCync',0);
			if($optVal>0) echo date("d.m.Y H:i:s",$optVal);
			else echo GetMessage('IPOLSDEK_OTHR_NOTCOMMITED');
		?>
		<input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_getOutLst_BUTTON')?>' id='IPOLSDEK_SO' onclick='IPOLSDEK_syncOrdrs()'/>
	</td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OPT_dostTimeout')?></td>
	<td>
		<?	
			$optVal = COption::GetOptionString($module_id,'dostTimeout',6);
			if(floatval($optVal)<=0) $optVal=6;
		?>
		<input type='text' value='<?=$optVal?>' name='dostTimeout' size="1"/>
	</td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OPT_timeoutRollback')?></td>
	<td>
		<?
			$optVal = COption::GetOptionString($module_id,'timeoutRollback',15);
			if(floatval($optVal)<=0) $optVal=15;
		?>
		<input type='text' value='<?=$optVal?>' name='timeoutRollback' size="1"/>
	</td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OPT_useOldServer')?></td>
	<td>
		<input type='checkbox' value='Y' name='useOldServer' <?=(COption::GetOptionString($module_id,'useOldServer','N') == 'Y')?'checked':''?>>
	</td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'><td colspan='2' style='text-align:center'>
	<input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_rewriteCities_BUTTON')?>' id='IPOLSDEK_REWRITECITIES' onclick='IPOLSDEK_rewriteCities()'/>
</td></tr>
<tr style='display:none' class='IPOLSDEK_service'><td colspan='2' style='text-align:center'>
	<br><input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_importCities_BUTTON')?>' id='IPOLSDEK_IMPORTCITIES' onclick='IPOLSDEK_importCities()'/>
</td></tr>