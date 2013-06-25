<?php

$dom = new DomDocument();
@$dom->loadHTMLFile('http://apims.doe.gov.my/apims/');
$classname = 'table1';
$finder = new DomXPath($dom);
$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
$xml = new DOMDocument(); 
$innerHTML = '';
foreach ($nodes as $node) {
   $bulletin = $xml->appendChild($xml->createElement("bulletin"));
   
   $xml->appendChild($xml->importNode($node,true));
   print_r($xml);
}
$innerHTML.=trim($xml->saveHTML()); 

echo $innerHTML;



$dom = new DOMDocument();
@$dom->loadHTMLFile('http://apims.doe.gov.my/apims/');
$finder = new DOMXPath($dom);

$classname = 'table1';
$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

$xml = new DOMDocument();


foreach($finder->query('//table/tr') as $tr) {
  $bulletin = $xml->appendChild($xml->createElement("bulletin"));
  $title = $finder->query('.//td[2]//a', $tr)->item(0)->nodeValue;
  $bulletin->appendChild($xml->createElement("title",$title));
  $type = $finder->query('.//td[3]/font', $tr)->item(0)->nodeValue;
  $bulletin->appendChild($xml->createElement("type",$type));
}
echo $xml->saveXML();