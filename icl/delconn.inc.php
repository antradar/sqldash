<?php

function delconn(){
	$connid=SGET('connid');
	global $sdb;
	$user=userinfo();

	if (!isset($user['groups']['connedit'])) apperror('access denied');
		
	checkgskey('delconn_'.$connid);	
	
	$query="delete from conns where connid=$connid";
	$sdb->query($query);
	
}
