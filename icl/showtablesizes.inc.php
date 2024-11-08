<?php

if (!isset($_GET['sqlmode'])||$_GET['sqlmode']!='sqlite') include 'subconnect.php';

function showtablesizes(){

	$dbname=checkdbname();
	
	if (!is_string($dbname)) return;
	
	global $db;
	global $sqlmode;
	global $SQL_ENGINE;
	
	if ($sqlmode!='sqlite'&&in_array($SQL_ENGINE,array('MySQL','MySQLi'))) sql_select_db($db,$dbname);
			
	$query="select TABLE_NAME AS tablename,
  round((DATA_LENGTH) / 1024 / 1024) as datasize,
  round((INDEX_LENGTH) / 1024 / 1024) as indexsize
  from
  information_schema.TABLES
  where
  TABLE_SCHEMA=?
  order by
  (DATA_LENGTH + INDEX_LENGTH)
  desc ";
  
  	$rs=sql_prep($query,$db,$dbname);
  	
  	?>
  	<table>
  	<tr>
  		<td><b>Table</b></td>
  		<td align="right"><b>Data (MB)</b></td>
  		<td align="right"><b>Index (MB)</b></td>
  	</tr>
  	<?php
  	
  	while ($myrow=sql_fetch_assoc($rs)){
	?>
	<tr>
		<td><?php echo $myrow['tablename'];?></td>
		<td align="right"><?php echo number_format($myrow['datasize']);?></td>
		<td align="right"><?php echo number_format($myrow['indexsize']);?></td>
	</tr>
	<?php  	
  	}//while
  	
  	?>
  	</table>
  	<?php
  
  
}