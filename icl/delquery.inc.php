<?php

include 'icl/lookupsquery.inc.php';

function delquery(){
	global $sdb;

	$squeryid=GETVAL('squeryid');	
	
	$user=userinfo();
	$userid=$user['userid'];
	
	
	$query="delete from squeries where userid=$userid and squeryid=$squeryid ";
	$rs=$sdb->query($query);
	
	lookupsquery();
}
