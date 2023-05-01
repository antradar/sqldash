<?php
include 'icl/reauth.inc.php';

function deluser(){
	$userid=GETVAL('userid');
	global $sdb;
	
	$user=userinfo();
	if (!$user['groups']['accounts']) die('Access denied');
		
	
	$query="delete from users where userid=$userid";
	$sdb->query($query);
	
	reauth();
}
