<?php

require_once 'Curler.php';

class Scrape
{
    //declare and initialize local variables
    public $gtin;
    public $gcp;
    public $path = '../cache_search_results/';
    public $searchResultsPage;
    public $targetSite = 'http://gepir.gs1.org/v32/xx/gtin.aspx?Lang=en-US';
    public $curler;
    public $postValues;

    /**
     * Instantiate with a valid gtin.
     *
     * @param $providedGCP from database
     * @return the new scrape with values
     */
    public function __construct($providedGTINObject)
    {
        //simplify use of GTIN
        $tempGTIN = $providedGTINObject->countryPrefix.$providedGTINObject->gcp.$providedGTINObject->fillDigits.$providedGTINObject->checkDigit;

        //initialize object variables
        $this->gtin = $tempGTIN;
        $this->gcp = $providedGTINObject->gcp;
        $this->searchResultsPage = $this->path.$tempGTIN.'.html';
        $this->curler = new Curler($this->targetSite);
        $this->postValues = array(  '__EVENTTARGET'=>"",
                                    '_ctl0%3AcphMain%3ATabContainerGTIN%3ATabPanelGTIN%3AtxtRequestGTIN'=>$tempGTIN,
                                    '_ctl0%3AcphMain%3ATabContainerGTIN%3ATabPanelGTIN%3AbtnSubmitGTIN'=>'Search');
        return $this;
    }
    /**
     * Generate a default GTIN based on the GCP and a target GTIN length
     *
     * @param int $shortGTIN
     * @return a valid check digit for the supplied GTIN
     */
    public function buildPosting()
    {
        $this->curler->followRedirects() // Will follow redirects option set to true.
            ->header('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8')
            ->header('Accept-Encoding', 'gzip, deflate')
            ->header('Accept-Language', 'en-US,en;q=0.5')
            ->header('Connection', 'keep-alive')
            ->header('Host', 'gepir.gs1.org')
            ->header('Content-Type', 'application/x-www-form-urlencoded')
            ->postArray($this->postValues)
            ->cookieJar('../cookieJar/gepirCookie') // Set a file to use as cookie jar.
            ->compressedResponse()
            ->suppressOutput() // Sets the RETURNTRANSFER option to true so that output is fetched as string instead of displayed automatically.
        ;
    }

    /**
     * Generate a default GTIN based on the GCP and a target GTIN length
     *
     * @param int $shortGTIN
     * @return a valid check digit for the supplied GTIN
     */
    public function performSearch()
    {
        //temporarily use this
        $file = '00629227000008.html';
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);

        $doc->loadHTMLFile("../cache_search_results/".$file);

        //return $doc;



        //declare local variable and run search
        $html = $this->curler->go(); // Replace go() with dryRun() to get debug info on the request without executing it.
        
        //after the search has been performed append the GCP to the results page for later reference
        $html->response = $html->response.'The passed GCP for this page was: <span id="passedGCP">'.$this->gcp.'</span>';

        //write the page out to cache file
        $html->writeResponse($this->searchResultsPage);
        //return resulting html for parsing
        return $html;
    }

}

?>
