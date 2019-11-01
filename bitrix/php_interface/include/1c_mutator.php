<?php
// Создадим функцию, которая будет добавлять или обновлять свойство 
// "Оригинальный номер", которого нет в CommerceML файле
   CModule::IncludeModule("iblock");
function catalog_property_mutator_1c()
{
   global /*$IBLOCK_ID,*/ $tmpid, $strError, $STT_PROP_ERROR, $STT_PROP_ADD, $STT_PROP_UPDATE, $arProperties;
   $ibp = new CIBlockProperty();
   $PROP_XML_ID = "CML2_TASTE2";
   $PROP_NAME = "Вкус2";
   $PROP_MULTIPLE = "N";
   $PROP_DEF = "";
   $IBLOCK_ID = 43;
   $res = CIBlock::GetProperties($IBLOCK_ID, Array(), Array("XML_ID"=>$PROP_XML_ID, "IBLOCK_ID"=>$IBLOCK_ID));
   $bNewRecord_tmp = False;
   if ($res_arr = $res->Fetch())
   {
      $PROP_ID = $res_arr["ID"];
      $res = $ibp->Update($PROP_ID,
         Array(
            "NAME" => $PROP_NAME,
            "MULTIPLE" => $PROP_MULTIPLE,
            "DEFAULT_VALUE" => $PROP_DEF,
         	"CODE"=>$PROP_XML_ID,
             "PROPERTY_TYPE" => "L",
            "TMP_ID" => $tmpid
            )
         );
   }
   else
   {
      $bNewRecord_tmp = True;
      $arFields = Array(
         "NAME" => $PROP_NAME,
         "ACTIVE" => "Y",
         "SORT" => "500",
         "DEFAULT_VALUE" => $PROP_DEF,
         "XML_ID" => $PROP_XML_ID,
      		"CODE"=>$PROP_XML_ID,
         "PROPERTY_TYPE" => "L",
         "TMP_ID" => $tmpid,
         "MULTIPLE" => $PROP_MULTIPLE,
         "IBLOCK_ID" => $IBLOCK_ID
      );

      $PROP_ID = $ibp->Add($arFields);
      $res = (IntVal($PROP_ID)>0);
   }

   if (!$res)
   {
      $strError .= "Ошибка загрузки свойства [".$PROP_ID."] \"".$PROP_NAME."\" (".$PROP_XML_ID."): ".$ibp->LAST_ERROR.".<br>";
      $STT_PROP_ERROR++;
   }
   else
   {
      if ($bNewRecord_tmp) $STT_PROP_ADD++;
      else $STT_PROP_UPDATE++;

      $arProperties[$PROP_XML_ID] = array($PROP_ID);
   }
   $strError .= "Ошибка загрузки свойства [".$PROP_ID."] \"".$PROP_NAME."\" (".$PROP_XML_ID."): ".$ibp->LAST_ERROR.".<br>";
   $STT_PROP_ERROR++;
   
}

// Создадим функцию, которая будет обновлять нестандартное свойство
// "Оригинальный номер", значения которого идут как атрибуты элемента
// Товар в CommerceML файле, и делать товар активным
// Кроме того запишем "Оригинальный номер" без пробелов в поле описания
// товара для предварительного просмотра
/*function catalog_product_mutator_1c(&$arLoadProduct, &$xProductNode, $bInsert)
{
    global $arProperties;

    $sOriginomer = $xProductNode->GetAttribute("Характеристики");
    $arLoadProduct["PROPERTY_VALUES"][$arProperties["CML2_TASTE"]] = explode(",",$sOriginomer);
    return $arLoadProduct;
}
*/
/*
function catalog_offer_mutator_1c(&$arLoadOffer, &$xOfferNode)
{
    $arLoadOffer["QUANTITY_TRACE"] = "Y";
    return $arLoadOffer;
}
*/
?>