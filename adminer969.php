<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->RestartBuffer();
?><pre><?php 

$dbProductPrice = CPrice::GetListEx(
        array(),
        array("PRODUCT_ID" => 77439),
        false,
        false,
        array("ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO")
);


while ($row = $dbProductPrice->Fetch()) {
    print_r($row);
}

$res = GetCatalogGroups(
'NAME','ASC'
);
while ($row = $res->Fetch()) {
print_r($row);
}
