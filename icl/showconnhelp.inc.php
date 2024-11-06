<?php

function showconnhelp(){
	
	$conntype=SGET('conntype');

	$appdomain=SQET('appdomain');
	$appclientid=SQET('appclientid');
	$appsecret=SQET('appsecret');
	$appredirurl=SQET('appredirurl');
	
	global $conntypes;
	
	if (!isset($conntypes[$conntype])) apperror('Invalid connection type');
		

	$fn='conntemplates/connhelp-'.$conntype.'.txt';
	
	if (!file_exists($fn)) apperror('Invalid template');
	
	$c=file_get_contents($fn);
	
	$repls=array(
		'appdomain'=>$appdomain,
		'appclientid'=>$appclientid,
		'appsecret'=>$appsecret,
		'appredirurl'=>$appredirurl
	);
	
	foreach ($repls as $k=>$v) $c=str_replace('%%'.$k.'%%',$v,$c);
	
?>
<div class="section">

	<textarea class="inplong"><?php echo htmlspecialchars($c);?></textarea>

</div>
<?php	
}