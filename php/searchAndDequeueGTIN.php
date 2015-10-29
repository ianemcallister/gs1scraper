<?php

//when javascript on the page launches this file get the queued GTIN, search it
//if results are sucessfully returned parse it and upadate the database

require_once 'GTINSearchQueue.php';

//posted variables
$passedCountryPrefix = $_GET['countryPrefix'];
$passedGCP = $_GET['gcp'];
$passedFillDigits = $_GET['fillDigits'];
$passedCheckDigit = $_GET['checkDigit'];

//instanciate a serach queue object
$activeQueue = new GTINSearchQueue();

//get the queued GTIN
$queuedGTIN = $activeQueue->peekFrontOfQueueGTIN();

//if SQL queued GTIN matches against html queued GTIN start the search
//if($activeQueue->validateQueuedGTINs($queuedGTIN, $passedCountryPrefix, $passedGCP, $passedFillDigits, $passedCheckDigit))
if(1)
{
	//search GS1 site
	$activeQueue->searchGS1Site($queuedGTIN);
	//echo "running search";

}


?>