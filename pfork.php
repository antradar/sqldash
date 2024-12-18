<?php

function pfork($cmd,$cwd){
	$pipes=array();
	$fd=array();

	$p=proc_open($cmd,$fd,$pipes,$cwd);
	
	$status=proc_get_status($p);
	$pid=$status['pid'];

	return $pid;		
}
