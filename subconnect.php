<?php

if (1==SQLDASH_AUTH_MODE){

	$connid=isset($_COOKIE['connid'])?$_COOKIE['connid']:null;

	if (!isset($connid)||!is_numeric($connid)) apperror('Select a connection first');
	
	$user=userinfo();
	$userid=$user['userid'];
	$query="select * from conns where connid=$connid "; // and userid=$userid";
	$rs=$sdb->query($query);
	if (!$myrow=$rs->fetchArray(SQLITE3_ASSOC)) apperror('Access denied. Invalid connection.');
	
	$connname=$myrow['connname'];
	$connhost=$myrow['connhost'];
	$connuser=$myrow['connuser'];
	$connpass=$myrow['connpass'];
	$conndbname=$myrow['conndbname'];
	
	$connpass=decstr($connpass,SQLDASH_DB_TOKEN);
	
	//todo: clickhouse special case?
		
	$db=@sql_get_db($connhost,$conndbname,$connuser,$connpass);

}//AUTH_MODE	
