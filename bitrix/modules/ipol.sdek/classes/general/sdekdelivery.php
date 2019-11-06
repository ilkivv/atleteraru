<?
cmodule::includeModule('sale');
IncludeModuleLangFile(__FILE__);

/*
	IPOLSDEK_CACHE_TIME - время кэша в секундах
	IPOLSDEK_NOCACHE    - если задан - не использовать кэш

	onBeforeDimensionsCount - габариты товаров
	onCompabilityBefore - годнота профилей
	onCalculate - готовность расчета
*/

class CDeliverySDEK extends sdekHelper{
	static $profiles     = false;
	static $hasPVZ       = false;//грузим ли ПВЗ

	static $date         = false; // срок доставки
	private static $_date = false; // дата доставки

	static $price        = false;
	
	static $orderWeight  = false;
	static $orderPrice   = false;
	
	static $sdekCity     = false;
	static $sdekSender   = false;
	static $goods        = false; // кг, см
	static $PVZcities    = false;
	static $POSTcities   = false;


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													База службы доставки
		== Init ==  == SetSettings ==  == GetSettings ==  == Compability ==  == Calculate ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	function Init(){
		return array(
			// Basic description
			"SID" => "sdek",
			"NAME" => GetMessage("IPOLSDEK_DELIV_NAME"),
			"DESCRIPTION" => GetMessage('IPOLSDEK_DELIV_DESCR'),
			"DESCRIPTION_INNER" => GetMessage('IPOLSDEK_DELIV_DESCRINNER'),
			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),
			"HANDLER" => __FILE__,

			// Handler methods
			"DBGETSETTINGS" => array("CDeliverySDEK", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliverySDEK", "SetSettings"),
			// "GETCONFIG" => array("CDeliverySDEK", "GetConfig"),

			"COMPABILITY" => array("CDeliverySDEK", "Compability"),      
			"CALCULATOR" => array("CDeliverySDEK", "Calculate"),      

			// List of delivery profiles
			"PROFILES" => array(
				"courier" => array(
					"TITLE" => GetMessage('IPOLSDEK_DELIV_COURIER_TITLE'),
					"DESCRIPTION" => GetMessage('IPOLSDEK_DELIV_COURIER_DESCR'),

					"RESTRICTIONS_WEIGHT" => array(0,300000),
					"RESTRICTIONS_SUM" => array(0),
					
					"RESTRICTIONS_MAX_SIZE" => "1000",
					"RESTRICTIONS_DIMENSIONS_SUM" => "1500",
				),
				"pickup" => array(
					"TITLE" => GetMessage('IPOLSDEK_DELIV_PICKUP_TITLE'),
					"DESCRIPTION" => GetMessage('IPOLSDEK_DELIV_PICKUP_DESCR'),

					"RESTRICTIONS_WEIGHT" => array(0,300000),
					"RESTRICTIONS_SUM" => array(0),
					"RESTRICTIONS_MAX_SIZE" => "1000",
					"RESTRICTIONS_DIMENSIONS_SUM" => "1500"
				),
				"inpost" => array(
					"TITLE" => GetMessage('IPOLSDEK_DELIV_INPOST_TITLE'),
					"DESCRIPTION" => GetMessage('IPOLSDEK_DELIV_INPOST_DESCR'),

					"RESTRICTIONS_WEIGHT" => array(0,20000),
					"RESTRICTIONS_SUM" => array(0),
					"RESTRICTIONS_MAX_SIZE" => "640",
					"RESTRICTIONS_DIMENSIONS_SUM" => "1430"
				),
			)
		);
	}

	function SetSettings($arSettings){
		return serialize($arSettings);
	}

	function GetSettings($strSettings){
		return unserialize($strSettings);
	}

	// метод проверки совместимости в данном случае практически аналогичен рассчету стоимости
	function Compability($arOrder, $arConfig){
		if(!self::isLogged() || empty($arOrder['LOCATION_TO']))
			return false;
		$arKeys = array();
		self::$orderWeight = $arOrder['WEIGHT'];
		self::$orderPrice  = $arOrder['PRICE'];

		$_SESSION['IPOLSDEK_CHOSEN'] = array();

		if(!self::$orderWeight){
			$tmpWeight = 0;
			$defWeight = Coption::GetOptionString(self::$MODULE_ID,'weightD',1000);
			if(Coption::GetOptionString(self::$MODULE_ID,'defMode',"O") == "O")
				$tmpWeight = $defWeight;
			else
				foreach($arOrder['ITEMS'] as $item)
					$tmpWeight+=$item['QUANTITY']*$defWeight;
		}
		else
			$tmpWeight = self::$orderWeight;

		$arCity = self::getCity(self::getNormalCity($arOrder['LOCATION_TO']),true);
		self::$sdekCity = $arCity['SDEK_ID'];
		if(self::$sdekCity){
			self::$city = $arCity['NAME'];
			self::$cityId = $arOrder['LOCATION_TO'];
			$arKeys[]='courier';
			if(self::checkPVZ())
				$arKeys[]='pickup';
			if(self::checkPOSTOMAT())
				$arKeys[]='inpost';
		}

		// отключаем те профили, где нет включенных тарифов
		foreach($arKeys as $key => $profile)
			if(!self::checkTarifAvail($profile))
				unset($arKeys[$key]);

		if(!COption::GetOptionString(self::$MODULE_ID,'pvzPicker',false))
			foreach($arKeys as $ind => $profile)
				if($profile=='pickup' || $profile=='inpost'){
					unset($arKeys[$ind]);
					break;
				}

		//проверяем возможность доставки, если не редактирование заказа
		if(strpos($_SERVER['REQUEST_URI'],"bitrix/admin/sale_order_new.php") === false && !$_POST['action']){
			if(!self::$goods){
				if($arOrder['ITEMS'])
					self::setGoods($arOrder['ITEMS']);
				else
					self::setOrderGoods($arOrder['ID']);
			}
			self::$sdekSender = self::getHomeCity();
			foreach($arKeys as $ind => $profile){
				$cachename = "IPOLSDEK|$profile|".self::$sdekCity."|".implode('|',self::$goods);
				$obCache = new CPHPCache();
				if($obCache->InitCache(defined("IPOLSDEK_CACHE_TIME")?IPOLSDEK_CACHE_TIME:86400,$cachename,"/IPOLSDEK/") && !defined("IPOLSDEK_NOCACHE"))
					$result = $obCache->GetVars();
				else{
					$result = self::formCalcRequest($profile);
					if($result['success']){
						$obCache->StartDataCache();
						$obCache->EndDataCache($result);
					}
				}
				if(!$result['success'])
					unset($arKeys[$ind]);
				else{
					if(!array_key_exists('IPOLSDEK_CHOSEN',$_SESSION))
						$_SESSION['IPOLSDEK_CHOSEN'] = array();
					$_SESSION['IPOLSDEK_CHOSEN'][$profile] = $result['tarif'];
				}
			}
		}

		$ifPrevent=true;
		foreach(GetModuleEvents(self::$MODULE_ID, "onCompabilityBefore", true) as $arEvent)
			$ifPrevent = ExecuteModuleEventEx($arEvent,Array($arOrder,$arConfig,$arKeys));

		if(is_array($ifPrevent)){ 
			$newKeys = array();
			foreach($ifPrevent as $val)
				if(in_array($val, $arKeys))
					$newKeys[] = $val;
			$arKeys = $newKeys;
		}

		if(!$ifPrevent) return array();

		// Подключение FrontEnd (для многостраничного компонента)
		if($_POST['CurrentStep'] > 1 && $_POST['CurrentStep'] < 4 && in_array('pickup',$arKeys))
			self::pickupLoader();

		return $arKeys;
	}

	function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false){
		if(!self::$sdekCity)
			self::$sdekCity = self::getCity($arOrder['LOCATION_TO']);
		if(!self::$sdekSender)
			self::$sdekSender = self::getHomeCity();
		if(!self::$goods){
			if($arOrder['ITEMS'])
				self::setGoods($arOrder['ITEMS']);
			else
				self::setOrderGoods($arOrder['ID']);
		}
		$cachename = "IPOLSDEK|$profile|".self::$sdekCity."|".implode('|',self::$goods);
		$obCache = new CPHPCache();

		if($obCache->InitCache(defined("IPOLSDEK_CACHE_TIME")?IPOLSDEK_CACHE_TIME:86400,$cachename,"/IPOLSDEK/") && !defined("IPOLSDEK_NOCACHE"))
			$result = $obCache->GetVars();
		else{
			$result = self::formCalcRequest($profile);
			if($result['success']){
				$obCache->StartDataCache();
				$obCache->EndDataCache($result);
			}
		}

		if($result['success']){
			$addTerm = intval(COption::GetOptionString(self::$MODULE_ID,'termInc',false));
			self::$date = ($result['termMin'] == $result['termMax']) ? ($result['termMin']+$addTerm) : ($result['termMin']+$addTerm)."-".($result['termMax']+$addTerm);
			self::$_date = array(
				date('d.m.Y',mktime()+($result['termMin']+$addTerm)*86400),
				date('d.m.Y',mktime()+($result['termMax']+$addTerm)*86400)
			);

			if(!self::$price)
				self::$price = array();

			self::$profiles[$profile] = array(
				"VALUE"   => $result['price'],
				"PRINT_VALUE" => CCurrencyLang::CurrencyFormat($result['price'],'RUB',true),
				"TRANSIT" => ($result['termMin'] == $result['termMax']) ?  ($result['termMax']+$addTerm) : ($result['termMin']+$addTerm).'-'.($result['termMax']+$addTerm),
			);
			
			if(!array_key_exists('IPOLSDEK_CHOSEN',$_SESSION))
				$_SESSION['IPOLSDEK_CHOSEN'] = array();
			$_SESSION['IPOLSDEK_CHOSEN'][$profile] = $result['tarif'];

			$arReturn = array(
				"RESULT"  => "OK",
				"VALUE"   => $result['price'],
				"TRANSIT" => ($result['termMin'] == $result['termMax']) ?  ($result['termMax']+$addTerm) : ($result['termMin']+$addTerm).'-'.($result['termMax']+$addTerm) . ((CheckVersion(self::getSaleVersion(),'15.0.0')) ? " ".GetMessage("SALE_DH_EMS_DAYS") :''),
				"TARIF"   => $result['tarif'],
			);
		}
		else{
			$erStr = '';
			foreach($result as $erCode => $erLabl)
				$erStr.="$erLabl ($erCode) ";
			$arReturn = array(
				"RESULT" => "ERROR",
				"TEXT"   => self::zaDEjsonit($erStr),
			);
		}

		foreach(GetModuleEvents(self::$MODULE_ID, "onCalculate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent,Array(&$arReturn,$profile,$arConfig,$arOrder));

		if($arReturn['RESULT'] == 'OK')
			self::$price[$profile] = $arReturn['VALUE'];

		return $arReturn;
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Запрос расчета
		== formCalcRequest ==  == calculateDost ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	// суть - проверяет тяжелогрузы и экспрессы и делает столько запросов, сколько надо
	function formCalcRequest($profile){
		$timeOutCheck = COption::GetOptionString(self::$MODULE_ID,'sdekDeadServer',false);
		if($timeOutCheck && (mktime() - $timeOutCheck) <  60 * COption::GetOptionString(self::$MODULE_ID,'timeoutRollback',15))
			$result = array('error' => GetMessage('IPOLSDEK_DEAD_SERVER'));
		else{
			$mode = false;
			if(self::$goods['W'] > 30)
				$mode = 'heavy';
			$result = self::calculateDost($profile,$mode);
			if($mode == 'heavy' || $result['price'] > floatval(COption::GetOptionString(self::$MODULE_ID,'cntExpress',500))){
				$newResult = self::calculateDost($profile,"express");
				if(!array_key_exists('price',$result) || ($result['price'] > $newResult['price'] && array_key_exists('price',$newResult)))
					$result = $newResult;
			}
		}
		return $result;
	}

	// обертка класса расчета доставки
	function calculateDost($tarif,$mode = false){
		try {
			$calc = new CalculatePriceDeliverySdek();
			$timeOut = COption::GetOptionString(self::$MODULE_ID,'dostTimeout',6);
			if(floatval($timeOut) <= 0) $timeOut = 6;
			$calc->setTimeout($timeOut);
			$calc->setAuth(
				COption::GetOptionString(self::$MODULE_ID,'logSDEK'),
				COption::GetOptionString(self::$MODULE_ID,'pasSDEK')
			);
			$calc->setSenderCityId(self::$sdekSender);
			$calc->setReceiverCityId(self::$sdekCity);
			// $calc->setDateExecute(date()); 2012-08-20 //устанавливаем дату планируемой отправки
			//устанавливаем тариф по-умолчанию
			if(is_numeric($tarif))
				$calc->setTariffId($tarif);
			//задаём список тарифов с приоритетами
			else{
				$arPriority = self::getListOfTarifs($tarif,$mode);
				if(!count($arPriority))
					return array('error' => 'no_tarifs');
				else
					foreach($arPriority as $tarId)
						$calc->addTariffPriority($tarId);
			}

			// $calc->setModeDeliveryId(3); //устанавливаем режим доставки
			//добавляем места в отправление
			// кг, см
			if(array_key_exists('W',self::$goods)){
				$calc->addGoodsItemBySize(self::$goods['W'],self::$goods['D_W'],self::$goods['D_H'],self::$goods['D_L']);
			}else
				foreach(self::$goods as $arGood)
					$calc->addGoodsItemBySize($arGood['W'],$arGood['D_W'],$arGood['D_H'],$arGood['D_L']);

			if($calc->calculate()===true){
				COption::SetOptionString(self::$MODULE_ID,'sdekDeadServer',false);
				$res = $calc->getResult();
				if(!is_array($res)) 
					$arReturn['error'] = GetMessage('IPOLSDEK_DELIV_SDEKISDEAD');
				else{
					$arReturn = array(
						'success' => true,
						'price'   => $res['result']['price'],
						'termMin' => $res['result']['deliveryPeriodMin'],
						'termMax' => $res['result']['deliveryPeriodMax'],
						'dateMin' => $res['result']['deliveryDateMin'],
						'dateMax' => $res['result']['deliveryDateMax'],
						'tarif'   => $res['result']['tariffId'],
					);
					if(array_key_exists('cashOnDelivery',$res['result']))
						$arReturn['priceLimit'] = $res['result']['cashOnDelivery'];
				}
			}elseif($calc->getResult() == 'noanswer'){
				COption::SetOptionString(self::$MODULE_ID,'sdekDeadServer',mktime());
				$arReturn['error'] = GetMessage('IPOLSDEK_DEAD_SERVER');
			}else{
				$err = $calc->getError();
				if(isset($err['error'])&&!empty($err))
					foreach($err['error'] as $e)
						$arReturn[$e['code']] = $e['text'];
			}
		} catch (Exception $e){
			$arReturn['error'] = $e->getMessage();
		}
		return $arReturn;
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Расчеты товаров
		== setOrderGoods ==  == setShipmentGoods ==  == setGoods ==  == handleBitrixComplects ==  == getGoodsDimensions ==  == getBasketGoods ==  == sumSizeOneGoods ==  == sumSize ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	// расчитывает товары для заказа
	public function setOrderGoods($orderId){
		if (isset($orderId) && $orderId > 0)
			$arFilter = array("ORDER_ID" => $orderId);
		else
			$arFilter = array("FUSER_ID" => CSaleBasket::GetBasketUserID(),"ORDER_ID" => "NULL");

		$goods = self::getBasketGoods($arFilter,true);

		self::setGoodsFromArray($goods);
	}

	// рассчитывает товары для отгрузки
	public function setShipmentGoods($shipmentID,$orderId=false){
		if(!self::canShipment())
			return false;
		if(!$orderId)
			$orderId = self::oIdByShipment($shipmentID);

		$arOrderGoods = self::getBasketGoods(array("ORDER_ID" => $orderId),true);

		$arOrderGoods = self::filterShipmentGoods($shipmentID,$arOrderGoods['goods'],true);

		self::setGoodsFromArray($arOrderGoods);
	}

	private function setGoodsFromArray($goods){ // from setOrderGoods & setShipmentGoods
		if(!self::$orderPrice)
			self::$orderPrice=$goods['price'];
		self::setGoods($goods['goods']);
	}

	public function filterShipmentGoods($shipmentID,$goods,$cntPrice = false){ // фильтрует товары по наличию их в отправлении setShipmentGoods && sdekclass::getGoodsArray
		if(!self::canShipment())
			return false;
		$arGoods = array();
		$dbGoods = Bitrix\Sale\ShipmentItem::getList(array('filter'=>array('ORDER_DELIVERY_ID'=>$shipmentID)));
		while($arGood = $dbGoods->Fetch())
			$arGoods[$arGood['BASKET_ID']] = $arGood['QUANTITY'];

		$ttlPrice = 0;

		foreach($goods as $goodId => $vals){
			if(!array_key_exists($vals['ID'],$arGoods)){
				unset($goods[$goodId]);
				continue;
			}

			if($vals['QUANTITY'] == $arGoods[$vals['ID']])
				$ttlPrice += $vals['PRICE'] * $vals['QUANTITY'];
			else{
				$goods[$goodId]['QUANTITY'] = $arGoods[$vals['ID']];
				$ttlPrice += $goods[$goodId]['PRICE'] * $arGoods[$vals['ID']];
			}
		}

		return ($cntPrice) ? array('goods' => $goods, 'price' => $ttlPrice) : $goods;
	}

	// устанавнивает $goods по $arOrderGoods
	public function setGoods($arOrderGoods){
		self::$goods = false;
		$arGoods = array();
		$arDefSetups = array(
			'W'   => COption::GetOptionString(self::$MODULE_ID,"weightD",1000) / 1000,
			'D_L' => COption::GetOptionString(self::$MODULE_ID,"lengthD",400 ) / 10,
			'D_W' => COption::GetOptionString(self::$MODULE_ID,"widthD" ,300 ) / 10,
			'D_H' => COption::GetOptionString(self::$MODULE_ID,"heightD",200 ) / 10,
		);
		$isDef = ("O"==COption::GetOptionString(self::$MODULE_ID,"defMode","O"));
		$arOrderGoods = self::handleBitrixComplects($arOrderGoods);

		$arGoods = self::getGoodsDimensions($arOrderGoods,$arDefSetups,$isDef);

		$TW = 0;
		foreach($arGoods['goods'] as $good){
			if(!$arGoods['isNoG'] || ($good['D_L'] && $good['D_W'] && $good['D_H']))
				$yp[]=self::sumSizeOneGoods($good['D_L'],$good['D_W'],$good['D_H'],$good['Q']);
			$TW += $good['W'] * $good['Q'];
		}

		$result = self::sumSize($yp);

		if($arGoods['isNoG']){
			$vDef = $arDefSetups['D_L'] * $arDefSetups['D_W'] * $arDefSetups['D_H'];
			$vCur = $result['L'] * $result['W'] * $result['H'];
			if($vCur < $vDef)
				$result = array(
					"L" => $arDefSetups['D_L'],
					"W" => $arDefSetups['D_W'],
					"H" => $arDefSetups['D_H']
				);
		}
		if($arGoods['isNoW'])
			$TW = ($TW > $arDefSetups['W']) ? $TW : $arDefSetups['W'];

		// СДЭК не воспринимает габариты меньше сантиметра
		foreach(array('L','W','H') as $lbl)
			if($result[$lbl] < 1)
				$result[$lbl] = 1;

		// перераспределение LWH в магии sumSize
		self::$goods = array(
			"D_L" => $result['L'],
			"D_W"  => $result['W'],
			"D_H" => $result['H'],
			"W" => $TW
		);
		if(!self::$orderWeight)
			self::$orderWeight=$TW*1000;
	}

	// режет товары из комплектов
	function handleBitrixComplects($goods){
		$arComplects = array();
		foreach($goods as $good)
			if(
				array_key_exists('SET_PARENT_ID',$good) && 
				$good['SET_PARENT_ID'] &&
				$good['SET_PARENT_ID'] != $good['ID']
			)
				$arComplects[$good['SET_PARENT_ID']]=true;
		if(defined("IPOLSDEK_DOWNCOMPLECTS") && IPOLSDEK_DOWNCOMPLECTS == true){
			foreach($goods as $key => $good)
				if(array_key_exists($good['ID'],$arComplects))
					unset($goods[$key]);
		}else
			foreach($goods as $key => $good)
				if(
					array_key_exists('SET_PARENT_ID',$good) && 
					array_key_exists($good['SET_PARENT_ID'],$arComplects) && 
					$good['SET_PARENT_ID'] != $good['ID']
				)
					unset($goods[$key]);
		return $goods;
	}

	// засовывает в товары габариты по установленным дефолтам
	public function getGoodsDimensions($arOrderGoods,$arDefSetups=false,$isDef='ungiven'){
		if(!$arDefSetups)
			$arDefSetups = array(
				'W'   => COption::GetOptionString(self::$MODULE_ID,"weightD",1000) / 1000,
				'D_L' => COption::GetOptionString(self::$MODULE_ID,"lengthD",400 ) / 10,
				'D_W' => COption::GetOptionString(self::$MODULE_ID,"widthD" ,300 ) / 10,
				'D_H' => COption::GetOptionString(self::$MODULE_ID,"heightD",200 ) / 10,
			);
		if($isDef == 'ungiven')
			$isDef = ("O"==COption::GetOptionString(self::$MODULE_ID,"defMode","O"));

		$arGoods = array();
		$isNoW = false;
		$isNoG = false;

		foreach(GetModuleEvents(self::$MODULE_ID,"onBeforeDimensionsCount",true) as $arEvent)
			ExecuteModuleEventEx($arEvent,Array(&$arOrderGoods));

		foreach($arOrderGoods as $gId => $arGood){
			$gabs = array_key_exists('~DIMENSIONS',$arGood)?$arGood['~DIMENSIONS']:$arGood['DIMENSIONS'];
			if(!is_array($gabs) && $gabs)
				$gabs = unserialize($gabs);

			$gWeight = (float)$arGood['WEIGHT'];

			if($isDef && !$isNoW && !$gWeight)
				$isNoW = true;
			if($isDef && !$isNoG && (!$gabs['LENGTH'] || !$gabs['WIDTH'] || !$gabs['HEIGHT']))
				$isNoG = true;
			$arGoods[$gId]=array(
				'W'   => ($gWeight)        ? ($gWeight/1000)      : ((!$isDef) ? $arDefSetups['W']   : false),
				'D_L' => ($gabs['LENGTH']) ? ($gabs['LENGTH']/10) : ((!$isDef) ? $arDefSetups['D_L'] : false),
				'D_W' => ($gabs['WIDTH'])  ? ($gabs['WIDTH']/10)  : ((!$isDef) ? $arDefSetups['D_W'] : false),
				'D_H' => ($gabs['HEIGHT']) ? ($gabs['HEIGHT']/10) : ((!$isDef) ? $arDefSetups['D_H'] : false),
				'Q'   => $arGood['QUANTITY'],
			);
		}

		return array(
			'goods' => $arGoods,
			'isNoW' => $isNoW,
			'isNoG' => $isNoG
		);
	}

	// берет товары из корзин по фильтру arFilter, считает общую цену | setOrderGoods, packController
	function getBasketGoods($arFilter=array(),$getPrice=false){
		$arGoods = array();

		$dbBasketItems = CSaleBasket::GetList(
			array(),
			$arFilter,
			false,
			false,
			array("ID","PRODUCT_ID", "PRICE", "QUANTITY",'CAN_BUY','DELAY',"NAME","DIMENSIONS","WEIGHT","PRICE","SET_PARENT_ID","LID")
		);
		while ($arItems = $dbBasketItems->Fetch())
			if ($arItems['CAN_BUY'] == 'Y' && $arItems['DELAY'] == 'N'){
				$arItems['DIMENSIONS'] = unserialize($arItems['DIMENSIONS']);
				$arGoods[$arItems["PRODUCT_ID"]]=$arItems;
				$ttlPrice+=$arItems['PRICE']*$arItems['QUANTITY'];
			}
		if($getPrice)
			return array(
				'goods' => $arGoods,
				'price' => $ttlPrice
			);
		else
			return $arGoods;
	}

	// отсортировать грузы по возрастанию
	function sumSizeOneGoods($xi,$yi,$zi,$qty){
		$ar = array($xi,$yi,$zi);
		sort($ar);
		if ($qty<=1) return (array('X'=>$ar[0],'Y'=>$ar[1],'Z'=>$ar[2]));

		$x1 = 0;
		$y1 = 0;
		$z1 = 0;
		$l = 0;

		$max1 = floor(Sqrt($qty));
		for($y=1;$y<=$max1;$y++){
			$i = ceil($qty/$y);
			$max2 = floor(Sqrt($i));
			for($z=1;$z<=$max2;$z++){
				$x = ceil($i/$z);
				$l2 = $x*$ar[0] + $y*$ar[1] + $z*$ar[2];
				if(($l==0)||($l2<$l)){
					$l = $l2;
					$x1 = $x;
					$y1 = $y;
					$z1 = $z;
				}
			}
		}
		return (array('X'=>$x1*$ar[0],'Y'=>$y1*$ar[1],'Z'=>$z1*$ar[2]));
	}

	//Суммируем размеры груза для вычисления объемного веса
	function sumSize($a){
		$n = count($a);
		if (!($n>0)) return(array('L'=>'0','W'=>'0','H'=>'0'));
		for($i3=1;$i3<$n;$i3++){
			// отсортировать размеры по убыванию
			for($i2=$i3-1;$i2<$n;$i2++){
				for($i=0;$i<=1;$i++){
					if($a[$i2]['X']<$a[$i2]['Y']){
						$a1 = $a[$i2]['X'];
						$a[$i2]['X'] = $a[$i2]['Y'];
						$a[$i2]['Y'] = $a1;
					};
					if(($i==0) && ($a[$i2]['Y']<$a[$i2]['Z'])){
						$a1 = $a[$i2]['Y'];
						$a[$i2]['Y'] = $a[$i2]['Z'];
						$a[$i2]['Z'] = $a1;
					}
				}
				$a[$i2]['Sum'] = $a[$i2]['X'] + $a[$i2]['Y'] + $a[$i2]['Z']; // сумма сторон
			}
			// отсортировать грузы по возрастанию
			for($i2=$i3;$i2<$n;$i2++)
				for($i=$i3;$i<$n;$i++)
					if($a[$i-1]['Sum']>$a[$i]['Sum']){
						$a2 = $a[$i];
						$a[$i] = $a[$i-1];
						$a[$i-1] = $a2;
					}
			// расчитать сумму габаритов двух самых маленьких грузов
			if($a[$i3-1]['X']>$a[$i3]['X']) $a[$i3]['X'] = $a[$i3-1]['X'];
			if($a[$i3-1]['Y']>$a[$i3]['Y']) $a[$i3]['Y'] = $a[$i3-1]['Y'];
			$a[$i3]['Z'] = $a[$i3]['Z'] + $a[$i3-1]['Z'];
			$a[$i3]['Sum'] = $a[$i3]['X'] + $a[$i3]['Y'] + $a[$i3]['Z']; // сумма сторон
		}

		return( array(
			'L'=>Round($a[$n-1]['X'],2),
			'W'=>Round($a[$n-1]['Y'],2),
			'H'=>Round($a[$n-1]['Z'],2)) 
		);
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Виджет
		== pickupLoader ==  == loadComponent ==  == onBufferContent ==  == no_json ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	static $city = '';
	static $cityId = 0; // bitrix
	static $selDeliv = '';

	function pickupLoader($arResult,$arUR){//подготавливает данные о доставке
		if(!self::isActive()) return;

		self::$orderWeight = $arResult['ORDER_WEIGHT'];
		self::$orderPrice  = $arResult['ORDER_PRICE'];

		$city = self::getCity($arUR['DELIVERY_LOCATION'],true);
		self::$cityId = $arUR['DELIVERY_LOCATION'];
		if($city){
			$city = str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),$city['NAME']);
			self::$city = $city;
		}
		self::$selDeliv = $arUR['DELIVERY_ID'];
	}

	function loadComponent(){ // подключает компонент
		if(self::isActive() && $_REQUEST['is_ajax_post'] != 'Y' && $_REQUEST["AJAX_CALL"] != 'Y' && !$_REQUEST["ORDER_AJAX"])
			$GLOBALS['APPLICATION']->IncludeComponent("ipol:ipol.sdekPickup", "order", array(),false);
	}

	function onBufferContent(&$content) {
		if(self::isActive() && self::$city){
			$noJson = self::no_json($content);
			if(($_REQUEST['is_ajax_post'] == 'Y' || $_REQUEST["AJAX_CALL"] == 'Y' || $_REQUEST["ORDER_AJAX"]) && $noJson){
				$content .= '<input type="hidden" id="sdek_city"   name="sdek_city"   value=\''.self::$city.'\' />';//вписываем город
				$content .= '<input type="hidden" id="sdek_cityID"   name="sdek_cityID"   value=\''.self::$cityId.'\' />';//вписываем город
				$content .= '<input type="hidden" id="sdek_dostav"   name="sdek_dostav"   value=\''.self::$selDeliv.'\' />';//вписываем выбранный вариант доставки
			}elseif($_REQUEST['action'] == 'refreshOrderAjax' && !$noJson)
				$content = substr($content,0,strlen($content)-1).',"sdek":{"city":"'.self::zajsonit(self::$city).'","cityId":"'.self::$cityId.'","dostav":"'.self::$selDeliv.'"}}';
		}
	}

	function no_json($wat){
		return is_null(json_decode(self::zajsonit($wat),true));
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Проверки ПВЗ и Почтоматов
		== wegihtPVZ ==  == checkPVZ ==  == checkPOSTOMAT ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	public function wegihtPVZ($weight=false,$src=false){
		if($src)
			self::$PVZcities = $src;
		if(!self::$PVZcities){
			self::$PVZcities = self::getListFile();
			self::$PVZcities = self::$PVZcities['PVZ'];
		}
		$arPVZs = self::$PVZcities;
		if(!self::$orderWeight && !$weight)
			return self::$PVZcities;
		$check = ($weight) ? $weight : self::$orderWeight;
		$check /= 1000;
		foreach($arPVZs as $city => $arPVZ)
			foreach($arPVZ as $code => $val)
				if(array_key_exists('WeightLim',$val)){
					if(
						$val['WeightLim']['MIN'] > $check ||
						$val['WeightLim']['MAX'] < $check
					)
						unset($arPVZs[$city][$code]);
				}
		return $arPVZs;
	}

	public function checkPVZ($city = ''){
		if(!self::$PVZcities){
			self::$PVZcities = self::getListFile();
			self::$PVZcities = self::$PVZcities['PVZ'];
		}
		if(!$city)
			$city = self::$city;
		elseif(!self::$city)
			self::$city = $city; 
		return array_key_exists(self::$city,self::$PVZcities);
	}

	public function checkPOSTOMAT($city = ''){
		if(!self::$POSTcities){
			self::$POSTcities = self::getListFile();
			self::$POSTcities = self::$POSTcities['POSTOMAT'];
		}
		if(!$city)
			$city = self::$city;
		elseif(!self::$city)
			self::$city = $city; 
		return array_key_exists(self::$city,self::$POSTcities);
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												проверки на возможность оплаты нал / безнал
		== checkNalD2P ==  == checkNalP2D ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	function checkNalD2P(&$arResult,$arUserResult,$arParams){
		if(
			$arParams['DELIVERY_TO_PAYSYSTEM'] == 'd2p' && 
			strpos($arUserResult['DELIVERY_ID'],'sdek:')!==false &&
			COption::GetOptionString(self::$MODULE_ID,"hideNal","Y") == 'Y'
		){
			$arBesnalPaySys = unserialize(COption::GetOptionString(self::$MODULE_ID,'paySystems','a:{}'));
			$isAvail = sqlSdekCity::getCityPM(self::getNormalCity($arUserResult['DELIVERY_LOCATION']),'BITRIX_ID');
			if(!is_bool($isAvail)){
				if($isAvail >= $arResult['ORDER_PRICE'])
					$isAvail = true;
				else
					$isAvail = false;
			}
			if(!$isAvail){
				foreach($arResult['PAY_SYSTEM'] as $id => $payDescr)
					if(!in_array($payDescr['ID'],$arBesnalPaySys))
						unset($arResult['PAY_SYSTEM'][$id]);
			}
		}
	}

	function checkNalP2D(&$arResult,$arUserResult,$arParams){
		if(
			$arParams['DELIVERY_TO_PAYSYSTEM'] == 'p2d' && 
			COption::GetOptionString(self::$MODULE_ID,"hideNal","Y") == 'Y'
		){
			$arBesnalPaySys = unserialize(COption::GetOptionString(self::$MODULE_ID,'paySystems','a:{}'));
			$isAvail = sqlSdekCity::getCityPM(self::getNormalCity($arUserResult['DELIVERY_LOCATION']),'BITRIX_ID');
			if(!is_bool($isAvail)){
				if($isAvail >= $arResult['ORDER_PRICE'])
					$isAvail = true;
				else
					$isAvail = false;
			}
			if(
				!$isAvail && 
				!in_array($arUserResult['PAY_SYSTEM_ID'],$arBesnalPaySys) &&
				array_key_exists('sdek',$arResult['DELIVERY'])
			)
				unset($arResult['DELIVERY']['sdek']);
		}
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												расчет стороннего заказа
		== setOrder ==  == countDelivery ==  == cntDelivsOld ==  == cntDelivsConverted ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	function setOrder($params=array()){ // устанавливает данные для заказа-пустышки
		self::$orderWeight = ($params['WEIGHT']) ? $params['WEIGHT'] : COption::GetOptionString(self::$MODULE_ID,'weightD',1000);
		self::$orderPrice  = ($params['PRICE'])  ? $params['PRICE']  : 1000;
		if($params['CITY_TO'])
			self::$sdekCity = self::getCity($params['CITY_TO']);
		
		if(!$params['GOODS'])
			$params['GOODS'] = array(array("WEIGHT"=>self::$orderWeight,'QUANTITY'=>1));
		if(!$params['GABS'])
			self::setGoods($params['GOODS']);
		else
			self::$goods = $params['GABS'];
	}

	function countDelivery($arOrder){
		cmodule::includeModule('sale');
		if(!$arOrder['CITY_TO_ID']){
			$cityTo = CSaleLocation::getList(array(),array('CITY_NAME'=>self::zaDEjsonit($arOrder['CITY_TO'])))->Fetch();
			if($cityTo){
				$_SESSION['IPOLSDEK_city'] = self::zaDEjsonit($arOrder['CITY_TO']);
				$arOrder['CITY_TO_ID'] = $cityTo['ID'];
			}
		}
		if($arOrder["DIMS"])
			$arOrder['GOODS'] = array(array(
				"QUANTITY"   => 1,
				"PRICE"      => ($arOrder['PRICE'])  ? $arOrder['PRICE']  : self::$orderPrice,
				"WEIGHT"     => ($arOrder['WEIGHT']) ? $arOrder['WEIGHT'] : self::$orderWeight,
				"DIMENSIONS" => array(
					"WIDTH"  => $arOrder["DIMS"]["WIDTH"],
					"HEIGHT" => $arOrder["DIMS"]["HEIGHT"],
					"LENGTH" => $arOrder["DIMS"]["LENGTH"],			
				),
			));

		$arProfiles = (self::isConverted()) ? self::cntDelivsConverted($arOrder) : self::cntDelivsOld($arOrder);

		$arReturn = self::zajsonit(array(
				'courier' => ($arProfiles['courier']['calc']) ? $arProfiles['courier']['calc'] : 'no',
				'pickup'  => ($arProfiles['pickup']['calc'])  ? $arProfiles['pickup']['calc']  : 'no',
				'inpost'  => ($arProfiles['inpost']['calc'])  ? $arProfiles['inpost']['calc']  : 'no',
				'date'    => self::$date,
				'c_date'  => self::$profiles['courier']['TRANSIT'],
				'p_date'  => self::$profiles['pickup']['TRANSIT'],
				'i_date'  => self::$profiles['inpost']['TRANSIT'],
			));

		if($arOrder['action'])
			echo json_encode($arReturn);
		else
			return $arReturn;
	}

	function cntDelivsOld($arOrder){//Выдает срок и стоимость доставки для виджета 
		if(array_key_exists('IPOLSDEK_LOG',$GLOBALS) && $GLOBALS['IPOLSDEK_LOG']) self::toLog($arOrder,'cntDelivs');
		$cityFrom = COption::getOptionString('ipol.sdek','departure');

		if(array_key_exists('IPOLSDEK_LOG',$GLOBALS) && $GLOBALS['IPOLSDEK_LOG']) self::toLog($arOrder,'arOrder');
		self::setOrder($arOrder);
		$list = self::getListFile();

		$psevdoOrder = array(
			"LOCATION_TO"   => $arOrder['CITY_TO_ID'],
			"LOCATION_FROM" => $cityFrom,
			"PRICE"         => ($arOrder['PRICE'])  ? $arOrder['PRICE']  : self::$orderPrice,
			"WEIGHT"        => ($arOrder['WEIGHT']) ? $arOrder['WEIGHT'] : self::$orderWeight,
		);
		if($arOrder["DIMS"])
			$psevdoOrder['ITEMS']=$arOrder['GOODS'];
		if(array_key_exists('IPOLSDEK_LOG',$GLOBALS) && $GLOBALS['IPOLSDEK_LOG']) self::toLog($psevdoOrder,'psevdoOrder');

		$arHandler = CSaleDeliveryHandler::GetBySID('sdek')->Fetch();
		$arProfiles = CSaleDeliveryHandler::GetHandlerCompability($psevdoOrder,$arHandler);
		if(array_key_exists('IPOLSDEK_LOG',$GLOBALS) && $GLOBALS['IPOLSDEK_LOG']) self::toLog($arProfiles,'arProfiles');
		foreach($arProfiles as $profName => $someArray){
			if(in_array($profName,$arOrder['FORBIDDEN'])) continue;
			$calc = CSaleDeliveryHandler::CalculateFull('sdek',$profName,$psevdoOrder,"RUB");
			if($calc['RESULT'] != 'ERROR')
				$arProfiles[$profName]['calc'] = ($calc['VALUE'])?CCurrencyLang::CurrencyFormat($calc['VALUE'],'RUB',true):GetMessage("IPOLSDEK_FREEDELIV");
		}

		return $arProfiles;
	}

	function cntDelivsConverted($arOrder){
		$basket = Bitrix\Sale\Basket::create();
		if(array_key_exists('GOODS',$arOrder) && count($arOrder['GOODS']))
			foreach($arOrder['GOODS'] as $key => $arGood){
				$basketItem = Bitrix\Sale\BasketItem::create($basket,'ipol.sdek',$key+1);
				$arGood['DIMENSIONS'] = ($arOrder['DIMENSIONS']) ? serialize($arOrder['DIMENSIONS']) : 'a:3:{s:5:"WIDTH";i:0;s:6:"HEIGHT";i:0;s:6:"LENGTH";i:0;}';
				$basketItem->initFields(
					array_merge(
						$arGood,
						array('DELAY'=>'N','CAN_BUY'=>'Y','CURRENCY'=>'RUB','RESERVED'=>'N','NAME'=>'testGood','SUBSCRIBE'=>'N')
					)
				);
				$basket->addItem($basketItem);
			}

		$order = Bitrix\Sale\Order::create();
		$order->setBasket($basket);
		$propertyCollection = $order->getPropertyCollection();
		$locVal = CSaleLocation::getLocationCODEbyID($arOrder['CITY_TO_ID']);
		$arProps = array();
		foreach ($propertyCollection as $property){
			$arProperty = $property->getProperty();
			if($arProperty["TYPE"] == 'LOCATION')
				$arProps[$arProperty["ID"]] = $locVal;
		}
		$propertyCollection->setValuesFromPost(array('PROPERTIES'=>$arProps),array());

		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->createItem();
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		$shipment->setField('CURRENCY', $order->getCurrency());
		foreach ($order->getBasket() as $item){
			$shipmentItem = $shipmentItemCollection->createItem($item);
			$shipmentItem->setQuantity($item->getQuantity());
		}

		$arShipments = array();
		$arDeliveryServiceAll = Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);
		foreach($arDeliveryServiceAll as $id => $deliveryObj){
			if($deliveryObj->isProfile() && $deliveryObj->getParentService()->getSid() == 'sdek'){
				$profName = self::defineDelivery($id);
				if(in_array($profName,$arOrder['FORBIDDEN'])) continue;
				$resCalc = Bitrix\Sale\Delivery\Services\Manager::calculateDeliveryPrice($shipment,$id);
				if($resCalc->isSuccess())
					$arShipments[$profName]['calc'] = ($resCalc->getDeliveryPrice()) ? CCurrencyLang::CurrencyFormat($resCalc->getDeliveryPrice(),'RUB',true):GetMessage("IPOLSDEK_FREEDELIV");
			}
		}

		return $arShipments;
	}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Общие функции модуля
		== getListOfTarifs ==  == getDateDeliv ==  
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	function getListOfTarifs($profile,$mode = false,$fSkipCheckBlocks = false){
		return self::getTarifList(array('type'=>$profile,'mode'=>$mode,'answer'=>'array','fSkipCheckBlocks' => $fSkipCheckBlocks));
	}

	function getDateDeliv($format = false){
		if(!self::$_date) return;
		if(self::$_date[0] == self::$_date[1]) $format = 0;
		return ($format === false) ? self::$_date[0]." - ".self::$_date[1] : self::$_date[$format];
	}

	// legacy
	public function forceSetGoods($orderId){
		self::setOrderGoods($orderId);
	}
}
?>