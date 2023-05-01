<?php

include 'icl/showconn.inc.php';

function updateconn(){
	$connid=GETVAL('connid');

	$connname=SQET('connname');
	$conntype=SQET('conntype');
	$connhost=SQET('connhost');
	$conndbname=SQET('conndbname');
	$connapiport=SQET('connapiport');
	$connuser=SQET('connuser');
	$connpass=SQET('connpass');


	global $db;
	$user=userinfo();
	$gsid=$user['gsid'];
	
	if (!isset($user['groups']['connedit'])) apperror('access denied');
	
	checkgskey('updateconn_'.$connid);


	$query="update conns set connname=?,conntype=?,connhost=?,conndbname=?,connapiport=?,connuser=?,connpass=? where connid=?";
	sql_prep($query,$db,array($connname,$conntype,$connhost,$conndbname,$connapiport,$connuser,$connpass,$connid));


	


	showconn($connid);
}
