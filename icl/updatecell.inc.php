<?php

function updatecell(){
	global $db;
	global $SQL_ENGINE;

	$dbname=GETSTR('dbname');
	$tablename=GETSTR('table');
	$pkey=GETSTR('pkey');
	$pval=$_GET['pval']; if ($pval=='') apperror('Invalid primary identity');
	$fkey=GETSTR('fkey');
	$fval=$_GET['fval'];
	$pfval=$_POST['pfval'];
	
	if ($pfval!='') $fval=$pfval;
	
	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))) sql_select_db($db,$dbname);
	
	ob_start();
	$query="update $tablename set $fkey=? where $pkey=?";		
	$rs=sql_prep($query,$db,array($fval,$pval));
	$res=ob_get_clean();
	
	if ($SQL_ENGINE=='ClickHouse'&&$res!=''){
		apperror($res);	
	}
	
	$query="select $fkey from $tablename where $pkey=?";
	$rs=sql_prep($query,$db,$pval);
	$myrow=sql_fetch_assoc($rs);
	$ffval=$myrow[$fkey];
	
	
	echo $ffval;
}
