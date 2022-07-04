<?php
include_once 'encdec.php';

$sqlmode=$_COOKIE['sqlmode'];
if ($_GET['sqlmode']=='sqlsrv'||$_POST['sqlmode']=='sqlsrv') $sqlmode='sqlsrv';
if ($_GET['sqlmode']=='clickhouse'||$_POST['sqlmode']=='clickhouse') $sqlmode='clickhouse';

switch ($sqlmode){
	case 'clickhouse': include_once 'sql-clickhouse.php'; break;
	case 'sqlsrv': include_once 'sql-sqlsrv.php'; break;
	default: include_once "sql-mysqli.php";
}


// use socket instead of TCP if the database is on the same, Linux server
// $db=sql_get_db(':/var/run/mysqld/mysqld.sock','gyrostart','root','mnstudio');

