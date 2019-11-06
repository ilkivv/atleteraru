<?
IncludeModuleLangFile(__FILE__);

/*
	onGoodsToRequest
*/

class sdekdriver extends sdekHelper{

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
		                            Формирование заявок на заказ
		== sendOrderRequest == -> == genOrderXML == ->  == getPacks == | == getGoods == -> getGoodsArray // == getMessId ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	function getMessId(){
		$mesId=(int)COption::GetOptionString(self::$MODULE_ID,'schet',0);
		COption::SetOptionString(self::$MODULE_ID,'schet',++$mesId);
		return $mesId;
	}

	// сборка упаковки в заказ
	protected function getPacks($oId,$mode,$orderParams){
		$arPacks = array();

		if(array_key_exists('packs',$orderParams) && $orderParams['packs'])
			foreach($orderParams['packs'] as $id => $content){
				$gabs = explode(' x ',$content['gabs']);
				$arPacks[$id]=array(
					'WEIGHT' => $content['weight']*1000,
					'LENGTH' => $gabs[0],
					'WIDTH'  => $gabs[1],
					'HEIGHT' => $gabs[2],
					'GOODS'  => self::getGoods($oId,$mode,$content['weight'],$content['goods'])
				);
			}
		else
			$arPacks[1]=array(
				'WEIGHT' => $orderParams["GABS"]['W']*1000,
				'LENGTH' => $orderParams["GABS"]['D_L'],
				'WIDTH'  => $orderParams["GABS"]['D_W'],
				'HEIGHT' => $orderParams["GABS"]['D_H'],
				'GOODS'  => self::getGoods($oId,$mode,$orderParams["GABS"]['W'])
			);

		return $arPacks; // вес - граммы, стороны - см
	}

	public function getGoodsArray($orderId,$shipmentID=false){
		if(!class_exists('CDeliverySDEK')) return array();
		$arGoods = CDeliverySDEK::getBasketGoods(array("ORDER_ID" => $orderId));
		$arGoods = CDeliverySDEK::handleBitrixComplects($arGoods);
		if($shipmentID && self::canShipment())
			$arGoods = CDeliverySDEK::filterShipmentGoods($shipmentID,$arGoods);
		$cntDims = CDeliverySDEK::getGoodsDimensions($arGoods);
		foreach($cntDims['goods'] as $goodId => $dimVals){
			$arGoods[$goodId]['WEIGHT']     = $dimVals['W']; 
			$arGoods[$goodId]['DIMENSIONS'] = array(
												'LENGTH' => $dimVals['D_L'],
												'WIDTH'  => $dimVals['D_W'],
												'HEIGHT' => $dimVals['D_H'],
											);
		}
		$hasIblock = cmodule::includemodule('iblock');
		$optARticul = COption::GetOptionString(self::$MODULE_ID,'articul',"ARTNUMBER");
		foreach($arGoods as $goodId => $good){
			$articul = false;
			if($optARticul && $hasIblock){
				$gd = CIBlockElement::GetList(array(),array('ID'=> $goodId,'LID'=>$good['LID']),false,false,array('ID','PROPERTY_'.$optARticul))->Fetch();
				if($gd && $gd["PROPERTY_{$optARticul}_VALUE"])
					$articul = $gd["PROPERTY_{$optARticul}_VALUE"];
			}
			$arGoods[$goodId]['ARTICUL'] = ($articul)?$articul:$goodId;
		}
		return $arGoods;
	}

	//сборка товаров в заказ
	protected function getGoods($oId,$mode,$givenWeight,$given=false){
		$givenWeight *= 1000;
		if($mode == 'order' || !self::canShipment())
			$arGoods = self::getGoodsArray($oId);
		else{
			$orderId = self::oIdByShipment($oId);
			$arGoods = self::getGoodsArray($orderId,$oId);
		}
		$arTG = array();

		$ttlWeight = 0;
		foreach($arGoods as $gId => $good){
			$doPack = ($given && array_key_exists($gId,$given));
			if(!$given || $doPack){
				$cnt = ($given) ? $given[$gId] : $good['QUANTITY'];
				$weight = ($good['WEIGHT']) ? $good['WEIGHT'] * 1000 : 1000;
				$arTG[$gId] = array(
					'price'    => $good['PRICE'],
					'cstPrice' => $good['PRICE'],
					'weight'   => $weight,
					'quantity' => $cnt,
					'name'     => $good['NAME'],
					'articul'  => $good['ARTICUL'],
				);
				
			}
		}

		foreach(GetModuleEvents(self::$MODULE_ID, "onGoodsToRequest", true) as $arEvent)
			ExecuteModuleEventEx($arEvent,Array(&$arTG,$oId));

		foreach($arTG as $gId => $vals)
			$ttlWeight += $vals['quantity'] * $vals['weight'];

		if($ttlWeight > $givenWeight){
			$kukan = floor($givenWeight *1000 / $ttlWeight);
			$ttlWeight = 0;
			foreach($arTG as $gId => $good){
				$nw = floor(($arTG[$gId]['weight'] * $kukan ) / $good['quantity']) / 1000;
				$arTG[$gId]['weight'] = $nw;
				$ttlWeight += $nw * $good['quantity'];
			}
		}

		if($ttlWeight < $givenWeight){
			$diff = $givenWeight - $ttlWeight;
			foreach($arTG as $gId => $good){
				if($good['quantity'] == 1)
					$applicant = $diff;
				else // really stupid stuff, but who cares
					$applicant = floor($diff * 1000 / $good['quantity']) / 1000;
				if($applicant * $good['quantity'] == $diff){
					$arTG[$gId]['weight'] += $applicant;
					$diff = 0;
					break;
				}
			}
			if($diff != 0) // if nothing helps
				foreach($arTG as $gId => $good){
					$arTG[$gId]['weight'] += round($diff * 1000 / $good['quantity']) / 1000;
					break;
				}
		}

		return $arTG;
	}

	//формирование xml заказа
	protected function genOrderXML($oId,$mesId=false,$mode=false){
		if(!self::isAdmin()){
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOACCESS'));
			return false;
		}
		if(!cmodule::includeModule('sale')) return;
		$headers = self::getXMLHeaders();

		$orderParams = self::GetByOI($oId,$mode);
		if(!$orderParams){
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOPARAMS'));
			return false;
		}
		$orderParams = unserialize($orderParams['PARAMS']);

		$baze = ($mode == 'shipment') ? self::getShipmentById($oId) : CSaleOrder::GetById($oId);




		$on = ($baze['ACCOUNT_NUMBER'])?$baze['ACCOUNT_NUMBER']:$oId;

		$bezNal = ($orderParams['isBeznal'] == 'Y')?true:false;

		if($mesId === false)
			$mesId=self::getMessId();

		$sendCity = self::getHomeCity();
		if(array_key_exists('courierCity',$orderParams) && $orderParams['courierCity']) 
			$sendCity = $orderParams['courierCity'];
		elseif(array_key_exists('departure',$orderParams) && $orderParams['departure'])
			$sendCity = $orderParams['departure'];

		$strXML = "<DeliveryRequest Number=\"".$mesId."\" Date=\"".$headers['date']."\" Account=\"".$headers['account']."\" Secure=\"".$headers['secure']."\" OrderCount=\"1\">
	<Order Number=\"".$on."\"
		SendCityCode=\"".$sendCity."\" 
		RecCityCode=\"".$orderParams["location"]."\" 
		RecipientName=\"".$orderParams["name"]."\" 
		";
		if($orderParams['email'])
			$strXML .= "RecipientEmail=\"".$orderParams['email']."\" ";
		$strXML .= "
		Phone=\"".$orderParams['phone']."\"";

		// стоимости доставки и упаковок
			// товары и упаковки
		if(!array_key_exists('toPay',$orderParams))
			$orderParams['toPay'] = 0;
		$packs = self::getPacks($oId,$mode,$orderParams);
		$goodsXML = '';
		foreach($packs as $number => $packContent){ // см, г
			$goodsXML .= "<Package Number=\"{$number}\" BarCode=\"{$number}\" Weight=\"{$packContent['WEIGHT']}\" SizeA=\"{$packContent['LENGTH']}\" SizeB=\"{$packContent['WIDTH']}\" SizeC=\"{$packContent['HEIGHT']}\">";
			foreach($packContent['GOODS'] as $goodId => $arGood){
				$toPay = ($bezNal || $orderParams['toPay'] == 0)?0:$arGood["price"];
				$cnt = (int)$arGood["quantity"];
				if($toPay){
					$all = $toPay * $cnt;
					if($all > $orderParams['toPay']){
						$toPay = $orderParams['toPay'] / $cnt;
						$orderParams['toPay'] = 0;
					}else
						$orderParams['toPay'] -= $all;
				}
				$goodsXML .= "
			<Item WareKey=\"".(($arGood["articul"])?$arGood["articul"]:$goodId)."\" Cost=\"".$arGood["cstPrice"]."\" Payment=\"".number_format($toPay,2,'.','')."\" Weight=\"".$arGood["weight"]."\" Amount=\"".$cnt."\" Comment=\"".str_replace('"',"'",$arGood["name"])."\"/>";
			}
			$goodsXML .= "
		</Package>
		";
		}

		if(!$bezNal){
			$priceDelivery = array_key_exists('deliveryP',$orderParams) ? $orderParams['deliveryP'] : $baze["PRICE_DELIVERY"];
			if($priceDelivery)
				$strXML .= "
		DeliveryRecipientCost=\"".$priceDelivery."\"";
		}

		if($orderParams['comment'])
			$strXML .= "
		Comment=\"".str_replace('"',"'",$orderParams['comment'])."\" ";
		$strXML .= "
		TariffTypeCode=\"".$orderParams['service']."\" ";
		if(array_key_exists('realSeller',$orderParams) && $orderParams['realSeller'])
			$strXML .= "
		SellerName=\"".$orderParams['realSeller']."\">
		";
		else
			$strXML .= ">
		";
		//адрес
		if($orderParams["PVZ"])
			$strXML .= "<Address PvzCode=\"".$orderParams["PVZ"]."\" />
		";
		elseif($orderParams["POSTOMAT"])
			$strXML .= "<Address PvzCode=\"".$orderParams["POSTOMAT"]."\" />
		";
		else
			$strXML .= "<Address Street=\"".str_replace('"',"'",$orderParams['street'])."\" House=\"".$orderParams['house']."\" Flat=\"".$orderParams['flat']."\" />
		";
		
		$strXML .= $goodsXML;

		if($payed){
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_CANTCALCPRICE'));
			return false;
		}

		//допуслуги
		if(array_key_exists('AS',$orderParams) && count($orderParams['AS']))
			foreach($orderParams['AS'] as $service => $nothing)
				$strXML .= "<AddService ServiceCode=\"".$service."\"></AddService>
		";

	$strXML .= "
	</Order>";
		if(in_array($orderParams['service'],self::getDoorTarifs()) && $orderParams['courierDate']){
			preg_match('/(\d\d).(\d\d).([\d]+)/',$orderParams['courierDate'],$matches);
			$orderParams['courierDate'] = $matches[3].'-'.$matches[2].'-'.$matches[1];
	$strXML .= "<CallCourier>
		<Call 
			Date=\"{$orderParams['courierDate']}\"
			TimeBeg=\"{$orderParams['courierTimeBeg']}\"
			TimeEnd=\"{$orderParams['courierTimeEnd']}\"
			SendCityCode=\"{$orderParams['courierCity']}\"
			Comment=\"{$orderParams['courierComment']}\"
			SendPhone=\"{$orderParams['courierPhone']}\"
			SenderName=\"{$orderParams['courierName']}\">
			<SendAddress 
				Street=\"{$orderParams['courierStreet']}\"
				House=\"{$orderParams['courierHouse']}\"
				Flat=\"{$orderParams['courierFlat']}\"
			/>
		</Call>
	</CallCourier>";
		}
	$strXML.="
</DeliveryRequest>";

		return $strXML;
	}

	function sendOrderRequest($oId,$mode='order'){
		if(!self::isAdmin()){
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOACCESS'));
			return false;
		}
		if(!$oId) return false;
		if(!cmodule::includemodule('sale')){self::errorLog(GetMessage("IPOLSDEK_ERRLOG_NOSALEOML"));return false;}//без модуля sale делать нечего
		$mesId=self::getMessId();
		$orderXML = self::genOrderXML($oId,$mesId,$mode);
		if(!$orderXML) return false;
		$sended = '';
		$result = self::sendToSDEK($orderXML,'new_orders');
		$return = false;
		if($result['code'] == 200){
			$xml=simplexml_load_string($result['result']);
			if($xml['ErrorCode'])
				self::toAnswer(self::zaDEjsonit($xml['Msg']),GetMessage("IPOLSDEK_SEND_UNBLSND").GetMessage("IPOLSDEK_ERRORLOG_ERRORCODE"));
			elseif($xml->DeliveryRequest['ErrorCode'])
				self::toAnswer(self::zaDEjsonit($xml->DeliveryRequest['Msg']),GetMessage("IPOLSDEK_SEND_UNBLSND").GetMessage("IPOLSDEK_ERRORLOG_ERRORCODE"));
			else{
				$arErrors = array();
				foreach($xml->Order as $orderMess){
					if($orderMess['ErrorCode'])
						$arErrors[(string)$orderMess['ErrorCode']] = (string)$orderMess['Msg'];
					elseif($orderMess['DispatchNumber']){
						$sended = (string)$orderMess['DispatchNumber'];
						if(COption::GetOptionString(self::$MODULE_ID,'setDeliveryId','Y') == 'Y'){
							if($mode == 'order')
								CSaleOrder::Update($oId,array('TRACKING_NUMBER'=>$sended));
							elseif(self::isConverted()) // <3 D7
								self::setShipmentField($oId,'TRACKING_NUMBER',$sended);
						}
					}
				}
				$status = (count($arErrors)) ? 'ERROR' : 'OK';
				sqlSdekOrders::updateStatus(array(
					"ORDER_ID" => $oId,
					"STATUS"   => $status,
					"SDEK_ID"  => $sended,
					"MESSAGE"  => serialize(self::zaDEjsonit($arErrors)),
					"MESS_ID"  => $mesId,
					"mode"     => $mode
				));
				if($status == 'ERROR')
					self::toAnswer(GetMessage("IPOLSDEK_SEND_NOTDENDED"));
				elseif(count($arErrors))
					self::toAnswer(GetMessage("IPOLSDEK_SEND_BADSENDED"));
				else{
					self::toAnswer(GetMessage("IPOLSDEK_SEND_SENDED"));
					$return = true;
				}
			}
		}
		else
			self::toAnswer(GetMessage("IPOLSDEK_SEND_UNBLSND").GetMessage("IPOLSDEK_ERRORLOG_BADRESPOND").$result['code']);
		return $return;
	}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												Манипуляции с информацией о заявках [БД + удаление]
	== updtOrder == == saveAndSend ==  == deleteRequest ==  
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	//База данных
	public static function GetByOI($ID,$mode=false){  // выбрать заявку по id заказа / отправления
		if(!self::isAdmin('R')) return false;
		return ($mode == 'shipment') ? sqlSdekOrders::GetBySI($ID) : sqlSdekOrders::GetByOI($ID);
	}

	function updtOrder($params){ // сохраняем информацию о заявке в БД, возвращаем ее ID
		if(!self::isAdmin()){
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOACCESS'));
			return false;
		}
		$params=self::zaDEjsonit($params);
		
		$arNeedFields = array('service','location','name','phone');
		if(in_array("PVZ",$params))
			$arNeedFields[]="PVZ";
		else
			array_merge($arNeedFields,array('street','house','flat'));
		if(in_array("courierDate",$params))
			array_merge($arNeedFields,array('courierDate','courierTimeBeg','courierTimeEnd','courierCity','courierStreet','courierHouse','courierFlat','courierPhone','courierName'));

		foreach($params as $prop => $val){
			if(in_array($prop,$arNeedFields) && !$val){
				echo GetMessage('IPOLSDEK_JS_SOD_'.$need)." ".GetMessage('IPOLSDEK_SOD_NOTGET');
				return false;
			}
			$params[$prop] = str_replace('"',"'",$val);
		}

		if(
			(!$params['orderId'] && $params['mode'] == 'order') ||
			(!$params['shipment'] && $params['mode'] == 'shipment')
		){
			echo GetMessage('IPOLSDEK_SOD_ORDERID')." ".GetMessage('IPOLSDEK_SOD_NOTGET');
			return false;
		}
		if(!$params['status'])
			$status='NEW';

		$orderId=($params['mode'] == 'order') ? $params['orderId'] : $params['shipment'];
		$source = ($params['mode'] == 'order') ? 0 : 1;
		unset($params['orderId']);
		unset($params['shipment']);
		unset($params['mode']);
		unset($params['action']);
		if($newId=sqlSdekOrders::Add(array('ORDER_ID'=>$orderId,'PARAMS'=>serialize($params),'STATUS'=>$status, 'SOURCE' => $source))){
			echo GetMessage('IPOLSDEK_SOD_UPDATED')."\n";
			return $newId;
		}
		else{
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOSAVED'));
			return false;
		}
	}

	function saveAndSend($params){ // кнопка "Сохранить и отправить" в редакторе заказа
		if(!self::isAdmin('R')) return false;
		if(self::updtOrder($params))
			self::sendOrderRequest((($params['mode'] == 'order') ? $params['orderId'] : $params['shipment']),$params['mode']);
		$err = self::getErrors();
		echo ($err)?$err:self::getAnswer();
	}

	function deleteRequest($oId,$mode='order'){
		if(!self::isAdmin()) return false;
		if(!cmodule::includemodule('sale')) return false;
		$request = self::GetByOI($oId,$mode);
		$return = false;
		if($request){
			if(in_array($request['STATUS'],array('OK','ERROR','NEW'))){
				$order = CSaleOrder::GetById($oId);
				$on = ($order['ACCOUNT_NUMBER'])?$order['ACCOUNT_NUMBER']:$oId;
				$headers = self::getXMLHeaders();
				$XML = '<?xml version="1.0" encoding="UTF-8" ?>
				<DeleteRequest Number="'.$request['MESS_ID'].'" Date="'.$headers['date'].'" Account="'.$headers['account'].'" Secure="'.$headers['secure'].'" OrderCount="1">
					<Order Number="'.$on.'" /> 
				</DeleteRequest>
				';
				
				$result = self::sendToSDEK($XML,'delete_orders');
				
				if($result['code'] != 200)
					self::toAnswer(GetMessage("IPOLSDEK_DRQ_UNBLDLT").GetMessage("IPOLSDEK_ERRORLOG_BADRESPOND").$result['code']);
				else{
					$xml = simplexml_load_string($result['result']);
					$arErrors = array();
					foreach($xml->DeleteRequest  as $orderMess)
						if($orderMess['ErrorCode'])
							$arErrors[(string)$orderMess['ErrorCode']] = (string)$orderMess['Msg'];
					if(!count($arErrors)){
						if(sqlSdekOrders::Delete($oId,$mode))
							$return = true;
						else
							self::toAnswer(GetMessage("IPOLSDEK_DRQ_CNTDELREQ"));
					}
					else
						self::toAnswer(GetMessage("IPOLSDEK_DRQ_GOTERRORS").print_r(self::zaDEjsonit($arErrors),true));
				}
			}
			else
				self::toAnswer(GetMessage("IPOLSDEK_DRQ_UNBLDLT").GetMessage("IPOLSDEK_DRQ_BADSTATUS"));
		}
		else
			self::toAnswer(GetMessage("IPOLSDEK_DRQ_UNBLDLT").GetMessage('IPOLSDEK_ERRLOG_NOREQ').$oId);
		return $return;
	}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Визуализация (форма оформления заявки)
		== onEpilog ==  == getExtraOptions ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	function onEpilog(){//Отображение формы
		$workMode = false;
		if(
			strpos($_SERVER['PHP_SELF'], "/bitrix/admin/sale_order_detail.php") !== false || 
			strpos($_SERVER['PHP_SELF'], "/bitrix/admin/sale_order_view.php")   !== false
		)
			$workMode = 'order';
		elseif(strpos($_SERVER['PHP_SELF'], "/bitrix/admin/sale_order_shipment_edit.php") !== false && self::canShipment())
			$workMode = 'shipment';
		if(!cmodule::includeModule('sale') || !self::isAdmin('R') || !$workMode)
			return false;

		sdekExport::loadExportWindow($workMode);
	}

	function getExtraOptions(){ // доп. настройки для заказов
		$arAddService = array(3,16,17,30,36,37);
		$src = COption::getOptionString(self::$MODULE_ID,'addingService',false);
		$svdOpts = unserialize(COption::getOptionString(self::$MODULE_ID,'addingService','a:0:{}'));
		$arReturn = array();
		foreach($arAddService as $asId)
			$arReturn[$asId] = array(
				'NAME' => GetMessage("IPOLSDEK_AS_".$asId."_NAME"),
				'DESC' => GetMessage("IPOLSDEK_AS_".$asId."_DESCR"),
				'SHOW' => ($svdOpts[$asId]['SHOW']) ? $svdOpts[$asId]['SHOW'] : (($src)?"N":"Y"),
				'DEF'  => ($svdOpts[$asId]['DEF'])  ? $svdOpts[$asId]['DEF']  : "N",
			);
		return $arReturn;
	}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Создание заказа
		== orderCreate ==  == controlProps ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	function orderCreate($oId,$arFields){
		if(cmodule::includemodule('sale') && self::controlProps() && array_key_exists('IPOLSDEK_CHOSEN',$_SESSION)){
			$checkTarif = self::defineDelivery($arFields['DELIVERY_ID']);
			$tarif = ($checkTarif) ? $checkTarif : 'courier';
			$op = CSaleOrderProps::GetList(array(),array("PERSON_TYPE_ID" =>$arFields['PERSON_TYPE_ID'],"CODE"=>"IPOLSDEK_CNTDTARIF"))->Fetch();
			if($op){
				$arFields = array(
				   "ORDER_ID" => $oId,
				   "ORDER_PROPS_ID" => $op['ID'],
				   "NAME" => GetMessage('IPOLSDEK_prop_name'),
				   "CODE" => "IPOLSDEK_CNTDTARIF",
				   "VALUE" => $_SESSION['IPOLSDEK_CHOSEN'][$tarif]
				);
				if(!CSaleOrderPropsValue::Add($arFields)){
					$prop = CSaleOrderPropsValue::GetList(array(),array("ORDER_ID" => $oId,"ORDER_PROPS_ID" => $op['ID']))->Fetch();
					if($prop && !$prop['VALUE'])
						CSaleOrderPropsValue::Update($prop['ID'],$arFields);
				}
			}
			unset($_SESSION['IPOLSDEK_CHOSEN']);
		}
	}

	function controlProps($mode = 1){// Свойство заказа, куда пишутся тарифы //1-add/update, 2-delete prop
		if(!CModule::IncludeModule("sale"))
			return false;
		$tmpGet=CSaleOrderProps::GetList(array("SORT" => "ASC"),array("CODE" => "IPOLSDEK_CNTDTARIF"));
		$existedProps=array();
		while($tmpElement=$tmpGet->Fetch())
			$existedProps[$tmpElement['PERSON_TYPE_ID']]=$tmpElement['ID'];

		if($mode=='1'){
			$return = true;
			$tmpGet = CSalePersonType::GetList(Array("SORT" => "ASC"), Array());
			$allPayers=array();
			while($tmpElement=$tmpGet->Fetch())
				$allPayers[]=$tmpElement['ID'];

			foreach($allPayers as $payer){
				$tmpGet = CSaleOrderPropsGroup::GetList(array("SORT" => "ASC"),array("PERSON_TYPE_ID" => $payer),false,array('nTopCount' => '1'));
				$tmpVal=$tmpGet->Fetch();
				$arFields = array(
				   "PERSON_TYPE_ID" => $payer,
				   "NAME" => GetMessage('IPOLSDEK_prop_name'),
				   "TYPE" => "TEXT",
				   "REQUIED" => "N",
				   "DEFAULT_VALUE" => "",
				   "SORT" => 100,
				   "CODE" => "IPOLSDEK_CNTDTARIF",
				   "USER_PROPS" => "N",
				   "IS_LOCATION" => "N",
				   "IS_LOCATION4TAX" => "N",
				   "PROPS_GROUP_ID" => $tmpVal['ID'],
				   "SIZE1" => 10,
				   "SIZE2" => 1,
				   "DESCRIPTION" => GetMessage('IPOLSDEK_prop_descr'),
				   "IS_EMAIL" => "N",
				   "IS_PROFILE_NAME" => "N",
				   "IS_PAYER" => "N",
				   "IS_FILTERED" => "Y",
				   "IS_ZIP" => "N",
				   "UTIL" => "Y"
				);
				if(!array_key_exists($payer,$existedProps))
					if(!CSaleOrderProps::Add($arFields))
						$return = false;
			}
			return $return;
		}
		if($mode=='2'){
			foreach($existedProps as $existedPropId)
				if (!CSaleOrderProps::Delete($existedPropId))
					echo "Error delete CNTDTARIF-prop id".$existedPropId."<br>";
		}
	}


	// подключение js и аналогичных файлов
	function getModuleExt($wat){
		$arDef = array(
			'courierTimeCheck' => ".php",
			'packController' => ".php",
			'mask_input' => '.js'
		);

		if(!array_key_exists($wat,$arDef)) return;

		$fPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.self::$MODULE_ID."/$wat{$arDef[$wat]}";
		if(file_exists($fPath))
			include_once($fPath);
	}

	// связки
	function senders($params = false){
		return sdekOption::senders($params);
	}

	// переходные функции
	function displayActPrint(&$list){
		sdekOption::displayActPrint($list);
	}
	function OnBeforePrologHandler(){
		sdekOption::OnBeforePrologHandler();
	}
	function agentUpdateList(){
		return sdekOption::agentUpdateList();
	}
	function agentOrderStates(){
		return sdekOption::agentOrderStates();
	}
	function select($arSelect,$arFilter){
		if(array_key_exists('ORDER_ID',$arFilter))
			$arFilter['SOURCE'] = 0;
		return sqlSdekOrders::select($arSelect,$arFilter);
	}
}
?>