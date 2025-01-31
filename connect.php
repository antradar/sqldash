<?php
include_once 'encdec.php';
include_once 'forminput.php';

if (1==SQLDASH_AUTH_MODE||2==SQLDASH_AUTH_MODE) $sdb=new SQLite3(SQLDASH_USERDB_PATH);


$sqlmode=isset($_COOKIE['sqlmode'])?$_COOKIE['sqlmode']:'';

if (isset($_GET['conntype'])) $_GET['sqlmode']=$_GET['conntype'];
if (isset($_POST['conntype'])) $_POST['sqlmode']=$_POST['conntype'];

if (SGET('sqlmode')=='mysqli'||SQET('sqlmode')=='mysqli') $sqlmode='mysqli';
if (SGET('sqlmode')=='sqlsrv'||SQET('sqlmode')=='sqlsrv') $sqlmode='sqlsrv';
if (SGET('sqlmode')=='clickhouse'||SQET('sqlmode')=='clickhouse') $sqlmode='clickhouse';
if (SGET('sqlmode')=='mongodb'||SQET('sqlmode')=='mongodb') $sqlmode='mongodb';
if (SGET('sqlmode')=='sfdx'||SQET('sqlmode')=='sfdx') $sqlmode='sfdx';
if (SGET('sqlmode')=='sfapi'||SQET('sqlmode')=='sfapi') $sqlmode='sfapi';
if (SGET('sqlmode')=='mysql-noprep'||SQET('sqlmode')=='mysql-noprep') $sqlmode='mysql-noprep';

switch ($sqlmode){
	case 'clickhouse': include_once 'sql-clickhouse.php'; break;
	case 'sqlsrv': include_once 'sql-sqlsrv.php'; break;
	case 'mongodb': include_once 'sql-mongodb.php'; break;
	case 'sfdx': include_once 'sql-sfdx.php'; break;
	case 'sfapi': include_once 'sql-sfapi.php'; break;
	case 'mysql-noprep': include_once 'sql-mysqli.php'; $SQL_ENGINE='MySQL-noprep'; break;
	default: include_once "sql-mysqli.php";
}

// use socket instead of TCP if the database is on the same, Linux server
// $db=sql_get_db(':/var/run/mysqld/mysqld.sock','gyrostart','root','mnstudio');

