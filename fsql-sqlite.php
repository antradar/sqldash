<?php
// Gyroscope SQL Wrapper
// SQLite implementation
// (c) Antradar Software

//run sqlite_install.php to generate the initial database file

$FSQL_ENGINE="SQLite3";

function fsql_escape($str){
	return addslashes($str);	
}

function fsql_get_db($dbfn){

	$db=new SQLite3($dbfn);
	return $db;
}

function fsql_query($query,$db,$internal=0){
	$rs=$db->query($query);
	
	global $last_sqlite_select;
	
	if (!$rs) echo 'SQL Error: '.$db->lastErrorMsg()."\r\n";
	
	$parts=explode(' ',strtolower(trim($query)));
	$verb=$parts[0];
	
	if ($verb=='select'&&!$internal) $last_sqlite_select=$query;
			
	return $rs;
}

function fsql_fetch_array($rs){
	return $rs->fetchArray();

}

function fsql_fetch_assoc($rs){
	return  $rs->fetchArray(SQLITE3_ASSOC);
}

function fsql_insert_id($db,$rs=null){

	return $db->lastInsertRowID();
}

function fsql_affected_rows($db,$rs){
	global $last_sqlite_select;
	
	$query="select count(*) as c from ($last_sqlite_select)t ";
	$rs=sql_query($query,$db,1);
	
	$myrow=sql_fetch_assoc($rs);
	$count=$myrow['c'];
	
	return max($count,$db->changes());
}

function fsql_begin_transaction(){
	die("not implemented!");
}

function fsql_commit(){
	die("not implemented!");
}

function fsql_rollback(){
	die("not implemented!");
}

/* Sample Connection

$db=sql_get_db("db.sqlite3");
*/
