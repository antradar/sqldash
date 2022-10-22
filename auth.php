<?php
if (file_exists('sqldashkey.php')) include 'sqldashkey.php';
else define('SQLDASHKEY','');

define ('GYROSCOPE_VERSION', '8.2');

//remember to personalize the project name
define ('GYROSCOPE_PROJECT', 'SQL Dash');


//ignore vendor settings if you are not a certified solution provider
define ('VENDOR_VERSION',''); 
define ('VENDOR_INITIAL','');
define ('VENDOR_NAME','');

/*
	a passphrase (or a "salt") has to be set
	comment out the timestamp for permanent login;
*/


$saltroot=SQLDASHKEY.'gyroscope_demo';
$salt=$saltroot.$_SERVER['REMOTE_ADDR'].date('Y-m-h');


if (!is_callable('hash_equals')){
	function hash_equals($a,$b){return $a==$b;}	
}

/*
	this function should be called at the very beginning of the page
	if the user is forced to login
*/

function login($silent=false){
	global $salt;
	global $saltroot;
	$salt2=$saltroot.$_SERVER['REMOTE_ADDR'].date('Y-m-h',time()-3600);
	global $_COOKIE;
	global $_SERVER;
	global $db;
	
	$db=null;
	
	//check cookie authenticity
	$login=isset($_COOKIE['login'])?$_COOKIE['login']:null;
	$dashpass=null;
	$signed=0;
	
	$dbhost=isset($_COOKIE['dbhost'])?$_COOKIE['dbhost']:null;
	if (!isset($dbhost)) $dbhost='localhost';

	$dbname=isset($_COOKIE['dbname'])?$_COOKIE['dbname']:null;
	if ($dbname=='') $dbname=null;
	
	if (isset($login)&&$login!=''){
		$rawpass=base64_decode($_COOKIE['dashpass']);
		$dashpass=decstr($rawpass,SQLDASHKEY);
		
		$db=sql_get_db($dbhost,$dbname,$login,$dashpass,null,'sqldashdb');
		//sync the condition with login.php
		if (isset($db)&&$db!==false&&(
			(is_array($db)&&isset($db['raw'])&&$db['raw'])
			||
			is_object($db)
			)) $signed=1;
	}

	if (!$signed) {
		if (!$silent) header('location: login.php?from='.$_SERVER['PHP_SELF']); else {header('HTTP/1.0 403 Forbidden');header('X-STATUS: 403');}
		die();
	}

}


function userinfo(){
	global $salt;
	global $saltroot;
	global $_COOKIE;
	
		//check cookie authenticity
	$login=isset($_COOKIE['login'])?$_COOKIE['login']:null;
	
	$info=array(
		'login'=>$_COOKIE['login'],
	);	
	
	
	return $info;
}

function checkdbname(){
	global $db;
	global $SQL_ENGINE;
	
	$dbname=$_COOKIE['dbname'];
	$dbname=noapos($dbname);

	if (!isset($dbname)||$dbname=='') apperror('Select a database first');

	
	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))) sql_select_db($db,$dbname);

	return $dbname;
}