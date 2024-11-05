<?php

function setactiveconn(){
	global $sdb;
	global $usehttps;
	
	$user=userinfo();
	$userid=$user['userid'];
	$connid=GETVAL('connid');
	
	$query="select connid,conntype from conns where connid=$connid "; // and userid=$userid";
	$rs=$sdb->query($query);
	if (!$myrow=$rs->fetchArray(SQLITE3_ASSOC)) apperror('Access denied. Invalid connection.');
	
	setcookie('connid',$connid,null,null,null,$usehttps,true);	
	setcookie('sqlmode',$myrow['conntype'],null,null,null,$usehttps,true);

	if ($myrow['conntype']=='sfdx'){
		setcookie('dbname','Salesforce');
	}
}