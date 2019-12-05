<?

 if( file_exists( $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/libs/autoload.php" ) ) {
	require_once( $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/libs/autoload.php" ) ;
}


if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/seo.php"))
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/seo.php");
	
	
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/events.php"))
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/events.php");

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/_const.php"))
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/_const.php");


AddEventHandler('main', 'OnEpilog', '_Check404Error');

function _Check404Error(){

    if (defined('ERROR_404') && ERROR_404 == 'Y') {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        
        include $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/header.php';
        include $_SERVER['DOCUMENT_ROOT'] . '/404.php';
        include $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/footer.php';
    }
}

/*
You can place here your functions and event handlers

AddEventHandler("module", "EventName", "FunctionName");
function FunctionName(params
{
	//code
}
*/
AddEventHandler ('catalog', 'OnSuccessCatalogImport1C', 'customCatalogImportStep');
AddEventHandler ('catalog', 'OnBeforeProductUpdate', 'OnProductUpdate');
AddEventHandler ('catalog', 'OnProductAdd', 'OnProductAdd');
AddEventHandler ("main", "OnBeforeUserRegister", "OnBeforeUserRegisterHandler");
AddEventHandler ("main", "OnBeforeUserAdd", "OnBeforeUserAddHandler");
AddEventHandler ("sale","OnSaleComponentOrderComplete","OnSaleComponentOrderComplete");

AddEventHandler ("catalog","OnBeforeCatalogStoreUpdate","OnBeforeCatalogStoreUpdate");



/*AddEventHandler("catalog", "OnGetOptimalPrice", 'OnGetOptimalPriceHandler');
function OnGetOptimalPriceHandler($productID, $quantity = 1, $arUserGroups = array(),
         $renewal = "Y", $arPrices = array(), $siteID = false, $arDiscountCoupons = false){
   $res = CPrice::GetBasePrice($productID);
   //print_r($res);
   $arPrice =  CPrice::GetByID($res['ID']);
   //print_r($arPrices);
   return $res;
}*/
mysql_query('SET time_zone = "+04:00"');
include 'multicity.php';
function OnProductUpdate($ID, &$arFields)
{
    $rsStore = CCatalogStore::GetList (array (), array (
        'ACTIVE' => 'Y',
        'ISSUING_CENTER' => 'Y',
        'SHIPPING_CENTER' => 'Y'
    ));
    while ( $arStore = $rsStore->Fetch () ) {
        $IDS[ ] = $arStore[ 'ID' ];
    }
    $rsStore = CCatalogStoreProduct::GetList (array (), array (
        'PRODUCT_ID' => $ID,
        'STORE_ID' => $IDS
    ));
    $amount = 0;
    while ( $arStore = $rsStore->Fetch () ) {
        $amount = $amount + $arStore[ 'AMOUNT' ];
    }
    $arFields[ "QUANTITY" ] = $amount;

    $arPrice = CCatalogProduct::GetMaxPrice ($ID, 1);

    if ($arPrice[ 'PRICE' ][ 'PRICE' ] > 0) {
        $mxResult = CCatalogSku::GetProductInfo ($ID);
        $db_props = CIBlockElement::GetProperty (45,$mxResult[ 'ID' ],array(),array('CODE'=>'MAX_PRICE'));
        $ar_props = $db_props->Fetch();
        if ($arPrice[ 'PRICE' ][ 'PRICE' ] > $ar_props['VALUE']) {
            CIBlockElement::SetPropertyValues ($mxResult[ 'ID' ], 45, $arPrice[ 'PRICE' ][ 'PRICE' ], 'MAX_PRICE');
        }
    }
    return true;
}

function OnProductAdd($ID, $arFields)
{
    OnProductUpdate ($ID, $arFields);
    CCatalogProduct::Update ($ID, array (
        'QUANTITY' => $arFields[ "QUANTITY" ],
        'QUANTITY_RESERVED' => 0
    ));
}

function OnBeforeUserAddHandler(&$arFields)
{
    if ($_POST[ 'cardNumber' ]) {
        $arFields[ 'UF_COUPON' ] = $_POST[ 'cardNumber' ];
    }
    return true;
}

function OnBeforeUserRegisterHandler($args)
{
    if ($args[ 'PERSONAL_CITY' ] && ! in_array ($args[ 'PERSONAL_CITY' ], array (
            2310,
            2312
        ))) {
        if (! $args[ 'UF_HOUSE' ]) {
            $error = 'Не указан номер дома';
            $GLOBALS[ 'APPLICATION' ]->ThrowException ($error);
            return false;
        }
        if (! $args[ 'UF_FLAT' ]) {
            $error = 'Не указана квартира';
            $GLOBALS[ 'APPLICATION' ]->ThrowException ($error);
            return false;
        }
        if (! $args[ 'PERSONAL_STREET' ]) {
            $error = 'Не указана улица';
            $GLOBALS[ 'APPLICATION' ]->ThrowException ($error);
            return false;
        }
        if (! $args[ 'SECOND_NAME' ]) {
            $error = 'Не указано отчество';
            $GLOBALS[ 'APPLICATION' ]->ThrowException ($error);
            return false;
        }
        if (! $args[ 'PERSONAL_ZIP' ]) {
            $error = 'Не указан индекс';
            $GLOBALS[ 'APPLICATION' ]->ThrowException ($error);
            return false;
        }
    }
    if ($_POST[ 'cardNumber' ]) {

        CModule::IncludeModule ("catalog");
        if (strlen ($_POST[ 'cardNumber' ]) != 5) {
            $error = 'Неверный номер дисконтной карты ';
            $GLOBALS[ 'APPLICATION' ]->ThrowException ($error);
            return false;
        } elseif (! CCatalogDiscountCoupon::IsExistCoupon ($_POST[ 'cardNumber' ])) {
            $GLOBALS[ 'APPLICATION' ]->ThrowException ('Выбранной дисконтной карты не существует');
            return false;
        } else {
            $rsUsers = CUser::GetList (($by = "id"), ($order = "desc"), Array (
                "UF_COUPON" => $_POST[ 'cardNumber' ]
            ), array ());
            if ($rsUsers->GetNext ()) {
                $GLOBALS[ 'APPLICATION' ]->ThrowException ('Пользователь с такой дисконтной картой уже существует ');
                return false;
            } elseif (CCatalogDiscountCoupon::IsExistCoupon ($_POST[ 'cardNumber' ])) {
                $arFilter = array (
                    'COUPON' => $_POST[ 'cardNumber' ]
                );
                $dbCoupon = CCatalogDiscountCoupon::GetList (array (), $arFilter);
                $arCoupon = $dbCoupon->Fetch ();
                $card_fio = explode (' ', $arCoupon[ 'DESCRIPTION' ]);
                if (mb_strtoupper(trim ($card_fio[ 0 ]) . ' ' . trim ($card_fio[ 1 ])) != mb_strtoupper(trim ($args[ "LAST_NAME" ]) . ' ' . trim ($args[ "NAME" ]))) {
                    $error = 'ФИО карты и регистрируемого пользователя должны совпадать';
                    $GLOBALS[ 'APPLICATION' ]->ThrowException ($error);
                    return false;
                } else {
                    return true;
                }
            } else {
                $error = 'Неверный номер дисконтной карты ';
                $GLOBALS[ 'APPLICATION' ]->ThrowException ($error);
                return false;
            }
        }
    }
    return true;
}

function getDiscountByPercent($percent)
{
    $Result = CCatalogDiscount::GetList (array (), array (
        'VALUE' => $percent,
        'VALUE_TYPE' => 'P',
        'CURRENCY' => 'RUB'
    ), false, false, array (
        'ID'
    ));
    $arResult = $Result->Fetch ();
    if (! $arResult[ 'ID' ]) {
        $arFields = array (
            'SITE_ID' => 's1',
            'ACTIVE' => "N",
            'NAME' => 'СКИДКА ' . $percent . '%',
            'SORT' => 100,
            'VALUE_TYPE' => 'P',
            'VALUE' => $percent,
            'CURRENCY' => 'RUB'
        );
        $arResult[ 'ID' ] = CCatalogDiscount::Add ($arFields);
    }
    return $arResult[ 'ID' ];
}

function addCard($card)
{
    if (CModule::IncludeModule ("catalog")) {
        $discountId = getDiscountByPercent ($card[ 'ТекущаяСкидка' ]);
        $arFilter = array (
            'XML_ID' => $card[ 'УникальныйИдентификатор' ]
        );
        $dbCoupon = CCatalogDiscountCoupon::GetList (array (), $arFilter);
        $arCoupon = $dbCoupon->Fetch ();
        if ($arCoupon[ 'ID' ] > 0) {
            $arCoupon[ 'DISCOUNT_ID' ] = $discountId;
            $arCoupon[ 'ACTIVE' ] = 'Y';
            $arCoupon[ 'COUPON' ] = "" . $card[ 'НомерКарты' ];
            $arCoupon[ 'DESCRIPTION' ] = "" . $card[ 'ФИОВладельца' ];
            $id = $arCoupon[ 'ID' ];
            unset ($arCoupon[ 'ID' ]);
            CCatalogDiscountCoupon::Update ($id, $arCoupon);
            $newCouponId = $arCoupon[ 'ID' ];
            echo "<br /> updated<br />";
        } else {
            $cardParams = array (
                'DISCOUNT_ID' => $discountId,
                'ACTIVE' => 'Y',
                'COUPON' => "" . $card[ 'НомерКарты' ],
                'ONE_TIME' => 'N',
                'DATE_APPLY' => '',
                'DESCRIPTION' => $card[ 'ФИОВладельца' ],
                'XML_ID' => $card[ 'УникальныйИдентификатор' ]
            );
            $newCouponId = CCatalogDiscountCoupon::Add ($cardParams);
        }
        CCatalogDiscount::Update ($discountId, array (
            "ACTIVE" => "Y"
        ));
    } else {
        echo "not loaded card";
    }
}

function deactivateDiscounts()
{
    CModule::IncludeModule ("catalog");
    $dbCoupon = CCatalogDiscountCoupon::GetList (array (), array());
    while ($arCoupon = $dbCoupon->Fetch ()) {
        CCatalogDiscountCoupon::Update ($arCoupon['ID'], array('ACTIVE'=>'N','COUPON'=>$arCoupon['COUPON'].'OLD'.rand(1,1000)));
    }
}

function importCards()
{
    global $DIR_NAME;

    $obXMLFile = new CIBlockXMLFile ();
    $res = $obXMLFile->GetList (array (), array (
        'NAME' => 'ДисконтныеКарты'
    ), array (
        'ID'
    ));
    $cards = array ();
    if ($row = $res->Fetch ()) {

        $cards = $obXMLFile->GetAllChildrenArray ($row[ 'ID' ]);
    }

    if (count ($cards) > 1) {
        deactivateDiscounts ();
        foreach ($cards as $card) {
            addCard ($card);
        }
    }
    $_SESSION[ "BX_CML2_IMPORT" ][ "NS" ][ 'custom' ][ 'cards_loaded' ] = true;
    print "progress\n";
    print "Список карт обработан";
    $contents = ob_get_contents ();
    ob_end_clean ();

    if (toUpper (LANG_CHARSET) != "WINDOWS-1251") {
        $contents = $GLOBALS[ 'APPLICATION' ]->ConvertCharset ($contents, LANG_CHARSET, "windows-1251");
    }

    header ("Content-Type: text/html; charset=windows-1251");
    print $contents;
    exit ();
}

function processProducts()
{
    CModule::IncludeModule ("iblock");
    $NS = &$_SESSION[ "BX_CML2_IMPORT" ][ "NS" ];
    $stepInterval = (int) COption::GetOptionString ("catalog", "1C_INTERVAL", "-");
    $startTime = time ();
    // Флаг импорта файла торговых предложений
    $isOffers = strpos ($_REQUEST[ 'filename' ], 'offers') !== false;

    if (! isset ($NS[ 'custom' ][ 'lastId' ])) {
        // Последний отработанный элемент для пошаговости.
        $NS[ 'custom' ][ 'lastId' ] = 0;
        $NS[ 'custom' ][ 'counter' ] = 0;
    }

    // Условия выборки элементов для обработки
    $arFilter = array (
        'IBLOCK_ID' => 45
    );

    $res = CIBlockElement::GetList (array (
        'ID' => 'ASC'
    ), array_merge ($arFilter, array (
        '>ID' => $NS[ 'custom' ][ 'lastId' ]
    )));
    $errorMessage = null;
    if (CModule::IncludeModule ("catalog")) {
        $arInfo = CCatalogSKU::GetInfoByProductIBlock (45);
    }
    while ( $arItem = $res->Fetch () ) {
        if (updateProduct ($arItem, $arInfo,true) === false) {
            $error = true;
        }

        if ($error === true) {
            print_r ($arItem);
            $errorMessage = 'Что-то случилось.';
            break;
        }

        $NS[ 'custom' ][ 'lastId' ] = $arItem[ 'ID' ];
        $NS[ 'custom' ][ 'counter' ] ++;

        // Прерывание по времени шага
        if ($stepInterval > 0 && (time () - $startTime) > $stepInterval) {
            break;
        }
    }

    if ($arItem != false) {
        if ($errorMessage === null) {
            print "progress\n";
            print "Обработано " . $NS[ 'custom' ][ 'counter' ] . ' элементов, осталось ' . $res->SelectedRowsCount ();
        } else {
            print "failure\n" . $errorMessage;
        }

        $contents = ob_get_contents ();
        ob_end_clean ();

        if (toUpper (LANG_CHARSET) != "WINDOWS-1251") {
            $contents = $GLOBALS[ 'APPLICATION' ]->ConvertCharset ($contents, LANG_CHARSET, "windows-1251");
        }

        header ("Content-Type: text/html; charset=windows-1251");
        print $contents;
        exit ();
    } else {
        $_SESSION[ "BX_CML2_IMPORT" ][ "NS" ][ 'custom' ][ 'products_processed' ] = true;
        $_SESSION[ "BX_CML2_IMPORT" ][ "NS" ][ 'custom' ][ 'lastId' ] = 0;
        $_SESSION[ "BX_CML2_IMPORT" ][ "NS" ][ 'custom' ][ 'counter' ] = 0;
    }
}

function processReservedOffers()
{
    CModule::IncludeModule ("sale");
    CModule::IncludeModule ("catalog");
    $arFilter = Array (
        "@STATUS_ID" => array (
            "N"
        ),
        'CANCELED' => 'N'
    );
    $rsSales = CSaleOrder::GetList (array (
        "DATE_INSERT" => "ASC"
    ), $arFilter);
    while ( $arSales = $rsSales->Fetch () ) {
        $orderIds[ ] = $arSales[ 'ID' ];
    }
    $dbBasketItems = CSaleBasket::GetList (array (
        "NAME" => "ASC",
        "ID" => "ASC"
    ), array (
        "LID" => 's1',
        "@ORDER_ID" => $orderIds
    ), false, false, array (
        "ID",
        "PRODUCT_ID",
        "QUANTITY"
    ));
    $productIds = array ();
    while ( $arItems = $dbBasketItems->Fetch () ) {
        $products[ ] = $arItems;
        $productIds[ $arItems[ 'PRODUCT_ID' ] ] = $productIds[ $arItems[ 'PRODUCT_ID' ] ] + $arItems[ 'QUANTITY' ];
    }
    if ($productIds) {
        foreach ($productIds as $k => $v) {
            $arFields = array (
                'QUANTITY_RESERVED' => $v
            ); // зарезервированное количество
            // echo $k . ' - ' . $v . '<br />';
            if (! CCatalogProduct::Update ($k, $arFields)) {
                print "failure\n Не обновилось значение у " . $k . ' равное ' . $v;
                $contents = ob_get_contents ();
                ob_end_clean ();
                if (toUpper (LANG_CHARSET) != "WINDOWS-1251") {
                    $contents = $GLOBALS[ 'APPLICATION' ]->ConvertCharset ($contents, LANG_CHARSET, "windows-1251");
                }

                header ("Content-Type: text/html; charset=windows-1251");
                print $contents;
                exit ();
            }
        }
    }
    $_SESSION[ "BX_CML2_IMPORT" ][ "NS" ][ 'custom' ][ 'reserve_processed' ] = true;
    $_SESSION[ "BX_CML2_IMPORT" ][ "NS" ][ 'custom' ][ 'lastId' ] = 0;
    $_SESSION[ "BX_CML2_IMPORT" ][ "NS" ][ 'custom' ][ 'counter' ] = 0;

    print "progress\n";
    print "Обработка резерва завершена. Найдено заказов: " . count ($orderIds) . '(' . join (",", $orderIds) . ') Обработано товаров: ' . count ($productIds);

    $contents = ob_get_contents ();
    ob_end_clean ();
    if (toUpper (LANG_CHARSET) != "WINDOWS-1251") {
        $contents = $GLOBALS[ 'APPLICATION' ]->ConvertCharset ($contents, LANG_CHARSET, "windows-1251");
    }

    header ("Content-Type: text/html; charset=windows-1251");
    print $contents;
    exit ();
}

function processOffers()
{
    $NS = &$_SESSION[ "BX_CML2_IMPORT" ][ "NS" ];
    $stepInterval = (int) COption::GetOptionString ("catalog", "1C_INTERVAL", "-");
    $startTime = time ();
    // Флаг импорта файла торговых предложений
    $isOffers = strpos ($_REQUEST[ 'filename' ], 'offers') !== false;

    if (! isset ($NS[ 'custom' ][ 'lastId' ])) {
        // Последний отработанный элемент для пошаговости.
        $NS[ 'custom' ][ 'lastId' ] = 0;
        $NS[ 'custom' ][ 'counter' ] = 0;
    }

    // Условия выборки элементов для обработки
    $arFilter = array (
        'IBLOCK_ID' => 46
    );

    $res = CIBlockElement::GetList (array (
        'ID' => 'ASC'
    ), array_merge ($arFilter, array (
        '>ID' => $NS[ 'custom' ][ 'lastId' ]
    )));
    $errorMessage = null;

    while ( $arItem = $res->Fetch () ) {

        // Что-нибудь делаем
        if (updateElement ($arItem) === false) {
            $error = true;
        }

        if ($error === true) {
            print_r ($arItem);
            $errorMessage = 'Что-то случилось.';
            break;
        }

        $NS[ 'custom' ][ 'lastId' ] = $arItem[ 'ID' ];
        $NS[ 'custom' ][ 'counter' ] ++;

        // Прерывание по времени шага
        if ($stepInterval > 0 && (time () - $startTime) > $stepInterval) {
            break;
        }
    }

    if ($arItem != false) {
        if ($errorMessage === null) {
            print "progress\n";
            print "Обработано " . $NS[ 'custom' ][ 'counter' ] . ' элементов, осталось ' . $res->SelectedRowsCount ();
        } else {
            print "failure\n" . $errorMessage;
        }

        $contents = ob_get_contents ();
        ob_end_clean ();

        if (toUpper (LANG_CHARSET) != "WINDOWS-1251") {
            $contents = $GLOBALS[ 'APPLICATION' ]->ConvertCharset ($contents, LANG_CHARSET, "windows-1251");
        }

        header ("Content-Type: text/html; charset=windows-1251");
        print $contents;
        exit ();
    } else {
        $_SESSION[ "BX_CML2_IMPORT" ][ "NS" ][ 'custom' ][ 'offers_processed' ] = true;
        //processReservedOffers ();
        $bs = new CIBlockSection ();
        $res = $bs->Update (1802, array (
            'ACTIVE' => 'N'
        ));
    }
}

function processIBlockOption()
{
    CModule::IncludeModule ("iblock");
    $ibp = new CIBlockProperty ();
    $PROP_XML_ID = "CML2_TASTE";
    $PROP_NAME = "Вкус";
    $PROP_MULTIPLE = "N";
    $PROP_DEF = "";
    $IBLOCK_ID = 46;
    $res = CIBlock::GetProperties ($IBLOCK_ID, Array (), Array (
        "XML_ID" => $PROP_XML_ID,
        "IBLOCK_ID" => $IBLOCK_ID
    ));
    $bNewRecord_tmp = False;
    if ($res_arr = $res->Fetch ()) {
        $PROP_ID = $res_arr[ "ID" ];
        $res = $ibp->Update ($PROP_ID, Array (
            "NAME" => $PROP_NAME,
            "MULTIPLE" => $PROP_MULTIPLE,
            "DEFAULT_VALUE" => $PROP_DEF,
            "CODE" => $PROP_XML_ID,
            "PROPERTY_TYPE" => "L",
            "TMP_ID" => $tmpid
        ));
    } else {
        $bNewRecord_tmp = True;
        $arFields = Array (
            "NAME" => $PROP_NAME,
            "ACTIVE" => "Y",
            "SORT" => "500",
            "DEFAULT_VALUE" => $PROP_DEF,
            "XML_ID" => $PROP_XML_ID,
            "CODE" => $PROP_XML_ID,
            "PROPERTY_TYPE" => "L",
            "TMP_ID" => $tmpid,
            "MULTIPLE" => $PROP_MULTIPLE,
            "IBLOCK_ID" => $IBLOCK_ID
        );

        $PROP_ID = $ibp->Add ($arFields);
        $res = (IntVal ($PROP_ID) > 0);
    }
    $_SESSION[ "BX_CML2_IMPORT" ][ "NS" ][ 'custom' ][ 'iblock_option_processed' ] = true;
    print "progress\n";
    print "Список свойств обработан";
    $contents = ob_get_contents ();
    ob_end_clean ();
    if (toUpper (LANG_CHARSET) != "WINDOWS-1251") {
        $contents = $GLOBALS[ 'APPLICATION' ]->ConvertCharset ($contents, LANG_CHARSET, "windows-1251");
    }
    header ("Content-Type: text/html; charset=windows-1251");
    print $contents;
    exit ();
}

function customCatalogImportStep()
{
    //mylog('rabotaet');
    global $DIR_NAME;
    $NS = &$_SESSION[ "BX_CML2_IMPORT" ][ "NS" ];
    if (! $NS[ 'custom' ][ 'cards_loaded' ]) {
        echo "start prepare cards";
        importCards ();
    }
    if (! $NS[ 'custom' ][ 'iblock_option_processed' ]) {
        processIBlockOption ();
    }
    if (! $NS[ 'custom' ][ 'products_processed' ]) {
        processProducts ();
    }

    if (! $NS[ 'custom' ][ 'offers_processed' ]) {
        processOffers ();
    }
    if (! $NS[ 'custom' ][ 'reserve_processed' ]) {
        processReservedOffers ();
    }
    if ($NS[ 'custom' ][ 'reserve_processed' ]) {
        $arSection = CIBlockSection::GetList (Array (
            "SORT" => "ASC"
        ), Array (
            'IBLOCK_ID' => $arItem[ 'IBLOCK_ID' ],
            'NAME' => 'Номенклатура'
        ))->Fetch ();
        $bs = new CIBlockSection ();
        $bs->Update ($arSection[ 'ID' ], array (
            'ACTIVE' => "N"
        ));
    }
    BXClearCache (true, "/");
}

function updateProduct($arItem, $arInfo,$reset_price=false)
{
    $itemId = $arItem['ID'];
    CIBlockElement::SetPropertyValueCode("$itemId", "MAX_PRICE", "0");
    $SKUIDS = array ();
    $rsOffers = CIBlockElement::GetList (array (), array (
        'IBLOCK_ID' => 46,
        'PROPERTY_' . $arInfo[ 'SKU_PROPERTY_ID' ] => $arItem[ 'ID' ]
    ), false, false, array (
        'CATALOG_PROPERTY_CML2_ATTRIBUTES',
        'ID',
        'IBLOCK_ID',
        'NAME'
    ));
    $skuCount = 0;
    while ( $arOffer = $rsOffers->GetNext () ) {
        $res = CIBlockElement::GetProperty (46, $arOffer[ 'ID' ], array (), array (
            'CODE' => 'CML2_ATTRIBUTES'
        ))->Fetch ();
        $SKUIDS[ ] = $arOffer[ 'ID' ];
        OnProductAdd ($arOffer[ 'ID' ], array ());
        $skuCount ++;
    }
    $amount = 0;
    if ($skuCount) {
        $rsStore = CCatalogStoreProduct::GetList (array (), array (
            'PRODUCT_ID' => $SKUIDS
        ));
        while ( $arStore = $rsStore->Fetch () ) {
            $amount = $amount + $arStore[ 'AMOUNT' ];
        }
    }

    $el = new CIBlockElement ();
    if (! $amount && !stristr($arItem['NAME'],'Сертификат')) {
        $arItem[ 'ACTIVE' ] = 'N';
        $arItem[ 'IBLOCK_SECTION_ID' ] = $ID;
        return $el->Update ($arItem[ 'ID' ], array (
            'ACTIVE' => 'N',
            'IBLOCK_ID' => $arItem[ 'IBLOCK_ID' ],
            //'PROPERTY_VALUES'=>array(419=>0)
        ));
    } else {
        $arItem[ 'ACTIVE' ] = 'Y';
        $el->Update ($arItem[ 'ID' ], array (
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $arItem[ 'IBLOCK_ID' ]
        ));
    }
    if ($arItem[ 'ACTIVE' ] == 'N') {
        return true;
    }
    $section_exist = false;
    $res = CIBlockElement::GetProperty ($arItem[ 'IBLOCK_ID' ], $arItem[ 'ID' ], Array (), Array (
        'CODE' => 'CML2_TRAITS'
    ));
    while ( $row = $res->Fetch () ) {
        if ('ВидНоменклатуры' == $row[ 'DESCRIPTION' ]) {
            $sectionRes = CIBlockSection::GetList (Array (
                "SORT" => "ASC"
            ), Array (
                'IBLOCK_ID' => $arItem[ 'IBLOCK_ID' ],
                'NAME' => $row[ 'VALUE' ]
            ));
            while ( $sect = $sectionRes->Fetch () ) {
                if ($sect) {
                    $ID = $sect[ 'ID' ];
                    $section_exist = true;
                }
            }
            if (! $section_exist) {
                $bs = new CIBlockSection ();
                $arFields = Array (
                    "ACTIVE" => "Y",
                    "IBLOCK_SECTION_ID" => 0,
                    "IBLOCK_ID" => $arItem[ 'IBLOCK_ID' ],
                    "NAME" => $row[ 'VALUE' ],
                    "SORT" => 100
                );
                $ID = $bs->Add ($arFields);
            } else {
                $bs = new CIBlockSection ();
                $bs->Update ($ID, array (
                    'ACTIVE' => "Y",
                    "NAME" => $row[ 'VALUE' ],
                    "IBLOCK_SECTION_ID" => 0
                ));
            }
            if ($ID) {
                $el = new CIBlockElement ();
                $arItem[ 'IBLOCK_SECTION_ID' ] = $ID;
                $itemId = $arItem[ 'ID' ];
                $rs = $el->Update ($itemId, array (
                    'IBLOCK_SECTION_ID' => $ID,
                    //'PROPERTY_VALUES'=>array(419=>'0')
                ));
            }
            break;
        }
    }
    return true;
}

function updateElement($arItem)
{
    $res = CIBlockElement::GetProperty ($arItem[ 'IBLOCK_ID' ], $arItem[ 'ID' ], Array (), Array (
        'CODE' => 'CML2_TASTE'
    ));

    $tasteRes = $res->GetNext ();
    $prop_id = $tasteRes[ 'ID' ];
    $res = CIBlockElement::GetProperty ($arItem[ 'IBLOCK_ID' ], $arItem[ 'ID' ], Array (), Array (
        'CODE' => 'CML2_ATTRIBUTES'
    ));
    $ar_res = $res->GetNext ();
    $vkus = $ar_res[ 'VALUE' ];
    if (! $vkus) {
        $vkus = '-';
    }
    $arFields = Array (
        "NAME" => "Вкус",
        "ACTIVE" => "Y",
        "SORT" => "100",
        "CODE" => "CML2_TASTE",
        "PROPERTY_TYPE" => "L",
        "IBLOCK_ID" => $arItem[ 'IBLOCK_ID' ]
    );
    $res = CIBlockPropertyEnum::GetList (array (), array (
        'CODE' => "CML2_TASTE",
        "IBLOCK_ID" => $arItem[ 'IBLOCK_ID' ]
    ));
    $CURRENT_OPTION = array ();
    while ( $ar_res = $res->Fetch () ) {
        if ($ar_res[ 'VALUE' ] == $vkus) {
            $CURRENT_OPTION = $ar_res;
        }
    }
    if (! $CURRENT_OPTION[ 'ID' ]) {
        $arr = array (
            "VALUE" => $vkus,
            "PROPERTY_ID" => $prop_id,
            "SORT" => 100,
            "DEF" => "N",
            "XML_ID" => md5 ($vkus)
        );
        $CURRENT_OPTION[ 'ID' ] = CIBlockPropertyEnum::Add ($arr);
    }
    CIBlockElement::SetPropertyValuesEx ($arItem[ 'ID' ], $arItem[ 'IBLOCK_ID' ], array (
        'CML2_TASTE' => $CURRENT_OPTION[ 'ID' ]
    ));
    return true;
}

if (! function_exists ('gzopen')) {

    function gzopen($file, $mode)
    {
        return gzopen64 ($file, $mode);
    }

    function gzseek($zp, $offset, $whence = SEEK_SET)
    {
        return gzseek64 ($zp, $offset, $whence);
    }
    #function gzread($zp,$len)
    #{
    #return gzread64($zp,$len);
    #}
    #function gzclose($zp)
    #{
    #return gzclose64($zp);
    #}
}

function OnSaleComponentOrderComplete($ID,$arOrder,$arParams)
{
    /*print_r($arOrder);
    die();*/
    if ('pecom:auto' == $arOrder['DELIVERY_ID']) {
        $arFields['PRICE_DELIVERY'] = 0;
        $arFields['PRICE'] = $arOrder['PRICE'] - $arOrder['PRICE_DELIVERY'];
        CSaleOrder::Update($ID, $arFields);
    }
}

function OnBeforeCatalogStoreUpdate($id, &$arFields)
{

    unset($arFields['TITLE']);
}

function getNewUrlById($id, $type = 'DETAIL'){
	
	$url = false;
	
	if (CModule::IncludeModule("iblock")){
		
		$arFilter = array(
			'ACTIVE' => "Y",
			'ID' => $id,
			'IBLOCK_ID' => 45
		);
		
		
	
		if($type == 'DETAIL'){		
			
			$arSelect = array("ID", "DETAIL_PAGE_URL");

			$BDRes = CIBlockElement::GetList(
				array(),            
				$arFilter,         
				false,         
				array("nTopCount" => 1),  
				$arSelect           
			);

			
			
			if($arRes = $BDRes->GetNext())
			{
				$url = $arRes['DETAIL_PAGE_URL'];
			}
			
		}elseif($type == 'SECTION'){
			
			$arSelect = array("ID", "SECTION_PAGE_URL");

			$BDRes = CIBlockSection::GetList(
				array(),            
				$arFilter,         
				false, 
				$arSelect,
				array("nTopCount" => 1)
			);

			
			if($arRes = $BDRes->GetNext())
			{
				$url = $arRes['SECTION_PAGE_URL'];
			}
		}
		
	}	
	
	
	return $url;
}


AddEventHandler("main", "OnBeforeProlog", 'addCartTurboYandex');


function addCartTurboYandex(){
	
	global $APPLICATION;
	
	$page = $APPLICATION->GetCurPage(false);
	
	//$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest(); 
	$productId = $_GET['id'];//(int)$request->get("id");
	
	if($page == '/personal/cart/' && $productId){
		
		if(CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){
			Add2BasketByProductID( $productId ) ;
		}		
	}

	
}


function DeleteOld()
{
    global $DB;
    CModule::IncludeModule("sale");
    $nDays = 90;
 
    $nDays = IntVal($nDays);
	
    $strSql =
        "SELECT f.ID ".
        "FROM b_sale_fuser f ".
        "LEFT JOIN b_sale_order o ON (o.USER_ID = f.USER_ID) ".
        "WHERE ".
			"   TO_DAYS(f.DATE_UPDATE)<(TO_DAYS(NOW())-".$nDays.") ". 
			" AND o.ID is null ". " AND f.USER_ID is null ". 
		"LIMIT 1000"; 
			
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__." Line: ".__LINE__);
		
    while ($ar_res = $db_res->Fetch())
    {
        CSaleBasket::DeleteAll($ar_res["ID"], false);
        CSaleUser::Delete($ar_res["ID"]);
    }
 
    return 'DeleteOld();';
}


?><? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/zyter.smtp/classes/general/cmodulezytersmtp.php");?>