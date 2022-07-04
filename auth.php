<?php

define ('GYROSCOPE_VERSION', '8.2');

//remember to personalize the project name
define ('GYROSCOPE_PROJECT', 'SQL Dash');

define('SQLDASHKEY','!;L%zw~aAQ._lt7.j7.aNdT-|_|pi*%=+spy8.!kdM-zf|Hc|v');

//ignore vendor settings if you are not a certified solution provider
define ('VENDOR_VERSION',''); 
define ('VENDOR_INITIAL','');
define ('VENDOR_NAME','');

//ignore modual settings if the product is a non-shared, custom solution
//define ('MOD_SERVER','https://www.antradar.com/gyroscope_mods.php');
//define ('MOD_KEY','mod_demo123');

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
	
	$dbhost=$_COOKIE['dbhost'];
	if (!isset($dbhost)) $dbhost='localhost';

	$dbname=$_COOKIE['dbname'];
	if ($dbname=='') $dbname=null;
	
	if (isset($login)&&$login!=''){
		$rawpass=base64_decode($_COOKIE['dashpass']);
		$dashpass=decstr($rawpass,SQLDASHKEY);
		
		$db=sql_get_db($dbhost,$dbname,$login,$dashpass);
		if (isset($db)&&$db) $signed=1;
			
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