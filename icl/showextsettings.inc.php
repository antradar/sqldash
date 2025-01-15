<?php

$ext=SGET('ext'); //todo: clean up input
if ($ext!='') {
	define('SQLDASH_NO_SUBCONNECT',1);
	include 'ext/'.$ext.'.ext.php'; //todo: check file existance
}

function showextsettings(){

	global $ext;
	
	//$exts=sqldash_getexts();
	
	global $allexts;
	$exts=$allexts;

	$user=userinfo();
	if (!isset($user['groups']['gsconfig'])) apperror('Access denied');
	
		
?>
<div class="section">
	<div class="sectiontitle">Team Settings: <?php echo $allexts['exts'][$ext]['name'];?></div>
	
	<div id="gsextsettings_<?php echo $ext;?>">
	<?php
	$func='ext_'.str_replace('/','_',$ext).'_gsconfig';
		
	$func();
	
	//echo '<pre>'; print_r($exts); echo '</pre>';
	
	?>
	</div>
	
</div>
<?php		
	
		
}
