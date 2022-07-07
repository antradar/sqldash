<?php
include_once 'encdec.php';
include_once 'forminput.php';

$sqlmode=isset($_COOKIE['sqlmode'])?$_COOKIE['sqlmode']:'';
if (SGET('sqlmode')=='sqlsrv'||SQET('sqlmode')=='sqlsrv') $sqlmode='sqlsrv';
if (SGET('sqlmode')=='clickhouse'||SQET('sqlmode')=='clickhouse') $sqlmode='clickhouse';

switch ($sqlmode){
	case 'clickhouse': include_once 'sql-clickhouse.php'; break;
	case 'sqlsrv': include_once 'sql-sqlsrv.php'; break;
	default: include_once "sql-mysqli.php";
}


// use socket instead of TCP if the database is on the same, Linux server
// $db=sql_get_db(':/var/run/mysqld/mysqld.sock','gyrostart','root','mnstudio');

