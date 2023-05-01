<?php

function reauth(){
	
	global $sdb;
	global $salt;
	global $wssecret;
	global $usehttps;
	
	$user=userinfo();
	$userid=intval($user['userid']);
	
	$gsexpiry=0;
	$gstier=0;
	
	//every portalized table should have its own gsexpiry and gstier
	
	$query="select login,dispname,active,virtualuser,groupnames from users where userid=$userid";
	$rs=$sdb->query($query);
			
	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	
	
	$login=$myrow['login'];
	$dispname=$myrow['dispname'];
	
	$active=$myrow['active'];
	$virtual=$myrow['virtualuser']??0;
	
	$groupnames=$myrow['groupnames'];
	$auth=md5($salt.$userid.$groupnames.$salt.$login.$salt.$dispname);
		
	
	//$wsskey=md5($wssecret.$gsid.date('Y-n-j-H').$userid).'-'.$gsid.'-'.$userid;
	
	if (!$active||$virtual){
		setcookie('userid',NULL,time()-3600,null,null,$usehttps,true);
		setcookie('gsid',NULL,time()-3600,null,null,$usehttps,true);
		setcookie('gsexpiry',NULL,time()-3600,null,null,$usehttps,true);
		setcookie('gstier',NULL,time()-3600,null,null,$usehttps,true);
		setcookie('login',NULL,time()-3600,null,null,$usehttps,true);
		setcookie('dispname',NULL,time()-3600,null,null,$usehttps,true);		
		setcookie('auth',NULL,time()-3600,null,null,$usehttps,true);
		setcookie('groupnames',NULL,time()-3600,null,null,$usehttps,true);		
	} else {
		//header('wsskey:'.$wsskey);
		setcookie('auth',$auth,null,null,null,$usehttps,true);
		setcookie('userid',$userid,null,null,null,$usehttps,true);
		setcookie('login',$login,null,null,null,$usehttps,true);
		setrawcookie('dispname',rawurlencode($dispname),null,null,null,$usehttps,true);
		setcookie('groupnames',$groupnames,null,null,null,$usehttps,true);
	}
}
