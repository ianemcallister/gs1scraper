<?php
/**
 * User: ianemcallister
 * Date: 10/25/15
 * Time: 8:24 PM
 * used for all sql interactions.  databse variables are saved in the config.php file.
 */


class SQLInterface
{
	//declaring and initializing local variables
	public $connection;

	/**
     * Instantiate with the connection details.
     *
     * @param $configs->servername
     *				  ->username
     *				  ->password
     *				  ->dbname
     * @return $this - the connection itself
     */
    public function __construct(array $configs)
    {
    	//build connection
        $this->connection = new mysqli($configs['servername'], $configs['username'], $configs['password'], $configs['dbname']);

        // Check connection
        if ($this->connection->connect_error) { die("Connection failed: " . $this->connection->connect_error); }   
        else { /*echo 'conection worked!';*/ }
        //return object
        return $this;
    }

    public function query($query)
    {
    	//return results of the passed in query
    	return $this->connection->query($query);
    }

    public function findDateRow()
    {
        /*DEAL WITH THIS LATER
        $select = " DECLARE @p_date DATETIME
                    SET     @p_date = CONVERT( DATETIME, '2015-10-29 17:56:50', 20 )

                    SELECT  *
                    FROM    searchesPerDay
                    WHERE   date == @p_date";

        $getDate = "GETDATE()";


        return $this->connection->query($select);
        */
    }

    public function insertGS1SubscriberRow($GTINSearchResults)
    {
        echo "<BR>Inserting GS1 Subscriber Row<BR>";

        $insert = " INSERT INTO `GS1_subscribers` (subscriberID, GLN, company, contact, gcp, lastChanged, status, providerGLN)
                    VALUES (NULL, 
                            '".$GTINSearchResults->returnGLN()."',
                            '".$GTINSearchResults->returnAddress()."',
                            '".$GTINSearchResults->returnContact()."',
                            '".$GTINSearchResults->returnGCP()."',
                            '".$GTINSearchResults->returnLastChanged()."',
                            '".$GTINSearchResults->returnStatus()."',
                            '".$GTINSearchResults->returnProviderGLN()."');";

        echo $insert;
        //run insert in to GTIN_Search_Results table
        $result = $this->connection->query($insert);

        var_dump($result);

    }

    public function dropHitListGCP($gcp)
    {
        $delete = " DELETE FROM 'HitList'
                    WHERE `gcp`=".$gcp;

        //run delete on GTINs_to_Search table
        $result = $this->connection->query($delete);
    }

    public function insertValidGCPRow($gcp)
    {
        $insert = " INSERT INTO `Valid_GCP` (`gcpID`, `GCP`)
                    VALUES (NULL, 
                    '".$gcp."');";

        //run insert in to Valid_GCP table
        $result = $this->connection->query($insert);
    }

    public function insertValidGCP_has_GS1SubscriberRow($gcp)
    {
        echo "<BR>inserting a GS1 subscriber row<BR>";
        //save the gcpID in to $gcpID value
        $select = " SELECT * 
                    FROM `Valid_GCP`
                    WHERE `GCP`='".$gcp."' 
                    LIMIT 1;";

        $gcpIDrow = $this->connection->query($select);
        $row = mysqli_fetch_array($gcpIDrow, MYSQLI_ASSOC);
        $gcpID = $row['gcpID'];
                
        //save the subscriberID value to $subscriberID
        $select = " SELECT * 
                    FROM `GS1_subscribers`
                    WHERE `gcp`='".$gcp." '
                    LIMIT 1";
        
        $subscriberIDrow = $this->connection->query($select);
        $row = mysqli_fetch_array($subscriberIDrow, MYSQLI_ASSOC);
        $subscriberID = $row['subscriberID'];
                
        //using those values now insert
        $insert = " INSERT INTO `Valid_GCP_has_GS1_Subscriber` (`gcpID`, `subscriberID`)
                    VALUES (".$gcpID.", 
                            ".$subscriberID.");";

        
        echo "<BR>".$insert."<BR>";
        //run insert in to Valid_GCP_has_GS1_Subscriber table
        $result = $this->connection->query($insert);
        var_dump($result);
    }

    public function HitList_has_Search_Result($gcp, $gtin)
    {
        echo "<BR>"."running hitlist has serach results"."<BR>";
        //save the gcpID in to $gcpID value
        $select = " SELECT * 
                    FROM `HitList`
                    WHERE `GCP`='".$gcp."'";

        $gcpIDrows = $this->connection->query($select);
        $row = mysqli_fetch_array($gcpIDrows, MYSQLI_ASSOC);
        $gcpID = $row['gcpID'];
                
        //save the search results value to $subscriberID
        $select = " SELECT * 
                    FROM `GTIN_Search_Results`
                    WHERE GTIN='".$gtin."'";

        $searchresultsIDrow = $this->connection->query($select);
        //if more than one row is returned, select the most recent date
        $row = mysqli_fetch_array($searchresultsIDrow);
        $searchresultsID = $row['GTINID'];

        //using those values now insert
        $insert = " INSERT INTO `HitList_has_Search_Result` (`hitListID`, `GTINID`)
                    VALUES ('".$gcpID."', 
                            '".$searchresultsID."');";

        //run insert in to Valid_GCP_has_GS1_Subscriber table
        $result = $this->connection->query($insert);
        var_dump($result);
    }

    public function dropQueueTopRow($gcp)
    {
        echo "going to delete Que row<BR>".$gcp;

        $delete = " DELETE FROM `GTINs_to_Search`
                    WHERE gcp= '".$gcp."';";

        echo $delete."<BR>";
        //run delete on GTINs_to_Search table
        $result = $this->connection->query($delete);
        var_dump($result);
    }

    public function addGTINSearchResultsTableRow()
    {

    }

    public function addHitList_has_Search_ResultRow()
    {

    }

    public function queueNewGTIN($gtin)
    {
        echo "<BR>"."queuing new gtin";
        //save the gcpID in to $gcpID value
        $select = " SELECT * 
                    FROM `HitList`
                    WHERE GCP='".$gtin->gcp." '
                    LIMIT 1";

        $gcpIDrow = $this->connection->query($select);
        $row = mysqli_fetch_array($gcpIDrow, MYSQLI_ASSOC);
        $gcpID = $row['gcpID'];

        $insert = " INSERT INTO `GTINs_to_Search` (`queueID`, `countryPrefix`, `gcp`, `fillDigits`, `checkDigit`, `hitListID`, `dateQueued`)
                    VALUES (NULL,
                            '".$gtin->countryPrefix."',
                            '".$gtin->gcp."',
                            '".$gtin->fillDigits."',
                            '".$gtin->checkDigit."',
                            '".$gcpID."',
                            NOW() );";
    
        echo "<BR>".$insert."<BR>";
        //run insert in to Valid_GCP_has_GS1_Subscriber table
        $result = $this->connection->query($insert);
    }

}

//$conn = new SQLInterface(require 'config.php');

?>