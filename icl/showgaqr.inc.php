<?php

include_once 'base32.php';
include_once 'encdec.php';
include 'makeslug.php';

function showgaqr($userid){
	global $sdb;
	global $codepage;
	global $dbsalt;
	
	$userid=intval($userid);
	
	$user=userinfo();
	
	$query="select usega,gakey,login from users where userid=$userid";
	$rs=$sdb->query($query);
	
	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	
	$enc_remote=0; //set $remote=1 in production
		
	$usega=$myrow['usega'];
	$gakey=$myrow['gakey'];
		
	if ($gakey!='') $gakey=decstr($gakey,GYROSCOPE_PROJECT.'gakey-'.$userid,$enc_remote); 
	$login=$myrow['login'];
	
	$gsproj_parts=explode(' ',GYROSCOPE_PROJECT);
	$gsproj=makeslug($gsproj_parts[0]);
	$dlogin=makeslug($login);
	
	$fresh=0;
	
	if ($gakey==''){
		$gakey=substr(encstr($dbsalt.$userid.time().rand(1,9999),$dbsalt),0,20);
		$dbgakey=encstr($gakey,GYROSCOPE_PROJECT.'gakey-'.$userid,$enc_remote); //use remote encryption key
		$query="update users set gakey='$dbgakey' where userid=$userid";
		$sdb->query($query);
		$fresh=1;
	}
			
	$secret=Base32::encode($gakey);
			
	$url="otpauth://totp/$gsproj-$dlogin?secret=$secret&issuer=$gsproj&digits=6&period=30";
	
	//echo $url;

	if (!$fresh){		
?>	
	<a class="hovlink" onclick="showhide('myaccount_gakeyview');">show QR setup code</a>
<?php
	} //fresh
?>

	<div style="display:none<?php if ($fresh) echo 'a';?>" id="myaccount_gakeyview">
<?php
	if (!$fresh&&$usega){
		
?>
	<div style="width:180px;text-align:center;padding-top:10px;">
		<a class="hovlink" onclick="resetgakey('<?php emitgskey('resetgakey');?>');">revoke</a>
	</div>
<?php
	}	
	
	/*
?>
	<img id="myqrcode" src="imgs/t.gif" style="background:#999999;" width="180">
<?php
	*/
?>
		<img id="myqrcode" src="<?php echo $codepage;?>?cmd=imgqrcode&data=<?php echo $url;?>" width="180">
	</div>
<?php	
			
}








