<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Test");
echo ini_get('max_execution_time');
//customCatalogImportStep();
/*$el = new CIBlockElement ();

$el->Update (59551, array (
        //'ACTIVE' => 'Y',
        //'IBLOCK_ID' => $arItem[ 'IBLOCK_ID' ],
        'PROPERTY_VALUES'=>array(419=>'400')
));
print_r($el);*/
?>
---
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>