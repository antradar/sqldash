<?php

include 'lb.php';
include 'lang.php';
include 'forminput.php';
include 'bcrypt.php';

include 'gsratecheck.php';

if (isset($usehttps)&&$usehttps) include 'https.php'; 
include 'connect.php';
include 'auth.php';
include 'xss.php';


$salt2=$saltroot.$_SERVER['REMOTE_ADDR'].'-'.$_SERVER['O_IP'].date('Y-m-j-H',time()-3600);

xsscheck();

$login=SQET('login');
$nopass=intval(SQET('nopass'));

if ($login==''){header('HTTP/1.0 403');die('.');}

header('gsfunc: ajx_2facheck');

list($rateok,$penalty)=gsratecheck_verify($_SERVER['REMOTE_ADDR'],$login);
if (!$rateok){
	header('prevalidation: too many login attempts');
	die();	
}

$dblogin=addslashes($login);

$query="select * from users where lower(login)=lower('$dblogin') and active=1 and (virtualuser=0 or virtualuser is null)";
$rs=$sdb->query($query);


$passok=0;

$federated=null;

if ($myrow=$rs->fetchArray(SQLITE3_ASSOC)){

	if ($nopass) $passok=1;
	else $passok=password_verify($dbsalt.$_POST['password'],$myrow['password']);
		
	$passreset=$myrow['passreset'];		
	
	$usega=$myrow['usega'];
	$gakey=$myrow['gakey'];
	
	if ($gakey=='') $usega=0;
	
	if ($passreset){
		$usega=0;
	}
	
	$userid=$myrow['userid'];
	
	$useyubi=$myrow['useyubi'];
	$yubimode=$myrow['yubimode'];		
	
} else {
	password_hash($dbsalt.time(),PASSWORD_DEFAULT,array('cost'=>PASSWORD_COST));
	
	//check with master server via a rpc call, build federated array if necessary
}


$tfas=array();
$foci=array();

if (!$passok){
	header('prevalidation: invalid credentials');
	die();
}

if ($passreset){
	header('prevalidation: reset bypass');
	die();	
}


if ($usega){
	array_push($tfas,'ga');
	array_push($foci,'gapin');
}

if ($useyubi&&$yubimode==0){
	array_push($tfas,'yubi');	
}

if (count($tfas)>0){
	header('tfas: '.implode(',',$tfas));
	if (count($foci)>0){
		header('focalpoint: '.$foci[0]);	
	}
}
