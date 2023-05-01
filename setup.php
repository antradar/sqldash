<?php

if (!defined('STDIN')){
	header('Location: ./');
	die();
}

echo "
SQL Dash Setup
==============

";

function prompt($prompt,$opts=null,$def=null,$validate_func=null,$passmask=0){
	$dprompt=$prompt;
	$dopts=array();
	if (isset($opts)){
		foreach ($opts as $k=>$v) array_push($dopts,$k.'-'.$v);
		$dprompt.=' ('.implode(', ',$dopts).')';
	}
	
	if ($def!='') $dprompt.='['.$def.']';
	
	do{
		if (!$passmask){
			$res=trim(readline($dprompt.': '));
		} else {
			if (trim(shell_exec("bash -c 'echo OK'"))!='OK'){
				die("  cannot obtain password from the terminal in a safe manner.\r\n");	
			}
			$res=trim(shell_exec("bash -c 'read -s -p \"".addslashes($dprompt.'[*]:')."\" mypass && echo \$mypass'"));
			echo "\r\n";
		}
		if ($res==''&&isset($def)) return $def;
				
		if (isset($opts)&&!in_array($res,array_keys($opts))){
			if ($res!='') echo "  Incorrect selection\r\n\r\n";	
		} else {
			if ($res!=''&&(!isset($validate_func)||$validate_func($res))) return $res;	
		}
		
	} while (1);

}

//todo: check existing settings


$authmode=prompt('Auth Mode',array(
	0=>'Development',
	1=>'Production'
),1);

if ($authmode==0) {
	$config=file_get_contents('config.seed');
	$repl=array(
		'authmode'=>$authmode,
		'authtoken'=>'',
		'dbtoken'=>'',
		'userdbpath'=>''
	);
	
	foreach ($repl as $k=>$v){
		$config=str_replace("%%$k%%",$v,$config);	
	}
	
	file_put_contents('config.php',$config);
		
	die("\r\n");
}

if ($authmode==1){
	if (!class_exists('SQLite3')){
		echo "\r\n  Managed accounts mode (Secure) requires the SQLite3 extension, which is not made available in this environment.\r\n\r\n";
		die();	
	}
	//todo: check existing settings
	$authsalt=prompt('Enter a random session salt (min. 16 chars)',null,null,function($res){
		if (strlen($res)<16) {echo "  Salt length too short. Must be at least 16 characters\r\n"; return false;}
		return true;
	});
	
	$dbsalt=prompt('Enter a random DB salt (min. 32 chars)',null,null,function($res) use ($authsalt){
		if (strlen($res)<32) {echo "  Salt length too short. Must be at least 32 characters\r\n"; return false;}
		if ($authsalt==$res) {echo "  DB salt cannot be the same as Session salt\r\n"; return false;}
		return true;
	});
	
	
	$login=prompt('Starter user login',null,'admin');
	$pass=prompt('Starter user password',null,null,null,1);
	$_=prompt('Retype password',null,null,function($res) use ($pass){
		if ($pass!=$res) {echo "  Passwords mismatch.\r\n"; return false;}
		return true;	
	},1);
	
	// /opt/sqllite/sqldash.db
	
	$dbfn=prompt('Full path for the user db',null,'/opt/writable/sqldash.db',function($res){
		if (strpos($res,"'")!==false) {echo "  Invalid file name.\r\n"; return false;}
		//return true; //for now
		if (file_exists($res)) {echo "  File already exists. Delete the .db file before proceeding.\r\n"; return false;}
		return true;
	});
	
	if (file_exists($dbfn)) unlink($dbfn);
	$db=new SQLite3($dbfn);
	
	$query="
	create table users (
		userid integer primary key,
		gsid integer,
		login text,
		dispname text,
		active integer,
		virtualuser integer,
		quicklist integer,
		darkmode integer,
		password text,
		passreset integer,
		groupnames text,
		usega integer,
		gakey text,
		useyubi integer,
		yubimode integer
	)";
	
	$rs=$db->query($query);
	
	$login=addslashes($login);
	$groupnames='users|admins|accounts|insert|delete|alter|create|drop';

	$np=password_hash($dbsalt.$pass,PASSWORD_DEFAULT,array('cost'=>12));
			
	$query="
	insert into users (gsid,login,dispname,active,virtualuser,quicklist,darkmode,password,groupnames,usega,useyubi) values (1,'$login','$login',1,0,1,0,'$np','$groupnames',0,0)
	";
	$rs=$db->query($query);
	
	$query="
	create table yubikeys (
		keyid integer primary key,
		userid integer,
		passless integer,
		keyname text,
		credid text,
		kty integer,
		alg integer,
		crv integer,
		x text,
		y text,
		n text,
		e text,
		attid text,
		lastsigncount integer
	)";
	
	$rs=$db->query($query);
	
	$query="
	create table conns (
		connid integer primary key,
		userid integer,
		connname text,
		conntype text,
		connhost text,
		connuser text,
		connpass text,
		connapiport text,
		conndbname text
	)";
	
	$rs=$db->query($query);	

	
	chmod($dbfn,0777);
	
	$config=file_get_contents('config.seed');
	$repl=array(
		'authmode'=>$authmode,
		'authtoken'=>$authsalt,
		'dbtoken'=>$dbsalt,
		'userdbpath'=>$dbfn
	);
	
	foreach ($repl as $k=>$v){
		$config=str_replace("%%$k%%",$v,$config);	
	}
	
	file_put_contents('config.php',$config);
	
	$ppath=str_replace('/'.basename($dbfn),'/',realpath($dbfn));
	echo "\r\nRemember to chmod $ppath so that it is also writable\r\n\r\n";

		
}//authmode==1

