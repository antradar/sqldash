<?php

if (!isset($_GET['sqlmode'])||$_GET['sqlmode']!='sqlite') include 'subconnect.php';

function addblankrow(){
	global $db;
	
	global $SQL_ENGINE;

	if (!isset($_GET['sqlmode'])||$_GET['sqlmode']!='sqlite') $dbname=checkdbname();
		
	global $sqlite_root;

	$table=GETSTR('table');

	$pkey=GETSTR('pkey');
	
	$user=userinfo();
	
	if (!isset($user['groups']['insert'])) apperror('Access denied');
	
	$query="insert into $table () values ()";
	$rs=sql_prep($query,$db);
	$recid=sql_insert_id($db,$rs);
	
	echo "Inserted: #$recid";
			
}

