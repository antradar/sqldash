<?php
include 'icl/showtable.inc.php';

function update_col_comment(){
	$dbname=GETSTR('dbname');
	$tablename=GETSTR('tablename');	
	$colname=GETSTR('colname');
	$comment=QETSTR('comment');
	
	
	global $db;
			
	sql_select_db($db,$dbname);	
	
	$query="describe $tablename";
	$rs=sql_query($query,$db);
	while ($myrow=sql_fetch_assoc($rs)){
		if ($myrow['Field']!=$colname) continue;
		
		$otype=$myrow['Type'];
		if ($myrow['Null']=='NO') $otype.=' not null ';
		if ($myrow['Default']) $otype.=' default '.$default;

		$query="alter table $tablename modify column $colname $otype comment '$comment' ";
		sql_query($query,$db);		
		
	}
	/*
	$query="alter table $tablename comment='$comment'";
	sql_prep($query,$db);
	*/
	
	showtable();
	
	
	
}