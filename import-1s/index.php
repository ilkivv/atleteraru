<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule("iblock");
$arFilter = array (
        'IBLOCK_ID' => 45
);
$sectionRes = CIBlockSection::GetList (Array ("SORT" => "ASC"), Array ('IBLOCK_ID' => $arItem[ 'IBLOCK_ID' ]));
    $bs = new CIBlockSection ();
while ( $sect = $sectionRes->Fetch () ) {
    $bs->Update($sect['ID'],array('ACTIVE'=>"N"));
}

$res = CIBlockElement::GetList (array (
        'ID' => 'ASC'
), array_merge ($arFilter, array (
        //'ID' =>47275 
)));
$errorMessage = null;
if (CModule::IncludeModule ("catalog")) {
    $arInfo = CCatalogSKU::GetInfoByProductIBlock(45);
}
while ( $arItem = $res->Fetch () ) {
    if (updateProduct ($arItem,$arInfo) === false) {
        $error = true;
    }
}

?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>