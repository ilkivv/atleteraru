<?
error_reporting(E_ERROR | E_PARSE);

define("STOP_STATISTICS", true);
define("NOT_CHECK_PERMISSIONS", true);

if($_GET["admin_section"]=="Y")
	define("ADMIN_SECTION", true);
else
	define("BX_PUBLIC_TOOLS", true);

if(!require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php")) die('prolog_before.php not found!');
IncludeModuleLangFile(__FILE__);
if(CModule::IncludeModule("comepay.payment") && CModule::IncludeModule("sale")) {
global $DB;
$bill_id = intval($_REQUEST['id']);
$arResult = $DB->Query('SELECT ORDER_ID, CREATED, PAY FROM comepay_payment where BILL_ID='.$bill_id.' and CANCELED=0');
$row = $arResult->Fetch();
if($row['ORDER_ID']){
	$arOrder = CSaleOrder::GetByID($row['ORDER_ID']);
    CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);
	$arResult = $DB->Query('SELECT BILL_ID, SUM, ORDER_ID FROM comepay_payment WHERE ORDER_ID='.$row['ORDER_ID'].' AND CANCELED=0 AND CREATED=0');
	$orders = array();
	while($data = $arResult->Fetch()){
		$orders[$data['BILL_ID']] = $data;
		if(CComepayPayment::CreateBill($data, $_REQUEST['phone'], $response)){
			$orders[$data['BILL_ID']]['CREATED'] = 1;
			$DB->Query('update comepay_payment set CREATED=1 WHERE BILL_ID='.$data['BILL_ID']);
		} else {
			if($bill_id==$data['BILL_ID']){
				echo GetMessage('COMEPAY.PAYMENT_ERROR'.$response->response->result_code,array('#RESULT_CODE#'=>$response->response->result_code));
			}
		}
	}
	if($row['CREATED']==1||$orders[$bill_id]['CREATED']==1){
		$server = CComepayPayment::getServer();
		header('Location: https://'.$server.':439/Order/external/main.action?shop='.CSalePaySystemAction::GetParamValue("PRV_ID")
			.'&transaction='.$bill_id.'&successUrl='.urlencode('http://'.$_SERVER['SERVER_NAME'].'/comepay_success.php').'&failUrl='.urlencode('http://'.$_SERVER['SERVER_NAME'].'/comepay_fail.php'));
	}

}
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>