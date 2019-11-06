<?
IncludeModuleLangFile(__FILE__);
class CComepayPayment {
	static $module_id = "comepay.payment";
	static $mode = 0;

	static function getServer() {
		return self::$mode?'moneytest.comepay.ru':'shop.comepay.ru';
	}

	static function getPort() {
		return self::$mode?'439':'443';
	}

	static function Request($url,$data,$headers=array(), $method = 'PUT') {
		$host = CComepayPayment::getServer();
		//$data = http_build_query($data);
		//$url = parse_url($url);
		$head = '';
		if (!empty($headers)){
			foreach($headers as $param=>$value){
				$head .= "{$value}\r\n";
			}
		}
		$fp = fsockopen("ssl://".$host, CComepayPayment::getPort(), $errno, $errstr, 30);
		$result = '';
		if (!$fp) {
		    echo "$errstr ($errno)<br />\n";
		    return false;
		} else {
		    $out = $method." {$url} HTTP/1.0\r\n";
		    $out .= "Host: {$host}\r\n";
		    $out .= $head;
		    //$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		    if($data){
			    $out .='Content-Length: ' . strlen($data)."\r\n";
			}
		    $out .= "Connection: Close\r\n\r\n";
		    $out .= $data;
		    fwrite($fp, $out);
		    $headers = "";
			while(!feof($fp))
			{
				$line = fgets($fp, 4096);
				if($line == "\r\n")
				{
					break;
				}
				$headers .= $line;
			}

				while(!feof($fp))
					$result .= fread($fp, 4096);

		    fclose($fp);
		}
		if( strpos($result, "<h2>401")!==FALSE) {
			$result = json_encode(array('response'=>array('result_code'=>99999)));
		}
		return $result;
	}

	 static function CheckSign($login, $password){
    	return isset($_SERVER['PHP_AUTH_USER'])?
    		($_SERVER['PHP_AUTH_USER']==$login&&$_SERVER['PHP_AUTH_PW']==$password):
    		($_SERVER['REMOTE_USER']=='Basic '.base64_encode($login.':'.$password));
    	;
    }
    static function separateSum($shouldPay){
    	$count = intval($shouldPay / 15000);
		$total_bills = array();
		for ($i=0;$i<$count;++$i){
			$total_bills[] = 15000;
		}
		$ostatok = round($shouldPay - $count*15000,2);
		if ($ostatok){
			if ($ostatok<50&&$count){
				array_pop($total_bills);
				$total_bills[] = 14950;
				$total_bills[] = 50 + $ostatok;
			} else {
				$total_bills[] = $ostatok;
			}
		}
		return $total_bills;
    }

    static function cancelBill($bill_id) {
    	$headers = array(
		'Accept: text/json',
			'Authorization: Basic '.base64_encode(CSalePaySystemAction::GetParamValue("SHOP_LOGIN").':'.CSalePaySystemAction::GetParamValue("SHOP_PASS")),
			'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
			);
    	$result = CComepayPayment::Request('/api/prv/'.CSalePaySystemAction::GetParamValue("PRV_ID").'/bills/BX'.$bill_id,'='.urlencode('status=rejected'),$headers,'PATCH');
    }

    static function OnSaleCancelOrderHandler($id,$val) {
    	global $DB;
    	if($val == 'Y'){
    		$arOrder = CSaleOrder::GetByID($id);
		    CSalePaySystemAction::InitParamArrays($arOrder, $arOrder["ID"]);
    		$arResult = $DB->Query("SELECT BILL_ID FROM comepay_payment WHERE ORDER_ID=".$id.' and CANCELED!=1');
    		while ($row = $arResult->Fetch()){
    			CComepayPayment::cancelBill($row['BILL_ID']);
    			$DB->Query('update comepay_payment set CANCELED=1 where BILL_ID='.$row['BILL_ID']);
    		}

    	}
    }

    static function CreateBill($data, $phone, $card, &$response) {
    	$headers = array(
		'Accept: text/json',
			'Authorization: Basic '.base64_encode(CSalePaySystemAction::GetParamValue("SHOP_LOGIN").':'.CSalePaySystemAction::GetParamValue("SHOP_PASS")),
			'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
			);
    	$comment = GetMessage("COMEPAY.PAYMENT_ORDER_COMMENT_PART",array('#ID#'=>$data['ORDER_ID']));
		if(ToUpper(SITE_CHARSET) != "UTF-8"){
			$comment = iconv("cp1251", "utf-8", $comment);
		}
    	$url = '/api/prv/'.CSalePaySystemAction::GetParamValue("PRV_ID").'/bills/BX'.$data['BILL_ID'];
    	if($card){
    		$user = 'bankcard';
    	} else {
    		$user = 'tel:'.$phone;
    	}
		$rawdata = array(
			'user'=>$user,
			'amount'=>$data['SUM'],
			'ccy'=>'RUB',
			'comment'=>$comment,
			'lifetime'=>date('Y-m-d\TH:i:s',strtotime('+'.IntVal(CSalePaySystemAction::GetParamValue("LIFETIME")).' hour'))

			);
		$data = '';
		foreach ($rawdata as $key => $param) {
			$data .= "$key=".urlencode($param)."&";
		}

		$response = CComepayPayment::Request($url,'='.urlencode($data),$headers);//'{"response":{"result_code":0}}';
		$response = json_decode($response);
		if($response && ($response->response->result_code==0 || $response->response->result_code==215)){
			return true;
		} else {
			return false;
		}
    }


}
?>