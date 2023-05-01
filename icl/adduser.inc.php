<?php

include 'icl/showuser.inc.php';

function adduser(){
	global $userroles;
	global $dbsalt;
	
	$user=userinfo();
	if (!$user['groups']['accounts']) die('Access denied');
		
	$login=GETSTR('login');
	$active=GETVAL('active');
	$virtual=GETVAL('virtual');
	$passreset=GETSTR('passreset');
	
	$dispname=GETSTR('dispname');
	
	$newpass=SQET('newpass');
	
	$np=password_hash($dbsalt.$newpass,PASSWORD_DEFAULT,array('cost'=>PASSWORD_COST));
		
	$groupnames=GETSTR('groupnames');	
	
	if ($virtual){
		$groupnames='users';
		$np=md5(time().rand(999,9999).'block');
		$passreset=0;
	}
	
	global $sdb;
	
	$query="select * from users where login like '$login'";
	$rs=$sdb->query($query);
	if ($myrow=$rs->fetchArray(SQLITE3_ASSOC)) apperror('User already exists. Use a different login.');
	
	$query="insert into users (login,dispname,active,virtualuser,passreset,groupnames,password) values ('$login','$dispname',$active,$virtual,'$passreset','$groupnames','$np') ";
	$rs=$sdb->query($query);
	
	$userid=$sdb->lastInsertRowID();

	if (!$userid) {
		header('apperror:Error creating User record');die();
	}
	
	
	header('newrecid:'.$userid);
	header('newkey:user_'.$userid);
	header('newparams:showuser&userid='.$userid);
	header("newloadfunc: reloadview('core.users','userlist');");
	
	showuser($userid);
}

