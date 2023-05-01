<?php
include 'config.php';

$usehttps=1; //enforcing HTTPS on production server, enable this on production server
$stablecf=0; //set to 1 when behind CloudFlare
$enableudf=0; //allow UDF editing, disable this on production server

date_default_timezone_set('America/Toronto');

define ('PASSWORD_COST',12);

$_SERVER['REMOTE_ADDR']=ip_strip_port($_SERVER['REMOTE_ADDR']??'');

$_SERVER['RAW_IP']=$_SERVER['REMOTE_ADDR'];
$_SERVER['O_IP']=$_SERVER['REMOTE_ADDR'];


if (isset($_SERVER['HTTP_X_REAL_IP'])) $_SERVER['REMOTE_ADDR']=ip_strip_port($_SERVER['HTTP_X_REAL_IP']);
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $_SERVER['REMOTE_ADDR']=ip_strip_port($_SERVER['HTTP_X_FORWARDED_FOR']);

if ($_SERVER['REMOTE_ADDR']==='::1') {
	$_SERVER['REMOTE_ADDR6']=ip_strip_port($_SERVER['REMOTE_ADDR']);
	$_SERVER['REMOTE_ADDR']='127.0.0.1';
}

if (isset($_SERVER['HTTP_X_REAL_IP'])) {
	$_SERVER['REMOTE_ADDR']=ip_strip_port($_SERVER['HTTP_X_REAL_IP']);
	$_SERVER['RAW_IP']=ip_strip_port($_SERVER['HTTP_X_REAL_IP']);
}
if (isset($_SERVER['HTTP_X_FORWARDED_SSL'])&&$_SERVER['HTTP_X_FORWARDED_SSL']==='on') $_SERVER['HTTPS']='on';
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
	$fparts=explode(',',ip_strip_port($_SERVER['HTTP_X_FORWARDED_FOR']));
	$_SERVER['REMOTE_ADDR']=$fparts[0];	
}

if (strpos(ip_strip_port($_SERVER['O_IP']),':')!==false&&ip_strip_port($_SERVER['O_IP'])!=='::1'){
	$ipparts=explode(':',ip_strip_port($_SERVER['O_IP']));
	$nipparts=array();
	$ipmax=4; $ipidx=0;
	while ($ipidx<$ipmax){
		array_push($nipparts,$ipparts[$ipidx]);
		$ipidx++;
	}
	$_SERVER['O_IP']=implode(':',$nipparts);	
}

if (isset($_SERVER['REMOTE_ADDR6'])&&($_SERVER['REMOTE_ADDR6']===$_SERVER['REMOTE_ADDR']||strpos($_SERVER['REMOTE_ADDR'],':')!==false)){
	$ipparts=explode(':',ip_strip_port($_SERVER['REMOTE_ADDR']));
	$nipparts=array();
	$ipmax=4; $ipidx=0;
	while ($ipidx<$ipmax){
		array_push($nipparts,$ipparts[$ipidx]);
		$ipidx++;
	}
	$_SERVER['REMOTE_ADDR6']=ip_strip_port($_SERVER['REMOTE_ADDR']);
	$_SERVER['REMOTE_ADDR']=implode(':',$nipparts);
}

if (isset($_SERVER['HTTP_GSXIP'])&&$_SERVER['HTTP_GSXIP']!='') $_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_GSXIP'];

if ($stablecf) $_SERVER['O_IP']=ip_strip_port($_SERVER['REMOTE_ADDR']);


if (trim($_SERVER['PHP_SELF'])=='') $_SERVER['PHP_SELF']=$_SERVER['SCRIPT_NAME'];


function ip_strip_port($ip){
	if (preg_match('/(\d+\.\d+\.\d+\.\d+):\d+/',$ip,$matches)) return $matches[1];
	if (preg_match('/\[(\S+?)\]\:\d+/',$ip,$matches)) return $matches[1];

	return $ip;		
}

include 'memcache.php'; //'memcache_stub.php'; 
cache_init();

