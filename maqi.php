<?php
ini_set('date.timezone', 'Asia/Kuala_Lumpur');
//ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)');
ini_set('max_execution_time', 0);
ini_set('memory_limit', '10000M');

/**
* Get the date from URL querystring.
* @param string query 
* @return date YYYY-mm-dd 
*/
if(isset($_GET['d']))
{
    $date = $_GET['d'];
}else{
    $date = date("Y-m-d");
}

// DOE URL
$url = "http://www.doe.gov.my/apims/engine.php?date=$date";

/**
* Load XML from DOE xml and parsing/convert to new xml/json.
* @param string $xml 
* @return xml/json 
*/
$xml = simpleXML_load_file($url,"SimpleXMLElement",LIBXML_NOCDATA); 


if($xml ===  FALSE)
{
   exit("Failed to open $url");
}
else { 
	$doc = new DOMDocument('1.0', 'UTF-8');
	$maqi = $doc->createElement("maqi");
	$doc->appendChild($maqi);

	$credit = $doc->createElement('credit','Department of Environment Malaysia');
	$maqi->appendChild($credit);

	$credit_URL = $doc->createElement('credit_URL','http://www.doe.gov.my');
	$maqi->appendChild($credit_URL);

	$image = $doc->createElement('image');
	$maqi->appendChild($image);

		$image_url = $doc->createElement('image_url', 'http://icons-ak.wxug.com/graphics/wu2/logo_130x80.png');
		$image->appendChild($image_url);
		
		$image_title = $doc->createElement('image_title', 'Malaysia Air Quality Index');
		$image->appendChild($image_title);
		
		$image_link = $doc->createElement('image_link', 'http://github.org/diperakui/maqi');
		$image->appendChild($image_link);	
	
	foreach($xml as $marker) {

		$lat = $marker['lat'];
		$lng = $marker['lng'];
		$html = htmlentities($marker['html']);
		
		
		// Clean XML file for html attribute. Ugly method. 
		// Value we need: State, City, Station, and 3 reading for air quality (morning: 7am, noon: 11am, evening: 5pm).
		$html = str_replace(array("\n", "\r", "\t", "&lt;b&gt;", "&lt;/b&gt;", "&lt;br&gt;"), '-', $html);
		$pieces = explode("-", $html);
		
		// State
		$state_tmp =$pieces[1];
		$state_exp = explode(":", $state_tmp);
		$state = $state_exp[1];
		$state = trim($state);
		
		// City and Station
		$city_x = explode(":", $pieces[2]);
		$city_x2 = explode(",", $city_x[1]);
		$station = $city_x2[0];	
		$station = trim($station);	

		$city = ( ! empty($city_x2[1]) ) ? trim($city_x2[1]) : $station;
		
		// 7AM
		$morning_tmp = explode(":", $pieces[4]);
		$morning = trim($morning_tmp[2]);

		// 11AM
		$noon_tmp = explode(":", $pieces[5]);
		$noon = trim($noon_tmp[2]);

		// 5PM
		$evening_tmp = explode(":", $pieces[6]);
		$evening = trim($evening_tmp[2]);
		
		$average = $marker['api'];
		
		// Got some data from DOE. Now lets creating XML file.
		$observation = $doc->createElement('observation');
		
		$maqi->appendChild($observation);
		
			$node_station = $doc->createElement('station', $station);
			$observation->appendChild($node_station);
			
			$node_city = $doc->createElement('city', $city);
			$observation->appendChild($node_city);
			
			$node_state = $doc->createElement('state', $state);
			$observation->appendChild($node_state);
			
			$node_country = $doc->createElement('country', 'MY');
			$observation->appendChild($node_country);
			
			$node_latitude = $doc->createElement('latitude', $lat);
			$observation->appendChild($node_latitude);
			$node_longitude = $doc->createElement('longitude', $lng);
			$observation->appendChild($node_longitude);
			
			$reading = $doc->createElement('reading');
			
			// observation date 
			$attribute = $doc->createAttribute('date');
			$attribute->value = $date;
			$observation->appendChild($attribute);
			
			$attribute = $doc->createAttribute('morning');
			$attribute->value = $morning;
			$reading->appendChild($attribute);
			
			$attribute = $doc->createAttribute('noon');
			$attribute->value = $noon;
			$reading->appendChild($attribute);
			
			$attribute = $doc->createAttribute('evening');
			$attribute->value = $evening;
			$reading->appendChild($attribute);
			
			$attribute = $doc->createAttribute('average');
			$attribute->value = $average;
			$reading->appendChild($attribute);
				
			$observation->appendChild($reading);			
	}
	$xml = $doc->saveXML();


	if(isset($_GET) && array_key_exists('e',$_GET) && $_GET['e'] == "xml"){

		header('Content-Type: text/xml');
		$file = 'maqi.xml';

	} else{

		header('Content-Type: application/json');
		//$xml = json_encode(simplexml_load_string($xml), JSON_PRETTY_PRINT);
		$xml = json_encode(simplexml_load_string($xml));
		$file = 'maqi.json';
		
	}

	if(isset($_GET) && array_key_exists('s',$_GET) && $_GET['s'] == "save"){
		file_put_contents($file, $xml);
	} else {
		echo $xml;
	}
}
?> 

