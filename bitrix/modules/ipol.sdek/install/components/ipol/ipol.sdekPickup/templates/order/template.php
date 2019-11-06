<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.CDeliverySDEK::$MODULE_ID.'/jsloader.php');
	global $APPLICATION;
	if($arParams['NOMAPS']!='Y')
		$APPLICATION->AddHeadString('<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>');
	$APPLICATION->AddHeadString('<link href="/bitrix/js/'.CDeliverySDEK::$MODULE_ID.'/jquery.jscrollpane.css" type="text/css"  rel="stylesheet" />');

	$objProfiles = array();
	$arModes = array(
		'PVZ' => array(
			'forced' => COption::GetOptionString(CDeliverySDEK::$MODULE_ID,'pvzID',false),
			'profs'  => CDeliverySDEK::getDeliveryId('pickup')
		),
		'POSTOMAT' => array(
			'forced' => COption::GetOptionString(CDeliverySDEK::$MODULE_ID,'pickupID',false),
			'profs'  => CDeliverySDEK::getDeliveryId('inpost')
		)
	);

	foreach($arModes as $mode => $content){
		$objProfiles[$mode] = array();
		if($content['forced'])
			$objProfiles[$mode] = array($content['forced'] => array(
				'tag' => false,
				'price' => false,
				'self' => true,
				'link' => array_pop($content['profs'])
			));
		else
			foreach($content['profs'] as $id)
				$objProfiles[$mode][$id] = array(
					'tag' => false,
					'price' => false,
					'self' => false,
				);
	}

	$linkName = COption::GetOptionString(CDeliverySDEK::$MODULE_ID,'buttonName','');
	if(!$linkName) $linkName = GetMessage("IPOLSDEK_FRNT_CHOOSEPICKUP");
	?>
		<script>
			var IPOLSDEK_pvz = {
				button: '<a href="javascript:void(0);" class="SDEK_selectPVZ" onclick="IPOLSDEK_pvz.selectPVZ(\'#id#\',\'#mode#\'); return false;"><?=$linkName?></a>',// html кнопки "выбрать ПВЗ".

				isActive: false, // открыт ли

				curProfile: false, // какой профиль в данный момент расчитывается

				curMode: false, // какой тип ПВЗ в данный момент используется.

				deliveries: <?=CUtil::PhpToJSObject($objProfiles)?>,

				city: '<?=CDeliverySDEK::$city?>',//город

				cityID: '<?=CDeliverySDEK::$cityId?>', // id город

				pvzInputs: [<?=substr($arResult['propAddr'],0,-1)?>],//инпуты, куда грузится адрес пвз

				pickFirst: function(where){
					if(typeof(where) != 'object')
						return false;
					for(var i in where)
						return i;
				},

				oldTemplate: false,

				makeHTMLId: function(id){
					return 'ID_DELIVERY_' + ((id == 'sdek_pickup' || id == 'sdek_inpost' ) ?  id : 'ID_'+id);
				},

				checkCheckedDel: function(delId,delivery){
					for(var i in delivery)
						if(delivery[i].CHECKED == 'Y'){
							if(delivery[i].ID == delId)
								return true;
							else
								return false;
						}
					return false;
				},

				guessCheckedDel: function(delId){
					return ('ID_DELIVERY_ID_'+delId == $('[name="DELIVERY_ID"]:checked').attr('ID'));
				},

				PVZ: <?=CUtil::PhpToJSObject($arResult['PVZ'])?>,

				POSTOMAT: <?=CUtil::PhpToJSObject($arResult['POSTOMAT'])?>,

				cityPVZ: {},//объект с ПВЗ города, там сидят они + координаты для Яндекса

				scrollPVZ: false,//скролл пунктов ПВЗ

				scrollDetail: false,//скролл детальной информации

				multiPVZ: false, // false, если ПВЗ в городе несколько, или его id

				init: function(){
					if(!IPOLSDEK_pvz.isFull(IPOLSDEK_pvz.deliveries.PVZ))
						console.warn('SDEK vidjet warn: no delivery for PVZ');
					if(!IPOLSDEK_pvz.isFull(IPOLSDEK_pvz.deliveries.POSTOMAT))
						console.warn('SDEK vidjet warn: no delivery for postomats');

					IPOLSDEK_pvz.oldTemplate = $('#ORDER_FORM').length;

					// ==== подписываемся на перезагрузку формы
					if(typeof BX !== 'undefined' && BX.addCustomEvent)
						BX.addCustomEvent('onAjaxSuccess', IPOLSDEK_pvz.onLoad); 

					// Для старого JS-ядра
					if (window.jsAjaxUtil){ // Переопределение Ajax-завершающей функции для навешивания js-событий новым эл-там
						jsAjaxUtil._CloseLocalWaitWindow = jsAjaxUtil.CloseLocalWaitWindow;
						jsAjaxUtil.CloseLocalWaitWindow = function (TID, cont){
							jsAjaxUtil._CloseLocalWaitWindow(TID, cont);
							IPOLSDEK_pvz.onLoad();
						}
					}

					$(window).resize(IPOLSDEK_pvz.positWindow);
					// == END
					IPOLSDEK_pvz.onLoad();

					//html маски
					$('body').append("<div id='SDEK_mask'></div>");
				},

				getPrices: function(){
					$.ajax({
						url: '/bitrix/js/ipol.sdek/ajax.php',
						type: 'POST',
						dataType: 'JSON',
						data: {
							action: 'countDelivery',
							CITY_TO: IPOLSDEK_pvz.city,
							WEIGHT: '<?=CDeliverySDEK::$orderWeight?>',
							PRICE : '<?=CDeliverySDEK::$orderPrice?>',
							CITY_TO_ID: IPOLSDEK_pvz.cityID,
							CURPROF: IPOLSDEK_pvz.curProfile,
							<?if(CDeliverySDEK::$goods && is_array(CDeliverySDEK::$goods)){?>
								GABS : <?=json_encode(CDeliverySDEK::$goods)?>,
							<?}?>
						},
						success: function(data){
							var links = {pickup:'PVZ',inpost:'POSTOMAT'};
							for(var i in links){
								var det = i.substr(0,1);
								if(data[i] != 'no'){
									if(typeof data[det+"_date"] == 'undefined') transDate = data.date;
									else transDate = data[det+"_date"];
									$('#SDEK_'+det+'Price').html(data[i]);
									$('#SDEK_'+det+'Date').html(transDate+"<?=GetMessage("IPOLSDEK_DAY")?>");
								}else{
									$('#SDEK_'+det+'Price').html("");
									$('#SDEK_'+det+'Date').html("<?=GetMessage("IPOLSDEK_NO_DELIV")?>");		
								}
							}
						}
					});
				},

				onLoad: function(ajaxAns){
					// место, где будет кнопка "выбрать ПВЗ"
					var tag = false;

					var newTemplateAjax = (typeof(ajaxAns) != 'undefined' && ajaxAns !== null && typeof(ajaxAns.sdek) == 'object') ? true : false;

					for(var i in IPOLSDEK_pvz.deliveries){
						for(var j in IPOLSDEK_pvz.deliveries[i]){
							tag = false;
							if(IPOLSDEK_pvz.deliveries[i][j].self)
								tag = $('#'+j);
							else{
								if(IPOLSDEK_pvz.oldTemplate){
									var parentNd=$('#'+IPOLSDEK_pvz.makeHTMLId(j));
									if(parentNd.closest('td', '#ORDER_FORM').length>0)
										tag = parentNd.closest('td', '#ORDER_FORM').siblings('td:last');
									else
										tag = parentNd.siblings('label').find('.bx_result_price');
								}
								else
									if(
										(arguments.length > 0 && typeof(ajaxAns.order) != 'undefined' && IPOLSDEK_pvz.checkCheckedDel(j,ajaxAns.order.DELIVERY))
										||
										(arguments.length == 0 && IPOLSDEK_pvz.guessCheckedDel(j))
									){
										if(!$('#IPOLSDEK_injectHere').length)
											$('#bx-soa-delivery').find('.bx-soa-pp-company-desc').after('<div id="IPOLSDEK_injectHere"></div>');
										if($('#IPOLSDEK_injectHere').length == 0){
											$('#bx-soa-delivery .bx-soa-section-title-container').on('click',function(){IPOLSDEK_pvz.onLoad();});
											$('#bx-soa-delivery .bx-soa-editstep').on('click',function(){IPOLSDEK_pvz.onLoad();});
											$('#bx-soa-region .pull-right').on('click',function(){IPOLSDEK_pvz.onLoad();});
										}else
											tag = $('#IPOLSDEK_injectHere');
									}
							}
							if(tag.length>0 && !tag.find('.SDEK_selectPVZ').length){
								IPOLSDEK_pvz.deliveries[i][j].price = (tag.html()) ? tag.html() : false;
								IPOLSDEK_pvz.deliveries[i][j].tag = tag;
								IPOLSDEK_pvz.labelPzv(j,i);
							}
						}
					}

					if($('#sdek_city').length>0){//обновляем город
						IPOLSDEK_pvz.city   = $('#sdek_city').val();
						IPOLSDEK_pvz.cityID = $('#sdek_cityID').val();
					}else{
						if(newTemplateAjax){
							IPOLSDEK_pvz.city   = ajaxAns.sdek.city;
							IPOLSDEK_pvz.cityID = ajaxAns.sdek.cityId;
						}else
							IPOLSDEK_pvz.loadProfile();//если нет sdek_city - грузим в первый раз => забираем из адреса ПВЗ и выставляем его
					}

					// какая доставка выбрана
					var sdekChecker = false;
					if($('#sdek_dostav').length>0){ 
						sdekChecker = $('#sdek_dostav').val();
						sdekChecker = (sdekChecker.indexOf(':') !== -1) ? sdekChecker.replace(":","_") : sdekChecker;
					}else
						if(newTemplateAjax)
							sdekChecker = ajaxAns.sdek.dostav;

					// выбран ПВЗ - "выбираем" его после перезагрузки
					if(sdekChecker && IPOLSDEK_pvz.curMode && typeof(IPOLSDEK_pvz.deliveries[IPOLSDEK_pvz.curMode][sdekChecker]) != 'undefined' && IPOLSDEK_pvz.pvzId)
						IPOLSDEK_pvz.choozePVZ(IPOLSDEK_pvz.pvzId,true);

					IPOLSDEK_pvz.getPrices();
				},

				labelPzv: function(i,mode){ // вставить ссылку на выбор ПВЗ и подпись
					if(typeof(IPOLSDEK_pvz.deliveries[mode][i]) == 'undefined')
						return false;
					var tmpHTML = "<div class='sdek_pvzLair'>"+IPOLSDEK_pvz.button.replace('#id#',i).replace('#mode#',mode) + "<br>";
					if(IPOLSDEK_pvz.pvzId && typeof(IPOLSDEK_pvz[mode][IPOLSDEK_pvz.city][IPOLSDEK_pvz.pvzId]) != 'undefined')
						tmpHTML += "<span class='sdek_pvzAddr'>" + IPOLSDEK_pvz[mode][IPOLSDEK_pvz.city][IPOLSDEK_pvz.pvzId].Address+"</span><br>";
					if(IPOLSDEK_pvz.deliveries[mode][i].price)
						tmpHTML += IPOLSDEK_pvz.deliveries[mode][i].price;
						tmpHTML += "</div>";

					IPOLSDEK_pvz.deliveries[mode][i].tag.html(tmpHTML);
					if(!IPOLSDEK_pvz.oldTemplate)
						$('.sdek_pvzLair .SDEK_selectPVZ').addClass('btn btn-default');
				},

				loadProfile:function(){//загрузка ПВЗ из профиля
					var chznPnkt=false;
					for(var i in IPOLSDEK_pvz.pvzInputs){
						chznPnkt = $('[name="ORDER_PROP_'+IPOLSDEK_pvz.pvzInputs[i]+'"]');
						if(chznPnkt.length>0)
							break;
					}
					if(!chznPnkt || chznPnkt.length==0) return;

					var seltdPVZ = chznPnkt.val();
					if(seltdPVZ.indexOf('#S')==-1) return;

					seltdPVZ=seltdPVZ.substr(seltdPVZ.indexOf('#S')+2);

					if(seltdPVZ <= 0)
						return false;
					else{
						var checks = ['PVZ','POSTOMAT'];
						var subChecks = ['PVZ','POSTOMAT'];
						for(var i in checks)
							if(
								typeof IPOLSDEK_pvz[checks[i]][IPOLSDEK_pvz.city] == 'undefined' ||
								typeof IPOLSDEK_pvz[checks[i]][IPOLSDEK_pvz.city][seltdPVZ] == 'undefined'
							)
								subChecks.splice(i,1);

						if(!subChecks.length)
							return false;
						else
							IPOLSDEK_pvz.curMode = subChecks.pop();
					}

					// выбрали ПВЗ
					IPOLSDEK_pvz.pvzAdress=IPOLSDEK_pvz.city+", "+IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][IPOLSDEK_pvz.city][seltdPVZ]['Address']+" #S"+seltdPVZ;
					IPOLSDEK_pvz.pvzId = seltdPVZ;

					//Выводим подпись о выбранном ПВЗ рядом с кнопкой "Выбрать ПВЗ"
					for(var i in IPOLSDEK_pvz.deliveries[IPOLSDEK_pvz.curMode])
						if(IPOLSDEK_pvz.deliveries[IPOLSDEK_pvz.curMode][i].tag)
							IPOLSDEK_pvz.labelPzv(i,IPOLSDEK_pvz.curMode);
				},

				initCityPVZ: function(){ // грузим пункты самовывоза для выбранного города
					var city = IPOLSDEK_pvz.city;
					var cnt = [];
					IPOLSDEK_pvz.cityPVZ = {};
					for(var i in IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][city]){
						IPOLSDEK_pvz.cityPVZ[i] = {
							'Name'     : (IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][city][i]['Name']) ? IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][city][i]['Name'] : IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][city][i]['Address'],
							'Address'  : IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][city][i]['Address'],
							'WorkTime' : IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][city][i]['WorkTime'],
							'Phone'    : IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][city][i]['Phone'],
							'Note'     : IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][city][i]['Note'],
							'cX'       : IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][city][i]['cX'],
							'cY'       : IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][city][i]['cY'],
						};
						cnt.push(i);
					}
					IPOLSDEK_pvz.cityPVZHTML();//грузим html PVZ (или POSTAMAT'a). Два раза пробегаем по массиву, но не критично.
					IPOLSDEK_pvz.multiPVZ = (cnt.length == 1) ? cnt.pop() : false;
				},
				
				cityPVZHTML: function(){ // заполняем список ПВЗ города
					var html = '';
					for(var i in IPOLSDEK_pvz.cityPVZ)
						html+='<p id="PVZ_'+i+'" onclick="IPOLSDEK_pvz.markChosenPVZ(\''+i+'\')" onmouseover="IPOLSDEK_pvz.Y_blinkPVZ(\''+i+'\',true)" onmouseout="IPOLSDEK_pvz.Y_blinkPVZ(\''+i+'\')">'+IPOLSDEK_pvz.paintPVZ(i)+'</p>';
					$('#SDEK_wrapper').html(html);
					IPOLSDEK_pvz.scrollPVZ=$('#SDEK_wrapper').jScrollPane({autoReinitialise: true});
				},
				
				paintPVZ: function(ind){ //красим адресс пвз, если задан цвет
					var addr = '';
					if(IPOLSDEK_pvz.cityPVZ[ind].color && IPOLSDEK_pvz.cityPVZ[ind].Address.indexOf(',')!==false)
						addr="<span style='color:"+IPOLSDEK_pvz.cityPVZ[ind].color+"'>"+IPOLSDEK_pvz.cityPVZ[ind].Address.substr(0,IPOLSDEK_pvz.cityPVZ[ind].Address.indexOf(','))+"</span><br>"+IPOLSDEK_pvz.cityPVZ[ind].Name;
					else
						addr=IPOLSDEK_pvz.cityPVZ[ind].Name;
					return addr;
				},

				//выбрали ПВЗ
				pvzAdress: '',
				pvzId: false,
				choozePVZ: function(pvzId,isAjax){// выбрали ПВЗ
					if(typeof IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][IPOLSDEK_pvz.city][pvzId] == 'undefined')
						return;

					IPOLSDEK_pvz.pvzAdress=IPOLSDEK_pvz.city+", "+IPOLSDEK_pvz[IPOLSDEK_pvz.curMode][IPOLSDEK_pvz.city][pvzId]['Address']+" #S"+pvzId;

					IPOLSDEK_pvz.pvzId = pvzId;

					var chznPnkt = false;
					if(typeof(KladrJsObj) != 'undefined') KladrJsObj.FuckKladr();

					IPOLSDEK_pvz.markUnable();

					if(typeof isAjax == 'undefined'){ // Перезагружаем форму (с применением новой стоимости доставки)
						var htmlId = IPOLSDEK_pvz.makeHTMLId(IPOLSDEK_pvz.curProfile);
						if(typeof IPOLSDEK_DeliveryChangeEvent == 'function')
							IPOLSDEK_DeliveryChangeEvent(htmlId);
						else{
							if(IPOLSDEK_pvz.oldTemplate){
								if(typeof $.prop == 'undefined') // <3 jquery
									$('#'+htmlId).attr('checked', 'Y');
								else
									$('#'+htmlId).prop('checked', 'Y');
								$('#'+htmlId).click();
							}else
								BX.Sale.OrderAjaxComponent.sendRequest();	
						}
						IPOLSDEK_pvz.close(true);
					}
				},

				markUnable: function(){
					for(var i in IPOLSDEK_pvz.pvzInputs){
						chznPnkt = $('#ORDER_PROP_'+IPOLSDEK_pvz.pvzInputs[i]);
						if(chznPnkt.length<=0)
							chznPnkt = $('[name="ORDER_PROP_'+IPOLSDEK_pvz.pvzInputs[i]+'"]');
						if(chznPnkt.length>0){
							chznPnkt.val(IPOLSDEK_pvz.pvzAdress);
							chznPnkt.css('background-color', '#eee').attr('readonly','readonly');
							break;
						}
					}
				},

				// отображение
				close: function(fromChoose){//закрываем функционал
					<?if(COption::GetOptionString(CDeliverySDEK::$MODULE_ID,'autoSelOne','') == 'Y'){?>
						if(IPOLSDEK_pvz.multiPVZ !== false && typeof(fromChoose) == 'undefined')
							IPOLSDEK_pvz.choozePVZ(IPOLSDEK_pvz.multiPVZ);
					<?}?>
					if(IPOLSDEK_pvz.scrollPVZ && typeof(IPOLSDEK_pvz.scrollPVZ.data('jsp'))!='undefined')
						IPOLSDEK_pvz.scrollPVZ.data('jsp').destroy();
					$('#SDEK_pvz').css('display','none');
					$('#SDEK_mask').css('display','none');
					IPOLSDEK_pvz.isActive = false;
				},

				selectPVZ: function(id, mode){ // выбор ПВЗ прям ПВЗ
					if(!IPOLSDEK_pvz.isActive){
						if(typeof(mode) == 'undefined')
							mode = 'PVZ';
						if(IPOLSDEK_pvz.curMode != mode){
							IPOLSDEK_pvz.Y_clearPVZ()
							IPOLSDEK_pvz.curMode = mode;
							$('[id^="SDEK_delivInfo_"]').css('display','none');
							$('#SDEK_delivInfo_'+mode).css('display','block');
						}
						if(arguments.length == 1 && typeof(IPOLSDEK_pvz.deliveries[mode][id] != 'undefined'))
							IPOLSDEK_pvz.curProfile = id;
						else{
							var first = IPOLSDEK_pvz.pickFirst(IPOLSDEK_pvz.deliveries[mode]);
							if(IPOLSDEK_pvz.deliveries[mode][first].self)
								IPOLSDEK_pvz.curProfile = IPOLSDEK_pvz.deliveries[mode][first].link;
							else
								IPOLSDEK_pvz.curProfile = IPOLSDEK_pvz.pickFirst(IPOLSDEK_pvz.deliveries[mode]);
						}

						IPOLSDEK_pvz.getPrices();
						IPOLSDEK_pvz.isActive = true;
						IPOLSDEK_pvz.positWindow();

						$('#SDEK_mask').css('display','block');

						IPOLSDEK_pvz.initCityPVZ();

						IPOLSDEK_pvz.Y_init();
					}
				},

				positWindow: function(){
					if(!IPOLSDEK_pvz.isActive) return;

					var hndlr = $('#SDEK_pvz');

					var left = ($(window).width()>hndlr.outerWidth()) ? (($(window).width()-hndlr.outerWidth())/2) : 0;

					if($(window).height() < 542){
						hndlr.css('height','100%');
						$('#SDEK_wrapper').css('height',hndlr.height()-82);
					}else{
						hndlr.css('height','');
						$('#SDEK_wrapper').css('height','');
					}

					hndlr.css({
						'display'   : 'block',
						'left'      : left,
					});
					hndlr.css({
						'top'       : ($(window).height()-hndlr.height())/2+$(document).scrollTop(),
					});

					if(typeof(IPOLSDEK_pvz.Y_map.controls) != 'undefined'){
						var leftZK = (hndlr.width()  < 900) ? hndlr.width() - 40     : 265;
						var topZK  = (hndlr.height() < 540)	? (hndlr.height()-206)/2 : 146;
						var control = IPOLSDEK_pvz.Y_map.controls.getContainer();
						$(control).find('[class*="_control"]').css({
							left:leftZK,
							top: topZK
						});
					}

					if(hndlr.width() > 700)
						$('.SDEK_all-items').css('display','block');
				},

				markChosenPVZ: function(id){
					if($('.sdek_chosen').attr('id')!='PVZ_'+id){
						$('.sdek_chosen').removeClass('sdek_chosen');
						$("#PVZ_"+id).addClass('sdek_chosen');
						IPOLSDEK_pvz.Y_selectPVZ(id);
					}
					if($('#SDEK_pvz').width() < 450 && $('.SDEK_all-items').css('display') != 'none')
						IPOLSDEK_pvz.handleArrow();
				},

				handleArrow: function(){
					$('.SDEK_arrow').toggleClass('up');
					$('.SDEK_all-items').slideToggle(300);
				},

				//Yкарты
				Y_map: false,//указатель на y-карту

				Y_init: function(){
					IPOLSDEK_pvz.Y_clearPVZ();
					if(typeof IPOLSDEK_pvz.city == 'undefined')
						IPOLSDEK_pvz.city = '<?=GetMessage('IPOLSDEK_FRNT_MOSCOW')?>';
					ymaps.geocode("<?=GetMessage("IPOLSDEK_RUSSIA")?>, "+IPOLSDEK_pvz.city , {
						results: 1
					}).then(function (res) {
							var checker = $('#SDEK_pvz').width();
							var firstGeoObject = res.geoObjects.get(0);
							var coords = firstGeoObject.geometry.getCoordinates();

							coords[1]-=(checker > 700) ? 0.2 : -(120 / checker);
							if(!IPOLSDEK_pvz.Y_map){
								IPOLSDEK_pvz.Y_map = new ymaps.Map("SDEK_map",{
									zoom:10,
									controls: [],
									center: coords
								});

								var hCheck = $('#SDEK_pvz').height();
								
								var ZK = new ymaps.control.ZoomControl({
									options : {
										position:{
											left : (checker > 700) ? 265 : checker - 40,
											top  : (hCheck > 540)  ? 146  : (hCheck - 206)/2
										}
									}
								});
								
								IPOLSDEK_pvz.Y_map.controls.add(ZK);
							}
							else{
								IPOLSDEK_pvz.Y_map.setCenter(coords);
								IPOLSDEK_pvz.Y_map.setZoom(10);
							}
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
						
						if(IPOLSDEK_pvz.cityPVZ[i].Note)
							baloonHTML += "<div><div class='sdek_baloonDiv'><a href='javascript:void(0)' title='"+IPOLSDEK_pvz.cityPVZ[i].Note+"'><?=GetMessage('IPOLSDEK_FRNT_DETAIL')?></a></div><div style='clear:both'></div></div>";
						baloonHTML += "<div><a id='SDEK_button' href='javascript:void(0)' onclick='IPOLSDEK_pvz.choozePVZ(\""+i+"\")'></a></div>";
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
					var checker = $('#SDEK_pvz').width();
					var adr = (checker > 700) ? 0.2 : -(120 / checker);
					IPOLSDEK_pvz.Y_map.setCenter([IPOLSDEK_pvz.cityPVZ[wat].cY,parseFloat(IPOLSDEK_pvz.cityPVZ[wat].cX)-adr]);
					IPOLSDEK_pvz.cityPVZ[wat].placeMark.balloon.open();
				},
				
				Y_blinkPVZ: function(wat,ifOn){
					if(typeof(ifOn)!='undefined' && ifOn)
						IPOLSDEK_pvz.cityPVZ[wat].placeMark.options.set({iconImageHref:"/bitrix/images/ipol.sdek/widjet/sdekActive.png"});
					else
						IPOLSDEK_pvz.cityPVZ[wat].placeMark.options.set({iconImageHref:"/bitrix/images/ipol.sdek/widjet/sdekNActive.png"});
				},

				Y_clearPVZ: function(){
					if(typeof(IPOLSDEK_pvz.Y_map.geoObjects) !== 'undefined')
						IPOLSDEK_pvz.Y_map.geoObjects.removeAll();
				},

				// загрузка
				readySt: {
					ymaps: false,
					jqui: false
				},
				inited: false,
				checkReady: function(wat){
					if(typeof(IPOLSDEK_pvz.readySt[wat]) !== 'undefined')
						IPOLSDEK_pvz.readySt[wat] = true;
					if(IPOLSDEK_pvz.readySt.ymaps && (IPOLSDEK_pvz.readySt.jqui || typeof($) != 'undefined') && !IPOLSDEK_pvz.inited){
						IPOLSDEK_pvz.inited = true;
						var tmpHTML = $('#SDEK_pvz').html();
						$('#SDEK_pvz').replaceWith('');
						$('body').append("<div id='SDEK_pvz'>"+tmpHTML+"</div>");
						IPOLSDEK_pvz.init();
					}
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
				// сервисные
				isFull: function(wat){
					if(typeof(wat) !== 'object') return (wat);
					else
						for(var i in wat)
							return true;
					return false;
				}
			}
			IPOLSDEK_pvz.ymapsBidner();
			IPOL_JSloader.checkScript('',"/bitrix/js/<?=CDeliverySDEK::$MODULE_ID?>/jquery.mousewheel.js");
			IPOL_JSloader.checkScript('$("body").jScrollPane',"/bitrix/js/<?=CDeliverySDEK::$MODULE_ID?>/jquery.jscrollpane.js",IPOLSDEK_pvz.jquiready);
		</script>
		<?// HTML виджета ?>
		<div id='SDEK_pvz'>
			<div id='SDEK_head'>
				<div id='SDEK_logo'><a href='http://ipolh.com' target='_blank'></a></div>
				<div id='SDEK_closer' onclick='IPOLSDEK_pvz.close()'></div>
			</div>
			<div id='SDEK_map'></div>
			<div id='SDEK_info'>
				<div id='SDEK_sign'><span><?=GetMessage("IPOLSDEK_LABELPVZ")?></span></div>
				<div id='SDEK_delivInfo_PVZ'><?=GetMessage("IPOLSDEK_CMP_PRICE")?>
					<span id='SDEK_pPrice'></span>,&nbsp;<?=GetMessage("IPOLSDEK_CMP_TRM")?>
					<span id='SDEK_pDate'></span>
				</div>
				<div id='SDEK_delivInfo_POSTOMAT'><?=GetMessage("IPOLSDEK_CMP_PRICE")?>
					<span id='SDEK_iPrice'></span>,&nbsp;<?=GetMessage("IPOLSDEK_CMP_TRM")?>
					<span id='SDEK_iDate'></span>
				</div>
				
				<div class="SDEK_all-items">
					<div id='SDEK_wrapper'></div>
					<div id='SDEK_ten'></div>
				</div>	
			</div>
		</div>
