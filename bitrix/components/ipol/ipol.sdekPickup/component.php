<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!cmodule::includeModule('ipol.sdek'))
	return false;

$allCities = false;
if(!is_array($arParams['CITIES']))
	$arParams['CITIES'] = array();
if(count($arParams['CITIES'])==0)
	$allCities=true;

$propAddr = Coption::GetOptionString(CDeliverySDEK::$MODULE_ID,'pvzPicker','');//определяем инпуты, куда писать адреса
$props = CSaleOrderProps::GetList(array(),array('CODE' => $propAddr));
$propAddr='';
while($prop=$props->Fetch())
	$propAddr.=$prop['ID'].',';

$arResult['propAddr'] = $propAddr;
$arResult['Regions'] = array();

if($_SESSION['IPOLSDEK_city'] && !count($arParams['CITIES'])==1)
	$arResult['city']=$_SESSION['IPOLSDEK_city'];
else
	$arResult['city']=(count($arParams['CITIES'])==1)?$arParams['CITIES'][0]:GetMessage('IPOLSDEK_MOSCOW');

if($arParams['CNT_DELIV'] == 'Y'){
	if($arParams['CNT_BASKET'] == 'Y')
		CDeliverySDEK::setOrderGoods();

	$arResult['ORDER'] = array(
		'WEIGHT' => (CDeliverySDEK::$goods['W'])*1000,
		'PRICE'  => CDeliverySDEK::$orderPrice,
		'GOODS'  => array(array(
			"WEIGHT"     => (CDeliverySDEK::$goods['W'])*1000,
			"QUANTITY"   => 1,
			"DIMENSIONS" => array(
				"WIDTH"  => (CDeliverySDEK::$goods['D_W'])*10,
				"HEIGHT" => (CDeliverySDEK::$goods['D_H'])*10,
				"LENGTH" => (CDeliverySDEK::$goods['D_L'])*10
			),
		)),
	);
	$tmpShort = $arResult['ORDER']['GOODS'][0];
	$arResult['ORDER']['GOODS_js'] = "[{WEIGHT:'{$tmpShort['WEIGHT']}',QUANTITY:1,DIMENSIONS:{WIDTH:'{$tmpShort['DIMENSIONS']['WIDTH']}',HEIGHT:'{$tmpShort['DIMENSIONS']['HEIGHT']}',LENGTH:'{$tmpShort['DIMENSIONS']['LENGTH']}'}}]";

	$arResult['DELIVERY'] = sdekHelper::cntDelivs(array(
		'CITY_TO'   => $arResult['city'],
		'WEIGHT'    => (CDeliverySDEK::$goods['W'])*1000,
		'PRICE'     => CDeliverySDEK::$orderPrice,
		'FORBIDDEN' => $arParams['FORBIDDEN']
	));
}

if($arReturn['DELIVERY']['pickup'] != 'no' || $arReturn['DELIVERY']['inpost'] != 'no')
	$arList = CDeliverySDEK::getListFile();
if($arReturn['DELIVERY']['pickup'] != 'no')
	$arList['PVZ'] = CDeliverySDEK::wegihtPVZ((CDeliverySDEK::$orderWeight)?false:COption::GetOptionString(CDeliverySDEK::$MODULE_ID,'weightD',1000),$arList['PVZ']);
if(count($arList)){
	foreach($arList as $mode => $arCities)
		foreach($arCities as $city => $arPVZ)
			if($allCities || in_array($city,$arParams['CITIES'])){
				$arResult[$mode][$city] = $arPVZ;
				if(!in_array($city,$arResult['Regions']))
					$arResult['Regions'][]=$city;
			}
}

$this->IncludeComponentTemplate();
?>