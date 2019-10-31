<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (CModule::IncludeModule('sale'))
{
    $arOrder = CSaleOrder::getByID($_REQUEST['order']);
    if ($arOrder){
         CSaleOrder::Update($_REQUEST['order'], array(
            "PAY_SYSTEM_ID" => $_REQUEST['payment_id']
        ));
    }
}
?>

