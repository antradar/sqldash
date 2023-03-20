<?php
include 'icl/dashquerydims_nav.inc.php';

function dashquerydims(){
	
	$queryidx=GETVAL('queryidx');
	
	$mode=GETSTR('mode');
	
	$dims=json_decode(SQET('dims'),1);
	
	$query=SQET('query');
	$sqlmode=SQET('sqlmode');
	
	
	if ($mode!='embed'){		
	

		
	
?>
<div class="section navdash">
	<div class="sectiontitle">Query Explorer #<?php echo $queryidx;?></div>
	<input id="dashquerydims_<?php echo $queryidx;?>_chartrelay" type="hidden" onchange="nav_loadcharts('dashquerydims','dashquerydimkey','dashquerydims','<?php echo $queryidx;?>');">
	<div>
		<input id="dashquerydimkey_<?php echo $queryidx;?>" autocomplete="off" style="margin-bottom:0;display:none;" class="img-mg">
	</div>
	
	<div id="dashquerydims_<?php echo $queryidx;?>">	
<?php
	}//embed
	
	dashquerydims_shownavs('dashquerydims','dashquerydims');
?>

	
	<?php
	//echo '<pre>'; print_r($dims); echo '</pre>';
	
	if ($mode!='embed'){
	?>
</div>
	<?php
	}//embed

}