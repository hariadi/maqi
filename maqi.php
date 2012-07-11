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
//$url = "http://www.doe.gov.my/apims/engine.php?date=$date";
$url = "2012-06-18.xml";

/**
* Load XML from DOE xml and parsing/convert to new xml/json.
* @param string $xml 
* @return xml/json 
*/
$xml = simpleXML_load_file($url,"SimpleXMLElement",LIBXML_NOCDATA); 


if($xml ===  FALSE)
{
   exit('Failed to open test.xml.');
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
		
		$city = $city_x2[1];
		$city = trim($city);
		// If $city not available, get $station value
		if(empty($city)) $city = $station;
		
		// 7AM
		$morning = $pieces[4];

		// 11AM
		$noon = $pieces[5];

		// 5PM
		$evening = $pieces[6];
		
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
			
			//$node_zip = $doc->createElement('zip', $zip);
			//$observation->appendChild($node_zip);
			
			$node_latitude = $doc->createElement('latitude', $lat);
			$observation->appendChild($node_latitude);
			$node_longitude = $doc->createElement('longitude', $lng);
			$observation->appendChild($node_longitude);
			
			$reading = $doc->createElement('reading');
			
			$attribute = $doc->createAttribute('date');
			$attribute->value = $date ;
			$reading->appendChild($attribute);
				
			$observation->appendChild($reading);
				
				$time = date('Y-m-d\TH:i:s\+08:00', strtotime($date . " +7 hours"));
				$node_morning = $doc->createElement('morning', $morning);
				$reading->appendChild($node_morning);
				
				$attribute = $doc->createAttribute('date');
				$attribute->value = $time ;
				$node_morning->appendChild($attribute);
				
				$reading->appendChild($node_morning);
				
				$time = date('Y-m-d\TH:i:s\+08:00', strtotime($date . " +11 hours"));
				$node_noon = $doc->createElement('noon', $noon);
				$attribute = $doc->createAttribute('date');
				$attribute->value = $time ;
				$node_noon->appendChild($attribute);
				$reading->appendChild($node_noon);
				
				$time = date('Y-m-d\TH:i:s\+08:00', strtotime($date . " +17 hours"));
				$node_evening = $doc->createElement('evening', $evening);
				$attribute = $doc->createAttribute('date');
				$attribute->value = $time ;
				$node_evening->appendChild($attribute);
				$reading->appendChild($node_evening);
				
				$node_avg = $doc->createElement('average', $average);
				$reading->appendChild($node_avg);
	}
	$xml = $doc->saveXML();
	
	if ($_GET['e'] == "xml")
	{
		header('Content-Type: text/xml');
		echo $xml;
	} else {
		header('Content-Type: application/json');
		echo json_encode(simplexml_load_string($xml));
	}
}
?> 

