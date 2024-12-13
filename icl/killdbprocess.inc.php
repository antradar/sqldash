<?php

$connid=isset($_COOKIE['connid'])?$_COOKIE['connid']:null;
if (isset($connid)) include 'subconnect.php';

include 'icl/showdbprocesses.inc.php';

function killdbprocess(){
	global $db;
	$user=userinfo();
	$procid=GETVAL('procid');
	
	if (!isset($user['groups']['kill'])) apperror('Access denied');
	
	$procids=array();
	
	$query="show processlist";
	$rs=sql_prep($query,$db);
	while ($myrow=sql_fetch_assoc($rs)){
		$procids[$myrow['Id']]=$myrow['Id'];	
	}
	
	if (isset($procids[$procid])){
		$query="kill $procid";
		sql_query($query,$db);
	}
	
	showdbprocesses();
	
}
