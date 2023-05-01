<?php

include 'icl/showuser.inc.php';
include 'icl/reauth.inc.php';

function updateuser(){
	global $userroles;
	global $userrolelocks;
	global $dbsalt;
	
	$user=userinfo();
	if (!$user['groups']['accounts']) apperror('Access denied');
	
	$myuserid=$user['userid'];
	
	$userid=GETVAL('userid');	
	$login=GETSTR('login');
	$active=GETVAL('active');
	$virtual=GETVAL('virtual');
	$passreset=GETVAL('passreset');

	$newpass=SQET('pass');


		
	$groupnames=GETSTR('groupnames');
	global $sdb;

	$query="select * from users where login='$login' and userid!=$userid";
	$rs=$sdb->query($query);
	if ($myrow=$rs->fetchArray(SQLITE3_ASSOC)){
		header('apperror: User already exists. Use a different login.');die();		
	}

	$query="select groupnames,virtualuser from users where userid=$userid";
	$rs=$sdb->query($query);
	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	
	$mygroupnames=array_flip(explode('|',$myrow['groupnames']));
	$lastvirtual=$myrow['virtualuser'];
				
	$gnames=explode('|',$groupnames);
	foreach ($gnames as $idx=>$gname){
		if (!isset($userroles[$gname])) unset($gnames[$idx]);
		if (in_array($gname,$userrolelocks)){
			if (!isset($user['groups'][$gname])&&!isset($mygroupnames[$gname])) unset($gnames[$idx]);
		}
	}

	foreach ($mygroupnames as $mygroupname=>$label){
		if ($mygroupname=='') continue;
		if (!isset($user['groups'][$mygroupname])&&in_array($mygroupname,$userrolelocks)&&!in_array($mygroupname,$gnames)){
			array_push($gnames,$mygroupname);	
		}
	}
			
	$groupnames=implode('|',$gnames);
		
	if ($virtual){
		$groupnames='users';
		$passreset=0;	
	}


	$query="update users set login='$login',active=$active, virtualuser=$virtual, passreset=$passreset, groupnames='$groupnames' ";
	if (!$virtual&&$newpass!='') {
		$np=password_hash($dbsalt.$newpass,PASSWORD_DEFAULT,array('cost'=>PASSWORD_COST));
		$query.=", password='$np' ";
	}


	$query.=" where userid=$userid";
	$sdb->query($query);


	reauth();
	showuser($userid);
	
}
