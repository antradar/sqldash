<?php

function slite_listdbs(){
	global $sqlite_root;
?>
<script>gid('tooltitle').innerHTML='<a>SQLite DBs</a>';</script>
<?php
	if (!isset($sqlite_root)||$sqlite_root==''){
?>

<div class="section">
	<div class="warnbox">
		The "$sqlite_root" variable is not set.
	</div>
</div>

<?php		
		return;
	}
	
?>
<div class="section">
<?php
	$dh=opendir($sqlite_root);
	$files=array();
	while ($file=readdir($dh)){
		$filetype=filetype($sqlite_root.$file);
		if ($filetype!='file') continue;
	?>
	<div class="listitem">
		<a onclick="setlitedb('<?php echo $file;?>');"><?php echo htmlspecialchars($file);?></a>
	</div>
	<?php
	}//while
?>
</div>
<script>
ajxjs(self.setlitedb,'databases.js');
ajxjs(self.addquery,'queries.js');
</script>
<?php	
	
}

