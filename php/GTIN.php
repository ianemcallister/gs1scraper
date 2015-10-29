<?php
	
	class GTIN 
	{
		public $countryPrefix;
		public $gcp;
		public $fillDigits;
		public $checkDigit;
		public $hitListID;

		public function __construct($passedGCP = '0000000')
		{
			$this->countryPrefix = '';
			$this->gcp = $passedGCP;
			$this->fillDigits = '';
			$this->checkDigit = '';
		}
		public function buildGTINObject($countryPrefix, $gcp, $fillDigits, $checkDigit)
		{
			$this->countryPrefix = $countryPrefix;
			$this->gcp = $gcp;
			$this->fillDigits = $fillDigits;
			$this->checkDigit = $checkDigit;

			//return the object built
			return $this;
		}
		public function gtin()
		{
			return $this->countryPrefix.$this->gcp.$this->fillDigits.$this->checkDigit;
		}
		public function checkDigit($shortGTIN)
		{
			//declare and initialize local variables
			$odds = [];
			$evens = [];
			$evenDigits = 0;
			$oddDigits = 0;
			$oddSum = 0;
			$evenSum = 0;
		
			//fill the even and odd arrays
			for($i = 1; $i <= strlen($shortGTIN); $i++)
			{
				//if digit is odd add to odd array, if even add to even array
				if(($i % 2) == 0)
				{
					$evens[$evenDigits] = $shortGTIN[$i-1];
					$evenDigits++;
				}
				else
				{
					$odds[$oddDigits] = $shortGTIN[$i-1];
					$oddDigits++;
				}

			}

			//calculate odds
			for($i = 0; $i < $oddDigits; $i++) { $oddSum = $oddSum + $odds[$i]; }
			//multiply the odd some by three
			$oddSum = $oddSum * 3;

			//calculate evens
			for($i = 0; $i < $evenDigits; $i++) {	$evenSum = $evenSum + $evens[$i];	}

			//calculate total sum & check digit
			$sum = $oddSum + $evenSum;
			$checkDigit = (10 - ($sum % 10));
			//check digits of 10 should be returned as 0
			if($checkDigit == 10) { $checkDigit = 0; }

			return $checkDigit;
		}
		public function defaultGTIN($gtinLength = 14)
		{
			
			//set country prefix to 0
			$this->countryPrefix = '0';
			//set the fillDigits to 0
			$this->fillDigits = str_repeat('0',($gtinLength - 1 - strlen($this->countryPrefix.$this->gcp)));
			//set check digit
			$this->checkDigit = $this->checkDigit($this->countryPrefix.$this->gcp.$this->fillDigits);
			//return the concatenated GTIN
			return $this->countryPrefix.$this->gcp.$this->fillDigits.$this->checkDigit;
			
		}
		public function countryPrefixIncriment($gtinLength = 14)
		{
			//incriment the countryPrefix
			$this->countryPrefix += 1;
			//no need to change the fill digits, keep as set elsewhere
			//recalculate the check digit
			$this->checkDigit = $this->checkDigit($this->countryPrefix.$this->gcp.$this->fillDigits);
			//return the concatenated GTIN
			return $this->countryPrefix.$this->gcp.$this->fillDigits.$this->checkDigit;
		}
		public function randomFillDigits($gtinLength = 14)
		{
			//declare and initialize local variable
			$fillDigits = '';
			//no need to change the countryPrefix, keep as was set elswhere
			$this->countryPrefix = '0';
			//generate enough digits 
			for($i = 0; $i < ($gtinLength - 1 - strlen($this->countryPrefix.$this->gcp)); $i++)
			{
				$fillDigits = $fillDigits.mt_rand(0,9);
			}
			//save the fill values for later
			$this->fillDigits = $fillDigits;
			//recalculate the check digit
			$this->checkDigit = $this->checkDigit($this->countryPrefix.$this->gcp.$this->fillDigits);
			//return the concatenated GTIN
			return $this->countryPrefix.$this->gcp.$this->fillDigits.$this->checkDigit;
		}
		public function setHitListID($hitListID)
		{
			$this->hitListID = $hitListID;
		}
	}
	/*
	$newGTIN = new GTIN('006924');

	echo $newGTIN->defaultGTIN()."<br>";
	echo $newGTIN->countryPrefixIncriment()."<br>";
	echo $newGTIN->randomFillDigits()."<br>";
	echo $newGTIN->gtin()."<br>";
	$newGTIN->countryPrefixIncriment();
	echo $newGTIN->gtin()."<br>";
	$newGTIN->countryPrefixIncriment();
	echo $newGTIN->gtin()."<br>";
	$newGTIN->countryPrefixIncriment();
	echo $newGTIN->gtin()."<br>";	
	*/
?>