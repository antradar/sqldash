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
	
	$appdomain=QETSTR('appdomain');
	$appclientid=QETSTR('appclientid');
	$appsecret=QETSTR('appsecret');
	$apptoken=QETSTR('apptoken');
	
	global $sdb;
	$user=userinfo();
	$userid=intval($user['userid']);
	
	if (!isset($user['groups']['connedit'])) apperror('access denied');	
	
	checkgskey('addconn');
	
	//todo: clickhouse special case?

	if ($conntype=='sfdx'){
		$dbconnuser=str_replace(' ','',$dbconnuser);
		$cmd='sfdx org:display --json -o '.$connuser.' --json 2>&1';
		$res=shell_exec($cmd);
		$obj=json_decode($res,1);
		if (isset($obj['status'])&&$obj['status']!=0){
			apperror('Invalid Salesforce credentials');
		}
	}

	$db=@sql_get_db($connhost,$conndbname,$connuser,$connpass);
		
	if ($conntype=='sfapi'){
		$token=sql_salesforce_gettoken($apptoken,$appclientid,$appsecret,$appdomain);
		if (trim($token)=='') apperror('Invalid OAuth credentials');	
	}
		

	
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
			
			
	
	
	$query="insert into conns (userid,connname,conntype,connhost,conndbname,connapiport,connuser,connpass, appdomain,appclientid,appsecret,apptoken) 
	values ($userid,'$connname','$conntype','$connhost',$conndbname,$connapiport,'$dbconnuser','$dbconnpass', '$appdomain', '$appclientid', '$appsecret','$apptoken') ";
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

