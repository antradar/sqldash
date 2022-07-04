<?
include 'lang.php';

include 'connect.php';
include 'auth.php';

$codepage='myservices.php';
$fastlane='phpx-services.php'; //change this name if HAProxy is set up to route by filename to a dedicated server

//define constants that are shared by both front- and back-end code
//repeat the settings in settings.tmpl.php


$userroles=array(
	'admins'=>'standard admin rights',
	'accounts'=>_tr('rights_accounts'),
	'upgrademods'=>'upgrade modules',
	'dbadmin'=>'db admin'	//uncomment this to reveal the dbadmin option for SQL Comp tool
);



$user=userinfo();


$toolbaritems=array(
'core.users'=>array('title'=>'Users','icon'=>'','modversion'=>'78','lockdown'=>1),
'sqldash.databases'=>array('title'=>'Databases','icon'=>'img-databases','modversion'=>'1'),
'sqldash.tables'=>array('title'=>'Tables','icon'=>'img-tables','modversion'=>'1'),
'core.reports'=>array('title'=>'Reports','icon'=>'img-reports','modversion'=>'78','lockdown'=>1,'groups'=>'admins'),
);


foreach ($toolbaritems as $idx=>$item) if (!$item) unset($toolbaritems[$idx]);
