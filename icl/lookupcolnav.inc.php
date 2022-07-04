<?php

function lookupcolnav(){
	$queryidx=GETVAL('queryidx');
	
	$colnames=json_decode($_POST['colnames'],1);
	
?>
<div class="section">
	<?php foreach ($colnames as $name){
		$dname=noapos(htmlspecialchars($name));
	?>
	<div class="listitem"><a onclick="if (gid('queryview_<?php echo $queryidx;?>')&&gid('colbm_<?php echo $queryidx;?>_<?php echo $dname;?>')) gid('queryview_<?php echo $queryidx;?>').scrollLeft=gid('colbm_<?php echo $queryidx;?>_<?php echo $dname;?>').offsetLeft;"><?php echo htmlspecialchars($name);?></a></div>
	<?php
	}
	?>
</div>
<?php
	
}