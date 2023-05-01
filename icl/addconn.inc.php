<?php

include 'icl/showconn.inc.php';

function addconn(){
	global $sqlmode;
	
	$connname=QETSTR('connname');
	$conntype=QETSTR('conntype');
	$connhost=QETSTR('connhost');
	$conndbname=QETSTR('conndbname'); 
	$connapiport=QETSTR('connapiport'); 
	$connuser=SQET('connuser'); $dbconnuser=addslashes($connuser);
	$connpass=SQET('connpass');
	
	global $sdb;
	$user=userinfo();
	$userid=intval($user['userid']);
	
	checkgskey('addconn');
	
	//todo: clickhouse special case?
		
	$db=@sql_get_db($connhost,$conndbname,$connuser,$connpass);
	
	if ($conndbname=='') $conndbname='null'; else $conndbname="'$conndbname'";
	if (!is_numeric($connapiport)) $connapiport='null';

	if ($conntype=='sqlsrv'&&$conndbname=='') apperror('Default Database cannot be empty');
				
	if (isset($db)&&$db!==false&&(
			(is_array($db)&&isset($db['raw'])&&$db['raw'])
			||
			is_object($db)
			)){
				
				$dbconnpass=encstr($connpass,SQLDASH_DB_TOKEN);
				
			} else {
				apperror("Invalid connection settings");	
			}
			
			
	
	
	$query="insert into conns (userid,connname,conntype,connhost,conndbname,connapiport,connuser,connpass) values ($userid,'$connname','$conntype','$connhost',$conndbname,$connapiport,'$dbconnuser','$dbconnpass') ";
	$rs=$sdb->query($query);

	$connid=$sdb->lastInsertRowID();
	
	if (!$connid) {
		apperror(_tr('error_creating_record').': '.$err);
	}
	

	
	header('newrecid:'.$connid);
	header('newkey:conn_'.$connid);
	header('newparams:showconn&connid='.$connid);
	
	showconn($connid);
}

