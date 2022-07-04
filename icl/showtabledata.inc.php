<?php

function showtabledata(){
	global $db;
	
	$dbname=checkdbname();
	
	$tablename=GETSTR('tablename');
	
?>
<div class="section">
	<div class="sectiontitle"><?echo $dbname;?> &raquo; <?echo $tablename;?></div>
<div class="stable_">
<table class="subtable" border="1" cellpadding="1" cellspacing="0">
<?		
	$query="select * from $tablename";
	$rs=sql_query($query,$db);
	
	$idx=0;
	while ($myrow=sql_fetch_assoc($rs)){
	
		if ($idx==0){
		foreach ($myrow as $k=>$v){
	?>
	<td><b><?echo $k;?></b></td>
	<?		
		}
	?>
	</tr>
	<?	
			
		}
	?>
	<tr>
	<?	
		foreach ($myrow as $k=>$v){
			$v=htmlspecialchars($v);
	?>
	<td><?echo $v;?></td>
	<?		
		}
	?>
	</tr>
	<?	
		$idx++;		
	}//while
?>
</table><!-- .subtable -->
</div><!-- .stable -->

</div><!-- section -->
<?	
}