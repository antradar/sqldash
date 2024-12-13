<?php

$connid=isset($_COOKIE['connid'])?$_COOKIE['connid']:null;
if (isset($connid)) include 'subconnect.php';

include 'icl/showdbprocesses.inc.php';


function killalldbprocesses(){
	global $db;
	
	$key=SGET('key');
	$procids=explode(',',SQET('procids'));
		
	$user=userinfo();
	
	if (!isset($user['groups']['kill'])) apperror('Access denied');
		
	$query="show processlist";
	$rs=sql_prep($query,$db);
	while ($myrow=sql_fetch_assoc($rs)){
		$procid=$myrow['Id'];
		if (!in_array($procid,$procids)) continue;
		
		$query="kill $procid";
		sql_query($query,$db);
		
				
	}
		
	showdbprocesses();
	
}
