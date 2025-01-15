<?php
include 'icl/reauth.inc.php';

function deluser(){
	$userid=GETVAL('userid');
	global $sdb;
	
	$user=userinfo();
	if (!$user['groups']['accounts']) die('Access denied');
		
	$gsid=intval($user['gsid']);
	
	$query="delete from users where gsid=$gsid and userid=$userid";
	$sdb->query($query);
	
	reauth();
}
