<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.CDeliverySDEK::$MODULE_ID.'/jsloader.php');
	global $APPLICATION;
	if($arParams['NOMAPS']!='Y')
		$APPLICATION->AddHeadString('<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>');
	$APPLICATION->AddHeadString('<link href="/bitrix/js/'.CDeliverySDEK::$MODULE_ID.'/jquery.jscrollpane.css" type="text/css"  rel="stylesheet" />');
	if($arParams['CNT_DELIV'] == 'Y')
		$order = "{
			WEIGHT : '{$arResult['ORDER']['WEIGHT']}',
			PRICE  : '{$arResult['ORDER']['PRICE']}',
			GOODS  : {$arResult['ORDER']['GOODS_js']}
		}";
	else{
		$order = 'false';
		if($arParams['DIMENSIONS']){
			$dimJS = "{
				LENGTH : '".(($arParams['DIMENSIONS']['LENGTH']) ? $arParams['DIMENSIONS']['LENGTH'] : 0)."',
				WIDTH  : '".(($arParams['DIMENSIONS']['WIDTH'])  ? $arParams['DIMENSIONS']['WIDTH']  : 0)."',
				HEIGHT : '".(($arParams['DIMENSIONS']['HEIGHT']) ? $arParams['DIMENSIONS']['HEIGHT'] : 0)."',
			}";
		}else
			$dimJS = false;
	}
	$forbidens = "[";
	if($arParams['FORBIDDEN'])
		foreach($arParams['FORBIDDEN'] as $forb)
			$forbidens .= "'$forb',";
	$forbidens .= "]";

	$mode = (!array_key_exists('MODE',$arParams) || $arParams['MODE'] == 'both') ? array('PVZ','POSTOMAT') : array($arParams['MODE']);
	?>
		<script>
			var IPOLSDEK_pvz = {
				city: '<?=$arResult['city']?>',//город

				pvzInputs: [<?=substr($IPOLSDEK_propAddr,0,-1)?>],//инпуты, куда грузится адрес пвз

				pvzLabel: '',//jq-объект элемента, куда лепим кнопку выбрать ПВЗ

				prices: {},

				profiles: <?=CUtil::PhpToJSObject($mode)?>,

				punctMode: false, 

				PVZ: <?=CUtil::PhpToJSObject($arResult['PVZ'])?>,

				POSTOMAT: <?=CUtil::PhpToJSObject($arResult['POSTOMAT'])?>,

				order: <?=$order?>,

				forbidden: <?=$forbidens?>,

				cityPVZ: {},//объект с ПВЗ города, там сидят они + координаты для Яндекса

				scrollPVZ: false,//скролл пунктов ПВЗ

				scrollDetail: false,//скролл детальной информации

				init: function(){
					IPOLSDEK_pvz.punctMode = (IPOLSDEK_pvz.profiles.length == 1) ? IPOLSDEK_pvz.profiles[0] : 'ALL';
					$('#SDEK_modController').css('display','none');
					IPOLSDEK_pvz.switchMode();
					<?if($arParams['CNT_DELIV'] != 'Y'){?>IPOLSDEK_pvz.loadCityCost();<?}?>
					<?if(!in_array("pickup",$arParams['FORBIDDEN'])){?>IPOLSDEK_pvz.Y_init();<?}?>
					<?if(in_array("pickup",$arParams['FORBIDDEN'])){?>
						$('#SDEK_map').css('display','none');
						$('#SDEK_info').css('display','none');
					<?}?>
				},

				chooseCity: function(city){
					$('#SDEK_citySel a').each(function(){
						$(this).css('display','');
						if($(this).attr('onclick').indexOf(city)!==-1){
							$(this).css('display','none');
							$('#SDEK_cityName').html($(this).text());
						}
					});
					$('#SDEK_citySel').css('display','none');
					IPOLSDEK_pvz.city = city;
					IPOLSDEK_pvz.switchMode();
					IPOLSDEK_pvz.resetCityName();
				},

				initCityPVZ: function(){ // грузим пункты самовывоза для выбранного города
					$('[id^="SDEK_delivInfo_"]').css('display','none');
					if(IPOLSDEK_pvz.punctMode != 'ALL')
						$('#SDEK_delivInfo_'+IPOLSDEK_pvz.punctMode).css('display','block');
					else
						$('#SDEK_modController').css('display','');
					var city = IPOLSDEK_pvz.city;
					IPOLSDEK_pvz.cityPVZ = {};
					if(IPOLSDEK_pvz.punctMode != 'POSTOMAT'){
						for(var i in IPOLSDEK_pvz.PVZ[city]){
							IPOLSDEK_pvz.cityPVZ[i] = {
								'Name'     : (IPOLSDEK_pvz.PVZ[city][i]['Name']) ? IPOLSDEK_pvz.PVZ[city][i]['Name'] : IPOLSDEK_pvz.PVZ[city][i]['Address'],
								'Address'  : IPOLSDEK_pvz.PVZ[city][i]['Address'],
								'WorkTime' : IPOLSDEK_pvz.PVZ[city][i]['WorkTime'],
								'Phone'    : IPOLSDEK_pvz.PVZ[city][i]['Phone'],
								'Note'     : IPOLSDEK_pvz.PVZ[city][i]['Note'],
								'cX'       : IPOLSDEK_pvz.PVZ[city][i]['cX'],
								'cY'       : IPOLSDEK_pvz.PVZ[city][i]['cY'],
								'type'	   : 'PVZ'
							}; 
						}
					}
					if(IPOLSDEK_pvz.punctMode != 'PVZ'){
						for(var i in IPOLSDEK_pvz.POSTOMAT[city]){
							IPOLSDEK_pvz.cityPVZ[i] = {
								'Name'     : (IPOLSDEK_pvz.POSTOMAT[city][i]['Name']) ? IPOLSDEK_pvz.POSTOMAT[city][i]['Name'] : IPOLSDEK_pvz.POSTOMAT[city][i]['Address'],
								'Address'  : IPOLSDEK_pvz.POSTOMAT[city][i]['Address'],
								'WorkTime' : IPOLSDEK_pvz.POSTOMAT[city][i]['WorkTime'],
								'Phone'    : IPOLSDEK_pvz.POSTOMAT[city][i]['Phone'],
								'Note'     : IPOLSDEK_pvz.POSTOMAT[city][i]['Note'],
								'cX'       : IPOLSDEK_pvz.POSTOMAT[city][i]['cX'],
								'cY'       : IPOLSDEK_pvz.POSTOMAT[city][i]['cY'],
								'type'	   : 'POSTOMAT'
							}; 
						}
					}
					IPOLSDEK_pvz.cityPVZHTML();//грузим html PVZ. Два раза пробегаем по массиву, но не критично.
				},
				
				cityPVZHTML: function(){ // заполняем список ПВЗ города
					if(IPOLSDEK_pvz.scrollPVZ && typeof(IPOLSDEK_pvz.scrollPVZ.data('jsp'))!='undefined')
						IPOLSDEK_pvz.scrollPVZ.data('jsp').destroy();
					if(IPOLSDEK_pvz.punctMode == 'ALL' || IPOLSDEK_pvz.profiles.length == 1)
						$('#SDEK_wrapper').height(400);
					else						
						$('#SDEK_wrapper').height(370);
					var html = '';
					for(var i in IPOLSDEK_pvz.cityPVZ)
						if(IPOLSDEK_pvz.punctMode == 'ALL'){
							html+='<p id="PVZ_'+i+'" onclick="IPOLSDEK_pvz.markChosenPVZ(\''+i+'\')" onmouseover="IPOLSDEK_pvz.Y_blinkPVZ(\''+i+'\',true)" onmouseout="IPOLSDEK_pvz.Y_blinkPVZ(\''+i+'\')" ><span class="IPOLSDEK_subPunct">'+IPOLSDEK_pvz.paintPVZ(i)+'</span>&nbsp;<span class="IPOLSDEK_subPunct_detail_'+IPOLSDEK_pvz.cityPVZ[i].type+'"></span></p>';
						}else
							html+='<p id="PVZ_'+i+'" onclick="IPOLSDEK_pvz.markChosenPVZ(\''+i+'\')" onmouseover="IPOLSDEK_pvz.Y_blinkPVZ(\''+i+'\',true)" onmouseout="IPOLSDEK_pvz.Y_blinkPVZ(\''+i+'\')" >'+IPOLSDEK_pvz.paintPVZ(i)+'</p>';
					$('#SDEK_wrapper').html(html);
					IPOLSDEK_pvz.scrollPVZ=$('#SDEK_wrapper').jScrollPane({autoReinitialise:true});
				},

				paintPVZ: function(ind){ //красим адресс пвз, если задан цвет
					var addr = '';
					if(IPOLSDEK_pvz.cityPVZ[ind].color && IPOLSDEK_pvz.cityPVZ[ind].Address.indexOf(',')!==false)
						addr="<span style='color:"+IPOLSDEK_pvz.cityPVZ[ind].color+"'>"+IPOLSDEK_pvz.cityPVZ[ind].Address.substr(0,IPOLSDEK_pvz.cityPVZ[ind].Address.indexOf(','))+"</span><br>"+IPOLSDEK_pvz.cityPVZ[ind].Name;
					else
						addr=IPOLSDEK_pvz.cityPVZ[ind].Name;
					if(IPOLSDEK_pvz.punctMode == 'ALL' && addr.length > 20)
						addr = addr.substr(0,18)+'...';
					return addr;
				},

				markChosenPVZ: function(id){
					if($('.sdek_chosen').attr('id')!='PVZ_'+id){
						$('.sdek_chosen').removeClass('sdek_chosen');
						$("#PVZ_"+id).addClass('sdek_chosen');
						IPOLSDEK_pvz.Y_selectPVZ(id);
					}
				},

				// список городов
				getCityName: function(){
					var text = $('#SDEK_citySearcher').val().toLowerCase();
					$('#SDEK_citySel').find('.SDEK_citySelect').each(function(){
						if(($(this).text().toLowerCase().indexOf(text)==-1))
							$(this).css('display','none');
						else
							$(this).css('display','');
					});
				},

				resetCityName: function(){
					$('#SDEK_citySearcher').val('');
					$('#SDEK_citySel').find('.SDEK_citySelect').css('display','');
				},

				showCitySel: function(){
					$('#SDEK_citySel').css('display','');
				},

				// режимы
				switchMode: function(mode){
					$('.SDEK_mC_block').removeClass('active');
					if(arguments.length)
						IPOLSDEK_pvz.punctMode = mode;
					$('#SDEK_mC_'+IPOLSDEK_pvz.punctMode).addClass('active');
					<?if(!in_array("pickup",$arParams['FORBIDDEN']) && !in_array("inpost",$arParams['FORBIDDEN'])){?>
						IPOLSDEK_pvz.initCityPVZ();
						IPOLSDEK_pvz.Y_init();
					<?}?>
					IPOLSDEK_pvz.loadCityCost();
				},

				//Yкарты
				Y_map: false,//указатель на y-карту

				Y_init: function(){
					if(typeof IPOLSDEK_pvz.city == 'undefined')
						IPOLSDEK_pvz.city = '<?=GetMessage('IPOLSDEK_FRNT_MOSCOW')?>';
					ymaps.geocode("<?=GetMessage("IPOLSDEK_RUSSIA")?>, "+IPOLSDEK_pvz.city , {
						results: 1
					}).then(function (res) {
							var firstGeoObject = res.geoObjects.get(0);
							var coords = firstGeoObject.geometry.getCoordinates();
							coords[1]-=0.2;
							if(!IPOLSDEK_pvz.Y_map){
								IPOLSDEK_pvz.Y_map = new ymaps.Map("SDEK_map",{
									zoom:10,
									controls: [],
									center: coords
								});
								var ZK = new ymaps.control.ZoomControl({
									options : {
										position:{
											left : 265,
											top  : 146
										}
									}
								});
								
								IPOLSDEK_pvz.Y_map.controls.add(ZK);
							}
							else{
								IPOLSDEK_pvz.Y_map.setCenter(coords);
								IPOLSDEK_pvz.Y_map.setZoom(10);
							}
							IPOLSDEK_pvz.Y_clearPVZ();
							IPOLSDEK_pvz.Y_markPVZ();
					});
				},

				Y_markPVZ: function(){
					for(var i in IPOLSDEK_pvz.cityPVZ){
						var baloonHTML  = "<div id='SDEK_baloon'>";
							baloonHTML += "<div class='SDEK_iAdress'>";
							if(IPOLSDEK_pvz.cityPVZ[i].Address.indexOf(',')!==-1){
								if(IPOLSDEK_pvz.cityPVZ[i].color)
									baloonHTML +=  "<span style='color:"+IPOLSDEK_pvz.cityPVZ[i].color+"'>"+IPOLSDEK_pvz.cityPVZ[i].Address.substr(0,IPOLSDEK_pvz.cityPVZ[i].Address.indexOf(','))+"</span>";
								else
									baloonHTML +=  IPOLSDEK_pvz.cityPVZ[i].Address.substr(0,IPOLSDEK_pvz.cityPVZ[i].Address.indexOf(','));
								baloonHTML += "<br>"+IPOLSDEK_pvz.cityPVZ[i].Address.substr(IPOLSDEK_pvz.cityPVZ[i].Address.indexOf(',')+1).trim();
							}
							else
								baloonHTML += IPOLSDEK_pvz.cityPVZ[i].Address;
							baloonHTML += "</div>";

							if(IPOLSDEK_pvz.cityPVZ[i].Phone)
								baloonHTML += "<div><div class='SDEK_iTelephone sdek_icon'></div><div class='sdek_baloonDiv'>"+IPOLSDEK_pvz.cityPVZ[i].Phone+"</div><div style='clear:both'></div></div>";
							if(IPOLSDEK_pvz.cityPVZ[i].WorkTime)
								baloonHTML += "<div><div class='SDEK_iTime sdek_icon'></div><div class='sdek_baloonDiv'>"+IPOLSDEK_pvz.cityPVZ[i].WorkTime+"</div><div style='clear:both'></div></div>";

							baloonHTML += "<div class='sdek_baloonPrice'></div>";

							if(IPOLSDEK_pvz.cityPVZ[i].Note)
								baloonHTML += "<div class='sdek_baloonInfo'><a href='javascript:void(0)' title='"+IPOLSDEK_pvz.cityPVZ[i].Note+"'><?=GetMessage('IPOLSDEK_FRNT_DETAIL')?></a></div>";

						baloonHTML += "</div>";
						
						IPOLSDEK_pvz.cityPVZ[i].placeMark = new ymaps.Placemark([IPOLSDEK_pvz.cityPVZ[i].cY,IPOLSDEK_pvz.cityPVZ[i].cX],{
							balloonContent: baloonHTML
						}, {
							iconLayout: 'default#image',
							iconImageHref: '/bitrix/images/ipol.sdek/widjet/sdekNActive.png',
							iconImageSize: [40, 43],
							iconImageOffset: [-10, -31]
						});
						IPOLSDEK_pvz.Y_map.geoObjects.add(IPOLSDEK_pvz.cityPVZ[i].placeMark);
						IPOLSDEK_pvz.cityPVZ[i].placeMark.link = i;
						IPOLSDEK_pvz.cityPVZ[i].placeMark.events.add('balloonopen',function(metka){
							IPOLSDEK_pvz.markChosenPVZ(metka.get('target').link);
						});
					}
				},

				Y_selectPVZ: function(wat){
					IPOLSDEK_pvz.cityPVZ[wat].placeMark.balloon.open();
					var ind = (IPOLSDEK_pvz.cityPVZ[wat].type == 'POSTOMAT') ? 'inpost' : 'pickup';
					$('#SDEK_baloon').find('.sdek_baloonPrice').html(IPOLSDEK_pvz.prices[ind][0]+"&nbsp"+IPOLSDEK_pvz.prices[ind][1]+"<div style='clear:both'></div>");

					IPOLSDEK_pvz.Y_map.setCenter([IPOLSDEK_pvz.cityPVZ[wat].cY,IPOLSDEK_pvz.cityPVZ[wat].cX]);
				},

				Y_blinkPVZ: function(wat,ifOn){
					if(typeof(ifOn)!='undefined' && ifOn)
						IPOLSDEK_pvz.cityPVZ[wat].placeMark.options.set({iconImageHref:"/bitrix/images/ipol.sdek/widjet/sdekActive.png"});
					else
						IPOLSDEK_pvz.cityPVZ[wat].placeMark.options.set({iconImageHref:"/bitrix/images/ipol.sdek/widjet/sdekNActive.png"});
				},

				Y_clearPVZ: function(){
					IPOLSDEK_pvz.Y_map.geoObjects.removeAll();
				},
				
				loadCityCost: function(){
					var data = (IPOLSDEK_pvz.order)?IPOLSDEK_pvz.order:{};
					data['CITY_TO'] = IPOLSDEK_pvz.city;
					data['action']  = 'countDelivery';
					data['FORBIDDEN'] = IPOLSDEK_pvz.forbidden;
					IPOLSDEK_pvz.prices = {};
					<?if($dimJS){?>data['DIMS'] = <?=$dimJS?>;<?}?>
					$.ajax({
						url: '/bitrix/js/ipol.sdek/ajax.php',
						type: 'POST',
						dataType: 'JSON',
						data: data,
						success: function(data){
							var transDate=false;
							if(data.courier != 'no')
								transDate = ((typeof data.c_date == 'undefined') ?  transDate = data.date : data.c_date) + "<?=GetMessage("IPOLSDEK_DAY")?>";
							else{
								data.courier = '';
								transDate = '<?=GetMessage("IPOLSDEK_NO_DELIV")?>';		
							}
							IPOLSDEK_pvz.prices.courier = [data.courier,transDate];
							$('#SDEK_cPrice').html(data.courier);
							$('#SDEK_cDate').html(transDate);

							if(data.pickup != 'no'){
								transDate = ((typeof data.p_date == 'undefined') ?  transDate = data.date : data.p_date) + "<?=GetMessage("IPOLSDEK_DAY")?>";
							}else{
								data.pickup = '';
								transDate = '<?=GetMessage("IPOLSDEK_NO_DELIV")?>';
							}
							IPOLSDEK_pvz.prices.pickup = [data.pickup,transDate];
							if(IPOLSDEK_pvz.punctMode != 'ALL'){
								$('#SDEK_pPrice').html(data.pickup);
								$('#SDEK_pDate').html(transDate);
							}else
								$('.IPOLSDEK_subPunct_detail_PVZ').each(function(){$(this).html(data.pickup+" &nbsp;"+transDate);});

							if(data.inpost != 'no'){
								transDate = ((typeof data.i_date == 'undefined') ?  transDate = data.date : data.i_date) + "<?=GetMessage("IPOLSDEK_DAY")?>";
							}else{
								data.inpost = '';
								transDate = '<?=GetMessage("IPOLSDEK_NO_DELIV")?>';
							}
							IPOLSDEK_pvz.prices.inpost = [data.inpost,transDate];
							if(IPOLSDEK_pvz.punctMode != 'ALL'){
								$('#SDEK_iPrice').html(data.inpost);
								$('#SDEK_iDate').html(transDate);
							}else
								$('.IPOLSDEK_subPunct_detail_POSTOMAT').each(function(){$(this).html(data.inpost+" &nbsp;"+transDate);});
						}
					});
				},

				// загрузка
				readySt: {
					ymaps: false,
					jqui: false
				},
				checkReady: function(wat){
					if(typeof(IPOLSDEK_pvz.readySt[wat]) !== 'undefined')
						IPOLSDEK_pvz.readySt[wat] = true;
					if(IPOLSDEK_pvz.readySt.ymaps && IPOLSDEK_pvz.readySt.jqui)
						IPOLSDEK_pvz.init();
				},

				jquiready: function(){IPOLSDEK_pvz.checkReady('jqui');},
				ympsready: function(){IPOLSDEK_pvz.checkReady('ymaps');},

				ymapsBindCntr: 0,
				ymapsBidner: function(){
					if(IPOLSDEK_pvz.ymapsBindCntr > 50){
						console.error('SDEK widjet error: no Y-maps');
						return;
					}
					if(typeof(ymaps) == 'undefined'){
						IPOLSDEK_pvz.ymapsBindCntr++;
						setTimeout(IPOLSDEK_pvz.ymapsBidner,100);
					}else
						ymaps.ready(IPOLSDEK_pvz.ympsready);
				},
			}
			IPOLSDEK_pvz.ymapsBidner();
			IPOL_JSloader.checkScript('',"/bitrix/js/<?=CDeliverySDEK::$MODULE_ID?>/jquery.mousewheel.js");
			IPOL_JSloader.checkScript('$("body").jScrollPane',"/bitrix/js/<?=CDeliverySDEK::$MODULE_ID?>/jquery.jscrollpane.js",IPOLSDEK_pvz.jquiready);
		</script>
		<div id='SDEK_pvz'>
			<div id='SDEK_title'>
				<div id='SDEK_cityPicker' <?=(count($arResult['Regions'])==1)?'style="visibility:hidden"':''?>>
					<div><?=GetMessage("IPOLSDEK_YOURCITY")?></div>
					<div>
						<div id='SDEK_cityLabel'>
							<a id='SDEK_cityName' href='javascript:void(0)' onmouseover='IPOLSDEK_pvz.showCitySel(); return false;'><?=$arResult['city']?></a>
							<div id='SDEK_citySel'>
								<input type='text' id='SDEK_citySearcher' placeholder='<?=GetMessage("IPOLSDEK_CITYSEARCH")?>' onkeyup='IPOLSDEK_pvz.getCityName()'/>
								<?foreach($arResult['Regions'] as $city){?>
									<a href='javascript:void(0)' <?=($city==CDeliverySDEK::toUpper($arResult['city']))?"style='display:none'":''?> onclick='IPOLSDEK_pvz.chooseCity("<?=$city?>");return false;' class='SDEK_citySelect'><?=$city?><br></a>
								<?}?>
							</div>
						</div>
					</div>
				</div>
				<div class='SDEK_mark'>
					<div class='SDEK_courierInfo'><strong><?=GetMessage("IPOLSDEK_COURIER")?></strong><br><?=GetMessage("IPOLSDEK_DELTERM")?></div>
					<div class='SDEK_courierInfo'>
						<span id='SDEK_cPrice'><?=($arResult['DELIVERY']['courier']!='no')?$arResult['DELIVERY']['courier']:""?></span>
						<br>
						<span id='SDEK_cDate'><?=($arResult['DELIVERY']['courier']!='no')?GetMessage("IPOLSDEK_DAY"):GetMessage("IPOLSDEK_NO_DELIV")?></span>
					</div>
					<div style='clear: both;'></div>
				</div>
				<div style='float:none;clear:both'></div>
			</div>
			<div id='SDEK_map'></div>
			<div id='SDEK_info'>
				<div id='SDEK_sign'><?=GetMessage("IPOLSDEK_LABELPVZ")?></div>
				<div id='SDEK_modController'>
					<?foreach(array('ALL','PVZ','POSTOMAT') as $mode){?>
						<div class='SDEK_mC_block' id='SDEK_mC_<?=$mode?>' onclick='IPOLSDEK_pvz.switchMode("<?=$mode?>")'><?=GetMessage("IPOLSDEK_TEMPL_$mode")?></div>
					<?}?>
					<div style='clear:both'></div>
				</div>
				<div id='SDEK_delivInfo_PVZ'><?=GetMessage("IPOLSDEK_CMP_PRICE")?>
					<span id='SDEK_pPrice'><?=($arResult['DELIVERY']['pickup']!='no')?$arResult['DELIVERY']['pickup']:""?></span>,&nbsp;<?=GetMessage("IPOLSDEK_CMP_TRM")?>
					<span id='SDEK_pDate'><?=($arResult['DELIVERY']['pickup']!='no')?$arResult['DELIVERY']['date'].GetMessage("IPOLSDEK_DAY"):""?></span>
				</div>
				<div id='SDEK_delivInfo_POSTOMAT'><?=GetMessage("IPOLSDEK_CMP_PRICE")?>
					<span id='SDEK_iPrice'><?=($arResult['DELIVERY']['inpost']!='no')?$arResult['DELIVERY']['inpost']:""?></span>,&nbsp;<?=GetMessage("IPOLSDEK_CMP_TRM")?>
					<span id='SDEK_iDate'><?=($arResult['DELIVERY']['inpost']!='no')?$arResult['DELIVERY']['date'].GetMessage("IPOLSDEK_DAY"):""?></span>
				</div>
				<div>
					<div id='SDEK_wrapper'></div>
				</div>
				<div id='SDEK_ten'></div>
			</div>
			<div id='SDEK_head'>
				<div id='SDEK_logo'><a href='http://ipolh.com' target='_blank'></a></div>
			</div>
		</div>