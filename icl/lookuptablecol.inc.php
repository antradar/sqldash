<?php

function lookuptablecol(){
	global $db;
	global $SQL_ENGINE;
	
	$tablename=noapos($_GET['table']);
	$mode=$_GET['mode'];
	
	switch ($SQL_ENGINE){
	case 'SQLSRV': $fname='COLUMN_NAME'; $tname='DATA_TYPE'; $query="select * from information_schema.columns where table_name = '$tablename'"; break;
	case 'ClickHouse': $fname='name'; $tname='type'; $query="describe $tablename"; break;
	default: $fname='Field'; $tname='Type'; $query="describe $tablename";	
	}	
	
	$stringtypes=array('varchar','nvarchar','String','text','blob','smalltext','mediumtext','longtext','mediumblob','longblob');
	
	$rs=sql_prep($query,$db);
?>
<div class="section">
<?php	

	while ($myrow=sql_fetch_assoc($rs)){
		$colname=$myrow[$fname];
		$type=$myrow[$tname];
		if ($mode=='stringonly'&&!in_array($type,$stringtypes)) continue;
	?>
	<div class="listitem"><a onclick="picklookup('<?php echo $colname;?>','<?php echo $colname;?>');"><?php echo $colname;?></a></div>
	<?php	
	}//while
?>
</div>
<?php	
	
}