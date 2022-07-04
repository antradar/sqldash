<?php

function bindecodelookupcell(){
	global $db;

	$dbname=_GET['dbname'];
		
	$tablename=$_GET['table'];
	$pkey=$_GET['pkey'];
	$pval=intval($_GET['pval']);
	$fkey=$_GET['fkey'];

	$query="select $fkey from $tablename where $pkey=$pval";
	$rs=sql_prep($query,$db);
	$myrow=sql_fetch_assoc($rs);
	$fval=$myrow[$fkey];
	
	$res=gzinflate($fval);
	
	?>
	<textarea class="inplong" style="font-size:12px;line-height:1.5em;"><?php echo htmlspecialchars($res);?></textarea>
	<?php
	
	
}