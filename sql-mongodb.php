<?php
$SQL_ENGINE='mongodb';

function sql_get_db($dbhost,$dbname,$dbuser,$dbpass){
	$dname='';
	if (isset($dbname)) $dname='/'.$dbname;
	$db=new MongoDB\Driver\Manager('mongodb://'.$dbhost.':27017'.$dname,array('username'=>$dbuser,'password'=>$dbpass));
	return $db;
}

function sql_prep($query,$db,$params=null){
	$dbname=isset($_COOKIE['dbname'])?$_COOKIE['dbname']:null;

	//$cmd=new MongoDb\Driver\Command(array('find'=>'cars'));
	//$rs=$db->executeCommand('cardb',$cmd);
	//echo '<pre>'; print_r($rs->toArray()); die();
	if (strtolower(trim($query))=='show databases'){
		$cmd=new MongoDb\Driver\Command(array('listDatabases'=>1));
		$rs=$db->executeCommand('admin',$cmd);
		return $rs->toArray()[0]->databases;

	}

	if (strtolower(trim($query))=='show tables'){
		$cmd=new MongoDb\Driver\Command(array('listCollections'=>1));
		$rs=$db->executeCommand($dbname,$cmd);
		return $rs;
	}
	
	if (preg_match("/show\s*tables\s*like\s*'(\S+?)'/",trim($query),$matches)){
		$keyword=trim($matches[1],'%');
		$cmd=new MongoDb\Driver\Command(array('listCollections'=>1,'filter'=>array('name'=>array('$regex'=>$keyword,'$options'=>'i'))));
		$rs=$db->executeCommand($dbname,$cmd);
		return $rs;
	}	

	if (preg_match('/^describe \S+$/',$query)){
		return array();
	}

	$obj=@json_decode(trim($query),1);
	if (isset($obj)){
		if (array_key_exists('cursor',$obj)&&!isset($obj['cursor'])) $obj['cursor']=new stdClass;
		$cmd=new MongoDb\Driver\Command($obj);
		$res=$db->executeCommand($dbname,$cmd);
		//echo '<pre>'; print_r($res->toArray()); echo '</pre>'; die();
		return $res->toArray();
	}

	echo "Unparsed query: $query <hr>";
	return array();


}
function sql_affected_rows($db,$rs){
	return count($rs);
}
function sql_query($query,$db){
	return sql_prep($query,$db);
}

function sql_fetch_assoc(&$rs,$assoc=0){
	if (is_object($rs)) $rs=$rs->toArray();
	if (count($rs)==0) return false;
	$robj=json_decode(json_encode(array_shift($rs)),1);
	$obj=array();
	$idx=0;
	if (!$assoc) return $robj;
	foreach ($robj as $k=>$v){
		$dv=$v;
		if ($k=='_id') $dv=$v['$oid'];
		$obj[$idx]=$dv;
		$obj[$k]=$v;
		$idx++;
	}
	//echo '<pre>'; print_r($obj); echo '</pre><hr>';
	return $obj;
}

function sql_fetch_array(&$rs){
	return sql_fetch_assoc($rs,1);
}

