<?php
class CSaleExport2XML extends CSaleExport
{
    function getOrderDiscountCupons($CUPONS)
    {
        $arCuponsNames = array_unique($CUPONS);
        
        if (!$arCuponsNames) {
            return;
        }
        $arFilter = array (
                'COUPON' => $arCuponsNames
        );
        $dbCoupon = CCatalogDiscountCoupon::GetList (array (), $arFilter);
        $discounts = array();
        while ($arCoupon = $dbCoupon->Fetch ())
        {
            $arCoupons[] = $arCoupon;
            $discounts[] = $arCoupon['DISCOUNT_ID'];
        }
        $discounts = array_unique($discounts);
        if (!$arCoupons) {
            return;
        }
        $Result =  CCatalogDiscount::GetList(array(),array('ID'=>$discounts),false,false,array('ID','VALUE'));
        while ($arDiscount = $Result->Fetch()) {
            $arDiscounts[$arDiscount['ID']] = $arDiscount;
        }
            ?>
                <?php foreach ($arCoupons as $k => $arCoupon) {?>
                <ДисконтнаяКарта>
                    <НомерКарты><?=$arCoupon['COUPON']?></НомерКарты>
                    <ФИОВладельца><?=$arCoupon['DESCRIPTION']?></ФИОВладельца>
                    <ТипКарты>Дисконтная</ТипКарты>
                    <ТекущаяСкидка><?=intval($arDiscounts[$arCoupon['DISCOUNT_ID']]['VALUE'])?></ТекущаяСкидка>
                    <УникальныйИдентификатор><?=$arCoupon['XML_ID']?></УникальныйИдентификатор>
                    <УникальныйИдентификаторЗаменяемойКарты></УникальныйИдентификаторЗаменяемойКарты>
                    <УникальныйИдентификаторВладельца></УникальныйИдентификаторВладельца>
                </ДисконтнаяКарта>
                <?php }?>
        <?php 
    }    
    
    function ExportOrders2Xml($arFilter = Array(), $nTopCount = 0, $currency = "", $crmMode = false, $time_limit = 0, $version = false, $arOptions = Array())
    {
        global $DB;
        $count = false;
        if(IntVal($nTopCount) > 0)
            $count = Array("nTopCount" => $nTopCount);
        $bNewVersion = (strlen($version) > 0);
        $bExportFromCrm = (isset($arOptions["EXPORT_FROM_CRM"]) && $arOptions["EXPORT_FROM_CRM"] === "Y");
    
        if(IntVal($time_limit) > 0)
        {
            //This is an optimization. We assume than no step can take more than one year.
            if($time_limit > 0)
                $end_time = time() + $time_limit;
            else
                $end_time = time() + 365*24*3600; // One year
    
            //$version
            $lastOrderPrefix = "LAST_ORDER_ID";
            if($crmMode)
            {
                $lastOrderPrefix = md5(serialize($arFilter));
                if(!empty($_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]) && IntVal($nTopCount) > 0)
                    $count["nTopCount"] = $count["nTopCount"]+count($_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]);
            }
            else
            {
                if(IntVal($_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]) > 0)
                {
                    $arFilter["<ID"] = $_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix];
                }
            }
        }
    
        $arResultStat = array(
                "ORDERS" => 0,
                "CONTACTS" => 0,
                "COMPANIES" => 0,
        );
    
        $accountNumberPrefix = COption::GetOptionString("sale", "1C_SALE_ACCOUNT_NUMBER_SHOP_PREFIX", "");
    
        $dbPaySystem = CSalePaySystem::GetList(Array("ID" => "ASC"), Array("ACTIVE" => "Y"), false, false, Array("ID", "NAME", "ACTIVE"));
        while($arPaySystem = $dbPaySystem -> Fetch())
            $paySystems[$arPaySystem["ID"]] = $arPaySystem["NAME"];
    
        $dbDelivery = CSaleDelivery::GetList(Array("ID" => "ASC"), Array("ACTIVE" => "Y"), false, false, Array("ID", "NAME", "ACTIVE"));
        while($arDelivery = $dbDelivery -> Fetch())
            $delivery[$arDelivery["ID"]] = $arDelivery["NAME"];
    
        $rsDeliveryHandlers = CSaleDeliveryHandler::GetAdminList(array("SID" => "ASC"));
        while ($arHandler = $rsDeliveryHandlers->Fetch())
        {
            if(is_array($arHandler["PROFILES"]))
            {
                foreach($arHandler["PROFILES"] as $k => $v)
                {
                    $delivery[$arHandler["SID"].":".$k] = $v["TITLE"]." (".$arHandler["NAME"].")";
                }
            }
        }
    
        $arStore = array();
        $arMeasures = array();
        if(CModule::IncludeModule("catalog"))
        {
            $dbList = CCatalogStore::GetList(
                    array("SORT" => "DESC", "ID" => "ASC"),
                    array("ACTIVE" => "Y", "ISSUING_CENTER" => "Y"),
                    false,
                    false,
                    array("ID", "SORT", "TITLE", "ADDRESS", "DESCRIPTION", "PHONE", "EMAIL", "XML_ID")
            );
            while ($arStoreTmp = $dbList->Fetch())
            {
                if(strlen($arStoreTmp["XML_ID"]) <= 0)
                    $arStoreTmp["XML_ID"] = $arStoreTmp["ID"];
                $arStore[$arStoreTmp["ID"]] = $arStoreTmp;
            }
    
            $dbList = CCatalogMeasure::getList(array(), array(), false, false, array("CODE", "MEASURE_TITLE"));
            while($arList = $dbList->Fetch())
            {
                $arMeasures[$arList["CODE"]] = $arList["MEASURE_TITLE"];
            }
        }
        if(empty($arMeasures))
            $arMeasures[796] = GetMessage("SALE_EXPORT_SHTUKA");
    
        $dbExport = CSaleExport::GetList();
        while($arExport = $dbExport->Fetch())
        {
            $arAgent[$arExport["PERSON_TYPE_ID"]] = unserialize($arExport["VARS"]);
        }
    
        $dateFormat = CSite::GetDateFormat("FULL");
    
        if ($crmMode)
        {
            echo "<"."?xml version=\"1.0\" encoding=\"UTF-8\"?".">\n";
    
            $arCharSets = array();
            $dbSitesList = CSite::GetList(($b=""), ($o=""));
            while ($arSite = $dbSitesList->Fetch())
                $arCharSets[$arSite["ID"]] = $arSite["CHARSET"];
        }
        else
            echo "<"."?xml version=\"1.0\" encoding=\"windows-1251\"?".">\n";
        ?>
    		<<?=GetMessage("SALE_EXPORT_COM_INFORMATION")?> <?=GetMessage("SALE_EXPORT_SHEM_VERSION")?>="<?=($bNewVersion ? "2.08" : "2.05")?>" <?=GetMessage("SALE_EXPORT_SHEM_DATE_CREATE")?>="<?=date("Y-m-d")?>T<?=date("G:i:s")?>" <?=GetMessage("SALE_EXPORT_DATE_FORMAT")?>="<?=GetMessage("SALE_EXPORT_DATE_FORMAT_DF")?>=yyyy-MM-dd; <?=GetMessage("SALE_EXPORT_DATE_FORMAT_DLF")?>=DT" <?=GetMessage("SALE_EXPORT_DATE_FORMAT_DATETIME")?>="<?=GetMessage("SALE_EXPORT_DATE_FORMAT_DF")?>=<?=GetMessage("SALE_EXPORT_DATE_FORMAT_TIME")?>; <?=GetMessage("SALE_EXPORT_DATE_FORMAT_DLF")?>=T" <?=GetMessage("SALE_EXPORT_DEL_DT")?>="T" <?=GetMessage("SALE_EXPORT_FORM_SUMM")?>="<?=GetMessage("SALE_EXPORT_FORM_CC")?>=18; <?=GetMessage("SALE_EXPORT_FORM_CDC")?>=2; <?=GetMessage("SALE_EXPORT_FORM_CRD")?>=." <?=GetMessage("SALE_EXPORT_FORM_QUANT")?>="<?=GetMessage("SALE_EXPORT_FORM_CC")?>=18; <?=GetMessage("SALE_EXPORT_FORM_CDC")?>=2; <?=GetMessage("SALE_EXPORT_FORM_CRD")?>=.">
    		<?
    		$arOrder = array("ID" => "DESC");
    		if ($crmMode)
    			$arOrder = array("DATE_UPDATE" => "ASC");
    
    		$arSelect = array(
    			"ID", "LID", "PERSON_TYPE_ID", "PAYED", "DATE_PAYED", "EMP_PAYED_ID", "CANCELED", "DATE_CANCELED",
    			"EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "EMP_STATUS_ID",
    			"PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID", "PRICE", "CURRENCY", "DISCOUNT_VALUE",
    			"SUM_PAID", "USER_ID", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "DATE_INSERT_FORMAT", "DATE_UPDATE", "USER_DESCRIPTION",
    			"ADDITIONAL_INFO", "PS_STATUS", "PS_STATUS_CODE", "PS_STATUS_DESCRIPTION", "PS_STATUS_MESSAGE", "PS_SUM", "PS_CURRENCY", "PS_RESPONSE_DATE",
    			"COMMENTS", "TAX_VALUE", "STAT_GID", "RECURRING_ID", "ACCOUNT_NUMBER", "SUM_PAID", "DELIVERY_DOC_DATE", "DELIVERY_DOC_NUM", "TRACKING_NUMBER", "STORE_ID",
    			"ID_1C", "VERSION",
    		);
    
    		$bCrmModuleIncluded = false;
    		if ($bExportFromCrm)
    		{
    			$arSelect[] = "UF_COMPANY_ID";
    			$arSelect[] = "UF_CONTACT_ID";
    			if (IsModuleInstalled("crm") && CModule::IncludeModule("crm"))
    				$bCrmModuleIncluded = true;
    		}
    
    		$dbOrderList = CSaleOrder::GetList($arOrder, $arFilter, false, $count, $arSelect);
    
    		while($arOrder = $dbOrderList->Fetch())
    		{
    			if ($crmMode)
    			{			
    				if($bNewVersion && is_array($_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]) && in_array($arOrder["ID"], $_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix]) && empty($arFilter["ID"]))
    					continue;
    				ob_start();
    			}
    
    			$arResultStat["ORDERS"]++;
    
    			$agentParams = $arAgent[$arOrder["PERSON_TYPE_ID"]];
    			$arProp = Array();
    			$arProp["ORDER"] = $arOrder;
    
    			if (IntVal($arOrder["USER_ID"]) > 0)
    			{
    				$dbUser = CUser::GetByID($arOrder["USER_ID"]);
    				if ($arUser = $dbUser->Fetch())
    					$arProp["USER"] = $arUser;
    			}
    			if ($bExportFromCrm)
    			{
    				$arProp["CRM"] = array();
    				$companyID = isset($arOrder["UF_COMPANY_ID"]) ? intval($arOrder["UF_COMPANY_ID"]) : 0;
    				$contactID = isset($arOrder["UF_CONTACT_ID"]) ? intval($arOrder["UF_CONTACT_ID"]) : 0;
    				if ($companyID > 0)
    				{
    					$arProp["CRM"]["CLIENT_ID"] = "CRMCO".$companyID;
    				}
    				else
    				{
    					$arProp["CRM"]["CLIENT_ID"] = "CRMC".$contactID;
    				}
    
    				$clientInfo = array(
    					"LOGIN" => "",
    					"NAME" => "",
    					"LAST_NAME" => "",
    					"SECOND_NAME" => ""
    				);
    
    				if ($bCrmModuleIncluded)
    				{
    					if ($companyID > 0)
    					{
    						$arCompanyFilter = array('=ID' => $companyID);
    						$dbCompany = CCrmCompany::GetListEx(
    							array(), $arCompanyFilter, false, array("nTopCount" => 1),
    							array("TITLE")
    						);
    						$arCompany = $dbCompany->Fetch();
    						unset($dbCompany, $arCompanyFilter);
    						if (is_array($arCompany))
    						{
    							if (isset($arCompany["TITLE"]))
    								$clientInfo["NAME"] = $arCompany["TITLE"];
    						}
    						unset($arCompany);
    					}
    					else if ($contactID > 0)
    					{
    						$arContactFilter = array('=ID' => $contactID);
    						$dbContact = CCrmContact::GetListEx(
    							array(), $arContactFilter, false, array("nTopCount" => 1),
    							array("NAME", "LAST_NAME", "SECOND_NAME")
    						);
    						$arContact = $dbContact->Fetch();
    						unset($dbContact, $arContactFilter);
    						if (is_array($arContact))
    						{
    							if (isset($arContact["NAME"]))
    								$clientInfo["NAME"] = $arContact["NAME"];
    							if (isset($arContact["LAST_NAME"]))
    								$clientInfo["LAST_NAME"] = $arContact["LAST_NAME"];
    							if (isset($arContact["SECOND_NAME"]))
    								$clientInfo["SECOND_NAME"] = $arContact["SECOND_NAME"];
    						}
    						unset($arContact);
    					}
    				}
    
    				$arProp["CRM"]["CLIENT"] = $clientInfo;
    				unset($clientInfo);
    			}
    			if(IntVal($arOrder["PAY_SYSTEM_ID"]) > 0)
    				$arProp["ORDER"]["PAY_SYSTEM_NAME"] = $paySystems[$arOrder["PAY_SYSTEM_ID"]];
    			if(strlen($arOrder["DELIVERY_ID"]) > 0)
    				$arProp["ORDER"]["DELIVERY_NAME"] = $delivery[$arOrder["DELIVERY_ID"]];
    
    			$dbOrderPropVals = CSaleOrderPropsValue::GetList(
    					array(),
    					array("ORDER_ID" => $arOrder["ID"]),
    					false,
    					false,
    					array("ID", "CODE", "VALUE", "ORDER_PROPS_ID", "PROP_TYPE")
    				);
    			while ($arOrderPropVals = $dbOrderPropVals->Fetch())
    			{
    				if ($arOrderPropVals["PROP_TYPE"] == "CHECKBOX")
    				{
    					if ($arOrderPropVals["VALUE"] == "Y")
    						$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = "true";
    					else
    						$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = "false";
    				}
    				elseif ($arOrderPropVals["PROP_TYPE"] == "TEXT" || $arOrderPropVals["PROP_TYPE"] == "TEXTAREA")
    				{
    					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = $arOrderPropVals["VALUE"];
    				}
    				elseif ($arOrderPropVals["PROP_TYPE"] == "SELECT" || $arOrderPropVals["PROP_TYPE"] == "RADIO")
    				{
    					$arVal = CSaleOrderPropsVariant::GetByValue($arOrderPropVals["ORDER_PROPS_ID"], $arOrderPropVals["VALUE"]);
    					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = $arVal["NAME"];
    				}
    				elseif ($arOrderPropVals["PROP_TYPE"] == "MULTISELECT")
    				{
    					$curVal = explode(",", $arOrderPropVals["VALUE"]);
    					foreach($curVal as $vm)
    					{
    						$arVal = CSaleOrderPropsVariant::GetByValue($arOrderPropVals["ORDER_PROPS_ID"], $vm);
    						$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] .=  ", ".$arVal["NAME"];
    					}
    					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = substr($arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]], 2);
    				}
    				elseif ($arOrderPropVals["PROP_TYPE"] == "LOCATION")
    				{
    					$arVal = CSaleLocation::GetByID($arOrderPropVals["VALUE"], LANGUAGE_ID);
    					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] =  ($arVal["COUNTRY_NAME"].((strlen($arVal["COUNTRY_NAME"])<=0 || strlen($arVal["REGION_NAME"])<=0) ? "" : " - ").$arVal["REGION_NAME"].((strlen($arVal["COUNTRY_NAME"])<=0 || strlen($arVal["CITY_NAME"])<=0) ? "" : " - ").$arVal["CITY_NAME"]);
    					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]."_CITY"] = $arVal["CITY_NAME"];
    					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]."_COUNTRY"] = $arVal["COUNTRY_NAME"];
    					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]."_REGION"] = $arVal["REGION_NAME"];
    				}
    				else
    				{
    					$arProp["PROPERTY"][$arOrderPropVals["ORDER_PROPS_ID"]] = $arOrderPropVals["VALUE"];
    				}
    			}
    
    			foreach($agentParams as $k => $v)
    			{
    				if(strpos($k, "REKV_") !== false)
    				{
    					if(!is_array($v))
    					{
    						$agent["REKV"][$k] = $v;
    					}
    					else
    					{
    						if(strlen($v["TYPE"])<=0)
    							$agent["REKV"][$k] = $v["VALUE"];
    						else
    							$agent["REKV"][$k] = $arProp[$v["TYPE"]][$v["VALUE"]];
    					}
    				}
    				else
    				{
    					if(!is_array($v))
    					{
    						$agent[$k] = $v;
    					}
    					else
    					{
    						if(strlen($v["TYPE"])<=0)
    							$agent[$k] = $v["VALUE"];
    						else
    							$agent[$k] = $arProp[$v["TYPE"]][$v["VALUE"]];
    					}
    				}
    			}
    			$orderDate = $DB->FormatDate($arOrder["DATE_INSERT_FORMAT"], $dateFormat, "YYYY-MM-DD HH:MI:SS");
    			list($date,$time) = explode(" ",$orderDate);
    			list($year,$month,$day) = explode("-",$date);
    			list($hours,$min,$sec) = explode(":",$time);
    			
    			$oderDate = date("Y-m-d",mktime($hours+3,$min,$sec,$month,$day,$year));
    			$orderTime = date("H:i:s",mktime($hours+3,$min,$sec,$month,$day,$year));
    			?>
    			<<?=GetMessage("SALE_EXPORT_DOCUMENT")?>>
    				<<?=GetMessage("SALE_EXPORT_ID")?>><?=$arOrder["ID"]?></<?=GetMessage("SALE_EXPORT_ID")?>>
    				<<?=GetMessage("SALE_EXPORT_NUMBER")?>><?=$accountNumberPrefix.$arOrder["ACCOUNT_NUMBER"]?></<?=GetMessage("SALE_EXPORT_NUMBER")?>>
    				<<?=GetMessage("SALE_EXPORT_DATE")?>><?=$oderDate;//$DB->FormatDate($arOrder["DATE_INSERT_FORMAT"], $dateFormat, "YYYY-MM-DD");?></<?=GetMessage("SALE_EXPORT_DATE")?>>
    				<<?=GetMessage("SALE_EXPORT_HOZ_OPERATION")?>><?=GetMessage("SALE_EXPORT_ITEM_ORDER")?></<?=GetMessage("SALE_EXPORT_HOZ_OPERATION")?>>
    				<<?=GetMessage("SALE_EXPORT_ROLE")?>><?=GetMessage("SALE_EXPORT_SELLER")?></<?=GetMessage("SALE_EXPORT_ROLE")?>>
    				<<?=GetMessage("SALE_EXPORT_CURRENCY")?>><?=htmlspecialcharsbx(((strlen($currency)>0)?substr($currency, 0, 3):substr($arOrder["CURRENCY"], 0, 3)))?></<?=GetMessage("SALE_EXPORT_CURRENCY")?>>
    				<<?=GetMessage("SALE_EXPORT_CURRENCY_RATE")?>>1</<?=GetMessage("SALE_EXPORT_CURRENCY_RATE")?>>
    				<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arOrder["PRICE"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
    				<?
    				if($bNewVersion)
    				{
    					?>
    					<<?=GetMessage("SALE_EXPORT_VERSION")?>><?=(IntVal($arOrder["VERSION"]) > 0 ? $arOrder["VERSION"] : 0)?></<?=GetMessage("SALE_EXPORT_VERSION")?>>
    					<?
    					if(strlen($arOrder["ID_1C"]) > 0)
    					{
    						?><<?=GetMessage("SALE_EXPORT_ID_1C")?>><?=htmlspecialcharsbx($arOrder["ID_1C"])?></<?=GetMessage("SALE_EXPORT_ID_1C")?>><?
    					}
    				}
    				if ($crmMode)
    				{
    					?><DateUpdate><?=$DB->FormatDate($arOrder["DATE_UPDATE"], $dateFormat, "YYYY-MM-DD HH:MI:SS");?></DateUpdate><?
    				}
    
    				$deliveryAdr = CSaleExport::ExportContragents(
    					$arOrder, $arProp, $agent, $arResultStat, $bNewVersion,
    					$bExportFromCrm ? array("EXPORT_FROM_CRM" => "Y") : array()
    				);
    				?>
    				<<?=GetMessage("SALE_EXPORT_TIME")?>><?=$orderTime;//$DB->FormatDate($arOrder["DATE_INSERT_FORMAT"], $dateFormat, "HH:MI:SS");?></<?=GetMessage("SALE_EXPORT_TIME")?>>
    				<<?=GetMessage("SALE_EXPORT_COMMENTS")?>><?=htmlspecialcharsbx($arOrder["COMMENTS"])?></<?=GetMessage("SALE_EXPORT_COMMENTS")?>>
    				<?
    				$dbOrderTax = CSaleOrderTax::GetList(
    					array(),
    					array("ORDER_ID" => $arOrder["ID"]),
    					false,
    					false,
    					array("ID", "TAX_NAME", "VALUE", "VALUE_MONEY", "CODE", "IS_IN_PRICE")
    				);
    				$i=-1;
    				$orderTax = 0;
    				while ($arOrderTax = $dbOrderTax->Fetch())
    				{
    					$arOrderTax["VALUE_MONEY"] = roundEx($arOrderTax["VALUE_MONEY"], 2);
    					$orderTax += $arOrderTax["VALUE_MONEY"];
    					$i++;
    					if($i == 0)
    						echo "<".GetMessage("SALE_EXPORT_TAXES").">";
    					?>
    					<<?=GetMessage("SALE_EXPORT_TAX")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($arOrderTax["TAX_NAME"])?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<<?=GetMessage("SALE_EXPORT_IN_PRICE")?>><?=(($arOrderTax["IS_IN_PRICE"]=="Y") ? "true" : "false")?></<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>
    						<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arOrderTax["VALUE_MONEY"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
    					</<?=GetMessage("SALE_EXPORT_TAX")?>>
    					<?
    				}
    				if($i != -1)
    					echo "</".GetMessage("SALE_EXPORT_TAXES").">";
    				?>
    				<?if(DoubleVal($arOrder["DISCOUNT_VALUE"]) > 0)
    				{
    					?>
    					<<?=GetMessage("SALE_EXPORT_DISCOUNTS")?>>
    						<<?=GetMessage("SALE_EXPORT_DISCOUNT")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_DISCOUNT")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arOrder["DISCOUNT_VALUE"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
    							<<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>false</<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>
    						</<?=GetMessage("SALE_EXPORT_DISCOUNT")?>>
    					</<?=GetMessage("SALE_EXPORT_DISCOUNTS")?>>
    					<?
    				}
    
    				$storeBasket = "";
    				if(IntVal($arOrder["STORE_ID"]) > 0 && !empty($arStore[$arOrder["STORE_ID"]]))
    				{
    					?>
    					<<?=GetMessage("SALE_EXPORT_STORIES")?>>
    						<<?=GetMessage("SALE_EXPORT_STORY")?>>
    							<<?=GetMessage("SALE_EXPORT_ID")?>><?=$arStore[$arOrder["STORE_ID"]]["XML_ID"]?></<?=GetMessage("SALE_EXPORT_ID")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($arStore[$arOrder["STORE_ID"]]["TITLE"])?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_ADDRESS")?>>
    								<<?=GetMessage("SALE_EXPORT_PRESENTATION")?>><?=htmlspecialcharsbx($arStore[$arOrder["STORE_ID"]]["ADDRESS"])?></<?=GetMessage("SALE_EXPORT_PRESENTATION")?>>
    								<<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
    									<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=GetMessage("SALE_EXPORT_STREET")?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
    									<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arStore[$arOrder["STORE_ID"]]["ADDRESS"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    								</<?=GetMessage("SALE_EXPORT_ADDRESS_FIELD")?>>
    							</<?=GetMessage("SALE_EXPORT_ADDRESS")?>>
    							<<?=GetMessage("SALE_EXPORT_CONTACTS")?>>
    								<<?=GetMessage("SALE_EXPORT_CONTACT")?>>
    									<<?=GetMessage("SALE_EXPORT_TYPE")?>><?=($bNewVersion ? GetMessage("SALE_EXPORT_WORK_PHONE_NEW") : GetMessage("SALE_EXPORT_WORK_PHONE"))?></<?=GetMessage("SALE_EXPORT_TYPE")?>>
    									<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arStore[$arOrder["STORE_ID"]]["PHONE"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    								</<?=GetMessage("SALE_EXPORT_CONTACT")?>>
    							</<?=GetMessage("SALE_EXPORT_CONTACTS")?>>
    						</<?=GetMessage("SALE_EXPORT_STORY")?>>
    					</<?=GetMessage("SALE_EXPORT_STORIES")?>>
    					<?
    					/*
    					$storeBasket = "				
    						<".GetMessage("SALE_EXPORT_STORIES").">
    							<".GetMessage("SALE_EXPORT_STORY").">
    								<".GetMessage("SALE_EXPORT_ID").">".$arStore[$arOrder["STORE_ID"]]["XML_ID"]."</".GetMessage("SALE_EXPORT_ID").">
    								<".GetMessage("SALE_EXPORT_ITEM_NAME").">".htmlspecialcharsbx($arStore[$arOrder["STORE_ID"]]["TITLE"])."</".GetMessage("SALE_EXPORT_ITEM_NAME").">
    							</".GetMessage("SALE_EXPORT_STORY").">
    						</".GetMessage("SALE_EXPORT_STORIES").">
    						";
    					*/
    				}
    				?>
    				
    				<<?=GetMessage("SALE_EXPORT_ITEMS")?>>
    				<?
    				$dbBasket = CSaleBasket::GetList(
    						array("NAME" => "ASC"),
    						array("ORDER_ID" => $arOrder["ID"]),
    						false,
    						false,
    						array("ID",'DISCOUNT_COUPON', "NOTES", "PRODUCT_XML_ID", "CATALOG_XML_ID", "NAME", "PRICE", "QUANTITY", "DISCOUNT_PRICE", "VAT_RATE", "MEASURE_CODE")
    					);
    				$basketSum = 0;
    				$priceType = "";
    				$bVat = false;
    				$vatRate = 0;
    				$vatSum = 0;
    				$orderDiscountCupons = array();
    				while ($arBasket = $dbBasket->Fetch())
    				{
    				    if ($arBasket['DISCOUNT_COUPON']) {
                            if (strpos(",",$arBasket['DISCOUNT_COUPON'])) {
                                $cupons = explode(",",$arBasket['DISCOUNT_COUPON']);
                                if ($cupons) {
                                    foreach ($cupons as $c => $v) {
                                        $orderDiscountCupons[] = trim($v);
                                    }
                                }
                            } else {
                            $orderDiscountCupons[] = trim($arBasket['DISCOUNT_COUPON']);
                            }
                        }
    					if(strlen($priceType) <= 0)
    						$priceType = $arBasket["NOTES"];
    					?>
    					<<?=GetMessage("SALE_EXPORT_ITEM")?>>
    						<<?=GetMessage("SALE_EXPORT_ID")?>><?=htmlspecialcharsbx($arBasket["PRODUCT_XML_ID"])?></<?=GetMessage("SALE_EXPORT_ID")?>>
    						<<?=GetMessage("SALE_EXPORT_CATALOG_ID")?>><?=htmlspecialcharsbx($arBasket["CATALOG_XML_ID"])?></<?=GetMessage("SALE_EXPORT_CATALOG_ID")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($arBasket["NAME"])?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<?
    						if($bNewVersion)
    						{
    							if(IntVal($arBasket["MEASURE_CODE"]) <= 0)
    								$arBasket["MEASURE_CODE"] = 796;
    							?>
    							<<?=GetMessage("SALE_EXPORT_UNIT")?>>
    								<<?=GetMessage("SALE_EXPORT_CODE")?>><?=$arBasket["MEASURE_CODE"]?></<?=GetMessage("SALE_EXPORT_CODE")?>>
    								<<?=GetMessage("SALE_EXPORT_FULL_NAME_UNIT")?>><?=htmlspecialcharsbx($arMeasures[$arBasket["MEASURE_CODE"]])?></<?=GetMessage("SALE_EXPORT_FULL_NAME_UNIT")?>>
    							</<?=GetMessage("SALE_EXPORT_UNIT")?>>
    							<<?=GetMessage("SALE_EXPORT_KOEF")?>>1</<?=GetMessage("SALE_EXPORT_KOEF")?>>
    							<?
    						}
    						else
    						{
    							?>
    							<<?=GetMessage("SALE_EXPORT_BASE_UNIT")?> <?=GetMessage("SALE_EXPORT_CODE")?>="796" <?=GetMessage("SALE_EXPORT_FULL_NAME_UNIT")?>="<?=GetMessage("SALE_EXPORT_SHTUKA")?>" <?=GetMessage("SALE_EXPORT_INTERNATIONAL_ABR")?>="<?=GetMessage("SALE_EXPORT_RCE")?>"><?=GetMessage("SALE_EXPORT_SHT")?></<?=GetMessage("SALE_EXPORT_BASE_UNIT")?>>
    							<?
    						}
    						if(DoubleVal($arBasket["DISCOUNT_PRICE"]) > 0)
    						{
    							?>
    							<<?=GetMessage("SALE_EXPORT_DISCOUNTS")?>>
    								<<?=GetMessage("SALE_EXPORT_DISCOUNT")?>>
    									<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ITEM_DISCOUNT")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    									<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arBasket["DISCOUNT_PRICE"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
    									<<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>false</<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>
    								</<?=GetMessage("SALE_EXPORT_DISCOUNT")?>>
    							</<?=GetMessage("SALE_EXPORT_DISCOUNTS")?>>
    							<?
    						}
    						?>
    						<<?=GetMessage("SALE_EXPORT_PRICE_PER_ITEM")?>><?=$arBasket["PRICE"]+$arBasket["DISCOUNT_PRICE"]?></<?=GetMessage("SALE_EXPORT_PRICE_PER_ITEM")?>>
    						<<?=GetMessage("SALE_EXPORT_QUANTITY")?>><?=$arBasket["QUANTITY"]?></<?=GetMessage("SALE_EXPORT_QUANTITY")?>>
    						<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=($arBasket["PRICE"]+$arBasket["DISCOUNT_PRICE"])*$arBasket["QUANTITY"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
    						<<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
    							<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    								<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_TYPE_NOMENKLATURA")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    								<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=GetMessage("SALE_EXPORT_ITEM")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    							</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    								<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_TYPE_OF_NOMENKLATURA")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    								<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=GetMessage("SALE_EXPORT_ITEM")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    							</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<?
    							$dbProp = CSaleBasket::GetPropsList(Array("SORT" => "ASC", "ID" => "ASC"), Array("BASKET_ID" => $arBasket["ID"], "!CODE" => array("CATALOG.XML_ID", "PRODUCT.XML_ID")), false, false, array("NAME", "VALUE", "CODE"));
    							while($arProp = $dbProp->Fetch())
    							{
    								?>
    								<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    									<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($arProp["NAME"])?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    									<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arProp["VALUE"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    								</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    								<?
    							}
    							?>
    						</<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
    						<?if(DoubleVal($arBasket["VAT_RATE"]) > 0)
    						{
    							$bVat = true;
    							$vatRate = DoubleVal($arBasket["VAT_RATE"]);
    							$basketVatSum = (($arBasket["PRICE"] / ($arBasket["VAT_RATE"]+1)) * $arBasket["VAT_RATE"]);
    							$vatSum += roundEx($basketVatSum * $arBasket["QUANTITY"], 2);
    							?>
    							<<?=GetMessage("SALE_EXPORT_TAX_RATES")?>>
    								<<?=GetMessage("SALE_EXPORT_TAX_RATE")?>>
    									<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_VAT")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    									<<?=GetMessage("SALE_EXPORT_RATE")?>><?=$arBasket["VAT_RATE"] * 100?></<?=GetMessage("SALE_EXPORT_RATE")?>>
    								</<?=GetMessage("SALE_EXPORT_TAX_RATE")?>>
    							</<?=GetMessage("SALE_EXPORT_TAX_RATES")?>>
    							<<?=GetMessage("SALE_EXPORT_TAXES")?>>
    								<<?=GetMessage("SALE_EXPORT_TAX")?>>
    									<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_VAT")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    									<<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>true</<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>
    									<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=roundEx($basketVatSum, 2)?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
    								</<?=GetMessage("SALE_EXPORT_TAX")?>>
    							</<?=GetMessage("SALE_EXPORT_TAXES")?>>
    							<?
    						}
    						?>
    						<?=$storeBasket?>
    					</<?=GetMessage("SALE_EXPORT_ITEM")?>>
    					<?
    					$basketSum += $arBasket["PRICE"]*$arBasket["QUANTITY"];
    				}
                    
    				if(IntVal($arOrder["PRICE_DELIVERY"]) > 0)
    				{
    					?>
    					<<?=GetMessage("SALE_EXPORT_ITEM")?>>
    						<<?=GetMessage("SALE_EXPORT_ID")?>>ORDER_DELIVERY</<?=GetMessage("SALE_EXPORT_ID")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_DELIVERY")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<?
    						if($bNewVersion)
    						{
    							?>
    							<<?=GetMessage("SALE_EXPORT_UNIT")?>>
    							<<?=GetMessage("SALE_EXPORT_CODE")?>>796</<?=GetMessage("SALE_EXPORT_CODE")?>>
    							<<?=GetMessage("SALE_EXPORT_FULL_NAME_UNIT")?>><?=htmlspecialcharsbx($arMeasures[796])?></<?=GetMessage("SALE_EXPORT_FULL_NAME_UNIT")?>>
    							</<?=GetMessage("SALE_EXPORT_UNIT")?>>
    							<<?=GetMessage("SALE_EXPORT_KOEF")?>>1</<?=GetMessage("SALE_EXPORT_KOEF")?>>
    						<?
    						}
    						else
    						{
    							?>
    							<<?=GetMessage("SALE_EXPORT_BASE_UNIT")?> <?=GetMessage("SALE_EXPORT_CODE")?>="796" <?=GetMessage("SALE_EXPORT_FULL_NAME_UNIT")?>="<?=GetMessage("SALE_EXPORT_SHTUKA")?>" <?=GetMessage("SALE_EXPORT_INTERNATIONAL_ABR")?>="<?=GetMessage("SALE_EXPORT_RCE")?>"><?=GetMessage("SALE_EXPORT_SHT")?></<?=GetMessage("SALE_EXPORT_BASE_UNIT")?>>
    						<?
    						}
    						?>
    						<<?=GetMessage("SALE_EXPORT_PRICE_PER_ITEM")?>><?=$arOrder["PRICE_DELIVERY"]?></<?=GetMessage("SALE_EXPORT_PRICE_PER_ITEM")?>>
    						<<?=GetMessage("SALE_EXPORT_QUANTITY")?>>1</<?=GetMessage("SALE_EXPORT_QUANTITY")?>>
    						<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$arOrder["PRICE_DELIVERY"]?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
    						<<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
    							<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    								<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_TYPE_NOMENKLATURA")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    								<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=GetMessage("SALE_EXPORT_SERVICE")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    							</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    								<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_TYPE_OF_NOMENKLATURA")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    								<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=GetMessage("SALE_EXPORT_SERVICE")?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    							</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
    						<?if($bVat)
    						{
    							$deliveryTax = roundEx((($arOrder["PRICE_DELIVERY"] / ($vatRate+1)) * $vatRate), 2);
    							if($orderTax > $vatSum && $orderTax == roundEx($vatSum + $deliveryTax, 2))
    							{
    								?>
    								<<?=GetMessage("SALE_EXPORT_TAX_RATES")?>>
    									<<?=GetMessage("SALE_EXPORT_TAX_RATE")?>>
    										<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_VAT")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    										<<?=GetMessage("SALE_EXPORT_RATE")?>><?=$vatRate * 100?></<?=GetMessage("SALE_EXPORT_RATE")?>>
    									</<?=GetMessage("SALE_EXPORT_TAX_RATE")?>>
    								</<?=GetMessage("SALE_EXPORT_TAX_RATES")?>>
    								<<?=GetMessage("SALE_EXPORT_TAXES")?>>
    									<<?=GetMessage("SALE_EXPORT_TAX")?>>
    										<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_VAT")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    										<<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>true</<?=GetMessage("SALE_EXPORT_IN_PRICE")?>>
    										<<?=GetMessage("SALE_EXPORT_AMOUNT")?>><?=$deliveryTax?></<?=GetMessage("SALE_EXPORT_AMOUNT")?>>
    									</<?=GetMessage("SALE_EXPORT_TAX")?>>
    								</<?=GetMessage("SALE_EXPORT_TAXES")?>>
    								<?
    							}
    						}
    						?>
    					</<?=GetMessage("SALE_EXPORT_ITEM")?>>
    					<?
    				}
    				?>
    				</<?=GetMessage("SALE_EXPORT_ITEMS")?>>
    				<?php if ($orderDiscountCupons) CSaleExport2XML::getOrderDiscountCupons($orderDiscountCupons);?>
    				<<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
    					<?if(strlen($arOrder["DATE_PAYED"])>0)
    					{
    						?>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DATE_PAID")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_PAYED"]?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<?
    					}
    					if(strlen($arOrder["PAY_VOUCHER_NUM"])>0)
    					{
    						?>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_PAY_NUMBER")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["PAY_VOUCHER_NUM"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<?
    					}
    					if(IntVal($arOrder["PAY_SYSTEM_ID"])>0)
    					{
    						?>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_PAY_SYSTEM")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($paySystems[$arOrder["PAY_SYSTEM_ID"]])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_PAY_SYSTEM_ID")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["PAY_SYSTEM_ID"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<?
    					}
    					if(strlen($arOrder["DATE_ALLOW_DELIVERY"])>0)
    					{
    						?>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DATE_ALLOW_DELIVERY")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_ALLOW_DELIVERY"]?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<?
    					}
    					if(strlen($arOrder["DELIVERY_ID"])>0)
    					{
    						?>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DELIVERY_SERVICE")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($delivery[$arOrder["DELIVERY_ID"]])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<?
    					}
    					?>
    					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>ДатаИзмененияЗаказа</<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=($arOrder["DATE_UPDATE"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_PAID")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=($arOrder["PAYED"]=="Y")?"true":"false";?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ALLOW_DELIVERY")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=($arOrder["ALLOW_DELIVERY"]=="Y")?"true":"false";?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_CANCELED")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=($arOrder["CANCELED"]=="Y")?"true":"false";?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_FINAL_STATUS")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=($arOrder["STATUS_ID"]=="F")?"true":"false";?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_STATUS")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?$arStatus = CSaleStatus::GetLangByID($arOrder["STATUS_ID"]); echo htmlspecialcharsbx("[".$arOrder["STATUS_ID"]."] ".$arStatus["NAME"]);?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    					<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_ORDER_STATUS_ID")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    					<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["STATUS_ID"]);?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    					<?if(strlen($arOrder["DATE_CANCELED"])>0)
    					{
    						?>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DATE_CANCEL")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_CANCELED"]?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_CANCEL_REASON")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["REASON_CANCELED"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<?
    					}
    					if(strlen($arOrder["DATE_STATUS"])>0)
    					{
    						?>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DATE_STATUS")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=$arOrder["DATE_STATUS"]?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<?
    					}
    					if(strlen($arOrder["USER_DESCRIPTION"])>0)
    					{
    						?>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    							<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_USER_DESCRIPTION")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    							<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($arOrder["USER_DESCRIPTION"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<?
    					}
    					$dbSite = CSite::GetByID($arOrder["LID"]);
    					$arSite = $dbSite->Fetch();
    					?>
    					<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_SITE_NAME")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<<?=GetMessage("SALE_EXPORT_VALUE")?>>[<?=$arOrder["LID"]?>] <?=htmlspecialcharsbx($arSite["NAME"])?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    					</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    					<?
    					if(!empty($agent["REKV"]))
    					{
    						foreach($agent["REKV"] as $k => $v)
    						{
    							if(strlen($agentParams[$k]["NAME"]) > 0 && strlen($v) > 0)
    							{
    								?>
    								<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    									<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=htmlspecialcharsbx($agentParams[$k]["NAME"])?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    									<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($v)?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    								</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    								<?
    							}
    						}
    					}
    
    					if(strlen($deliveryAdr) > 0)
    					{
    						?>
    						<<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    						<<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>><?=GetMessage("SALE_EXPORT_DELIVERY_ADDRESS")?></<?=GetMessage("SALE_EXPORT_ITEM_NAME")?>>
    						<<?=GetMessage("SALE_EXPORT_VALUE")?>><?=htmlspecialcharsbx($deliveryAdr)?></<?=GetMessage("SALE_EXPORT_VALUE")?>>
    						</<?=GetMessage("SALE_EXPORT_PROPERTY_VALUE")?>>
    
    						<?
    					}
    					?>
    				</<?=GetMessage("SALE_EXPORT_PROPERTIES_VALUES")?>>
    			</<?=GetMessage("SALE_EXPORT_DOCUMENT")?>>
    			<?
    			if ($crmMode)
    			{
    				$c = ob_get_clean();
    				$c = CharsetConverter::ConvertCharset($c, $arCharSets[$arOrder["LID"]], "utf-8");
    				echo $c;
    				$_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix][] = $arOrder["ID"];
    			}
    			else
    			{
    				$_SESSION["BX_CML2_EXPORT"][$lastOrderPrefix] = $arOrder["ID"];
    			}
    
    			if(IntVal($time_limit) > 0 && time() > $end_time)
    			{
    				break;
    			}
    		}
    		?>
    		</<?=GetMessage("SALE_EXPORT_COM_INFORMATION")?>>
    		<?
    		return $arResultStat;
    	}
    	
}
