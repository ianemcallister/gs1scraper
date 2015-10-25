<?php
/**
 * User: ianemcallister
 * Date: 10/25/15
 * Time: 8:24 PM
 * used for all sql interactions.  databse variables are saved in the config.php file
 */

class SQLInterface
{
	//declaring and initializing local variables
	public $connection;

	/**
     * Instantiate with a valid URL.
     *
     * @param $config->servername
     *				 ->username
     *				 ->password
     *				 ->dbname
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
}

$conn = new SQLInterface(require 'config.php');

?>