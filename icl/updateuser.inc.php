<?php

include 'icl/showuser.inc.php';
include 'icl/reauth.inc.php';

function updateuser(){
	global $userroles;
	global $dbsalt;
	
	$user=userinfo();
	if (!$user['groups']['accounts']) die('Access denied');
	
	$userid=GETVAL('userid');	
	$login=GETSTR('login');
	$active=GETVAL('active');
	$virtual=GETVAL('virtual');
	$passreset=GETVAL('passreset');

	$newpass=SQET('pass');
	$np=password_hash($dbsalt.$newpass,PASSWORD_DEFAULT,array('cost'=>PASSWORD_COST));


		
	$groupnames=GETSTR('groupnames');
	
	if ($virtual){
		$groupnames='users';
		$passreset=0;	
	}

	global $sdb;

	$query="select * from users where login='$login' and userid!=$userid";
	$rs=$sdb->query($query);
	if ($myrow=$rs->fetchArray(SQLITE3_ASSOC)){
		header('apperror: User already exists. Use a different login.');die();		
	}

	$query="update users set login='$login',active=$active, virtualuser=$virtual, passreset=$passreset, groupnames='$groupnames' ";
	if (!$virtual&&$newpass!='') $query.=", password='$np' ";


	$query.=" where userid=$userid";
	$sdb->query($query);


	reauth();
	showuser($userid);
	
}
