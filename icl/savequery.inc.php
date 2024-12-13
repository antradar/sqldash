<?php

function savequery(){
	global $sdb;
	global $SQL_ENGINE;
	
	$qname=GETSTR('qname');
	$squery=SQLite3::escapeString(SQET('squery'));
	
	$user=userinfo();
	$userid=$user['userid'];
	
	
	$query="insert into squeries (userid,squeryname,squeryconntype,squery) 
	values ($userid,'$qname','$SQL_ENGINE','$squery') ";
	$rs=$sdb->query($query);

	$squeryid=$sdb->lastInsertRowID();
	
	if (!$squeryid){
		apperror('Error saving query');	
	}
	if ($qname==''){
		$query="update squeries set squeryname='Q_$squeryid' where squeryid=$squeryid";
		$sdb->query($query);	
	}
		
	header('squeryid: '.$squeryid);
		
}