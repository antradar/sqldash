<?php

include 'libcbor.php';
include 'icl/listyubikeys.inc.php';

function addyubikey(){
	global $sdb;
	global $saltroot;

	$user=userinfo();
	$userid=$user['userid'];
		
	$attid=SQET('id');
	$clientdata=SQET('clientdata');
	$rawatt=strtr(SQET('att'),' ','+');
	$att=base64_decode($rawatt);
	
	$clientobj=json_decode($clientdata,1);	
	//echo '<pre>'; print_r($clientobj); echo '</pre>';
	
	$challenge=base64_decode($clientobj['challenge']);
	$challenge_=sha1($userid.$saltroot);

	if ($challenge!=$challenge_) apperror('Invalid credential challenge');
	
	$offset=0;
	$dec=cbor_decode($att,$offset);
	
	//echo '<pre>'; print_r($dec); echo '</pre>';
	
	$fmt=$dec['fmt'];	
	//if ($fmt!='none'&&$fmt!='packed') apperror('This authentication format ['.$fmt.'] is not supported');
	
	$authflags=$dec['authData']['flags'];
	$attestdata=$dec['authData']['attestdata'];
	
	if ($authflags['userpresent']!=1) apperror('User must be present'); //||$authflags['userverified']!=1
	if ($authflags['attested']!=1||!isset($attestdata)) apperror('Missing attestation data');
	
	$credid=$attestdata['credid'];
	$credkey=$attestdata['credkey'];
	
	$kty=isset($credkey['kty'])?$credkey['kty']:null;
	$alg=isset($credkey['alg'])?$credkey['alg']:null;
	$crv=isset($credkey['crv'])?$credkey['crv']:null; //ec
	$x=isset($credkey['x'])?$credkey['x']:null; //ec
	$y=isset($credkey['y'])?$credkey['y']:null; //ec
	$n=isset($credkey['n'])?$credkey['n']:null; //rsa
	$e=isset($credkey['e'])?$credkey['e']:null; //rsa
	
	$keyname=substr($attid,0,8);
		
	$query="insert into yubikeys (userid,keyname,attid,credid,kty,alg,crv,x,y,n,e) values ($userid,'$keyname','$attid','$credid','$kty','$alg','$crv','$x','$y','$n','$e')";
	$sdb->query($query);
		
	listyubikeys();
}
