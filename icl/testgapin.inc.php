<?php

include 'icl/calcgapins.inc.php';
//include 'encdec.php';

function testgapin(){
	global $sdb;
	
	$msg='Invalid PIN';
	$user=userinfo();
	$userid=$user['userid'];
	
	
	$query="select gakey from users where userid=$userid";
	$rs=$sdb->query($query);
	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	
	$gakey=$myrow['gakey'];
		
	$enc_remote=0; //set $remote=1 in production, sync with showgaqr.inc.php
	
	if ($gakey!='') $gakey=decstr($gakey,GYROSCOPE_PROJECT.'gakey-'.$userid,$enc_remote); //remote key

	$pin=str_replace(array(' ','-','.'),'',SQET('pin'));
	
	$pins=calcgapins($gakey);
		
	if (in_array($pin,$pins)){
		$msg='PIN Valid';	
	} else {
		//echo implode(', ',$pins);	
	}

	
	header('pinres: '.tabtitle($msg));
		
		
}