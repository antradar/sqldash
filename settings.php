<?php

$sqlite_root='/opt/sqlite/';
$profile_root='/opt/sqldash/';

include 'lang.php';

include 'connect.php';
include 'auth.php';

$codepage='myservices.php';
$fastlane='phpx-services.php'; //change this name if HAProxy is set up to route by filename to a dedicated server

$conntypes=array(
	'mysqli'=>'MySQLi',
	'mysql-noprep'=>'MySQL compat. No Prep.',
	'sqlsrv'=>'SQLSrv',
	'clickhouse'=>'ClickHouse/HTTP',
	'mongodb'=>'MongoDB',
	'sfdx'=>'Salesforce/CLI',
	'sfapi'=>'Salesforce Connected App API',
);

//plugins are detachable modules maintained by sqldash
if (file_exists('plugins/borg.php')) include 'plugins/borg.php';

//extensions are maintaned by 3rd parties

$sqldash_exts=array(
//	'antradar/querymaker'
);

//define constants that are shared by both front- and back-end code
//repeat the settings in settings.tmpl.php

$userroles=array(
	//'admins'=>'standard admin rights',
	'accounts'=>_tr('rights_accounts'),
	'connedit'=>'create and edit connections',
	'update'=>'update records',
	'insert'=>'insert records',
	'delete'=>'delete records',
	'alter'=>'alter tables',
	'create'=>'create tables',
	'rename'=>'rename tables',
	'truncate'=>'truncate collections',
	'drop'=>'drop tables',
	'kill'=>'terminate processes',
	'gsconfig'=>'manage team settings',
);

$userrolelocks=array_keys($userroles);


$user=userinfo();


$toolbaritems=array(
'codegen.conns'=>array('title'=>'Connections','icon'=>'img-conns'),
'sqldash.databases'=>array('title'=>'Databases','icon'=>'img-databases','modversion'=>'1'),
'sqldash.tables'=>array('title'=>'Tables','icon'=>'img-tables','modversion'=>'1'),
'sqldash.sqlite'=>array('title'=>'SQLite','icon'=>'img-sqlite'),
'sqldash.repos'=>array('title'=>'Snapshots','icon'=>'img-repos'),	
	'sqldash.repoarchives'=>array('title'=>'Snapshot Archives','icon'=>''),	
'core.settings'=>array('title'=>'Settings','icon'=>'img-settings','modversion'=>'78','lockdown'=>1),
	'core.users'=>array('title'=>'Users'),
'core.reports'=>array('title'=>'Reports','icon'=>'img-reports','modversion'=>'78','lockdown'=>1,'groups'=>'admins'),
);

if (!isset($sqlmode)) $sqlmode=isset($_COOKIE['sqlmode'])?$_COOKIE['sqlmode']:'';

if (1!=SQLDASH_AUTH_MODE&&2!=SQLDASH_AUTH_MODE){
	unset($toolbaritems['codegen.conns']);
	unset($toolbaritems['core.settings']);
}

//if ($sqlmode=='mysqli'){
	$toolbaritems['ghostsql']=array('title'=>'GhostSQL','icon'=>'img-ghost');	
//}


if (!isset($borg_repos)) unset($toolbaritems['sqldash.repos']);

foreach ($toolbaritems as $idx=>$item) if (!$item) unset($toolbaritems[$idx]);
