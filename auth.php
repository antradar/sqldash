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


$saltroot=SQLDASHKEY.SQLDASH_AUTH_TOKEN;
$salt=$saltroot.$_SERVER['REMOTE_ADDR'].date('Y-m-j-H');

$dbsalt=SQLDASH_DB_TOKEN;

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
	$salt2=$saltroot.$_SERVER['REMOTE_ADDR'].date('Y-m-j-H',time()-3600);
	global $_COOKIE;
	global $_SERVER;
	global $db;
	global $usehttps;
	
	$db=null;
	
	//check cookie authenticity
	$login=isset($_COOKIE['login'])?$_COOKIE['login']:null;
	if (1==SQLDASH_AUTH_MODE||2==SQLDASH_AUTH_MODE){
		$login=isset($_COOKIE['login'])?$_COOKIE['login']:null;
		$dispname=isset($_COOKIE['dispname'])?$_COOKIE['dispname']:null;
		$userid=isset($_COOKIE['userid'])?$_COOKIE['userid']:null;
		$gsid=isset($_COOKIE['gsid'])?$_COOKIE['gsid']:null;
		$auth=isset($_COOKIE['auth'])?$_COOKIE['auth']:null;
	
		$groupnames=isset($_COOKIE['groupnames'])?$_COOKIE['groupnames']:null;
		
		$auth_=md5($salt.$userid.$groupnames.$salt.$login.$salt.$dispname.$salt.$gsid);
		$auth2_=md5($salt2.$userid.$groupnames.$salt2.$login.$salt2.$dispname.$salt2.$gsid);
				
		if (!isset($login)||(!hash_equals($auth,$auth_)&&!hash_equals($auth,$auth2_))||$auth===''||$auth===null) {
					
			$tail='';
			if (isset($_GET['keynav'])) $tail='?keynav';
					
			if (!$silent) header('location: login.php?from='.$_SERVER['PHP_SELF'].$tail); else {header('HTTP/1.0 403 Forbidden');header('X-STATUS: 403');die('.');}
			die();
		}
		
		if ($auth===$auth2_){
			setcookie('auth',$auth_,null,null,null,$usehttps,true);
		}

		
		return;	
	}

	
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

	$login=isset($_COOKIE['login'])?$_COOKIE['login']:null;
		
	if (1==SQLDASH_AUTH_MODE||2==SQLDASH_AUTH_MODE){
	
		$dispname=isset($_COOKIE['dispname'])?$_COOKIE['dispname']:null;
		$userid=isset($_COOKIE['userid'])?$_COOKIE['userid']:null;
		$gsid=isset($_COOKIE['gsid'])?$_COOKIE['gsid']:null;
		$auth=isset($_COOKIE['auth'])?$_COOKIE['auth']:null;
			
		$groupnames=isset($_COOKIE['groupnames'])?$_COOKIE['groupnames']:null;
		$salt2=$saltroot.$_SERVER['REMOTE_ADDR'].date('Y-m-j-H',time()-3600);
			
		$auth_=md5($salt.$userid.$groupnames.$salt.$login.$salt.$dispname.$salt.$gsid);
		$auth2_=md5($salt2.$userid.$groupnames.$salt2.$login.$salt2.$dispname.$salt2.$gsid);
			
		
		
		if (!isset($login)||!isset($auth)||(!hash_equals($auth,$auth_)&&!hash_equals($auth,$auth2_))) return array('groups'=>array());
		
		$info=array(
			'login'=>stripslashes($_COOKIE['login']),
			'dispname'=>$_COOKIE['dispname'],
			'userid'=>$_COOKIE['userid'],
			'gsid'=>$_COOKIE['gsid'],
			'groups'=>array()
		);	
		
		$groups=explode('|',($_COOKIE['groupnames']??''));
		foreach ($groups as $group) $info['groups'][$group]=true;
		
		return $info;

	}//auth	
	
		//check cookie authenticity
	
	$info=array(
		'login'=>$_COOKIE['login']??null,
	);	
	
	
	return $info;
}

function checkdbname(){
	global $db;
	global $SQL_ENGINE;
	
	$dbname=$_COOKIE['dbname'];
	$dbname=noapos($dbname);

	if ($SQL_ENGINE!='sfapi'){
		if (!isset($dbname)||$dbname=='') apperror('Select a database first');
	}

	
	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))) sql_select_db($db,$dbname);

	return $dbname;
}
function makegskey($verb,$groupnames=''){
	global $gsreqkey;
	global $_SERVER;
	global $_COOKIE;
	
	$user=userinfo();
	$userid=$user['userid'];
	
	$gsfrac=preg_replace('/[^A-Za-z0-9-]/','',$_COOKIE['gsfrac']);
		
	$key=md5($gsfrac.$gsreqkey.'_'.$userid.'_'.$verb.'_'.$_SERVER['REMOTE_ADDR'].'-'.$_SERVER['O_IP']);
	if ($groupnames!=''){
		$found=0;
		
		$gns=explode(',',$groupnames);
		foreach ($gns as $gn){
			if (trim($gn)!=''&&in_array(trim($gn),array_keys($user['groups']))) {$found=1;break;}
		}
		
		if (!$found) return '';
	}
	
	
	return $key;
}

function emitgskey($verb,$groupnames=''){
	echo makegskey($verb,$groupnames);	
}

function checkgskey($verb){
	global $gsreqkey;
	global $_SERVER;

	$user=userinfo();
	$userid=$user['userid'];	
		
	//$key=$_SERVER['HTTP_X_GSREQ_KEY'];
	$key=isset($_POST['X-GSREQ-KEY'])?$_POST['X-GSREQ-KEY']:'';

		
	$gsfrac=preg_replace('/[^A-Za-z0-9-]/','',$_COOKIE['gsfrac']);
	
	$key_=md5($gsfrac.$gsreqkey.'_'.$userid.'_'.$verb.'_'.$_SERVER['REMOTE_ADDR'].'-'.$_SERVER['O_IP']);
	if ($key!==$key_) apperror('gskey: request denied');
}
