<?php

function lookupquerydim(){
	global $SQL_ENGINE;
	
	$queryidx=GETVAL('queryidx');
	$table=GETSTR('table');
	$fkey=SGET('fkey');
	
	global $db;
	
	$colinfo=array();
	
	$query="describe $table";
	if ($SQL_ENGINE=='SQLSRV') $query="select * from information_schema.columns where table_name = '$table'";
 
	$rs=sql_prep($query,$db);
	while ($myrow=sql_fetch_assoc($rs)){
		//echo '<pre>'; print_r($myrow); echo '</pre>';
		if ($SQL_ENGINE=='SQLSRV'){
			$myrow['Field']=$myrow['COLUMN_NAME'];
			$myrow['Type']=$myrow['DATA_TYPE'];
		}
		if ($SQL_ENGINE=='ClickHouse'){
			$myrow['Field']=$myrow['name']; unset($myrow['name']);
			$myrow['Type']=$myrow['type']; unset($myrow['type']);			
		}
		if (strtolower($myrow['Field'])==strtolower($fkey)) $colinfo=$myrow;
	}//while

	unset($colinfo['COLUMN_NAME'],$colinfo['DATA_TYPE'],$colinfo['TABLE_CATALOG'],$colinfo['TABLE_NAME'],$colinfo['ORDINAL_POSITION'],$colinfo['COLLATION_NAME'],$colinfo['TABLE_SCHEMA']);

				
	//echo '<pre>'; print_r($colinfo); echo '</pre>';
	
?>
<div class="section">
	<div class="infobox">
		<?php echo htmlspecialchars($table);?>.<?php echo htmlspecialchars($fkey);?>	
	</div>
	<?php
	if (!isset($colinfo['Key'])||$colinfo['Key']==''){
	?>
	<div class="warnbox">
		Adding this Unindexed field could slow down performance.
	</div>
	<?php	
	}
	$dimtypes=array(
		'dim'=>'Discrete Dimension',
		'range'=>'Numeric Range',
		'daterange'=>'Date Range',
	);
	
	foreach ($dimtypes as $k=>$v){
	?>
	<div class="listitem">
		<a onclick="addquerydim(<?php echo $queryidx;?>,'<?php echo $k;?>','<?php echo $fkey;?>');"><?php echo $v;?></a>
	</div>
	<?php	
	}//foreach
	
	?>

</div>
<?php	
}