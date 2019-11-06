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

    while( @ob_end_clean() );
    header("Content-Type: text/xml");
    $bill_id = str_replace("BX", "", $_REQUEST['bill_id']);
    $arResult = $DB->Query('SELECT ORDER_ID from comepay_payment where BILL_ID='.intval($bill_id), false, "File: ".__FILE__."<br>Line: ".__LINE__);

    $row = $arResult->Fetch();
    $order_id  = $row['ORDER_ID'];
    $arOrder = CSaleOrder::GetByID($order_id);
    CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);
    $resultcode = 151;
    $amount = floatval($_REQUEST['amount']);
    $id = CSalePaySystemAction::GetParamValue("SHOP_LOGIN");
    $pass = CSalePaySystemAction::GetParamValue("SHOP_NOTIFY_PASS");

    if (CComepayPayment::CheckSign($id,
            $pass
        )){
        $strPS_STATUS_MESSAGE = GetMessage("COMEPAY.PAYMENT_STATUS_".ToUpper($_REQUEST['status']));
        if ($status == 'paid') {


            $strSql = "
                SELECT
                    *
                FROM comepay_payment
                WHERE ORDER_ID=".intval($order_id)."
            ";

            $resResult = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
            $payments = array();
            while($row = $resResult->Fetch()){
                $payment[$row['BILL_ID']] = $row;
            }
            if (floatval($payment[$bill_id]['SUM'])==$amount){
                $payment[$bill_id]['PAY'] = 1;
                $DB->Query("update comepay_payment set PAY=1 where BILL_ID=".intval($bill_id), false, "File: ".__FILE__."<br>Line: ".__LINE__);
            }
            $full_payment = 1;
            $payment_sum = 0;
            foreach($payment as $bill){
                if ($bill['PAY'] == 0) {
                    $full_payment = 0;
                } else {
                    $payment_sum += $bill['SUM'];
                }
            }
            if ($full_payment) {
                $PS_STATUS = 'Y';
                $PS_STATUS_DESCRIPTION = GetMessage('COMEPAY.PAYMENT_ORDER_DESCRIPTION',array("#PAYER#"=>$_REQUEST['user']));
            } else {
                $PS_STATUS = 'N';
                $PS_STATUS_DESCRIPTION= GetMessage('COMEPAY.PAYMENT_ORDER_PART_PAYMENT',array("#SUM#"=>$payment_sum));
            }

            $arFields = array(
                    "PS_STATUS" => $PS_STATUS,
                    "PS_STATUS_CODE" => $_REQUEST['status'],
                    "PS_STATUS_DESCRIPTION" => $PS_STATUS_DESCRIPTION,
                    "PS_RESPONSE_DATE" => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
                    "PS_STATUS_MESSAGE" => $strPS_STATUS_MESSAGE,
                    "PS_SUM"=>$payment_sum
                    /*'PAY_VOUCHER_NUM'=>$_REQUEST['operation_id'],
                    'PAY_VOUCHER_DATE'=>Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)),strtotime($_REQUEST['datetime'])),*/
                );
            if($full_payment)CSaleOrder::PayOrder($arOrder["ID"], "Y", true, true);
            CSaleOrder::Update($arOrder["ID"], $arFields);
        } else {
            //TODO если статус отменён то помечать выставленный счёт как canceled
            if($status == 'rejected' || $status == 'unpaid' || $status == 'expired'){
                $DB->Query("update comepay_payment set CANCELED=1 where BILL_ID=".intval($bill_id), false, "File: ".__FILE__."<br>Line: ".__LINE__);
            }
        }

        $resultcode = 0;
    }
    echo "<?xml version=\"1.0\"?><result><result_code>$resultcode</result_code></result>";


}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>