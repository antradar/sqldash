<?php

function delconn(){
	$connid=SGET('connid');
	global $sdb;
	$user=userinfo();
	
	checkgskey('delconn_'.$connid);	
	
	$query="delete from conns where connid=$connid";
	$sdb->query($query);
	
}
