<?php
$SQL_ENGINE='sfdx';

function sql_get_db($dbhost,$dbname,$dbuser){
    return array('raw'=>$dbuser);
}

function sql_query($query,$db){
	return sql_prep($query,$db);
}

function sql_prep($query,$db,$params=null){
    $connid=intval($_COOKIE['connid']);
    global $sdb;
    $squery="select connuser from conns where connid=$connid";
    $rs=$sdb->query($squery);
    $myrow=$rs->fetchArray(SQLITE3_ASSOC);
    $org=$myrow['connuser'];

    $pquery=strtolower(trim($query)); //for parsing
    if (preg_match('/^show\s*tables /',trim($pquery))){
        $pquery=preg_replace('/^show\s*tables /i','SELECT QualifiedApiName, IsCustomSetting, IsCustomizable FROM EntityDefinition ',trim($query));

        //$cmd='sfdx data:query -q "SELECT count() FROM EntityDefinition " --json -o '.$org.' --json 2>&1';

        $cmd='sfdx data:query -q "'.$pquery.'" --json -o '.$org.' 2>&1';
        $res=shell_exec($cmd);
        $obj=json_decode($res,1);
        $tables=$obj['result']['records'];
        $res=array('records'=>array());
        foreach ($tables as $table){
            array_push($res['records'],array(
                $table['QualifiedApiName'],
                $table['IsCustomSetting'],
            ));
        }

        return $res;
    }

	if (preg_match('/^describe (\S+)$/',$query,$matches)){
		$fields=sql_sfdx_schema($matches[1],$org);
		return array('records'=>$fields);
	}
	
	if (preg_match('/select\s+count\(\*?\)\s+as\s+c\s+from\s+\(([\S\s]+)(?U)\)\s+count_query/i',$query,$matches)){
		$subquery=$matches[1];
		$query=preg_replace('/select [\S\s]+?from /i','select count() from ',$subquery);
		
	}

	if (preg_match('/^select\s+\*\s+from\s+(\S+)/i',$query,$matches)){
		$fields=sql_sfdx_schema($matches[1],$org);
		$fs=array();
		foreach ($fields as $field) array_push($fs,$field['Field']);
		$fieldlist=implode(', ',$fs);
		$query=preg_replace('/^select\s+\*/','select '.$fieldlist,$query);
		
	}

    $cmd='sfdx data:query -q "'.$query.'" --json -o '.$org.' 2>&1';
    $res=shell_exec($cmd); 
    $obj=json_decode($res,1);
    if (isset($obj['result'])){
        return $obj['result'];
    }
    
    //echo '<pre>'; print_r($obj); echo '</pre>';
    
    if (isset($obj)&&isset($obj['name'])&&isset($obj['message'])){
	 	echo $obj['name'].": ".$obj['message']." ";
	    return;   
    }

    return array(); //empty filler

}

function sql_sfdx_schema($table,$org){
	$cmd='sfdx force:schema:sobject:describe -s '.$table.' --json -o '.$org.' 2>&1';
	
	$ckey='sdfx_schema_'.md5($cmd);
	$res=cache_get($ckey);
	if (!$res){
		$res=shell_exec($cmd);
		cache_set($ckey,$res,300);
	} else {
		//echo 'Using cached schema<br>';	
	}
	$obj=json_decode($res,1);
	//echo '<pre>'; print_r($obj); echo '</pre>';
	//pretty_array($obj);
	$rawfields=$obj['result']['fields'];
	$fields=array();
	foreach ($rawfields as $field){
		$dkey=null;
		if ($field['filterable']||$field['groupable']||$field['sortable']) $dkey='MUL';
		if ($field['unique']) $dkey='UNI';
		if ($field['type']=='id') $dkey='PRI';
		array_push($fields,array(
			'Field'=>$field['name'],
			'Type'=>$field['type'],
			'Key'=>$dkey,
		));	
	}
	//echo '<pre>'; print_r($fields); echo '</pre>';
	return $fields;
}

function sql_fetch_assoc(&$rs,$assoc=0){
    if (!isset($rs['records'])) return false;
    //echo '<pre>'; var_dump($rs); echo '</pre>'; die();

    if (count($rs['records'])==0){
	    if (isset($rs['totalSize'])){
		    $ors=$rs;
		    $ors['c']=$rs['totalSize'];
		    unset($ors['totalSize']);
			unset($rs['totalSize']);
			unset($ors['records']);		    
		    return $ors;
	    } else return false;
    } else {
	    if (isset($rs['totalSize'])) unset($rs['totalSize']);
		$robj=json_decode(json_encode(array_shift($rs['records'])),1);
		if (isset($robj['attributes'])) unset($robj['attributes']);
	}
    
	return $robj;
}

function sql_fetch_array(&$rs){
	return sql_fetch_assoc($rs,1);
}

function sql_affected_rows($db,$rs){
	if (isset($rs['totalSize'])) return $rs['totalSize'];
	return count($rs);

}


