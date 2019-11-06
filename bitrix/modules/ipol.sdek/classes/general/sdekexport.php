<?
IncludeModuleLangFile(__FILE__);

/*
	onGoodsToRequest
*/

class sdekExport extends sdekHelper{
	static $workMode    = false;
	static $orderId     = false;
	static $shipmentID  = '';

	static $orderDescr  = false;
	static $requestVals = false;
	static $isLoaded    = false;

	static $subRequests = false;

	public function loadExportWindow($workMode){
		self::$workMode = $workMode;
		if($workMode == 'order'){
			self::$orderId = $_REQUEST['ID'];
			$reqId = self::$orderId;
		}else{
			self::$orderId    = $_REQUEST['order_id'];
			self::$shipmentID = $_REQUEST['shipment_id'];
			$reqId = self::$shipmentID;
		}

		self::$orderDescr = self::getOrderDescr(self::$orderId,$workMode);

		if(
			COption::GetOptionString(self::$MODULE_ID,'showInOrders','Y') == 'N' &&
			!self::$orderDescr['info']['DELIVERY_SDEK']
		)
			return;

		self::$requestVals = sdekdriver::GetByOI($reqId,$workMode);

		if(self::noSendings())
			include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/orderDetail.php");
		else
			self::showExisted();
	}

	//получаем город заказа по его id
	static $optCity = false;
	static $arTmpArLocation=false;
	public function getOrderCity($id){ // используетс€ только в orderDetail.php
		if(!self::$optCity)
			self::$optCity = COption::GetOptionString(self::$MODULE_ID,'location',false);
		if(!is_array(self::$arTmpArLocation)) self::$arTmpArLocation=array();

		if(array_key_exists('IPOLSDEK_LOG',$GLOBALS) && $GLOBALS['IPOLSDEK_LOG']) self::toLog(self::$optCity,'getOrderCity optcity');

		$oCity=CSaleOrderPropsValue::GetList(array(),array('ORDER_ID'=>$id,'CODE'=>self::$optCity))->Fetch();
		if(array_key_exists('IPOLSDEK_LOG',$GLOBALS) && $GLOBALS['IPOLSDEK_LOG']) self::toLog($oCity,'oCity - city from order');
		if($oCity['VALUE']){
			if(is_numeric($oCity['VALUE'])){
				if(in_array($oCity['VALUE'],self::$arTmpArLocation))
					$oCity=self::$arTmpArLocation[$oCity['VALUE']];
				else{
					$cityId = self::getNormalCity($oCity['VALUE']);
					$tmpCity=CSaleLocation::GetList(array(),array("ID"=>$cityId,"CITY_LID"=>'ru'))->Fetch();
					if(!$tmpCity)
						$tmpCity=CSaleLocation::GetByID($cityId);
					if(array_key_exists('IPOLSDEK_LOG',$GLOBALS) && $GLOBALS['IPOLSDEK_LOG']) self::toLog($tmpCity,'founded city');
					self::$arTmpArLocation[$oCity['VALUE']]=($tmpCity['CITY_NAME_LANG'])?$tmpCity['CITY_NAME_LANG']:$tmpCity['CITY_NAME'];
					$oCity=str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),self::$arTmpArLocation[$oCity['VALUE']]);
				}
			}
			else
				$oCity=$oCity['VALUE'];
		}
		else
			$oCity=false;

		return $oCity;
	}

	// получение информации о заказе
	public function getOrderDescr($oId,$mode){
		$arOrderDescr = array('info'=>array(),'properties'=>array());

		if(self::isConverted()){
			// информаци€ о заказе
			$orderInfo = Bitrix\Sale\Order::load($oId);
			$arUChecks = array("COMMENTS","PAY_SYSTEM_ID","PAYED","PRICE","SUM_PAID","PRICE_DELIVERY");
			if($mode == 'order'){
				$ds = $orderInfo->getDeliverySystemId();
				foreach($ds as $id){
					$arOrderDescr['info']['DELIVERY_SDEK'] = (bool)self::defineDelivery($id);
					if($arOrderDescr['info']['DELIVERY_SDEK'])
						break;
				}
				$arUChecks[]="ACCOUNT_NUMBER";
			}else{
				if(!self::$shipmentID)
					self::$shipmentID = intval($_REQUEST["shipment_id"]);
				$shipment = self::getShipmentById(self::$shipmentID);

				if($shipment){
					$arOrderDescr['info']['DELIVERY_SDEK'] = (bool)self::defineDelivery($shipment['DELIVERY_ID']);
					$arOrderDescr['info']['ACCOUNT_NUMBER'] = $shipment['ACCOUNT_NUMBER'];
				}
			}

			foreach($arUChecks as $code)
				$arOrderDescr['info'][$code] = $orderInfo->getField($code);
			// свойства
			$arProps = $orderInfo->loadPropertyCollection()->getArray();
			foreach($arProps['properties'] as $arProp){
				$val = array_pop($arProp['VALUE']);
				if($val)
					$arOrderDescr['properties'][$arProp['CODE']] = $val;
			}
		}else{
			// информаци€ о заказе
			$order = CSaleOrder::getById($oId);
			$arOrderDescr['info']['DELIVERY_SDEK'] = (strpos($orderinfo['DELIVERY_ID'],'sdek:') === 0);
			$arUChecks = array("COMMENTS","PAY_SYSTEM_ID","PAYED","ACCOUNT_NUMBER","PRICE","SUM_PAID","PRICE_DELIVERY");
			foreach($arUChecks as $code)
				$arOrderDescr['info'][$code] = $order[$code];
			// свойства
			$orderProps=CSaleOrderPropsValue::GetOrderProps($oId);
			while($orderProp=$orderProps->Fetch())
				$arOrderDescr['properties'][$orderProp['CODE']] = $orderProp['VALUE'];
		}

		return $arOrderDescr;
	}

	function loadGoodsPack($packs){ // рассовывает упаковки по товарам
		CDeliverySDEK::$goods = array();
		foreach($packs as $pack){
			$arGabs = explode(' x ',$pack['gabs']);
			if(count($arGabs) != 3) continue;
			CDeliverySDEK::$goods[] = array(
				'D_W' => $arGabs[0],
				'D_L' => $arGabs[1],
				'D_H' => $arGabs[2],
				'W'   => $pack['weight']
			);
		}
	}

	// расчет габаритов товаров по указанным параметрам
	function countGoods($params){
		$arGCatalog = array();
		if(!cmodule::includeModule('catalog')) return;
		if(!count($params['goods'])){
			echo "G{0,0,0,}G";
			return;
		}
		$gC = CCatalogProduct::GetList(array(),array('ID'=>array_keys($params['goods'])));
		while($element=$gC->Fetch())
			$arGCatalog[$element['ID']] = array(
				'WEIGHT' => $element['WEIGHT'],
				'LENGTH' => $element['LENGTH'],
				'WIDTH'  => $element['WIDTH'],
				'HEIGHT' => $element['HEIGHT']
			);

		$arGoods = array();
		foreach($params['goods'] as $goodId => $cnt)
			$arGoods[$goodId] = array(
				'ID'		    => $goodId,
				'PRODUCT_ID'    => $goodId,
				'QUANTITY'      => $cnt,
				'CAN_BUY'       => 'Y',
				'DELAY'         => 'N',
				'SET_PARENT_ID' => false,
				'WEIGHT'		=> $arGCatalog[$goodId]['WEIGHT'],
				'DIMENSIONS' 	=> array(
					'LENGTH' => $arGCatalog[$goodId]['LENGTH'],
					'WIDTH'  => $arGCatalog[$goodId]['WIDTH'],
					'HEIGHT' => $arGCatalog[$goodId]['HEIGHT']
				),
			);
		CDeliverySDEK::setGoods($arGoods);
		echo "G{".CDeliverySDEK::$goods['D_L'].",".CDeliverySDEK::$goods['D_W'].",".CDeliverySDEK::$goods['D_H'].",}G";
	}

	// посчитать все возможные тарифы
	function countAlltarifs($arParams){
		if(!$arParams['orderId']||!$arParams['cityTo']) return false;
		$tarifs = self::getTarifList(array('fSkipCheckBlocks'=>true));
		$pzvTarifs     = self::arrVals($tarifs['pickup']);
		$courierTarifs = self::arrVals($tarifs['courier']);
		$tarifDescr = self::getExtraTarifs();

		self::setCalcData($arParams);

		$rezTarifs = array();
		foreach($tarifs as $type => $arTarifs){
			$arTarifs = self::arrVals($arTarifs);
			foreach($arTarifs as $id){
				if($arTarif[$id]['SHOW'] == 'N') continue;
				$result = CDeliverySDEK::calculateDost($id);
				if($result['success'])
					$rezTarifs[$type][$result['tarif']] = array(
						'name'    => $tarifDescr[$result['tarif']]['NAME'],
						'price'   => $result['price'],
						'termMin' => $result['termMin'],
						'termMax' => $result['termMax'],
					);
			}
		}

		return $rezTarifs;
	}

	//вывести список тарифов
	function htmlTaritfList($params){
		$list = self::countAlltarifs($params);

		$strHtml = '';
		foreach($list as $type => $tarifs){
			$strHtml.="<tr><td colspan='4' style='text-align:center;font-weight:bold;'>".GetMessage("IPOLSDEK_DELIV_".strtoupper($type)."_TITLE")."</td></tr>";
			foreach($tarifs as $id => $descr)
				$strHtml.="<tr id='IPOLSDEK_tarifsTable_".$id."'><td>".$descr['name']."</td><td style='text-align:center;'>".$descr['price']."</td><td style='text-align:center;'>".(($descr['termMin'] == $descr['termMax'])?$descr['termMin']:$descr['termMin']." - ".$descr['termMax'])."</td><td><input type='button' value='".GetMessage('IPOLSDEK_FRNT_CHOOSE')."' onclick='IPOLSDEK_oExport.allTarifs.select(\"".$id."\");'></td></tr>";
		}

		if($strHtml)
			echo "<table id='IPOLSDEK_allTarifs'>".$strHtml."</table>";
	}

	// перерасчет доставки
	public function extCountDeliv($arParams){
		if(!$arParams['orderId'] || !$arParams['cityTo'] || !$arParams['tarif'])
			return false;

		self::setCalcData($arParams);

		$result = CDeliverySDEK::calculateDost($arParams['tarif']);

		if($arParams['action'])
			echo json_encode($result);
		else
			return $result;
	}

	// установка габаритов дл€ расчета доставки
	private function setCalcData($arParams){
		if(!array_key_exists('packs',$arParams) || !$arParams['packs']){
			if(!array_key_exists('gabs',$arParams)){
				if($arParams['mode'] == 'order')
					CDeliverySDEK::setOrderGoods($arParams['orderId']);
				else
					CDeliverySDEK::setShipmentGoods($arParams['shipment'],$arParams['orderId']);
			}else
				CDeliverySDEK::$goods = $arParams['gabs'];
		}else
			self::loadGoodsPack($arParams['packs']);

		CDeliverySDEK::$sdekSender = ($arParams['cityFrom']) ? $arParams['cityFrom'] : self::getHomeCity();
		CDeliverySDEK::$sdekCity   = $arParams['cityTo'];
	}

	// св€зка заказов / отгрузок
	public function noSendings(){
		self::$subRequests = array();
		if(!self::isConverted() || self::$requestVals)
			return true;
		if(self::$workMode == 'shipment'){
			$req = sdekdriver::GetByOI(self::$orderId,'order');
			if($req)
				self::$subRequests = array($req);
		}else{
			$shipments = Bitrix\Sale\Shipment::getList(array('filter'=>array('ORDER_ID' => self::$orderId)));
			$unsended = array();
			while($element=$shipments->Fetch()){
				$req = sdekdriver::GetByOI($element['ID'],'shipment');
				if($req)
					self::$subRequests[]=$req;
				else
					$unsended[] = $element['ID'];
			}
			if(count(self::$subRequests))
				self::$subRequests['unsended'] = $unsended;
		}
		return !(bool)count(self::$subRequests);
	}

	// ќ Ќќ ќ“ќЅ–ј∆≈Ќ»я »ћ≈ёў»’—я «ј√–”«ќ 
	public function showExisted(){
		CJSCore::Init(array("jquery"));
		$unsended = false;
		if(array_key_exists('unsended',self::$subRequests)){
			$unsended = self::$subRequests['unsended'];
			unset(self::$subRequests['unsended']);
		}
		?>
			<style>
				.IPOLSDEK_sendedTable{
					background-color: #FFFFFF;
					border: 1px solid #DCE7ED;
					width: 100%;
					margin: 5px 0px;
					padding: 5px;
				}
			</style>
			<script>
			var IPOLSDEK_existedInfo = {
				load: function(){
					if($('#IPOLSDEK_btn').length) return;
					$('.adm-detail-toolbar').find('.adm-detail-toolbar-right').prepend("<a href='javascript:void(0)' onclick='IPOLSDEK_existedInfo.showWindow()' class='adm-btn' id='IPOLSDEK_btn'><?=GetMessage('IPOLSDEK_JSC_SOD_BTNAME')?></a>");
				},
				// окно
				wnd: false,
				showWindow: function(){
					if(!IPOLSDEK_existedInfo.wnd){
						var html=$('#IPOLSDEK_wndOrder').html();
						$('#IPOLSDEK_wndOrder').html('');
						IPOLSDEK_existedInfo.wnd = new BX.CDialog({
							title: "<?=GetMessage('IPOLSDEK_JSC_SOD_WNDTITLE')?>",
							content: html,
							icon: 'head-block',
							resizable: true,
							draggable: true,
							height: '350',
							width: '400',
							buttons: []
						});
					}
					IPOLSDEK_existedInfo.wnd.Show();
				},
				print: function(oId){
					$('#IPOLSDEK_print_'+oId).attr('disabled','true');
					$('#IPOLSDEK_print_'+oId).val('<?=GetMessage("IPOLSDEK_JSC_SOD_LOADING")?>');
					$.ajax({
						url  : "/bitrix/js/<?=self::$MODULE_ID?>/ajax.php",
						type : 'POST',
						data : {
							action : 'printOrderInvoice',
							oId    : oId,
							mode   : '<?=(self::$workMode == 'shipment') ? 'order' : 'shipment'?>'
						},
						dataType : 'json',
						success  : function(data){
							$('#IPOLSDEK_print_'+oId).removeAttr('disabled');
							$('#IPOLSDEK_print_'+oId).val('<?=GetMessage("IPOLSDEK_JSC_SOD_PRNTSH")?>');
							if(data.result == 'ok')
								window.open('/upload/<?=self::$MODULE_ID?>/'+data.file);
							else
								alert(data.error);
						}
					});
				},

				curDelete: false,
				delete: function(oId,status){
					if(IPOLSDEK_existedInfo.curDelete != false)
						return;
					$('#IPOLSDEK_delete_'+oId).attr('disabled','true');
					IPOLSDEK_existedInfo.curDelete = oId;
					if(status == 'NEW' || status == 'ERROR' || status == 'DELETE'){
						if(confirm("<?=GetMessage('IPOLSDEK_JSC_SOD_IFDELETE')?>"))
							$.post(
								"/bitrix/js/<?=self::$MODULE_ID?>/ajax.php",
								{action:'delReqOD',oid:oId,mode:'<?=(self::$workMode == 'order') ? 'shipment' : 'order'?>'},
								function(data){
									IPOLSDEK_existedInfo.onDelete(data);
								}
							);
					}else{
						if(status == 'OK'){
							if(confirm("<?=GetMessage('IPOLSDEK_JSC_SOD_IFKILL')?>"))
								$.post(
									"/bitrix/js/<?=self::$MODULE_ID?>/ajax.php",
									{action:'killReqOD',oid:oId,mode:'<?=(self::$workMode == 'order') ? 'shipment' : 'order'?>'},
									function(data){
										if(data.indexOf('GD:')===0)
											IPOLSDEK_existedInfo.onDelete(data.substr(3));
										else{
											alert(data);
											$('#IPOLSDEK_print_'+IPOLSDEK_existedInfo.curDelete).removeAttr('disabled');
										}
									}
								);
						}
					}
				},
				onDelete: function(data){
					alert(data);
					$('#IPOLSDEK_sT_'+IPOLSDEK_existedInfo.curDelete).replaceWith('');
					if($('.IPOLSDEK_sendedTable').length == 0)
						document.location.reload();
					IPOLSDEK_existedInfo.curDelete = false;
				}
			};
			$(document).ready(IPOLSDEK_existedInfo.load);
			</script>
			<div style='display:none' id='IPOLSDEK_wndOrder'>
				<div><?=GetMessage('IPOLSDEK_JSC_NOWND_'.self::$workMode)?></div>
				<?foreach(self::$subRequests as $request){?>
					<table class='IPOLSDEK_sendedTable' id='IPOLSDEK_sT_<?=$request['ORDER_ID']?>'>
						<tr>
							<?if(self::$workMode == 'shipment'){?>
								<td><?=GetMessage("IPOLSDEK_JSC_SOD_order")?></td>
								<td>
									<a target='_blank' href='/bitrix/admin/sale_order_detail.php?ID=<?=$request[
								"ORDER_ID"]?>'><?=$request['ORDER_ID']?></a>
							<?}else{?>
								<td><?=GetMessage("IPOLSDEK_JSC_SOD_shipment")?></td>
								<td>
									<a target='_blank' href='/bitrix/admin/sale_order_shipment_edit.php?order_id=<?=self::$orderId?>&shipment_id=<?=$request[
								"ORDER_ID"]?>'><?=$request[
								"ORDER_ID"]?></a>
							<?}?>
							</td>
						</tr>
						<tr><td><?=GetMessage('IPOLSDEK_JS_SOD_STATUS')?></td><td><?=$request['STATUS']?></td></tr>
						<tr><td colspan='2'><small><?=GetMessage('IPOLSDEK_JS_SOD_STAT_'.$request['STATUS'])?></small></td></tr>
						<?if($request['SDEK_ID']){?><tr><td><?=GetMessage('IPOLSDEK_JS_SOD_SDEK_ID')?></td><td><?=$request['SDEK_ID']?></td></tr><?}?>
						<?if($request['MESS_ID']){?><tr><td><?=GetMessage('IPOLSDEK_JS_SOD_MESS_ID')?></td><td><?=$request['MESS_ID']?></td></tr><?}?>
						<tr><td colspan='2'><hr></td></tr>
						<tr><td colspan='2'>
							<?if(in_array($request['STATUS'],array('OK','ERROR','NEW','DELETD'))){?>
							<input id='IPOLSDEK_delete_<?=$request['ORDER_ID']?>' value="<?=GetMessage('IPOLSDEK_JSC_SOD_DELETE')?>" onclick="IPOLSDEK_existedInfo.delete(<?=$request['ORDER_ID']?>,'<?=$request['STATUS']?>'); return false;" type="button">&nbsp;&nbsp;
							<?}?>
							<?if($request['STATUS'] == 'OK'){?>
							<input id='IPOLSDEK_print_<?=$request['ORDER_ID']?>' value="<?=GetMessage('IPOLSDEK_JSC_SOD_shtrih')?>" onclick="IPOLSDEK_existedInfo.print(<?=$request['ORDER_ID']?>); return false;" type="button">&nbsp;&nbsp;
							<?}?>
							<?if($request['SDEK_ID']){?><a href="http://www.edostavka.ru/track.html?order_id=<?=$request['SDEK_ID']?>" target="_blank"><?=GetMessage('IPOLSDEK_JSC_SOD_FOLLOW')?></a><?}?>
						</td></tr>
					</table>
				<?}?>
				<?if($unsended){?>
				<div>
					<?=GetMessage('IPOLSDEK_JSC_NOWND_noSended')?>
					<?foreach($unsended as $shipmintId){?><a target='_blank' href='/bitrix/admin/sale_order_shipment_edit.php?order_id=<?=self::$orderId?>&shipment_id=<?=$shipmintId?>'><?=$shipmintId?></a>&nbsp;
					<?}?>
				</div>
				<?}?>
			</div>
		<?
	}
}
?>