<?php
include 'subconnect.php';

function exportcsv(){
	global $db;
	global $SQL_ENGINE;
	
	$queryidx=GETVAL('queryidx');
	$query=SQET('csvquery_'.$queryidx); $query=trim($query,';');
		
	$sqlmode=SQET('sqlmode');

	$dbname=GETSTR('dbname');
	if ($sqlmode!='sqlite'&&in_array($SQL_ENGINE,array('MySQL','MySQLi'))) sql_select_db($db,$dbname);

	$rs=sql_prep($query,$db);
	$idx=0;
	

	header('Content-Type: application/csv');
	header("Content-disposition: attachment; filename=\"export_q${queryidx}.csv\"");
	
	$f=fopen('php://output','wt');
		
	while ($myrow=sql_fetch_assoc($rs)){
		if ($idx==0){
			fputcsv($f,array_keys($myrow));			
		}
		fputcsv($f,array_values($myrow));
		$idx++;
	}//while

	fclose($f);
	
}

