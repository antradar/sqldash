<?php
include 'icl/reauth.inc.php';

function authpump(){
	if (1==SQLDASH_AUTH_MODE||2==SQLDASH_AUTH_MODE) reauth();
	$hb=$_GET['hb'];
	$ret=preg_replace('/[^\d]/','',$hb);
	if (strlen($ret)>40) $ret=substr($ret,0,40);
	echo $ret;
	die();
}
