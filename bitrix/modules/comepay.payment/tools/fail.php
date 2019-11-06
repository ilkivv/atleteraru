<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
global $DB;
IncludeModuleLangFile(__FILE__);
$order_id = intval(str_replace('BX','',$_REQUEST['order']));
$billResult = $DB->Query('SELECT ORDER_ID FROM comepay_payment where BILL_ID='.$order_id);
$data = $billResult->Fetch();
if($data){
	$oResult = $DB->Query('SELECT sum(`SUM`) as total FROM comepay_payment where ORDER_ID='.intval($data['ORDER_ID']).
		' and BILL_ID!='.$order_id.' and PAY=0 AND CANCELED=0');
		echo GetMessage('COMEPAY.PAYMENT_SOME_ERROR');
	echo "<br><a href='/personal/order/detail/".$data['ORDER_ID']."/'>".GetMessage('COMEPAY.PAYMENT_GOTO_ORDER')."</a>";
} else {
	echo GetMessage('COMEPAY.PAYMENT_ORDER_NOT_FOUND');
}
?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>