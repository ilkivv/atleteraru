<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("������� �������");
echo ini_get('max_execution_time');
customCatalogImportStep();
?>
---
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>