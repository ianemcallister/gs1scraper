<?php

require_once 'SQLInterface.php';
require_once 'GTIN.php';
require_once 'scrape.php';
require_once 'ParseGS1Response.php';
require_once 'Curler.php';
//require_once 'parse.php';

class GTINSearchQueue 
{
	//class variables
	public $SQLConnection;

	//constructor
	public function __construct()
	{
		$this->SQLConnection = new SQLInterface(require 'config.php');
	}
	public function enqueueGTIN($gtin)
	{

	}
	public function dequeueGTIN()
	{

	}
	/*
	*	@return a gtin object
	*/
	public function peekFrontOfQueueGTIN()
	{
		//query the front of the que
		$frontOfQueueRequest = 'SELECT * FROM `GTINs_to_Search` LIMIT 1';

		//run the query through the database
		$frontOfQueueObject = $this->SQLConnection->query($frontOfQueueRequest);

		//convert object to a usable array
		$frontOfQueueValues = mysqli_fetch_array($frontOfQueueObject);

		//assign table values to object variables
		$countryPrefix = $frontOfQueueValues['countryPrefix'];
		$gcp = $frontOfQueueValues['gcp'];
		$fillDigits = $frontOfQueueValues['fillDigits'];
		$checkDigit = $frontOfQueueValues['checkDigit'];

		//declare and initialize local GTIN object
		$aGTIN = new GTIN($gcp);
		$aGTIN->buildGTINObject($countryPrefix, $gcp, $fillDigits, $checkDigit);

		//return the GTIN for search
		
		return $aGTIN;
	}
	public function validateQueuedGTINs($queuedGTIN, $passedCountryPrefix, $passedGCP, $passedFillDigits, $passedCheckDigit)
	{
		if(	$queuedGTIN->countryPrefix == $passedCountryPrefix &&
			$queuedGTIN->gcp == $passedGCP &&
			$queuedGTIN->fillDigits == $passedFillDigits &&
			$queuedGTIN->checkDigit == $passedCheckDigit )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function searchGS1Site($queuedGTIN)
	{
		//instanciate a new scrape object
		$aScrape = new Scrape($queuedGTIN);
		//build the scrape
		$aScrape->buildPosting();
		//perform the search and retrieve the page for parsing
		$fullResultsResponse = $aScrape->performSearch();
		
		//convert the response to DOMDocument
		$html = new DOMDocument();
		$html->loadHTML($fullResultsResponse->response);

		//if the results were returned from GS1 parse the results, otherwise exit with an error
		if(strchr($html->saveXML(), '<td class="footerText">'))
		{
			//parse the results
			$GTINSearchResults = new ParseGS1Response($html);

			//generate an error report
			$GTINSearchResults->errorReport();

			//regardless of result record the search
			$tempGTIN = $queuedGTIN->countryPrefix.$queuedGTIN->gcp.$queuedGTIN->fillDigits.$queuedGTIN->checkDigit;
			$insert = " INSERT INTO `GTIN_Search_Results` (`GTINID`, `GTIN`, `responderNumber`, `numberOfResponses`, `rcCode`, `errorMessage`,`informationProvider`, `dateSearched`)
                    	VALUES (NULL, 
                    			'".$tempGTIN."',
                    			'".$GTINSearchResults->responderNumber->nodeValue."',
                    	    	'".$GTINSearchResults->noOfResponse->nodeValue."',
                    			'".$GTINSearchResults->rcCode->nodeValue."',
                    			'".$GTINSearchResults->errorMessage->nodeValue."',
                    			'".$GTINSearchResults->informationProvider->nodeValue."',
                   				NOW() );";

			//run insert in to GTIN_Search_Results table
			$result = $this->SQLConnection->query($insert);

			/*
			//update the searchesPerDay table
			$update = " UPDATE `searchesPerDay` 
                    	SET `searchesRun`= `searchesRun` + 1
                    	WHERE `date`=".$company->gcp;
			*/

			//respond to the search results
			//check for errors and handle various error messages
			if($GTINSearchResults->errorMessage->nodeValue == 'Unknown country prefix')
			{
				//record the GTIN search result
				//incriment the country prefix and reque the next GTIN
				//alert error
				echo "Unknown country prefix";
			}
			else if ($GTINSearchResults->errorMessage->nodeValue == 'No record found')
			{
				//delete the top row of the queue at GTINs_to_Search
				$this->SQLConnection->dropQueueTopRow($GTINSearchResults->returnGCP());
				//insert HitList_has_Search_Result
				$this->SQLConnection->HitList_has_Search_Result($GTINSearchResults->returnGCP(), $GTINSearchResults->returnGTIN());
				//generate new random GTIN
				$gtin = new GTIN($GTINSearchResults->returnGCP());
				$gtin->randomFillDigits();
				//insert GTIN to bottom of queue at GTINs_to_Search
				$this->SQLConnection->queueNewGTIN($gtin);
				//alert error
				echo "No record found";
			}
			else if ($GTINSearchResults->errorMessage->nodeValue == 'Server error')
			{
				//record the GTIN search result
				//requeue GTIN for later search
				//alert error
				echo "Server error";
			}
			else if ($GTINSearchResults->errorMessage->nodeValue == 'No error')
			{
				//insert GS1 Subscriber row
				$this->SQLConnection->insertGS1SubscriberRow($GTINSearchResults);
				
				//drop GTINs_to_Search row
				$this->SQLConnection->dropQueueTopRow($GTINSearchResults->returnGCP());

				//drop HitList GCP row
				$this->SQLConnection->dropHitListGCP($GTINSearchResults->returnGCP());

				//insert Valid_GCP row
				$this->SQLConnection->insertValidGCPRow($GTINSearchResults->returnGCP());

				//insert Valid_GCP_has_GS1_Subscriber row
				$this->SQLConnection->insertValidGCP_has_GS1SubscriberRow($GTINSearchResults->returnGCP());

				//notify the user
				echo "No error";
			}
			else if ($GTINSearchResults->errorMessage->nodeValue == 'Daily request limit exceeded')
			{
				echo "Daily request limit exceeded";
			}
			/*
			else if ($GTINSearchResults->responses->nodeValue > 1)
			{
				//is a GLN???
				//alert unknown error
			}
			*/
			else
			{
				//record GTIN search result
				//requeue GTIN for later search
				//alert unknown error
			}
		}
		else
		{
			//alert error
			echo "There was an error with your search, please try again"."<BR>";
			//retur false
			return false;
		}
	}
	public function dispalyFullQueue()
	{

	}
	public function isEmptyQueue()
	{

	}
	public function loadGCPListFromSQL($table)
	{
		//build query
		$hitListQuery = "SELECT * FROM `".$table."`";

		//run the query through the database
		$databaseGCPs = $this->SQLConnection->query($hitListQuery);

		//return arrap of databse results
		return $databaseGCPs;
	}
	public function convertGCPstoGTIN($databaseGCPs)
	{
		//declare local variable
		$gtins = array();

		while($row = mysqli_fetch_array($databaseGCPs, MYSQLI_ASSOC))
		{
			$gtin = new GTIN($row['GCP']);
			$gtin->defaultGTIN();
			$gtin->setHitListID($row['gcpID']);
			array_push($gtins, $gtin);
			//echo $gtin->countryPrefix.$gtin->gcp.$gtin->fillDigits.$gtin->checkDigit."<BR>";
		}
		
		return $gtins;
	}
	public function loadGTINListToQueue($gtins)
	{
		$i = 0;

		//loop through GTINs
		while(isset($gtins[$i]))
		{
			//for each GTIN build and run a query to add to the que
			$insert = " INSERT INTO `GTINs_to_Search` (`queueID`, `countryPrefix`, `gcp`, `fillDigits`, `checkDigit`, `hitListID`,`dateQueued`)
                    	VALUES (NULL, '".$gtins[$i]->countryPrefix."',
                    				  '".$gtins[$i]->gcp."',
                    				  '".$gtins[$i]->fillDigits."',
                    				  '".$gtins[$i]->checkDigit."',
                    				  '".$gtins[$i]->hitListID."',
                    				   NOW() );";
			
			//run query
			$result = $this->SQLConnection->query($insert);

			//incriment $i
			$i = $i + 1;
		}
	}
}

?>