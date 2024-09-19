<?php

include 'icl/showconn.inc.php';

function updateconn(){
	global $sdb;

	$connid=GETVAL('connid');

	$connname=QETSTR('connname');
	$conntype=QETSTR('conntype');
	$connhost=QETSTR('connhost');
	$conndbname=QETSTR('conndbname');
	$connapiport=QETSTR('connapiport');
	$connuser=QETSTR('connuser');
	$connpass=SQET('connpass'); $connpass=encstr($connpass,SQLDASH_DB_TOKEN);

	$user=userinfo();
	$gsid=$user['gsid'];
	
	if (!isset($user['groups']['connedit'])) apperror('access denied');
	
	checkgskey('updateconn_'.$connid);


	$query="update conns set connname='$connname',conntype='$conntype',connhost='$connhost',conndbname='$conndbname',connapiport='$connapiport',connuser='$connuser',connpass='$connpass' where connid=$connid";
	$sdb->query($query);


	showconn($connid);
}
