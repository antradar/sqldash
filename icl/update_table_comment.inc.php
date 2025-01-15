<?php
include 'icl/showtable.inc.php';

function update_table_comment(){
	$dbname=GETSTR('dbname');
	$tablename=GETSTR('tablename');	
	$comment=QETSTR('comment');
	
	
	global $db;
			
	sql_select_db($db,$dbname);	
	
	$query="alter table $tablename comment='$comment'";
	
	sql_prep($query,$db);
	
	showtable();
	
	
	
}