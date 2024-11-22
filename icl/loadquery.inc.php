<?php

function loadquery(){
	global $sdb;
	global $SQL_ENGINE;
	
	$squeryid=GETVAL('squeryid');
	$user=userinfo();
	$userid=$user['userid'];
		
	$query="select squery,squeryconntype from squeries where squeryid=$squeryid";
	$rs=$sdb->query($query);

	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	$squery=$myrow['squery'];
	
	echo $squery;
	
}