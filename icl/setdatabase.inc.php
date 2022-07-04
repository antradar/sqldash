<?php

function setdatabase(){
	$dbname=GETSTR('dbname');
	
	setcookie('dbname',$dbname);	
}