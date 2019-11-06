<?
	IncludeModuleLangFile(__FILE__);

	class sdekOption extends sdekHelper{
		
		/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Авторизация
		== auth ==  == logoff ==
		()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

		function auth($params){
			if(!$params['login'] || !$params['password'])
				die('No auth data');
			if(!class_exists('CDeliverySDEK'))
				die('No main class founded');
			sdekdriver::$MODULE_ID;
			if(!function_exists('curl_init'))
				die(GetMessage("IPOLSDEK_AUTH_NOCURL"));

			COption::SetOptionString(self::$MODULE_ID,'logSDEK',$params['login']);
			COption::SetOptionString(self::$MODULE_ID,'pasSDEK',$params['password']);

			CDeliverySDEK::$sdekCity   = 44;
			CDeliverySDEK::$sdekSender = 44;
			CDeliverySDEK::setOrder();

			$resAuth = CDeliverySDEK::calculateDost(136);
			if(!$resAuth['success'])
				$resAuth = CDeliverySDEK::calculateDost(10);

			if(array_key_exists('IPOLSDEK_LOG',$GLOBALS) && $GLOBALS['IPOLSDEK_LOG']) self::toLog(array('params'=>$params,'resAuth'=>$resAuth),'auth');

			if($resAuth['success']){
				COption::SetOptionString(self::$MODULE_ID,'logged',true);

				RegisterModuleDependences("main", "OnEpilog", self::$MODULE_ID, "sdekdriver", "onEpilog");
				RegisterModuleDependences("main", "OnEndBufferContent", self::$MODULE_ID, "CDeliverySDEK", "onBufferContent");
				RegisterModuleDependences("sale", "OnSaleComponentOrderOneStepDelivery", self::$MODULE_ID, "CDeliverySDEK", "pickupLoader",900);
				RegisterModuleDependences("sale", "OnSaleComponentOrderOneStepProcess", self::$MODULE_ID, "CDeliverySDEK", "loadComponent",900);
				RegisterModuleDependences("sale", "OnSaleComponentOrderOneStepComplete", self::$MODULE_ID, "sdekdriver", "orderCreate"); // создание заказа
				RegisterModuleDependences("sale", "OnSaleComponentOrderOneStepPaySystem", self::$MODULE_ID, "CDeliverySDEK", "checkNalD2P"); // проверка платежных систем
				RegisterModuleDependences("sale", "OnSaleComponentOrderOneStepDelivery", self::$MODULE_ID, "CDeliverySDEK", "checkNalP2D"); // проверка платежных систем

				// печать
				RegisterModuleDependences("main", "OnAdminListDisplay", self::$MODULE_ID, "sdekOption", "displayActPrint");
				RegisterModuleDependences("main", "OnBeforeProlog", self::$MODULE_ID, "sdekOption", "OnBeforePrologHandler");

				CAgent::AddAgent("sdekdriver::agentUpdateList();", self::$MODULE_ID);//обновление листов
				CAgent::AddAgent("sdekdriver::agentOrderStates();",self::$MODULE_ID,"N",1800);//обновление статусов заказов

				if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_delivery/delivery_sdek.php"))
					CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::$MODULE_ID."/install/delivery/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_delivery/", true, true);

				echo "G".GetMessage('IPOLSDEK_AUTH_YES');
			}
			else{
				COption::SetOptionString(self::$MODULE_ID,'logSDEK','');
				COption::SetOptionString(self::$MODULE_ID,'pasSDEK','');

				$retStr=GetMessage('IPOLSDEK_AUTH_NO');
				foreach($resAuth as $erCode => $erText)
					$retStr.=self::zaDEjsonit($erText." (".$erCode."). ");
				
				echo $retStr;
			}
		}

		function logoff(){
			COption::SetOptionString(self::$MODULE_ID,'logSDEK','');
			COption::SetOptionString(self::$MODULE_ID,'pasSDEK','');
			COption::SetOptionString(self::$MODULE_ID,'logged',false);
			CAgent::RemoveModuleAgents('ipol.sdek');
			UnRegisterModuleDependences("main", "OnEpilog", self::$MODULE_ID, "sdekdriver", "onEpilog");
			UnRegisterModuleDependences("main", "OnEndBufferContent", self::$MODULE_ID, "CDeliverySDEK", "onBufferContent");
			UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepDelivery", self::$MODULE_ID, "CDeliverySDEK", "pickupLoader");
			UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepProcess", self::$MODULE_ID, "CDeliverySDEK", "loadComponent");

			UnRegisterModuleDependences("main", "OnAdminListDisplay", self::$MODULE_ID, "sdekOption", "displayActPrint");
			UnRegisterModuleDependences("main", "OnBeforeProlog", self::$MODULE_ID, "sdekOption", "OnBeforePrologHandler");

			UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepComplete", self::$MODULE_ID, "sdekdriver", "orderCreate");
			UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepPaySystem", self::$MODULE_ID, "CDeliverySDEK", "checkNalD2P");
			UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepDelivery", self::$MODULE_ID, "CDeliverySDEK", "checkNalP2D");
		}

		// отображение таблицы о заявках
		function tableHandler($params){
			$arSelect[0]=($params['by'])?$params['by']:'ID';
			$arSelect[1]=($params['sort'])?$params['sort']:'DESC';

			$arNavStartParams['iNumPage']=($params['page'])?$params['page']:1;
			$arNavStartParams['nPageSize']=($params['pgCnt']!==false)?$params['pgCnt']:1;
			
			foreach($params as $code => $val)
				if(strpos($code,'F')===0)
					$arFilter[substr($code,1)]=$val;

			$requests   = self::select($arSelect,$arFilter,$arNavStartParams);
			$adServises = sdekdriver::getExtraOptions();
			$strHtml='';
			$tarifs = self::getExtraTarifs();
			
			$arRules = array(
				'noLabel' => array('courierTimeBeg','courierTimeEnd','packs'),
				'header'  => array(
					'courierDate' => GetMessage('IPOLSDEK_STT_SENDER'),
					'street'      => GetMessage('IPOLSDEK_STT_ADDRESS'),
					'line'        => GetMessage('IPOLSDEK_STT_ADDRESS'),
					'PVZ'         => GetMessage('IPOLSDEK_STT_ADDRESS'),
					'packs'		  => GetMessage('IPOLSDEK_STT_PACKS'),
				),
			);

			$isConverted = self::isConverted();
			if($isConverted)
				\Bitrix\Main\Loader::includeModule('sale');
			while($request=$requests->Fetch()){
				$reqParams=unserialize($request['PARAMS']);
				$paramsSrt='';
				foreach($reqParams as $parCode => $parVal){
					if(array_key_exists($parCode,$arRules['header']))
						$paramsSrt .= "<strong>".$arRules['header'][$parCode]."</strong><br>";
					if(!in_array($parCode,$arRules['noLabel']))
						$paramsSrt.=GetMessage("IPOLSDEK_JS_SOD_$parCode").": ";

					switch($parCode){
						case "AS"      : foreach($parVal as $code => $noThing)
											 if(array_key_exists($code,$adServises))
												 $paramsSrt.= $adServises[$code]['NAME']." (".$code."), ";
										 $paramsSrt = substr($paramsSrt,0,strlen($paramsSrt)-2)."<br>";
										 break;
						case "GABS"    : $paramsSrt.= $parVal['D_L']."x".$parVal['D_W']."x".$parVal['D_H']." ".GetMessage("IPOLSDEK_cm")." ".$parVal['W']." ".GetMessage('IPOLSDEK_kg');break;
						case "service" : $paramsSrt.=$tarifs[$parVal]['NAME']."<br>"; break;
						case "courierTimeBeg": $paramsSrt.= GetMessage("IPOLSDEK_JS_SOD_courierTime").": ".$parVal." - ".$reqParams["courierTimeEnd"]."<br>"; break;
						case "courierTimeEnd": break;
						case "packs"   : foreach($parVal as $place => $params){
											$paramsSrt.="<span style='font-style:italic'>".GetMessage('IPOLSDEK_JS_SOD_Pack')." ".$place."</span><br>";
											$paramsSrt.=GetMessage('IPOLSDEK_dims').": ".$params['gabs']." (".GetMessage('IPOLSDEK_cm').")<br>";
											$paramsSrt.=GetMessage('IPOLSDEK_weight').": ".$params['weight']." ".GetMessage('IPOLSDEK_kg')."<br>";
											$paramsSrt.=GetMessage('IPOLSDEK_goods').": ";
											foreach($params['goods'] as $gId => $cnt )
												$paramsSrt.=$gId." ($cnt), ";
											$paramsSrt = substr($paramsSrt,0,strlen($paramsSrt)-2)."<br>";
										 };
										 break;
						case 'toPay'   : 
						case 'deliveryP' : $paramsSrt.=$parVal." ".GetMessage('IPOLSDEK_JSC_SOD_RUB')."<br>"; break;
						case 'departure':
						case 'location' : $city = sqlSdekCity::getBySId($parVal);
											$paramsSrt.= $city['NAME']." (".$city['REGION'].")<br>";
						break;
						default        : $paramsSrt.=$parVal."<br>"; break;
					}
				}
					
				$message=unserialize($request['MESSAGE']);
				$message=implode('<br>',$message);
				
				$addClass='';
				if($request['STATUS']=='OK')
					$addClass='IPOLSDEK_TblStOk';
				if($request['STATUS']=='ERROR')
					$addClass='IPOLSDEK_TblStErr';
				if($request['STATUS']=='TRANZT')
					$addClass='IPOLSDEK_TblStTzt';	
				if($request['STATUS']=='DELETE')
					$addClass='IPOLSDEK_TblStDel';
				if($request['STATUS']=='STORE')
					$addClass='IPOLSDEK_TblStStr';			
				if($request['STATUS']=='CORIER')
					$addClass='IPOLSDEK_TblStCor';		
				if($request['STATUS']=='PVZ')
					$addClass='IPOLSDEK_TblStPVZ';			
				if($request['STATUS']=='OTKAZ')
					$addClass='IPOLSDEK_TblStOtk';			
				if($request['STATUS']=='DELIVD')
					$addClass='IPOLSDEK_TblStDvd';

				if($isConverted){
					if($request['SOURCE'] == 1){
						$oId = self::oIdByShipment($request['ORDER_ID']);
						$arActions = array(
							'link'    => '/bitrix/admin/sale_order_shipment_edit.php?order_id='.$oId.'&shipment_id='.$request['ORDER_ID'].'&lang=ru',
							'delete'  => 'IPOLSDEK_table.delReq('.$request['ORDER_ID'].',\\\'shipment\\\');',
							'print'   => 'IPOLSDEK_table.print('.$request['ORDER_ID'].',\\\'shipment\\\')',
							'destroy' => 'IPOLSDEK_table.killReq('.$request['ORDER_ID'].',\\\'shipment\\\')',
						);
					}else
						$arActions = array(
							'link'    => '/bitrix/admin/sale_order_view.php?ID='.$request['ORDER_ID'].'&lang=ru',
							'delete'  => 'IPOLSDEK_table.delReq('.$request['ORDER_ID'].',\\\'order\\\');',
							'print'   => 'IPOLSDEK_table.print('.$request['ORDER_ID'].',\\\'order\\\')',
							'destroy' => 'IPOLSDEK_table.killReq('.$request['ORDER_ID'].',\\\'order\\\')',
						);
				}else
					$arActions = array(
						'link'    => 'sale_order_detail.php?ID='.$request['ORDER_ID'].'&lang=ru',
						'delete'  => 'IPOLSDEK_table.delReq('.$request['ORDER_ID'].',\\\'order\\\');',
						'print'   => 'IPOLSDEK_table.print('.$request['ORDER_ID'].',\\\'order\\\')',
						'destroy' => 'IPOLSDEK_table.killReq('.$request['ORDER_ID'].',\\\'order\\\')',
					);

				$contMenu='<td class="adm-list-table-cell adm-list-table-popup-block" onclick="BX.adminList.ShowMenu(this.firstChild,[{\'DEFAULT\':true,\'GLOBAL_ICON\':\'adm-menu-edit\',\'DEFAULT\':true,\'TEXT\':\''.GetMessage('IPOLSDEK_STT_TOORDR').'\',\'ONCLICK\':\'BX.adminPanel.Redirect([],\\\''.$arActions['link'].'\\\', event);\'}';
				if($request['STATUS']=='ERROR' || $request['STATUS']=='NEW' || $request['STATUS']=='DELETE')
					$contMenu.=',{\'GLOBAL_ICON\':\'adm-menu-delete\',\'TEXT\':\''.GetMessage('IPOLSDEK_JSC_SOD_DELETE').'\',\'ONCLICK\':\''.$arActions['delete'].'\'}';
				else
					$contMenu.=',{\'GLOBAL_ICON\':\'adm-menu-view\',\'TEXT\':\''.GetMessage('IPOLSDEK_JSC_SOD_FOLLOW').'\',\'ONCLICK\':\'IPOLSDEK_table.follow('.$request['SDEK_ID'].');\'}';
				if($request['STATUS']=='OK'){
					$contMenu.=',{\'TEXT\':\''.GetMessage('IPOLSDEK_JSC_SOD_PRNTSH').'\',\'ONCLICK\':\''.$arActions['print'].'\'}';
					$contMenu.=',{\'GLOBAL_ICON\':\'adm-menu-delete\',\'TEXT\':\''.GetMessage('IPOLSDEK_JSC_SOD_DESTROY').'\',\'ONCLICK\':\''.$arActions['destroy'].'\'}';
				}
				$contMenu.='])"><div class="adm-list-table-popup"></div></td>';
				$strHtml.='<tr class="adm-list-table-row '.$addClass.'">
								'.$contMenu.'
								<td class="adm-list-table-cell"><div>'.$request['ID'].'</div></td>
								<td class="adm-list-table-cell"><div>'.$request['MESS_ID'].'</div></td>
								<td class="adm-list-table-cell"><div><a href="'.$arActions['link'].'" target="_blank">'.$request['ORDER_ID'].'</a></div></td>
								<td class="adm-list-table-cell"><div>'.$request['STATUS'].'</div></td>
								<td class="adm-list-table-cell"><div>'.$request['SDEK_ID'].'</div></td>';
				if($isConverted)
					$strHtml.='<td class="adm-list-table-cell"><div>'.(($request['SOURCE'] == 1)?GetMessage('IPOLSDEK_STT_shipment'):GetMessage('IPOLSDEK_STT_order')).'</div></td>';
				$strHtml.='<td class="adm-list-table-cell"><div><a href="javascript:void(0)" onclick="IPOLSDEK_table.shwPrms($(this).siblings(\'div\'))">'.GetMessage('IPOLSDEK_STT_SHOW').'</a><div style="height:0px; overflow:hidden">'.$paramsSrt.'</div></div></td>
								<td class="adm-list-table-cell"><div>'.$message.'</div></td>
								<td class="adm-list-table-cell"><div>'.date("d.m.y H:i",$request['UPTIME']).'</div></td>
							</tr>';
			}

			echo json_encode(
				self::zajsonit(
					array(
						'ttl'=>$requests->NavRecordCount,
						'mP'=>$requests->NavPageCount,
						'pC'=>$requests->NavPageSize,
						'cP'=>$requests->NavPageNomer,
						'sA'=>$requests->NavShowAll,
						'html'=>$strHtml
					)
				)
			);
		}


		/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Функции для печати
			== getOrderInvoice ==  == killOldInvoices == == displayActPrint ==  == OnBeforePrologHandler ==
		()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/
		
		function getOrderInvoice($orders){ // получаем квитанцию от сдека
			self::killOldInvoices(); //удаляем старые квитанции
			if(!$orders){
				return array(
					'result' => 'error',
					'error'  => 'No order id'
				);
			}
			if(!is_array($orders))
				$orders = array('order' => $orders);

			$XML = '';
			$arMade = array();
			$ttlOrders = 0;
			foreach($orders as $mode => $IDs){
				$requests = sqlSdekOrders::select(array(),array("ORDER_ID"=>$IDs,"SOURCE"=>($mode == 'order')?0:1));
				while($request=$requests->Fetch()){
					if($request['SDEK_ID'])
						$XML.='<Order DispatchNumber="'.$request['SDEK_ID'].'"/>';
						$arMade[$mode][]=$request['ORDER_ID'];
						$ttlOrders ++;
					}
			}
			if(!count($arMade)){
				return array(
					'result' => 'error',
					'error'  => 'No orders founded'
				);
			}
			$headers = self::getXMLHeaders();
			$copies = (int)COption::GetOptionString(self::$MODULE_ID,"numberOfPrints",2);
			if(!$copies) $copies = 1;
			$XML = '<?xml version="1.0" encoding="UTF-8" ?>
			<OrdersPrint Date="'.$headers['date'].'" Account="'.$headers['account'].'" Secure="'.$headers['secure'].'"  OrderCount="'.$ttlOrders.'" CopyCount="'.$copies.'">'.$XML."</OrdersPrint>";
			$result = self::sendToSDEK($XML,"orders_print");
			if(strpos($result['result'],'<')===0){
				$answer = simplexml_load_string($result['result']);
				$errAnswer = '';
				foreach($answer->OrdersPrint as $print)
					$errAnswer .= $print['Msg'].". ";
				foreach($answer->Order as $print)
					$errAnswer .= $print['Msg'].". ";
				return array(
					'result' => 'error',
					'error'  => $errAnswer
				);
			}else{
				if(!file_exists($_SERVER['DOCUMENT_ROOT']."/upload/".self::$MODULE_ID))
					mkdir($_SERVER['DOCUMENT_ROOT']."/upload/".self::$MODULE_ID);
				$mTime = mktime();
				file_put_contents($_SERVER['DOCUMENT_ROOT']."/upload/".self::$MODULE_ID."/".$mTime.".pdf",$result['result']);
				$arReturn = array(
					'result' => 'ok',
					'file'   => $mTime.".pdf"
				);
				foreach($arMade as $mode => $ids){
					$diff = array_diff($orders[$mode],$ids);
					if(count($diff))
						$arReturn['errors'] .= implode(', ',$diff).", ";
				}
				if(array_key_exists('errors',$arReturn))
					$arReturn['errors'] = substr($arReturn['errors'],0,strlen($arReturn['errors'])-2);
				return $arReturn;
			}
		}
		function killOldInvoices(){ // удаляет старые файлы с инвойсами
			$dirPath = $_SERVER['DOCUMENT_ROOT']."/upload/".self::$MODULE_ID."/";
			$dirContain = scandir($dirPath);
			foreach($dirContain as $contain){
				if(strpos($contain,'.pdf')!==false && (mktime() - (int)filemtime($dirPath.$contain)) > 1300)
					unlink($dirPath.$contain);
			}
		}

		function displayActPrint(&$list){ // действие для печати актов
			if (!empty($list->arActions))
				CJSCore::Init(array('ipolSDEK_printOrderActs'));
			if($GLOBALS['APPLICATION']->GetCurPage() == "/bitrix/admin/sale_order.php")
				$list->arActions['ipolSDEK_printOrderActs'] = GetMessage("IPOLSDEK_SIGN_PRNTSDEK");
		}

		function OnBeforePrologHandler(){ // нажатие на печать актов
			if(!array_key_exists('action', $_REQUEST) || !array_key_exists('ID', $_REQUEST) || $_REQUEST['action'] != 'ipolSDEK_printOrderActs')
				return;
			$ifActs = (COption::GetOptionString(self::$MODULE_ID,'prntActOrdr','O') == 'A')?true:false; // другой способ печати документов, если true, печатаем только акт

			$unFounded  = array(); // не найденные (не отосланные) заказы
			$arRequests = array(); // все заявки вида тип => массив id-шников
			$requests = sqlSdekOrders::select(array(),array("ORDER_ID"=>$_REQUEST["ID"],'SOURCE'=>0));
				while($request=$requests->Fetch()){
					if(!$request['SDEK_ID'])
						$unFounded[$request['ORDER_ID']] = true;
					else
						$arRequests['order'][] = $request['ORDER_ID'];
				}
			foreach($_REQUEST["ID"] as $orderId)
				if(!in_array($orderId,$arRequests['order']))
					$unFounded[$orderId] = true;

			if(count($unFounded) && self::isConverted()){
				\Bitrix\Main\Loader::includeModule('sale');
				$arShipments = array();
				foreach(array_keys($unFounded) as $id){
					$shipments = Bitrix\Sale\Shipment::getList(array('filter'=>array('ORDER_ID' => $id)));
					while($shipment=$shipments->Fetch())
						$arShipments[$shipment['ID']] = $shipment['ORDER_ID'];
				}
				$requests = sqlSdekOrders::select(array(),array("ORDER_ID"=>array_keys($arShipments),'SOURCE'=>1));
				while($request=$requests->Fetch()){
					if($request['SDEK_ID']){
						$arRequests['shipment'][] = $request['ORDER_ID'];
						unset($unFounded[$arShipments[$request['ORDER_ID']]]);
					}
				}
			}
			$badOrders = (count($unFounded)) ? implode(',',array_keys($unFounded)) : false;
			if(!$ifActs){
				$shtrihs = self::getOrderInvoice($arRequests);
				$badOrders .= ($shtrihs['errors']) ? '\n'.$shtrihs['errors'] : ''; // errors - расхождения, error - если коллапс
			}
			?>
			<script type="text/javascript">
				<?if(count($arRequests) && !$shtrihs['error']){
					if(self::canShipment()){?>
						window.open('/bitrix/js/<?=self::$MODULE_ID?>/printActs.php?orders=<?=implode(":",$arRequests['order'])?>&shipments=<?=implode(":",$arRequests['shipment'])?>','_blank');
					<?}else{?>
						window.open('/bitrix/js/<?=self::$MODULE_ID?>/printActs.php?ORDER_ID=<?=implode(":",$arRequests['order'])?>','_blank');
					<?}
					if(!$ifActs && $shtrihs['file']){?>
						window.open('/upload/<?=self::$MODULE_ID?>/<?=$shtrihs['file']?>','_blank');
					<?}
					if($badOrders){?>
						alert('<?=GetMessage("IPOLSDEK_PRINTERR_BADORDERS").$badOrders?>');
					<?}?>
				<?}else{?>
					alert('<?=GetMessage("IPOLSDEK_PRINTERR_TOTALERROR").'\n'.$shtrihs['error']?> ');
				<?}?>
			</script>
		<?}

		function formActArray(){
			if(!cmodule::includeModule('sale')) return;
			if(self::canShipment())
				$arIds = array('order'=>explode(":",$_REQUEST['orders']),'shipment'=>explode(":",$_REQUEST['shipments']));
			else
				$arIds = array('order'=>explode(":",$_REQUEST['ORDER_ID']));
			$arOrders = array();
			$ttlPay = 0;
			$dWeight = COption::GetOptionString($module_id,'weightD',1000);
			foreach($arIds as $mode => $arId)
				if(count($arId))
					foreach($arId as $id){
						$req=sqlSdekOrders::select(array(),array('ORDER_ID'=>$id,'SOURCE'=>($mode == 'shipment') ? 1 : 0))->Fetch();
						if(!$req)
							continue;
						$params = unserialize($req['PARAMS']);
						$baze  = ($mode == 'shipment') ? self::getShipmentById($id) : CSaleOrder::GetById($id);
						$price = array_key_exists('toPay',$params) ? $params['toPay'] : ((float)($baze['PRICE'] - $baze['PRICE_DELIVERY']));
						$toPay = (array_key_exists('toPay',$params) && array_key_exists('deliveryP',$params)) ? ($params['toPay'] + $params['deliveryP']) : (($params['isBeznal']=='Y') ? 0 : (float)$baze['PRICE']);
						$arOrders[] = array(
							'ID'     => ($baze['ACCOUNT_NUMBER']) ? $baze['ACCOUNT_NUMBER'] : $id,
							'SDEKID' => $req['SDEK_ID'],
							'WEIGHT' => ($params['GABS']['W'])?$params['GABS']['W']:($dWeight)/1000,
							'PRICE'  => $price,
							'TOPAY'  => $toPay
						);
						$ttlPay+=$price;
					}
			return array('arOrders' => $arOrders, 'ttlPay' => $ttlPay);
		}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												Отображение опций
	== placeFAQ ==  == placeHint ==  == getSDEKCity ==  == printSender ==  == placeStatuses ==  == makeSelect ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		function placeFAQ($code){?>
				<a class="ipol_header" onclick="$(this).next().toggle(); return false;"><?=GetMessage('IPOLSDEK_FAQ_'.$code.'_TITLE')?></a>
				<div class="ipol_inst"><?=GetMessage('IPOLSDEK_FAQ_'.$code.'_DESCR')?></div>
		<?}

		function placeHint($code){?>
			<div id="pop-<?=$code?>" class="b-popup" style="display: none; ">
				<div class="pop-text"><?=GetMessage("IPOLSDEK_HELPER_".$code)?></div>
				<div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
			</div>
		<?}

		function getSDEKCity($city){
			$cityId = self::getNormalCity($city);
			$SDEKcity = sqlSdekCity::getByBId($cityId);
			return $SDEKcity;
		}

		function printSender($city){
			$SDEKcity = self::getSDEKCity($city);
			if(!$SDEKcity)
				echo "<tr><td colspan='2'>".GetMessage('IPOLSDEK_LABEL_NOSDEKCITY')."</td><tr>";
			else{
				COption::SetOptionString(self::$MODULE_ID,'departure',$SDEKcity['BITRIX_ID']);
				echo "<tr><td>".GetMessage('IPOLSDEK_OPT_depature')."</td><td>".($SDEKcity['NAME'])."</td><tr>";
			}
		}

		function placeStatuses($option){
			if(self::canShipment()){
				$arStatuses = array();
				$arStShipment = array();
				foreach($option as $key => $val)
					if(strpos($val[0],'status') !== false){
						unset($option[$key]);
						$arStatuses[] = $val;
					}elseif(strpos($val[0],'stShipment') !== false){
						unset($option[$key]);
						$arStShipment[] = $val;
					}
				ShowParamsHTMLByArray($option);
			?><tr><td></td><td><div class='IPOLSDEK_sepTable'><?=GetMessage('IPOLSDEK_STT_order')?></div><div class='IPOLSDEK_sepTable'><?=GetMessage('IPOLSDEK_STT_shipment')?></div></td></tr><?
			foreach($arStatuses as $key => $description){?>
				<tr>
					<td><?=$description[1]?></td>
					<td>
						<div class='IPOLSDEK_sepTable'>
							<?self::makeSelect($description[0],$description[4],COption::GetOptionString(self::$MODULE_ID,$description[0],''));?>
						</div>
						<div class='IPOLSDEK_sepTable'>
							<?
							$name = str_replace('status','stShipment',$description[0]);
							self::makeSelect($name,$arStShipment[$key][4],COption::GetOptionString(self::$MODULE_ID,$name,''));?>
						</div>
					</td>
				</tr>
			<?}

			}else
				ShowParamsHTMLByArray($option);
		}

		function makeSelect($id,$vals,$def=false,$atrs=''){?>
			<select <?if($id){?>name='<?=$id?>' id='<?=$id?>'<?}?> <?=$atrs?>>
			<?foreach($vals as $val => $sign){?>
				<option value='<?=$val?>' <?=($def == $val)?'selected':''?>><?=$sign?></option>
			<?}?>
			</select>
		<?}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												Функции для опций
	== killSchet ==  == killUpdt ==  == clearCache ==  == printOrderInvoice ==  == killReqOD ==  == delReqOD ==  == callOrderStates ==  == callUpdateList ==  == goSlaughterCities ==  == senders == 
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		function killSchet(){ // Сбрасываем счетчик заявок в опциях
			if(!self::isAdmin()) return false;
			echo COption::SetOptionString(self::$MODULE_ID,'schet',0);
		}

		function killUpdt($wat){ // Убираем информацию об обновлении
			if(unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/hint.txt"))
				echo 'done';
			else
				echo 'fail';
		}

		function clearCache(){//Очистка кэша
			$obCache = new CPHPCache();
			$obCache->CleanDir('/IPOLSDEK/');
			echo "Y";
		}

		function printOrderInvoice($params){ // печать заказа
			if(!array_key_exists('mode',$params))
				$params['mode'] = 'order';
			$resPrint = self::getOrderInvoice(array($params['mode'] => $params['oId']));
			echo json_encode(self::zajsonit($resPrint));
		}

		function killReqOD($params,$mode=false){// удаление заявки из СДЕКа
			if(!self::isAdmin()) return false;
			$oid = (is_array($params)) ? $params['oid'] : $params;
			if(!$mode)
				$mode = (array_key_exists('mode',$params)) ? $params['mode'] : 'order';
			if(sdekdriver::deleteRequest($oid,$mode))
				echo "GD:".GetMessage("IPOLSDEK_DRQ_DELETED");
			else
				echo self::getAnswer();
		}

		function delReqOD($params,$mode=false){// удаление заявки из БД
			if(!self::isAdmin()) return false;
			$oid = (is_array($params)) ? $params['oid'] : $params;
			if(!$mode)
				$mode = (array_key_exists('mode',$params)) ? $params['mode'] : 'order';
			if(self::CheckRecord($oid,$mode))
				sqlSdekOrders::Delete($oid,$mode);
			echo GetMessage("IPOLSDEK_DRQ_DELETED");
		}

		function callOrderStates(){ // запрос статусов заказов из опций
			self::getOrderStates();
			$err = self::getErrors();
			echo ($err)?($err):date("d.m.Y H:i:s",COption::GetOptionString(self::$MODULE_ID,'statCync',mktime()));
		}

		function callUpdateList($params){ // запрос на синхронизацию из опций
			if(!array_key_exists('citiesDone',$params)){
				$us=self::updateCities();
				if($us['result'] == 'error')
					$arReturn = array(
						'result' => 'error',
						'text'	 => GetMessage("IPOLSDEK_SYNCTY_ERR_HAPPENING")." ".$us['result'],
					);
				else{
					$arReturn = array(
						'result' => $us['result'],
						'text'   => ($us['result'] == 'end') ? GetMessage('IPOLSDEK_SYNCTY_LBL_SCD') : GetMessage('IPOLSDEK_SYNCTY_LBL_PROCESS')." ".$us['done']."/".$us['total']
					);
				}
			}else{
				if(self::updateList())
					$arReturn = array(
						'result' => 'done',
						'text'   => GetMessage('IPOLSDEK_UPDT_DONE').date("d.m.Y H:i:s",filemtime($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/list.php")),
					);
				else
					$arReturn = array(
						'result' => 'error',
						'text'   => GetMessage('IPOLSDEK_UPDT_ERR'),
					);
			}
			echo json_encode(self::zajsonit($arReturn));
		}
		
		function goSlaughterCities($params){ // переопределение городов
			if(!self::isAdmin()) return false;
			$result = self::slaughterCities();
			if($result == 'done'){
				$us=self::updateCities();
				if($us['result']!='error')
					echo "done";
				else
					echo GetMessage("IPOLSDEK_ERRLOG_ERRSUNCCITY")." ".$us['error'];
			}else
				GetMessage("IPOLSDEK_DELCITYERROR")." ".$result;
		}
	
		function senders($params = false){
			if(!self::isAdmin('R')) return false;
			$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.self::$MODULE_ID.'/senders.txt';
			if($params){
				$dir = $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.self::$MODULE_ID;
				if(!file_exists($dir))
					mkdir($dir);
				return file_put_contents($path,serialize($params));
			}
			elseif(file_exists($path))
				return unserialize(file_get_contents($path));
			else
				return false;
		}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												Функции для агентов
	== agentUpdateList ==  == agentOrderStates ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		function agentUpdateList(){ // вызов обновления списка городов, самовывозов и услуг
			if(!self::updateList())
				self::errorLog(GetMessage('IPOLSDEK_UPDT_ERR'));
			self::updateCities();
			return 'sdekOption::agentUpdateList();';
		}

		function agentOrderStates(){ // вызов обновления статусов заказов
			self::getOrderStates();
			sdekOption::killOldInvoices(); // удаляем заодно старые печати к заказам
			return 'sdekOption::agentOrderStates();';
		}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Синхронизации
		== getOrderStates ==  == updateList == == updateCities == == requestCityFile ==  == slaughterCities ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

		function getOrderStates(){//запрос статусов заказов
			if(!cmodule::includemodule('sale')){self::errorLog(GetMessage("IPOLSDEK_ERRLOG_NOSALEOOS"));return false;}//без модуля sale делать нечего

			$headers = self::getXMLHeaders();
			$return = false;

			$dateFirst = date("Y-m-d",COption::GetOptionString(self::$MODULE_ID,'statCync',0));

			$XML = '<?xml version="1.0" encoding="UTF-8" ?>
			<StatusReport Date="'.$headers['date'].'" Account="'.$headers['account'].'" Secure="'.$headers['secure'].'">
				<ChangePeriod DateFirst="'.$dateFirst.'" DateLast="'.date('Y-m-d').'"/>
			</StatusReport>
			';

			$result = self::sendToSDEK($XML,'status_report_h');
			if($result['code'] != 200)
				self::errorLog(GetMessage("IPOLSDEK_GOS_UNBLSND").GetMessage("IPOLSDEK_ERRORLOG_BADRESPOND").$result['code']);
			else{
				$xml = simplexml_load_string($result['result']);

				$arStateCorr = array(
					 1 => "OK",
					 2 => "DELETE",
					 3 => "STORE",
					 4 => "DELIVD",
					 5 => "OTKAZ",
					 6 => "TRANZT",
					 7 => "TRANZT",
					 8 => "TRANZT",
					 9 => "TRANZT",
					10 => "TRANZT",
					11 => "CORIER",
					12 => "PVZ",
					13 => "TRANZT",
					16 => "TRANZT",
					17 => "TRANZT",
					18 => "TRANZT",
					19 => "TRANZT",
					20 => "TRANZT",
					21 => "TRANZT",
					22 => "TRANZT"
				);

				global $USER;
				if(!is_object($USER))
					$USER = new CUser();

				$arOrders = array();
				foreach($xml->Order as $orderMess){
					$state = (int)$orderMess->Status['Code'];
					if(array_key_exists($state,$arStateCorr)){// описан ли статус
						$arOrder = sqlSdekOrders::select(array(),array('SDEK_ID' => (string)$orderMess['DispatchNumber']))->Fetch();
						if(!$arOrder) // not from API
							continue;
						$mode = ($arOrder['SOURCE'] == 1 ) ? 'shipment' : 'order';
						if($arStateCorr[$state] == 'DELETE')
							sqlSdekOrders::Delete($arOrder['ORDER_ID'],$mode);
						if($arOrder['OK']){
							if(!sqlSdekOrders::updateStatus(array(
								"ORDER_ID" => $arOrder['ORDER_ID'],
								"STATUS"   => $arStateCorr[$state],
								"SOURCE"   => $arOrder['SOURCE']
							)))
								self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_CANTUPDATEREQ').$arOrder['ORDER_ID'].". ".GetMessage('IPOLSDEK_GOS_STATUS').$arStateCorr[$state].".");
							else{
								$newStat = COption::GetOptionString(self::$MODULE_ID,(($arOrder['SOURCE'] == 1)?"stShipment":"status").$arStateCorr[$state],false);
								if($newStat && strlen($newStat) < 3){
									if($arOrder['SOURCE'] == 1){ // отправление
										$shipment = self::getShipmentById($arOrder['ORDER_ID']);
										if($shipment['STATUS_ID'] != $newStat)
											if(!self::setShipmentField($arOrder['ORDER_ID'],'STATUS_ID',$newStat))
												self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_CANTUPDATESHP').$arOrder['ORDER_ID'].". ".GetMessage('IPOLSDEK_GOS_STATUS').$arStateCorr[$state].".");
									}else{ // заказ
										$order = CSaleOrder::GetByID($arOrder['ORDER_ID']);
										if($order['STATUS_ID'] != $newStat){
											if(!CSaleOrder::StatusOrder($arOrder['ORDER_ID'],$newStat))
												self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_CANTUPDATEORD').$arOrder['ORDER_ID'].". ".GetMessage('IPOLSDEK_GOS_STATUS').$arStateCorr[$state].".");
										}
										if(
											$state == 4 && 
											COption::GetOptionString(self::$MODULE_ID,"markPayed",false) == 'Y' &&
											$order['PAYED'] != 'Y'
										)
												if(!CSaleOrder::PayOrder($arOrder['ORDER_ID'],"Y"))
													self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_CANTMARKPAYED').$arOrder['ORDER_ID'].". ");
									}
								}
							}
						}else // попытка оформить неподтвержденный заказ
							self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_BADREQTOUPDT'.$mode).$arOrder['ORDER_ID'].". ".GetMessage('IPOLSDEK_GOS_STATUS').$arStateCorr[$state].".");
					}else
						self::errorLog(GetMessage("IPOLSDEK_GOS_HASERROR").GetMessage("IPOLSDEK_GOS_UNKNOWNSTAT").((int)$orderMess['Number'])." : ".$state." (".(string)$orderMess->Status['Description']."). ".GetMessage("IPOLSDEK_GOS_NOTUPDATED"));
				}
				COption::SetOptionString(self::$MODULE_ID,'statCync',mktime());
			}
		}

		function updateList(){ // обновление списка пунктов самовывоза
			self::ordersNum();
			$errors = false;
			$request = self::sendToSDEK(false,'pvzlist','type=ALL');
			if($request['code'] == 200){
				$arList = array();
				$xml=simplexml_load_string($request['result']);
				foreach($xml as $key => $val){
					$cityCode = (string)$val['CityCode'];
					if(!sqlSdekCity::getBySId($cityCode))
						continue;
					$type = (string)$val['Type'];
					$city = (string)$val["City"];
					if(strpos($city,'(') !== false)
						$city = trim(substr($city,0,strpos($city,'(')));
					if(strpos($city,',') !== false)
						$city = trim(substr($city,0,strpos($city,',')));
					$code = (string)$val["Code"];
					$arList[$type][$city][$code]=array(
						'Name'     => (string)$val['Name'],
						'WorkTime' => (string)$val['WorkTime'],
						'Address'  => (string)$val['Address'],
						'Phone'    => (string)$val['Phone'],
						'Note'     => (string)$val['Note'],
						'cX'       => (string)$val['coordX'],
						'cY'       => (string)$val['coordY'],
					);
					if($val->WeightLimit){
						$arList[$type][$city][$code]['WeightLim'] = array(
							'MIN' => (float)$val->WeightLimit['WeightMin'],
							'MAX' => (float)$val->WeightLimit['WeightMax']
						);
					}
				}
			}
			else{
				$strInfo = GetMessage('IPOLSDEK_FILE_UNBLUPDT').$request['code'].".";
				$errors = true;
			}
			file_put_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/list.php",json_encode($arList));
			if($strInfo && COption::GetOptionString(self::$MODULE_ID,'logged',false)){
				$file=fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/hint.txt","a");
				fwrite($file,"<br><br><strong>".date('d.m.Y H:i:s')."</strong><br>".$strInfo);
				fclose($file);
			}

			if(!COption::GetOptionString(self::$MODULE_ID,'logged',false) && $request['code']!=200)
				return array('code' => $request['code']);
			return !$errors;
		}

		function updateCities($params=array()){
			$exportClass = false;
			cmodule::includeModule('sale');
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/tmpExport.txt"))
				$exportClass = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/tmpExport.txt"));
			else
				$exportClass = new cityExport($params['timeout']);

			$exportClass->start();

			if($exportClass->result['result'] == 'error')
				self::errorLog(GetMessage("IPOLSDEK_ERRLOG_ERRSUNCCITY")." ".$exportClass->result['error']);

			if($params['mode'] == 'json')
				echo json_encode($exportClass->result);
			else
				return $exportClass->result;
		}

		function slaughterCities(){
			if(!self::isAdmin()) return false;
			global $DB;
			if($DB->Query("SELECT 'x' FROM ipol_sdekcities", true)){
				$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".self::$MODULE_ID."/install/db/mysql/unInstallCities.sql");
				if($errors !== false)
					return "error.".implode("", $errors);
				$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".self::$MODULE_ID."/install/db/mysql/installCities.sql");
				if($errors !== false)
					return "error.".implode("", $errors);
				return 'done';
			}
		}

		function requestCityFile(){
			$request = self::nativeReq('city.csv');
			if($request['code'] != '200'){
				self::errorLog(GetMessage('IPOLSDEK_FILEIPL_UNBLUPDT').$request['code']);
				return false;
			}
			file_put_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/city.csv",$request['result']);
			return true;
		}

		protected function ordersNum(){
			cmodule::includeModule('sale');
			// требование СДЭК по сбору статистики, сколько заявок сделано через модуль
			$lastId = COption::GetOptionString(self::$MODULE_ID,'lastSuncId',0);
			$arOrders = array();
			$bdReqs = sqlSdekOrders::select(array("ID","ASC"),array(">ID"=>$lastId,"OK"=>true));
			while($arReq=$bdReqs->Fetch()){
				$year  = date("Y",$arReq['UPTIME']);
				if(!array_key_exists($year,$arOrders))
					$arOrders[$year] = array();

				$month = date("m",$arReq['UPTIME']);
				if(array_key_exists($month,$arOrders[$year]))
					$arOrders[$year][$month]['vis'] += 1;
				else
					$arOrders[$year][$month]['vis'] = 1;
				$arOrders[$year][$month]['id'][] = $arReq['ORDER_ID'];
				if($lastId < $arReq['ID'])
					$lastId = $arReq['ID'];
			}

			foreach($arOrders as $year => $arYear)
				foreach($arYear as $month => $arMonth){
					$ttlPrice = 0;
					$orders = CSaleOrder::GetList(array(),array('ID'=>$arMonth['id']),false,false,array('ID','PRICE'));
					while($order=$orders->Fetch())
						$ttlPrice += $order['PRICE'];
					$arOrders[$year][$month]['prc'] = round($ttlPrice);
					unset($arOrders[$year][$month]['id']);
				}

			if(count($arOrders)){
				$request = self::nativeReq('sdekStat.php',array(
					'req' => json_encode(self::zajsonit(array(
						'reqs' => $arOrders,
						'acc'  => COption::GetOptionString(self::$MODULE_ID,'logSDEK',''),
						'host' => $_SERVER['SERVER_NAME'],
						'cms'  => 'bitrix'
					)))
				));
				if(
					$request['code']=='200' &&
					strpos($request['result'],'good') !== false
				)
					COption::SetOptionString(self::$MODULE_ID,'lastSuncId',$lastId);
			}
		}

		
		/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Импорт городов
			== setImport ==  == handleImport ==  == getCityTypeId ==
		()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		function setImport($mode = 'Y'){
			if(is_array($mode))
				$mode = (array_key_exists('mode',$mode)) ? $mode['mode'] : 'Y';
			COption::SetOptionString(self::$MODULE_ID,'importMode',$mode);
		}

		function handleImport($params){
			if(!self::isAdmin()) return false;
			cmodule::includeModule('sale');
			$fname = ($params['fileName']) ? $params['fileName'] : 'tmpImport.txt';
			switch($params['mode']){
				case 'setSync': $sync = self::updateCities($_REQUEST['timeOut']);
					if($sync['result'] == 'pause')
						 $arReturn = array(
							'text' => GetMessage('IPOLSDEK_IMPORT_PROCESS_SYNC').$sync['done'].GetMessage("IPOLSDEK_IMPORT_PROCESS_FROM").$sync['total'],
							'step' => 'contSync',
							'result' => $sync
						 );
					else
						$arReturn = array(
							'text' => GetMessage('IPOLSDEK_IMPORT_STATUS_SDONE')."<br><br>",
							'step' => 'startImport',
							'result' => $sync
						 );
				break;
				case 'setImport': 
					$importClass = new cityExport($params['timeOut'],$fname);
					$importClass->pauseImport();
					if($importClass->error)
						$arReturn = array(
							'text'   => GetMessage('IPOLSDEK_IMPORT_ERROR_lbl').$importClass->error,
							'step' 	 => false,
							'result' => 'error',
						);
					else
						$arReturn = array(
							'text'   => GetMessage('IPOLSDEK_IMPORT_STATUS_MDONE'),
							'step'   => 'init',
							'result' => $importClass->result,
						);
				break;
				case 'process' :
					if(!file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/{$fname}"))
						$arReturn = array(
							'text'   => GetMessage('IPOLSDEK_IMPORT_ERROR_NOFILES'),
							'step' 	 => false,
							'result' => 'error',
						);
					else{
						$importClass = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/{$fname}"));
						$importClass->loadCities();
						$errors = ($importClass->error) ? GetMessage('IPOLSDEK_IMPORT_ERROR_WHILEIMPORT')."<div class='IPOLSDEK_import_errors'>".$importClass->error."</div>" : '';
						if($importClass->result['result'] == 'end'){
							$arReturn = array(
								'text'   => GetMessage('IPOLSDEK_IMPORT_STATUS_IDONE').$importClass->result['added'].".".$errors ,
								'step' 	 => 'endImport',
								'result' => $importClass->result
							);
							self::setImport('N');
						}else
							$arReturn = array(
								'text'   => "> ".GetMessage('IPOLSDEK_IMPORT_PROCESS_'.$importClass->result['mode'])." ".GetMessage('IPOLSDEK_IMPORT_PROCESS_WORKING').($importClass->result['done']).GetMessage('IPOLSDEK_IMPORT_PROCESS_FROM').$importClass->result['total']." ".$errors,
								'step' 	 => 'process',
								'result' => 'process',
							);
					}
				break;
			}
			if($params['noJson'])
				return $arReturn;
			else
				echo json_encode(sdekdriver::zajsonit($arReturn));
		}

		function getCityTypeId(){
			if(!sdekdriver::isLocation20()) return;
			$tmp = \Bitrix\Sale\Location\TypeTable::getList(array('select'=>array('*'),'filter'=>array('CODE'=>'CITY')))->Fetch();
			return (is_array($tmp) && array_key_exists('ID',$tmp)) ? $tmp['ID'] : false;
		}


		/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Связки и общие
			== select ==  == CheckRecord ==  == nativeReq ==
		()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		public static function select($arOrder=array("ID","DESC"),$arFilter=array(),$arNavStartParams=array()){ // выборка
			if(!self::isAdmin('R')) 
				return false; 
			return sqlSdekOrders::select($arOrder,$arFilter,$arNavStartParams);
		}
		public static function CheckRecord($orderId,$mode='order'){// проверка наличия заявки для заказа / отгрузки
			if(!self::isAdmin('R')) 
				return false;
			return sqlSdekOrders::CheckRecord($orderId,$mode);
		}

		private function nativeReq($where,$what=false){
			if(!$where) return false;
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,'http://ipolh.com/webService/sdek/'.$where);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			if($what){
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $what);
			}
			$result = curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return array(
				'result' => $result,
				'code'   => $code
			);
		}
	}
?>