<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
global $DB;
IncludeModuleLangFile(__FILE__);
$billResult = $DB->Query('SELECT ORDER_ID FROM comepay_payment where BILL_ID='.intval($_REQUEST['order']));
$data = $billResult->Fetch();
if($data){
	$oResult = $DB->Query('SELECT sum(`SUM`) as total FROM comepay_payment where ORDER_ID='.intval($data['ORDER_ID']).
		' and BILL_ID!='.intval($_REQUEST['order']).' and PAY=0 AND CANCELED=0');
	$result = $oResult->Fetch();
	if($result['total']>0){
		echo GetMessage('COMEPAY.PAYMENT_LEFT_TO_PAY',Array('#SUM#'=>$result['total']));
	} else {
		echo GetMessage('COMEPAY.PAYMENT_SUCCESS_PAYMENT');
	}
	echo "<br><a href='/personal/order/detail/".$data['ORDER_ID']."/'>".GetMessage('COMEPAY.PAYMENT_GOTO_ORDER')."</a>";
} else {
	echo GetMessage('COMEPAY.PAYMENT_ORDER_NOT_FOUND');
}
?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>