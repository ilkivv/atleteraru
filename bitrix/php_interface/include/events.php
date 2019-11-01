<?

use morphos\Russian\GeographicalNamesInflection;


AddEventHandler("main", "OnEndBufferContent", "ChangeMyContent");
function ChangeMyContent(&$content)
{  
   
   $city['NAME'] = $GLOBALS['CURRENT_CITY']["NAME"];
   
   //var_dump(GeographicalNamesInflection::getCase($city['NAME'], 'предложный'));
 
	
	$search = array(
		'{{city|именительный}}',
	  	'{{city|родительный}}',
		'{{city|дательный}}',
		'{{city|винительный}}',
		'{{city|творительный}}',
		'{{city|предложный}}'
   );
   

    global $USER, $APPLICATION;
	
    if(!$APPLICATION->GetShowIncludeAreas() && !defined("ADMIN_SECTION")) {

		$replace = array(
			$city['NAME'],
			GeographicalNamesInflection::getCase($city['NAME'], 'родительный'),
			GeographicalNamesInflection::getCase($city['NAME'], 'дательный'),
			GeographicalNamesInflection::getCase($city['NAME'], 'винительный'),
			GeographicalNamesInflection::getCase($city['NAME'], 'творительный'),
			GeographicalNamesInflection::getCase($city['NAME'], 'предложный')
	   ); 


		 $content = str_replace($search, $replace, $content);
	} 
	
}