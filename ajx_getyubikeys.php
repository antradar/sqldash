<?php

include 'lb.php';
include 'connect.php';
include_once 'forminput.php';

$login=GETSTR('login');
if ($login==''){header('HTTP/1.0 403');die('.');}


$query="select attid from yubikeys,users where yubikeys.userid=users.userid and useyubi=1 and login='$login'";
$rs=$sdb->query($query);

$attids=array();
while ($myrow=$rs->fetchArray(SQLITE3_ASSOC)) array_push($attids,$myrow['attid']);
echo implode(',',$attids); 