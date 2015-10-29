<?php

class ParseGS1Response
{
	//for all searches
	public $searchHTML;
	public $responderNumber;
	public $noOfResponse;
	public $rcCode;
	public $errorMessage;
	public $informationProvider;

	//the gcp
	public $gcp;

	//the gtin
	public $gtin;

	//for successful searches
	private $GLN;
	private $company;
	private $contact;
	private $lastChanged;
	private $status;
	private $providerGLN;

	//temporarily use
	private $address;

	//constructor
	public function __construct($passedInHTML)
	{
		//instanciate object with search result HTML
		$this->searchHTML = $passedInHTML;
	}
	
	//essential extraction methods
	public function extractGCP()
	{
		//not all pages include a GCP, if they do...
		if($this->searchHTML->getElementByID('passedGCP'))
		{
			//set the value and return true
			$this->gcp = $this->searchHTML->getElementByID('passedGCP');
			return true;
		}
		else if($this->searchHTML->getElementByID('resultTable'))
		{
			//declare and initialize local variable
			$resultTableRows;

			//check for last changed value
			if($this->searchHTML->getElementByID('resultTable'))
			{
				$resultTableRows = $this->searchHTML->getElementByID('resultTable')->getElementsByTagName('td');
				$targetNode = $resultTableRows->item(4);
			}

			//return contact
    		$this->gcp = $targetNode;
    		return true;	
		}
		else
		{
			//otherwise return false
			return false;
		}
		
	}
	public function extractGTIN()
	{
		$this->gtin = $this->searchHTML->getElementByID("_ctl0_cphMain_TabContainerGTIN_TabPanelGTIN_txtRequestGTIN");
	}
	public function extractResponderNumber()
	{
		$this->responderNumber = $this->searchHTML->getElementByID('_ctl0_cphMain_SRgtin_lblNumberGLN');
	}
	public function extractNoOfResponses()
	{
		$this->noOfResponse = $this->searchHTML->getElementByID('_ctl0_cphMain_SRgtin_lblnumberofHits');
	}
	public function extractRCCode()
	{
		$this->rcCode = $this->searchHTML->getElementByID('_ctl0_cphMain_SRgtin_lblNumberErrorGLN');
	}
	public function extractErrorMessage()
	{
		$this->errorMessage = $this->searchHTML->getElementByID('_ctl0_cphMain_SRgtin_lblErrorGLN');
		//return the value collected
		return $this->errorMessage->nodeValue;
	}
	public function extractInformationProvider()
	{
		$this->informationProvider = $this->searchHTML->getElementByID('_ctl0_cphMain_SRgtin_MoreInfo');
		//return the value collected
		return $this->informationProvider->nodeValue;
	}

	public function errorReport()
	{
		//compile error report
		$this->extractResponderNumber();
		$this->extractErrorMessage();
		$this->extractRCCode();
		$this->extractNoOfResponses();
		$this->extractInformationProvider();
		$this->extractGTIN();

		//save values to object
		$errorReport = array (
			'Error_Message' => $this->errorMessage->nodeValue,
			'RC_Code' => $this->rcCode->nodeValue,
			'Responder_Number' => $this->responderNumber->nodeValue,
			'Number_of_Responses' => $this->noOfResponse->nodeValue,
			);
		
		//if a GCP was included pass it
		if($this->extractGCP())
		{
			//array_push($errorReport['GCP'], $this->gcp->nodeValue);
		}
		
		//return the object
		return $errorReport;
	}

	//successful search extraction methods
	public function extractCompany()
	{
		//NOTICE - pulling out address right now, at some point we'll break it apart to make seperate fields for address

		//declare and initialize local variables
		$address = '';

		//check for address format, save value in address
		if($this->searchHTML->getElementByID('addressscroll'))
    	{
    		$address = $this->searchHTML->getElementByID('addressscroll');
    	}
    	else if ($this->searchHTML->getElementByID('Address'))
    	{
    		$address = $this->searchHTML->getElementByID('Address');
   		}
   		else 
   		{
   			$address = "n/a";
   		}
		
		//parse apart address in to needed values
		$this->address = $address;
		return $this->address->nodeValue;
	}
	public function extractContact()
	{
		//declare and initialize local variable
		$contact = '';
		//check if by contact first, if not try by table property, if not set as n/a
		if($this->searchHTML->getElementByID('Contact'))
    	{
    		$contact = $this->searchHTML->getElementByID('Contact');
    	}
    	else if($this->searchHTML->getElementByID('resultTable'))
    	{

    	}
    	else
    	{
    		$contact = "n/a";
    	}

    	//return contact
    	$this->contact = $contact;
    	return $this->contact->nodeValue;
	}
	public function extractLastChanged()
	{
		//declare and initialize local variable
		$resultTableRows;

		//check for last changed value
		if($this->searchHTML->getElementByID('resultTable'))
		{
			$resultTableRows = $this->searchHTML->getElementByID('resultTable')->getElementsByTagName('td');
			$targetNode = $resultTableRows->item(3);
		}

		//return contact
    	$this->lastChanged = $targetNode;
    	return $this->lastChanged->nodeValue;
	}
	public function extractStatus()
	{
		//declare and initialize local variable
		$resultTableRows;

		//check for last changed value
		if($this->searchHTML->getElementByID('resultTable'))
		{
			$resultTableRows = $this->searchHTML->getElementByID('resultTable')->getElementsByTagName('td');
			$targetNode = $resultTableRows->item(5);
		}

		//return contact
    	$this->status = $targetNode;
    	return $this->status->nodeValue;
	}
	public function extractProviderGLN()
	{
		//declare and initialize local variable
		$resultTableRows;

		//check for last changed value
		if($this->searchHTML->getElementByID('resultTable'))
		{
			$resultTableRows = $this->searchHTML->getElementByID('resultTable')->getElementsByTagName('td');
			$targetNode = $resultTableRows->item(6);
		}

		//return contact
    	$this->providerGLN = $targetNode;
    	return $this->providerGLN->nodeValue;
	}
	public function extractGLN()
	{
		//declare and initialize local variable
		$resultTableRows;

		//check for last changed value
		if($this->searchHTML->getElementByID('resultTable'))
		{
			$resultTableRows = $this->searchHTML->getElementByID('resultTable')->getElementsByTagName('td');
			$targetNode = $resultTableRows->item(0);
		}

		//return contact
    	$this->gln = $targetNode;
    	return $this->gln->nodeValue;
	}

	public function searchResultsReport()
	{
		//COME BACK TO THIS LATER

		//declare and initialize local variables
		$searchResultsReport = array(
			'Address' => 'start',
			);

		//call individual methods
		if($this->extractCompany())
		{
			//address now but update to company when results can be pulled apart
			array_push($searchResultsReport['Address'], 'test');
		}
		$this->extractContact();
		$this->extractLastChanged();
		$this->extractStatus();
		$this->extractProviderGLN();
		
		//return the object upon completion
		return $searchResultsReport;
	}


	//getter methods
	public function returnCompany()
	{
		return $this->company->nodeValue;
	}
	public function returnAddress()
	{
		if(!isset($this->address->nodeValue))
		{
			$this->extractCompany();
		}
		return $this->address->nodeValue;
	}
	public function returnContact()
	{
		if(!isset($this->contact->nodeValue))
		{
			$this->extractContact();
		}
		return $this->contact->nodeValue;	
	}
	public function returnLastChanged()
	{
		if(!isset($this->lastChanged->nodeValue))
		{
			$this->extractLastChanged();
		}
		return $this->lastChanged->nodeValue;
	}
	public function returnStatus()
	{
		if(!isset($this->status->nodeValue))
		{
			$this->extractStatus();
		}
		return $this->status->nodeValue;
	}
	public function returnProviderGLN()
	{
		if(!isset($this->providerGLN->nodeValue))
		{
			$this->extractProviderGLN();
		}
		return $this->providerGLN->nodeValue;
	}
	public function returnGLN()
	{
		if(!isset($this->gln->nodeValue))
		{
			$this->extractGLN();
		}
		return $this->gln->nodeValue;
	}
	public function returnGCP()
	{
		if(!isset($this->gcp->nodeValue))
		{
			$this->extractGCP();
		}
		return $this->gcp->nodeValue;
	}
	public function returnGTIN()
	{
		if(!isset($this->gtin->nodeValue))
		{
			$this->extractGTIN();
		}
		return $this->gtin->nodeValue;
	}
	public function returnInformationProvider()
	{
		if(!isset($this->informationProvider->nodeValue))
		{
			$this->extractInformationProvider();
		}
		return $this->informationProvider->nodeValue;
	}
}

?>