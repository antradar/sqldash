<?php
include 'bcrypt.php';
include 'passtest.php';

function setaccountpass(){
	global $dbsalt;
	global $sdb;
	
	$user=userinfo();
	$userid=$user['userid'];
	
	
	$usega=GETVAL('usega');
	$useyubi=GETVAL('useyubi');
	$yubimode=GETVAL('yubimode');
	$yubimode=0; //cannot be optional
	
	$darkmode=GETVAL('darkmode');
	
	//set useyubi to 0 if no devices are enrolled
	$query="select count(*) as kcount from yubikeys where userid=$userid";
	$rs=$sdb->query($query);
	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	$kcount=$myrow['kcount'];
	if (!$kcount) $useyubi=0;	

	$rawpass=$_POST['pass'];
	
	if ($_POST['oldpass']!=''){
	//	$passcheck=passtest($rawpass);
	//	if ($passcheck['grade']==0) apperror('A weak password cannot be used.');	
	}
	

	$query="select * from users where userid=$userid";
	$rs=$sdb->query($query);
	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	
	if ($_POST['oldpass']!=''&&!password_verify($dbsalt.$_POST['oldpass'],$myrow['password'])) die('invalid password');

	$params=array();
	$query="update users set ";
	if ($_POST['oldpass']!='') {
		$pass=password_hash($dbsalt.$_POST['pass'],PASSWORD_DEFAULT,array('cost'=>PASSWORD_COST));		
		$query.=" password='$pass', passreset=0, ";
	}
	$query.=" usega=$usega, useyubi=$useyubi, yubimode=$yubimode, darkmode=$darkmode where userid=$userid";
	
	$sdb->query($query);

	if ($_POST['oldpass']=='') echo 'Account settings updated'; else tr('password_changed'); 
}