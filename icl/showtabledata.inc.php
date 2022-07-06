<?php

function showtabledata(){
	global $db;
	
	$dbname=checkdbname();
	
	$tablename=GETSTR('tablename');
	
?>
<div class="section">
	<div class="sectiontitle"><?php echo $dbname;?> &raquo; <?php echo $tablename;?></div>
<div class="stable_">
<table class="subtable" border="1" cellpadding="1" cellspacing="0">
<?php		
	$query="select * from $tablename";
	$rs=sql_query($query,$db);
	
	$idx=0;
	while ($myrow=sql_fetch_assoc($rs)){
	
		if ($idx==0){
		foreach ($myrow as $k=>$v){
	?>
	<td><b><?php echo $k;?></b></td>
	<?php		
		}
	?>
	</tr>
	<?php	
			
		}
	?>
	<tr>
	<?php	
		foreach ($myrow as $k=>$v){
			$v=htmlspecialchars($v);
	?>
	<td><?php echo $v;?></td>
	<?php		
		}
	?>
	</tr>
	<?php	
		$idx++;		
	}//while
?>
</table><!-- .subtable -->
</div><!-- .stable -->

</div><!-- section -->
<?php	
}