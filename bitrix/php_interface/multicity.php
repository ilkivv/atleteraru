<?php 

CModule::IncludeModule ("iblock");
$parts = explode(".",$_SERVER['HTTP_HOST']);
if ($parts[0] == 'krasnoyarsk') {
    $cityID = 74935;
} elseif($parts[0] == 'kemerovo') {
    $cityID = 76341;
}else {
    $cityID = 74934;
}
$rsCities = CIBlockElement::GetList(array('SORT'=>'ASC','NAME'=>'ASC'),array(/*'ACTIVE'=>'Y',*/'IBLOCK_ID'=>51,'ID'=>$cityID),false,array("nTopCount"=>1));
while ($arCity = $rsCities->GetNextElement()) {
    $arCurrentCity= $arCity->GetFields();
    $arProps = $arCity->GetProperties();
    foreach ($arProps as $k => $v) {
        $arCurrentCity['PROPERTIES'][$v['CODE']] = $v;
    }
}
if (!$arCurrentCity) {
    $rsCities = CIBlockElement::GetList(array('SORT'=>'ASC','NAME'=>'ASC'),array('ACTIVE'=>'Y','IBLOCK_ID'=>51),false,array("nTopCount"=>1));
    while ($arCity = $rsCities->GetNextElement()) {
        $arCurrentCity = $arCity->GetFields();
        $arProps = $arCity->GetProperties();
        foreach ($arProps as $k => $v) {
            $arCurrentCity['PROPERTIES'][$v['CODE']] = $v;
        }
    }   
}

$arCurrentCity['HIDE_CHECK_AVAILABILITY'] = ($cityID != 74934);
$_GLOBALS['CURRENT_CITY'] = $arCurrentCity;
$GLOBALS['CURRENT_CITY'] = $_GLOBALS['CURRENT_CITY'];