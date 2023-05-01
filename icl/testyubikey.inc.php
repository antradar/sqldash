<?php
include 'libcbor.php';

function testyubikey(){
	
	global $sdb;
	
	$user=userinfo();
	$userid=$user['userid'];

	$attid=SQET('id');
	$clientdata=SQET('clientdata');
	$signature=strtr(SQET('signature'),' ','+');
	$clientauth=strtr(SQET('auth'),' ','+');
	
	//$clientobj=json_decode($clientdata,1);
	//echo '<pre>'; print_r($clientobj); echo '</pre>';
	
	$query="select * from yubikeys where userid=$userid and attid='$attid' ";
	$rs=$sdb->query($query);
	if (!$myrow=$rs->fetchArray(SQLITE3_ASSOC)){
		echo "Cannot find a key in the registry.";
		return;	
	}

	$keyid=intval($myrow['keyid']);
	$keyname=$myrow['keyname'];
	$kty=$myrow['kty'];
	$alg=$myrow['alg'];
	$crv=$myrow['crv']; $x=$myrow['x']; $y=$myrow['y'];
	$n=$myrow['n']; $e=$myrow['e'];
	
	$lastsigncount=$myrow['lastsigncount'];
	
	
	echo "Found matching device ".htmlspecialchars($keyname)."<br><br>";

	$newsigncount=0;
	$err='';
	$res=cbor_validate($kty,$alg,$crv,$x,$y,$n,$e,$clientdata,$clientauth,$signature,1,$lastsigncount,$newsigncount,$err);
	
	if ($res==1) {
		echo "Validated! =) &nbsp; #$lastsigncount";
		$query="update yubikeys set lastsigncount=$newsigncount where keyid=$keyid";
		$sdb->query($query);		
	} else echo "Authentication failed. =( ".$err;	
	
	
}
