<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
IncludeModuleLangFile(__FILE__);
CModule::IncludeModule("comepay.payment");
if ($_SERVER["REQUEST_METHOD"] == "POST" && trim($_POST["SET_NEW_PHONE"])!="")
	$phone = trim($_POST["NEW_PHONE"]);
else
	$phone = trim(CSalePaySystemAction::GetParamValue("CLIENT_PHONE"));

$repl = array(' ','-','(',')');
$phone = str_replace($repl,"",$phone);
if(preg_match('/^8(\d{10})$/', $phone,$m)){
	$phone = '+7'.$m[1];
}
if(preg_match('/^7(\d{10})$/', $phone,$m)){
	$phone = '+7'.$m[1];
}
if(preg_match('/^0(\d{9})$/', $phone,$m)){
	$phone = '+380'.$m[1];
}
$orderID = (strlen(CSalePaySystemAction::GetParamValue("ORDER_ID")) > 0) ? CSalePaySystemAction::GetParamValue("ORDER_ID") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];

$shouldPay = str_replace(",", ".", CSalePaySystemAction::GetParamValue("SHOULD_PAY"));
if (CSalePaySystemAction::GetParamValue("CURRENCY")){
	$shouldPay = number_format(CCurrencyRates::ConvertCurrency($shouldPay,CSalePaySystemAction::GetParamValue("CURRENCY"),'RUB'),2,'.','');
}

$shopID = CSalePaySystemAction::GetParamValue("SHOP_LOGIN");
$restID = CSalePaySystemAction::GetParamValue("PRV_ID");
//созданные в модуле и выставленные в comepay счета
$paymentResult = $DB->Query('SELECT BILL_ID, SUM FROM comepay_payment WHERE ORDER_ID='.$DB->ForSQL($orderID).' AND CANCELED=0 AND CREATED=1');
//созданные в модуле, но не выставленные в comepay счета
$paymentNewResult = $DB->Query('SELECT BILL_ID, SUM FROM comepay_payment WHERE ORDER_ID='.$DB->ForSQL($orderID).' AND CANCELED=0 AND CREATED=0');
$oResult = $DB->Query('SELECT sum(`SUM`) as total FROM comepay_payment where ORDER_ID='.$DB->ForSQL($orderID).
		' and PAY=1');
$payed = $oResult->Fetch();
if (
		(
		!preg_match("#^\\+\d{1,15}$#",trim($_POST["NEW_PHONE"]),$match) // не верно указан телефон
		&& !isset($_POST['CARD_PAYMENT']) 								// не выбрана оплата по карте
		&& $_POST['CARD_PAYMENT']!=1									//
		) && ( !$paymentResult->Fetch() || $paymentNewResult->Fetch())		// счета ещё не выставлены
	)
{
	//показываем форму ввода телефона
	?>

	<form method="post" action="<?= POST_FORM_ACTION_URI?>" name="comepay_form">
		<p  class="comepay_payment_submit_error_phone_msg"><?= GetMessage("COMEPAY.PAYMENT_ORDER_PHONE_ERROR")?></p>
		<input type="text"  class="comepay_payment_submit_phone_input" name="NEW_PHONE" size="30" value="<?= $phone?>" /><br>
		<? if (CSalePaySystemAction::GetParamValue("CARD_PAYMENT",0)):?>
		<input type="checkbox" id="comepay_card_payment" name="CARD_PAYMENT" style="margin:4px;" value="1" onclick="if(this.checked){document.forms['comepay_form'].NEW_PHONE.readOnly=true;}else{document.forms['comepay_form'].NEW_PHONE.readOnly=false;}"> <label for="comepay_card_payment" style="vertical-align: middle"><img src="/bitrix/modules/comepay.payment/payment/comepay.payment/visa_mastercard_logo.jpg" style="height:24px;margin-right: 3px;"><?= GetMessage("COMEPAY.PAYMENT_CARD_PAYMENT")?></label><br>
		<? endif;?>
		<input type="submit" class="comepay_payment_submit_phone_button" name="SET_NEW_PHONE" value="<?= GetMessage("COMEPAY.PAYMENT_SEND_NEW_PHONE")?>" />
	</form>
	<?
}
else
{
	$html = '<br>';
	global $DB;

	$strSql = "
		SELECT
			*
		FROM comepay_payment
		WHERE ORDER_ID='".$DB->ForSQL($orderID)."'
		AND CANCELED=0
	";
	$resResult = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	$data = $resResult->Fetch();
	if ($data) {
		//Счета добавлены в базу
		$bills = array();
		$bills[] = $data;
		while($row = $resResult->Fetch()) {
			$bills[] = $row;
		}
		$new = 1;
		foreach ($bills as $bill) {
			if($bill['CREATED']) {
				$new = 0;
				break;
			}
		}
		if($new) {
			//счета ещё не выставленны в comepay
			$sum = 0;
			foreach ($bills as $bill) {
				$sum += $bill['SUM'];
			}
			if($sum == $shouldPay) {
				//Сумма не отличается
				if(count($bill>2)){
					$html .= '<div class="comepay_payment_disclaimer">'.GetMessage('COMEPAY.PAYMENT_DISCLAIMER').'</div>';
				}
				if($payed && isset($payed['total']) && $payed['total']>0){
					$html .= GetMessage('COMEPAY.PAYMENT_PAYED',array('#SUM#'=>$payed['total'])).'<br>';
				}
				foreach ($bills as $bill) {
					$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$bill['SUM']))."<form action='/bitrix/tools/comepay_create.php' method='post'>
					<input type='hidden' name='phone' value='$phone'>
					<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
					<input type='hidden' name='id' value='{$bill['BILL_ID']}'>
					<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
					</form><br>";
				}
			} else {
				//Сумма отличается, сменился курс или состав заказа
				$DB->Query('DELETE FROM comepay_payment WHERE ORDER_ID='.$orderID);
				$sum = CComepayPayment::separateSum($shouldPay);
				if($payed && isset($payed['total']) && $payed['total']>0){
					$html .= GetMessage('COMEPAY.PAYMENT_PAYED',array('#SUM#'=>$payed['total'])).'<br>';
				}
				foreach ($sum as $value) {
					$DB->Query("INSERT INTO comepay_payment (ORDER_ID,ITERATOR,PAY,SUM,CREATED,CANCELED) VALUES ({$orderID},0,0,'{$value}',0,0)", false, "File: ".__FILE__."<br>Line: ".__LINE__);
					$id = $DB->LastID();

					$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$value))."<form action='/bitrix/tools/comepay_create.php' method='post'>
					<input type='hidden' name='phone' value='$phone'>
					<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
					<input type='hidden' name='id' value='$id'>
					<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
					</form><br>";
				}

			}
		} else {
			//счета уже выставленны
			$sum = 0;
			foreach ($bills as $bill) {
				$sum += $bill['SUM'];
			}
			if($sum == $shouldPay) {
				//Сумма не отличается
				if($payed && isset($payed['total']) && $payed['total']>0){
					$html .= GetMessage('COMEPAY.PAYMENT_PAYED',array('#SUM#'=>$payed['total'])).'<br>';
				}
				foreach ($bills as $bill) {
					if (!$bill['PAY']) {
						$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$bill['SUM']))."<form action='/bitrix/tools/comepay_create.php' method='post'>
						<input type='hidden' name='phone' value='$phone'>
						<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
						<input type='hidden' name='id' value='{$bill['BILL_ID']}'>
						<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
						</form><br>";
					}
				}
			} else {
				//Сумма отличается, сменился курс или состав заказа
				$pay = 0;
				foreach ($bills as $bill) {
					if($bill['PAY']) {
						$pay = 1;
						break;
					}
				}
				if (!$pay) {
					//нет оплаченных счетов
					if ($sum < $shouldPay){
						// сумма выставленных счетов меньше новой суммы
						if($payed && isset($payed['total']) && $payed['total']>0){
							$html .= GetMessage('COMEPAY.PAYMENT_PAYED',array('#SUM#'=>$payed['total'])).'<br>';
						}
						foreach ($bills as $bill) {
							$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$bill['SUM']))."<form action='/bitrix/tools/comepay_create.php' method='post'>
							<input type='hidden' name='phone' value='$phone'>
							<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
							<input type='hidden' name='id' value='{$bill['BILL_ID']}'>
							<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
							</form><br>";
						}
						$new_sum = CComepayPayment::separateSum($shouldPay - $sum);
						foreach ($new_sum as $value) {
							$DB->Query("INSERT INTO comepay_payment (ORDER_ID,ITERATOR,PAY,SUM,CREATED,CANCELED) VALUES ({$orderID},0,0,'{$value}',0,0)", false, "File: ".__FILE__."<br>Line: ".__LINE__);
							$id = $DB->LastID();
							$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$value))."<form action='/bitrix/tools/comepay_create.php' method='post'>
							<input type='hidden' name='phone' value='$phone'>
							<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
							<input type='hidden' name='id' value='$id'>
							<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
							</form><br>";
						}
					} else {
						//сумма выставленных счетов больше новой суммы
						$total = 0;
						$i = 0;
						while($total<=$shouldPay){
							$total += $bills[$i]['SUM'];
							$i++;
						}
						$i--;
						$total -= $bills[$i]['SUM'];
						$count = count($bills);
						for ($j=$i;$j<$count;$j++){
							CComepayPayment::cancelBill($bills[$j]['BILL_ID']);
							$DB->Query('UPDATE comepay_payment set CANCELED=1 where BILL_ID='.$bills[$j]['BILL_ID']);
							unset($bills[$j]);
						}
						if($payed && isset($payed['total']) && $payed['total']>0){
							$html .= GetMessage('COMEPAY.PAYMENT_PAYED',array('#SUM#'=>$payed['total'])).'<br>';
						}
						foreach ($bills as $bill) {
							$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$bill['SUM']))."<form action='/bitrix/tools/comepay_create.php' method='post'>
							<input type='hidden' name='phone' value='$phone'>
							<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
							<input type='hidden' name='id' value='{$bill['BILL_ID']}'>
							<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
							</form><br>";
						}
						$new_sum = CComepayPayment::separateSum($shouldPay - $total);
						foreach ($new_sum as $value) {
							$DB->Query("INSERT INTO comepay_payment (ORDER_ID,ITERATOR,PAY,SUM,CREATED,CANCELED) VALUES ({$orderID},0,0,'{$value}',0,0)", false, "File: ".__FILE__."<br>Line: ".__LINE__);
							$id = $DB->LastID();
							$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$value))."<form action='/bitrix/tools/comepay_create.php' method='post'>
							<input type='hidden' name='phone' value='$phone'>
							<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
							<input type='hidden' name='id' value='$id'>
							<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
							</form><br>";
						}

					}
				} else {
					//есть оплаченные счета
					if ($sum < $shouldPay) {
						//нова сумма больше выставленных счетов
						if($payed && isset($payed['total']) && $payed['total']>0){
							$html .= GetMessage('COMEPAY.PAYMENT_PAYED',array('#SUM#'=>$payed['total'])).'<br>';
						}
						foreach ($bills as $bill) {
							if (!$bill['PAY']) {
								$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$bill['SUM']))."<form action='/bitrix/tools/comepay_create.php' method='post'>
								<input type='hidden' name='phone' value='$phone'>
								<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
								<input type='hidden' name='id' value='{$bill['BILL_ID']}'>
								<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
								</form><br>";
							}
						}
						$new_sum = CComepayPayment::separateSum($shouldPay - $sum);
						foreach ($new_sum as $value) {
							$DB->Query("INSERT INTO comepay_payment (ORDER_ID,ITERATOR,PAY,SUM,CREATED,CANCELED) VALUES ({$orderID},0,0,'{$value}',0,0)", false, "File: ".__FILE__."<br>Line: ".__LINE__);
							$id = $DB->LastID();
							$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$value))."<form action='/bitrix/tools/comepay_create.php' method='post'>
							<input type='hidden' name='phone' value='$phone'>
							<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
							<input type='hidden' name='id' value='$id'>
							<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
							</form><br>";
						}
					} else {
						//новая сумма меньше выставленных счетов
						$payed_sum = 0;
						foreach ($bills as $bill) {
							if ($bill['PAY']) {
								$payed_sum += $bill['SUM'];
							}
						}
						if ($payed_sum == $shouldPay){
							CSaleOrder::PayOrder($orderID, "Y", true, true);
							$html .= GetMessage('COMEPAY.PAYMENT_ORDER_FULLPAYMENT');
						}
						if ($payed_sum > $shouldPay) {
							CSaleOrder::PayOrder($orderID, "Y", true, true);
							$html .= GetMessage('COMEPAY.PAYMENT_BIG_SUM');
						}
						if($payed_sum < $shouldPay) {
							//сумма оплаченных счетов меньше новой суммы
							foreach ($bills as $bill) {
								if(!$bill['PAY']){
									CComepayPayment::cancelBill($bill['BILL_ID']);
									$DB->Query('UPDATE comepay_payment set CANCELED=1 where BILL_ID='.$bill['BILL_ID']);
								}
							}

							$new_sum = CComepayPayment::separateSum($shouldPay - $payed_sum);
							if($payed && isset($payed['total']) && $payed['total']>0){
								$html .= GetMessage('COMEPAY.PAYMENT_PAYED',array('#SUM#'=>$payed['total'])).'<br>';
							}
							foreach ($new_sum as $value) {
								$DB->Query("INSERT INTO comepay_payment (ORDER_ID,ITERATOR,PAY,SUM,CREATED,CANCELED) VALUES ({$orderID},0,0,'{$value}',0,0)", false, "File: ".__FILE__."<br>Line: ".__LINE__);
								$id = $DB->LastID();
								$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$value))."<form action='/bitrix/tools/comepay_create.php' method='post'>
								<input type='hidden' name='phone' value='$phone'>
								<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
								<input type='hidden' name='id' value='$id'>
								<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
								</form><br>";
							}
						}
					}
				}
			}
		}
	} else {
		//показ после офрмления заказ
		$sum = CComepayPayment::separateSum($shouldPay);
		if(count($sum)==1 && isset($_POST['SET_NEW_PHONE'])) {
			$DB->Query("INSERT INTO comepay_payment (ORDER_ID,ITERATOR,PAY,SUM,CREATED) VALUES ({$orderID},0,0,'{$sum[0]}',0)", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$id = $DB->LastID();
			$html .= "<form action='/bitrix/tools/comepay_create.php' method='post' name='comepay_payment_form'>
			<input type='hidden' name='phone' value='$phone'>
			<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
			<input type='hidden' name='id' value='$id'>
			</form>".GetMessage('COMEPAY.PAYMENT_REDIRECT_TO_PAYMENT');
			$html .= "<script type='text/javascript'>";
        	$html.= ' document.forms.comepay_payment_form.submit();';
        	$html.= ' </script>';
		} else {
			if(count($sum)>1){
				$html .= '<div class="comepay_payment_disclaimer">'.GetMessage('COMEPAY.PAYMENT_DISCLAIMER').'</div>';
			}
			if($payed && isset($payed['total']) && $payed['total']>0){
				$html .= GetMessage('COMEPAY.PAYMENT_PAYED',array('#SUM#'=>$payed['total'])).'<br>';
			}
			foreach ($sum as $value) {
				$DB->Query("INSERT INTO comepay_payment (ORDER_ID,ITERATOR,PAY,SUM,CREATED) VALUES ({$orderID},0,0,'{$value}',0)", false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$id = $DB->LastID();
				$html .= GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT_BILL',array('#SUM#'=>$value))."<form action='/bitrix/tools/comepay_create.php' method='post'>
				<input type='hidden' name='phone' value='$phone'>
				<input type='hidden' name='card' value='".intval($_POST["CARD_PAYMENT"])."'>
				<input type='hidden' name='id' value='$id'>
				<button type='submit'>".GetMessage('COMEPAY.PAYMENT_GOTO_PAYMENT')."</button>
				</form><br>";
			}
		}
	}

	echo $html;
}
?>