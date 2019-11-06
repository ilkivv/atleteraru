<?php
use morphos\Russian\GeographicalNamesInflection;

AddEventHandler("main", "OnBeforeEndBufferContent", 'SeoManager');
AddEventHandler("main", "OnPageStart", 'SeoManagerText');

define("IBLOCK_SEO", 52);

function SeoManager() { 


	//echo "<pre>"; var_dump($GLOBALS['CURRENT_CITY']["NAME"]); echo "</pre>";
	
	
	


	if (defined("ERROR_404") && ERROR_404 == 'Y' || defined("ADMIN_SECTION"))
		return false;
	
	if(CModule::IncludeModule("iblock")) {
		global $APPLICATION;
		$currentPageUrl = $_SERVER['REQUEST_URI'];//$APPLICATION->GetCurPage(false);

		
		$CONFIG = ARRAY(
			'IBLOCK_ID' => IBLOCK_SEO,
			'PROPERTY_URL' => $currentPageUrl,
			'ACTIVE' => 'Y'
		);	

		$title_set = $desction_set = $keywords_set = false;

		$page = CIBlockElement::GetList(array(), $CONFIG, false, false, array('PROPERTY_H1', 'PROPERTY_TITLE', 'PROPERTY_DESCRIPTION', 'PROPERTY_KEYWORDS'))->GetNext();
		
		
		
		if($page) {
			
			if(!empty($page['PROPERTY_H1_VALUE'])) {      
				
				
				$APPLICATION->SetTitle($page['PROPERTY_H1_VALUE']);
				
            }

			if(!empty($page['PROPERTY_TITLE_VALUE']) ) {
                $title_set = true;			
				
                $APPLICATION->SetPageProperty('title', $page['PROPERTY_TITLE_VALUE']);
            }

			if(!empty($page['PROPERTY_KEYWORDS_VALUE'])) {
                $keywords_set = true;
                $APPLICATION->SetPageProperty('keywords', $page['PROPERTY_KEYWORDS_VALUE']);
            }

			if(!empty($page['PROPERTY_DESCRIPTION_VALUE'])) {
                $desction_set = true;		
				
                $APPLICATION->SetPageProperty('description', $page['PROPERTY_DESCRIPTION_VALUE']);
            }
		}
		
		
		
		if($GLOBALS['CURRENT_CITY']["NAME"]){
			
			$cityR = GeographicalNamesInflection::getCase($GLOBALS['CURRENT_CITY']["NAME"], 'предложный');
		}
		
		
		
		$title =  $APPLICATION->GetPageProperty('title');
		$description =  $APPLICATION->GetPageProperty('description');
		
		if($title){			
			$title .= ' в ' .$cityR;
			$APPLICATION->SetPageProperty('title', $title);	
				
		}
		
		
		if($description){	
			$description .= ' в ' .$cityR;
			$APPLICATION->SetPageProperty('description', $description);
		}
		
		
	}
}

function SeoManagerText() { 
	
	if (defined("ERROR_404") && ERROR_404 == 'Y' || defined("ADMIN_SECTION"))
		return false;
	
    if(CModule::IncludeModule("iblock")) {
        global $APPLICATION;
		$currentPageUrl = $_SERVER['REQUEST_URI']; //$APPLICATION->GetCurPage(false);
		
        $CONFIG = ARRAY(
            'IBLOCK_ID' => IBLOCK_SEO,
            'PROPERTY_URL' => $currentPageUrl,
			'ACTIVE' => 'Y'
        );

        $page = CIBlockElement::GetList(array(), $CONFIG, false, false, array('PROPERTY_TEXT'))->GetNext();
		
		//echo "<pre>"; var_dump($page); echo "</pre>";

        if($page) {
            global $APPLICATION;
            if(isset($page['PROPERTY_TEXT_VALUE']) && !empty($page['~PROPERTY_TEXT_VALUE']['TEXT']))
                $APPLICATION->SetPageProperty('additional_text', $page['~PROPERTY_TEXT_VALUE']['TEXT']);
        }

    }

}