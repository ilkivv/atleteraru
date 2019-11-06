<?
// IPOLSDEK_LOG - вывод лога

IncludeModuleLangFile(__FILE__);

class sdekHelper{
	static $MODULE_ID = "ipol.sdek";


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														логи
			== toLog ==  == errorLog ==  == getErrors ==  == toAnswer ==  == getAnswer ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		// Обычный лог
	static $tmpLogFile = false;
	function toLog($wat,$sign='',$noAction=false){
		if($noAction && array_key_exists('action',$_REQUEST)) return;
		if($sign) $sign.=" ";
		if(!self::$tmpLogFile){
			self::$tmpLogFile = fopen($_SERVER['DOCUMENT_ROOT'].'/SDEKLog.txt','w');
			fwrite(self::$tmpLogFile,"\n\n".date('H:i:s d.m')."\n"); 
		}
		fwrite(self::$tmpLogFile,$sign.print_r($wat,true)."\n"); 
	}
		// Лог ошибок
	static $ERROR_REF = '';
	function errorLog($error){
		if(!COption::GetOptionString(self::$MODULE_ID,'logged',false))
			return;
		self::$ERROR_REF .= $error."\n";
		$file=fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/errorLog.txt","a");
		fwrite($file,"\n".date("d.m.Y H:i:s")." ".$error);
		fclose($file);
	}
	function getErrors(){
		return self::$ERROR_REF;
	}
		// Лог ответов
	static $ANSWER_REF;
	function toAnswer($wat,$sign=''){
		if($sign) $sign.=" ";
		if(self::$ANSWER_REF) self::$ANSWER_REF.="\n";
		self::$ANSWER_REF.=$sign.print_r($wat,true);
	}
	function getAnswer(){
		return self::$ANSWER_REF;
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														кодировки
			== zajsonit ==  == zaDEjsonit ==  == toUpper ==  
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	function zajsonit($handle){
		if(LANG_CHARSET !== 'UTF-8'){
			if(is_array($handle))
				foreach($handle as $key => $val){
					unset($handle[$key]);
					$key=self::zajsonit($key);
					$handle[$key]=self::zajsonit($val);
				}
			else
				$handle=$GLOBALS['APPLICATION']->ConvertCharset($handle,LANG_CHARSET,'UTF-8');
		}
		return $handle;
	}
	function zaDEjsonit($handle){
		if(LANG_CHARSET !== 'UTF-8'){
			if(is_array($handle))
				foreach($handle as $key => $val){
					unset($handle[$key]);
					$key=self::zaDEjsonit($key);
					$handle[$key]=self::zaDEjsonit($val);
				}
			else
				$handle=$GLOBALS['APPLICATION']->ConvertCharset($handle,'UTF-8',LANG_CHARSET);
		}
		return $handle;
	}

	function toUpper($str){
		$str = str_replace( //H8 ANSI
			array(
				GetMessage('IPOLSDEK_LANG_YO_S'),
				GetMessage('IPOLSDEK_LANG_CH_S'),
				GetMessage('IPOLSDEK_LANG_YA_S')
			),
			array(
				GetMessage('IPOLSDEK_LANG_YO_B'),
				GetMessage('IPOLSDEK_LANG_CH_B'),
				GetMessage('IPOLSDEK_LANG_YA_B')
			),
			$str
		);
		if(function_exists('mb_strtoupper'))
			return mb_strtoupper($str,LANG_CHARSET);
		else
			return strtoupper($str);
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Запросы
			== sendToSDEK ==  == getXMLHeaders ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


 	public function sendToSDEK($XML=false,$where=false,$get=false){
		if(!$where) return false;
		$where .= '.php' . (($get) ? "?".$get : '');
		$ch = curl_init();
		if($where == 'new_orders.php' && COption::GetOptionString(self::$MODULE_ID,'crazyHosters','N') != 'Y')
			curl_setopt($ch,CURLOPT_URL,'http://proxy.apiship.ru/cdek/new_orders.php');
		else{
			if(COption::GetOptionString(self::$MODULE_ID,'useOldServer','N') == 'Y'){
				curl_setopt($ch,CURLOPT_URL,'http://gw.edostavka.ru/'.$where);
				curl_setopt($ch,CURLOPT_PORT,11443);
			}else
				curl_setopt($ch,CURLOPT_URL,'http://int.cdek.ru/'.$where);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if($XML){
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, self::zajsonit(array('xml_request' => $XML)));
		}

		$result = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if(array_key_exists('IPOLSDEK_LOG',$GLOBALS) && $GLOBALS['IPOLSDEK_LOG']) self::toLog(array('XML'=>$XML,'where'=>$where,'code'=>$code,'result'=>$result),'sendToSDEK');

		return array(
			'code'   => $code,
			'result' => $result
		);
	}

	function getXMLHeaders(){
		$date = date('Y-m-d');
		return array(
			'date'    => $date,
			'account' => COption::getOptionString(self::$MODULE_ID,'logSDEK'),
			'secure'  => md5($date."&".COption::getOptionString(self::$MODULE_ID,'pasSDEK'))
		);
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Тарифы
			== getTarifList ==  == checkTarifAvail ==  == getDoorTarifs ==  == getExtraTarifs ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	function getTarifList($params=array()){
		// type - тип, pickup или courier
		// mode - что выдавать: 
		// answer - выводить строкой (string) или массивом со значениями (array)
		$arList = array(
			'pickup'  => array(
				'usual'   => array(234,136,138),
				'heavy'   => array(15,17),
				'express' => array(62,63,5,10,12)
			),
			'courier' => array(
				'usual'   => array(233,137,139),
				'heavy'   => array(16,18),
				'express' => array(11,1,3,61,60,59,58,57,83)
			),
			'inpost' => array(
				'usual'   => array(302,301),
			),
		);
		$blocked = unserialize(COption::GetOptionString(sdekdriver::$MODULE_ID,'tarifs','a:{}'));
		if(count($blocked) && (!array_key_exists('fSkipCheckBlocks',$params) || !$params['fSkipCheckBlocks'])){
			foreach($blocked as $key => $val)
				if(!array_key_exists('BLOCK',$val))
					unset($blocked[$key]);
			if(count($blocked))
				foreach($arList as $tarType => $arTars)
					foreach($arTars as $tarMode => $arTarIds)
						foreach($arTarIds as $key => $arTarId)
							if(array_key_exists($arTarId,$blocked))
								unset($arList[$tarType][$tarMode][$key]);
		}
		$answer = $arList;
		if($params['type']){
			if(is_numeric($params['type'])) $type = ($params['type']==136)?$type='pickup':$type='courier';
			else $type = $params['type'];
			$answer = $answer[$type];
			
			if($params['mode'] && array_key_exists($params['mode'],$answer))
				$answer = $answer[$params['mode']];
		}
		
		if(array_key_exists('answer',$params)){
			$answer = self::arrVals($answer);
			if($params['answer'] == 'string'){
				$answer = implode(',',$answer);
				$answer = substr($answer,0,strlen($answer));
			}
		}
		return $answer;
	}

	function checkTarifAvail($profile = false){ // проверяет доступность рассчета доставки по отключенным тарифам
		$tarifs = self::getTarifList(array('type'=>$profile,'answer'=>'array'));
		return (count($tarifs)>0);
	}

	function getDoorTarifs($isStr=false){ // определяет, какие тарифы - до двери
		$arList = array(1,12,17,18,138,139,233,301);
		if($isStr){
			$arList = implode(',',$arList);
			$arList = substr($arList,0,strlen($arList));
		}
		return $arList;
	}

	function getExtraTarifs(){ // доп. настройки для тарифов
		$arTarifs = array(136,137,138,139,233,234,1,3,5,10,11,12,15,16,17,18,57,58,59,60,61,62,63,83,302,301);
		$svdOpts = unserialize(COption::getOptionString(sdekdriver::$MODULE_ID,'tarifs','a:0:{}'));
		$arReturn = array();
		foreach($arTarifs as $tarifId)
			$arReturn[$tarifId] = array(
				'NAME'  => GetMessage("IPOLSDEK_tarif_".$tarifId."_NAME")." (".$tarifId.")",
				'DESC'  => GetMessage("IPOLSDEK_tarif_".$tarifId."_DESCR"),
				'SHOW'  => ($svdOpts[$tarifId]['SHOW']) ? $svdOpts[$tarifId]['SHOW'] : "N",
				'BLOCK' => ($svdOpts[$tarifId]['BLOCK']) ? $svdOpts[$tarifId]['BLOCK'] : "N",
			);
		return $arReturn;
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Типы и активности доставок
			== getDeliveryId ==  == defineDelivery ==  == getDelivery ==  == isActive ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	function getDeliveryId($profile){ // возвращает id доставок профиля
		$profiles = array();
		if(self::isConverted()){
			$dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
				 'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
				 'filter' => array('CODE' => 'sdek:'.$profile)
			));
			while($dPS = $dTS->Fetch())
				$profiles[]=$dPS['ID'];
		}else
			$profiles = array('sdek_'.$profile);
		return $profiles;
	}

	function defineDelivery($id){ // определяет профиль доставки
		if(self::isConverted() && strpos($id,':') === false){
			$dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
				 'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
				 'filter' => array('ID' => $id)
			))->Fetch();
			$delivery = $dTS['CODE'];
		}else
			$delivery = $id;
		$position = strpos($delivery,'sdek:');
		return ($position === 0) ? substr($delivery,5) : false;
	}

	function getDelivery(){// Проверка активности СД
		if(!cmodule::includeModule("sale")) return false;
		if(self::isConverted()){
			$dS = Bitrix\Sale\Delivery\Services\Table::getList(array(
				 'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
				 'filter' => array('CODE' => 'sdek')
			))->Fetch();
		}else
			$dS = CSaleDeliveryHandler::GetBySID('sdek')->Fetch();
		return $dS;
	}

	function isActive(){
		$dS = self::getDelivery();
		return ($dS && $dS['ACTIVE'] == 'Y');
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Возьня с городами и местоположениями
			== getErrCities ==  == getNormalCity ==  == isLocation20 ==  == isCityAvail ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	function getErrCities(){//ошибочные города
		if(!file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/errCities.json"))
			return false;
		return self::zaDEjsonit(json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/errCities.json"),true));
	}

	function getNormalCity($cityId){// местоположения 2.0, получаем id городa
		if(self::isLocation20() && $cityId){//getLocationIDbyCODE
			$cityType = \Bitrix\Sale\Location\TypeTable::getList(array('filter'=>array('=CODE'=>'CITY')))->Fetch();
			if(strlen($cityId) >= 10)
				$city = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=CODE' => $cityId)))->Fetch();
			else
				$city = \Bitrix\Sale\Location\LocationTable::getById($cityId)->Fetch();

			if($city['TYPE_ID'] != $cityType['ID']){
				$newCityId = false;
				while(!$newCityId){
					if(empty($city['PARENT_ID']))
						break;
					$city = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=ID' => $city['PARENT_ID'])))->Fetch();
					if($city['TYPE_ID'] == $cityType['ID'])
						$newCityId = $city['ID'];
				}
				$cityId = $newCityId;
			}
			$cityId = $city['ID'];
		}
		return $cityId;
	}

	function isLocation20(){
		return (method_exists("CSaleLocation","isLocationProMigrated") && CSaleLocation::isLocationProMigrated());
	}

	function isCityAvail($city,$mode=false){// Проверка возможности доставки в город
		if(!is_numeric($city)){

			$cityName = str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),$city);
			$city = CSaleLocation::getList(array(),array('CITY_NAME'=>self::zaDEjsonit($city)))->Fetch();
			if($city)
				$cityId = $city['ID'];
		}else{
			$cityId = $city;
			$city = CSaleLocation::GetByID($cityId);
			$cityName = str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),$city['CITY_NAME']);
		}
		$return = false;
		if($city){
			$arCity = sqlSdekCity::getByBId($cityId);
			if($arCity['SDEK_ID']){
				$return = array('courier');
				if(CDeliverySDEK::checkPVZ($cityName))
					$return[]='pickup';
			}
		}
		return $return;
	}

    public function getCity($location,$ifFull = false){ // получить город из БД по его коду / id
		if(!$location)
			return false;
		$arCity = sqlSdekCity::getByBId($location);
		if(!$arCity)
			$arCity = sqlSdekCity::getByBId(self::getNormalCity($location));
		if($ifFull)
			return $arCity;
		else
			return $arCity['SDEK_ID'];
    }

	public function getHomeCity(){ // домашний город
		return self::getCity(COption::GetOptionString(self::$MODULE_ID,'departure'));
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Сервисные и без группы
			== getListFile ==  == arrVals ==  == isEqualArrs ==  == isLogged ==  == isConverted ==  == isAdmin ==  == getSaleVersion ==  == oIdByShipment ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	function getListFile($noEnc=false){// получем данные из LIST - файла в том формате, в котором они... должны... быть...
		if(!file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/list.php")) return array();
		$arList = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/list.php"),true);
		if(!$noEnc)
			$arList = self::zaDEjsonit($arList);
		foreach(GetModuleEvents(self::$MODULE_ID,"onPVZListReady",true) as $arEvent)
			ExecuteModuleEventEx($arEvent,Array(&$arList));
		return $arList;
	}

	function arrVals($arr){ // очень служебная
		$return = array();
		foreach($arr as $key => $val)
			if(is_array($val))
				$return = array_merge($return,self::arrVals($val));
			else
				$return []= $val;
		return $return;
	}

	function isEqualArrs($arr1,$arr2){ // еще более служебная
		foreach($arr1 as $key => $val)
			if(!array_key_exists($key,$arr2) || $arr1[$key] != $arr2[$key])
				return false;
			else
				unset($arr2[$key]);

		if(count($arr2))
			return false;

		return true;	
	}

	function isLogged(){
		return COption::GetOptionString(self::$MODULE_ID,"logged",false);
	}

	function isConverted(){
		return (COption::GetOptionString("main","~sale_converted_15",'N') == 'Y');
	}

	function isAdmin($min = 'W'){
		$rights = CMain::GetUserRight(self::$MODULE_ID);
		$DEPTH = array('D'=>1,'R'=>2,'W'=>3);
		return($DEPTH[$min] <= $DEPTH[$rights]);
	}

	protected function getSaleVersion(){
		include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/install/version.php');
		return $arModuleVersion['VERSION'];
	}

	// отправления
	function oIdByShipment($shipmentID){
		if(!self::isConverted())
			return false;
		\Bitrix\Main\Loader::includeModule('sale');
		$shipment = self::getShipmentById($shipmentID);
		return $shipment['ORDER_ID'];
	}

	protected function setShipmentField($shipmentId,$field,$value){
		if(!$shipmentId || !self::isConverted())
			return false;
		$order = \Bitrix\Sale\Order::load(self::oIdByShipment($shipmentId));
		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->getItemById($shipmentId);
		$shipment->setField($field,$value);
		$order->save();
		return true;
	}

	function getShipmentById($shipmentId){
		if(!self::isConverted())
			return false;
		\Bitrix\Main\Loader::includeModule('sale');
		return Bitrix\Sale\Shipment::getList(array('filter'=>array('ID' => $shipmentId)))->Fetch();
	}

	function canShipment(){
		return (self::isConverted() && COption::GetOptionString(self::$MODULE_ID,'shipments','N') == 'Y');
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														LEGACY
			== cntDelivs ==  == defineProto ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	function cntDelivs($arOrder){//Выдает срок и стоимость доставки для виджета 
		return CDeliverySDEK::cntDelivsOld($arOrder);
	}

	function defineProto(){
		return (
			!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
			$_SERVER['SERVER_PORT'] == 443 ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ||
			isset($_SERVER['HTTP_X_HTTPS']) && $_SERVER['HTTP_X_HTTPS'] ||
			isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'https'
		) ? 'https' : 'http';
	}
}

CModule::AddAutoloadClasses(
    sdekHelper::$MODULE_ID,
    array(
        'sdekdriver'				 => '/classes/general/sdekclass.php',
        'CDeliverySDEK'				 => '/classes/general/sdekdelivery.php',
        'sdekOption'				 => '/classes/general/sdekoption.php',
        'sdekExport'				 => '/classes/general/sdekexport.php',
		'sqlSdekOrders'				 => '/classes/mysql/sqlSdekOrders.php',
		'sqlSdekCity'				 => '/classes/mysql/sqlSdekCity.php',
		'CalculatePriceDeliverySdek' => '/classes/sdekMercy/calculator.php',
		'cityExport'				 => '/classes/sdekMercy/syncCityClass.php'
        )
);

?>