<?php

include 'fsql-sqlite.php';

function slite_showdb(){
	
	global $sqlite_root;
	$dbfn=basename(SGET('dbfn'));
	
	$fdb=fsql_get_db($sqlite_root.$dbfn);
	
?>
<div class="section">
	<div class="sectiontitle"><?php echo htmlspecialchars($dbfn);?> / tables</div>
	
	<div class="col">
<?php	
	$query="select name from sqlite_master where type='table'";
	$rs=fsql_query($query,$fdb);

	
	while ($myrow=fsql_fetch_assoc($rs)){
		$table=$myrow['name'];
	?>
	<div class="listitem">
		<a onclick="addquery('<?php echo $dbfn;?>','<?php echo $table;?>',null,'sqlite');"><?php echo htmlspecialchars($table);?></a>
	</div>
	<?php	
	}//while
	
	
?>

	</div><!-- col -->
	<div class="clear"></div>
</div>
<?php
		
}

