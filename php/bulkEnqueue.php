<?php

require_once 'GTINSearchQueue.php';

//instanciate a serach queue object
$activeQueue = new GTINSearchQueue();

//get GCP list to be queued
$targetList = $activeQueue->loadGCPListFromSQL('HitList');

//generate GTINs to be queued
$gtins = $activeQueue->convertGCPstoGTIN($targetList);

/*
for($i=0;$i<360;$i++)
{
	echo $gtins[$i]->countryPrefix.$gtins[$i]->gcp.$gtins[$i]->fillDigits.$gtins[$i]->checkDigit."<BR>";
}*/

$activeQueue->loadGTINListToQueue($gtins);

?>