<?php

function setdatabase(){
	global $sdb;
	$dbname=GETSTR('dbname');
		
	setcookie('dbname',$dbname);

}