<?php
$SQL_ENGINE='sfapi';

function sql_get_db($dbhost,$dbname,$dbuser){
    return array('raw'=>'static_client');
}

function sql_query($query,$db){
	return sql_prep($query,$db);
}

function sql_prep($query,$db,$params=null){
    $connid=intval($_COOKIE['connid']);
    global $sdb;
    $squery="select appdomain,appclientid,appsecret,apptoken from conns where connid=$connid";
    $rs=$sdb->query($squery);
    $myrow=$rs->fetchArray(SQLITE3_ASSOC);
    $appdomain=$myrow['appdomain'];
    $appclientid=$myrow['appclientid'];
    $appsecret=$myrow['appsecret'];
    $apptoken=$myrow['apptoken'];
    
	$token=sql_salesforce_gettoken($apptoken,$appclientid,$appsecret,$appdomain);
    
    $pquery=strtolower(trim($query)); //for parsing
    if (preg_match('/^show\s*tables/',trim($pquery))){
        $pquery=preg_replace('/^show\s*tables/i','SELECT QualifiedApiName, IsCustomSetting, IsCustomizable FROM EntityDefinition ',trim($query));

        $obj=sql_salesforce_query($appdomain,$token,$pquery);
        
        $tables=$obj['records'];
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
		$fields=sql_sfdx_schema($matches[1],$token,$appclientid,$appsecret,$appdomain);
		return array('records'=>$fields);
	}
	
	if (preg_match('/select\s+count\(\*?\)\s+as\s+c\s+from\s+\(([\S\s]+)(?U)\)\s+count_query/i',$query,$matches)){
		$subquery=$matches[1];
		$query=preg_replace('/select [\S\s]+?from /i','select count() from ',$subquery);
		
	}

	if (preg_match('/^select\s+\*\s+from\s+(\S+)/i',$query,$matches)){
		$fields=sql_sfdx_schema($matches[1],$token,$appclientid,$appsecret,$appdomain);
		$fs=array();
		foreach ($fields as $field) array_push($fs,$field['Field']);
		$fieldlist=implode(', ',$fs);
		$query=preg_replace('/^select\s+\*/','select '.$fieldlist,$query);
		
	}

    $obj=sql_salesforce_query($appdomain,$token,$query);
	
    if (isset($obj['done'])){
        return $obj;
    }
    
    //echo '<pre>'; print_r($obj); echo '</pre>';
    
    if (isset($obj)&&isset($obj['name'])&&isset($obj['message'])){
	 	echo $obj['name'].": ".$obj['message']." ";
	    return;   
    }

    return array(); //empty filler

}

function sql_sfdx_schema($table,$token,$appclientid,$appsecret,$appdomain){
	
	
	//$cmd='sfdx force:schema:sobject:describe -s '.$table.' --json -o '.$org.' 2>&1';
		

	$ckey='sdfx_schema_'.md5($table.$token.$appclientid.$appsecret.$appdomain);
	$obj=cache_get($ckey);
	if (!$obj){
		$obj=sql_salesforce_request($appdomain,$token,'sobjects/'.$table.'/describe');
		cache_set($ckey,$obj,300);
	} else {
		//echo 'Using cached schema<br>';	
	}
	
	$rawfields=$obj['fields'];
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


function sql_salesforce_gettoken($rtoken,$clientid,$secret,$domain){

	$mkey='sqldash_sftoken_'.md5($rtoken.':'.$clientid.':'.$secret.':'.$domain);
	
	if (is_callable('cache_get')){
		$token=cache_get($mkey);
		if (isset($token)&&$token!='') {
			return $token;
		}
	}
	
	$url='https://'.$domain.'/services/oauth2/token';
	$curl=curl_init($url);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
	curl_setopt($curl,CURLOPT_POST,1);
	curl_setopt($curl,CURLOPT_POSTFIELDS,'grant_type=refresh_token&client_id='.$clientid.'&client_secret='.$secret.'&refresh_token='.$rtoken);
	curl_setopt($curl,CURLOPT_HTTPHEADER,array(
		'Accept: application/json'
	));
	
	$res=curl_exec($curl);
	

	$obj=json_decode($res,1);
	$token=$obj['access_token'];
	
	if ($token!=''){
		cache_set($mkey,$token,3600);
	}

	return $token;

}

function sql_salesforce_request($domain,$token,$method,$data=null,$hmethod=null){
	global $salesforce_domain;

	$method=trim($method,'/');
	$url='https://'.$domain.'/services/data/v55.0/'.$method;

	$curl=curl_init($url);

	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
	//curl_setopt($curl,CURLOPT_VERBOSE,1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);

	curl_setopt($curl,CURLOPT_HTTPHEADER,array(
		'Accept: application/json',
		'Authorization: Bearer '.$token,
		'Content-Type: application/json'
	));
	
	if (isset($data)){
		if (!isset($hmethod)) curl_setopt($curl,CURLOPT_POST,1);
		else curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $hmethod);
		curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
	} else {
		if (isset($hmethod)) curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $hmethod);
	}

	$res=curl_exec($curl);

	$obj=json_decode($res,1);
	if (!$obj) $obj=array('error'=>'not json','raw'=>$res);

	return $obj;
	
}

function sql_salesforce_query($domain,$token,$query){
	return sql_salesforce_request($domain,$token,'query/?q='.rawurlencode($query));
}
