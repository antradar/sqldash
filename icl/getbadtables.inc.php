<?php

function getbadtables($res){
    $badtables=array();

    $looproot=&$res['query_block']['nested_loop'];
    if (!isset($looproot)) $looproot=&$res['query_block']['ordering_operation']['nested_loop'];
    if (isset($looproot)&&count($looproot)==0&&isset($res['query_block']['grouping_operation'])){
        $looproot=&$res['query_block']['grouping_operation']['nested_loop'];
    }
    
    if (!isset($looproot)) $looproot=&$res['nested_loop'];
    if (!isset($looproot)){
	 	if (isset($res['optimized_away_subqueries'])) { //todo: handle multiple
		 	$looproot=&$res['optimized_away_subqueries'][0]['query_block']['nested_loop'];
	 	}
    }
    
    if (!isset($looproot)&&isset($res['ordering_operation'])) $looproot=&$res['ordering_operation']['nested_loop'];
    if (!isset($looproot)&&isset($res['table'])) $looproot=&$res['table']['nested_loop'];
    
    if (!isset($looproot)) {
	 	echo 'Warn: failed to locate Explain loop root<br>';
	    return array();   
    }
    foreach ($looproot as $nloop){
        if (!isset($nloop['table'])) continue;
        if (!isset($nloop['table']['possible_keys'])||count($nloop['table']['possible_keys'])==0) {
            if (!isset($nloop['table']['key'])) array_push($badtables,$nloop['table']['table_name']);
        }
    }

	return $badtables;
}	