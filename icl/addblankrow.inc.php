<?php

function addblankrow(){
	global $db;
	
	global $SQL_ENGINE;
	
	global $sqlite_root;

	$table=GETSTR('table');

	$pkey=GETSTR('pkey');
	
	$query="insert into $table () values ()";
	$rs=sql_prep($query,$db);
	$recid=sql_insert_id($db,$rs);
	
	echo "Inserted: #$recid";
			
}

