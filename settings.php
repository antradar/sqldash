<?php

$sqlite_root='/opt/sqlite/';

include 'lang.php';

include 'connect.php';
include 'auth.php';

$codepage='myservices.php';
$fastlane='phpx-services.php'; //change this name if HAProxy is set up to route by filename to a dedicated server

//define constants that are shared by both front- and back-end code
//repeat the settings in settings.tmpl.php


$userroles=array(
	'admins'=>'standard admin rights',
	'accounts'=>_tr('rights_accounts')
);



$user=userinfo();


$toolbaritems=array(
'core.users'=>array('title'=>'Users','icon'=>'','modversion'=>'78','lockdown'=>1),
'sqldash.databases'=>array('title'=>'Databases','icon'=>'img-databases','modversion'=>'1'),
'sqldash.tables'=>array('title'=>'Tables','icon'=>'img-tables','modversion'=>'1'),
'sqldash.sqlite'=>array('title'=>'SQLite','icon'=>'img-sqlite'),	
'core.reports'=>array('title'=>'Reports','icon'=>'img-reports','modversion'=>'78','lockdown'=>1,'groups'=>'admins'),
);

$sqlmode=isset($_COOKIE['sqlmode'])?$_COOKIE['sqlmode']:'';



if ($sqlmode=='mysqli'){
	$toolbaritems['ghostsql']=array('title'=>'GhostSQL','icon'=>'img-ghost');	
}



foreach ($toolbaritems as $idx=>$item) if (!$item) unset($toolbaritems[$idx]);
