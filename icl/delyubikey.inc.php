<?php

include 'icl/listyubikeys.inc.php';

function delyubikey(){
	global $sdb;

	$user=userinfo();
	$userid=$user['userid'];
	
	$keyid=GETVAL('keyid');

	$query="delete from yubikeys where keyid=$keyid and userid=$userid";
	$sdb->query($query);		
		
	listyubikeys();
}
