<?php

function showtable(){
	global $db;
	global $SQL_ENGINE;
	
	$dbname=GETSTR('dbname'); //checkdbname();
	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))) sql_select_db($db,$dbname);
	
	$tablename=GETSTR('tablename');
	
?>
<div class="section">
	<div class="sectiontitle"><?echo $dbname;?> &raquo; <?echo $tablename;?></div>
	
	<div style="margin-bottom:10px;">
	<button onclick="ajxjs(self.addquery,'queries.js');addquery('<?php echo $dbname;?>','<?php echo $tablename;?>');">Run Query</button>
	</div>
<?php

	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))){
		$query="show table status where Name=?";
		$rs=sql_prep($query,$db,$tablename);
		$myrow=sql_fetch_assoc($rs);
	
		//echo '<pre>'; print_r($myrow); echo '</pre>';

		$engine=$myrow['Engine'];
		$options=$myrow['Create_options'];
	
		if ($engine==''&&$myrow['Comment']!=''){
		?>
		<div class="warnbox">
			<?php echo htmlspecialchars($myrow['Comment']);?>
		</div>
		</div><!-- section -->
		<?	
			return;
		}
	
?>
<div class="stable">
<table class="subtable">
	<tr><td>Engine:</td><td><?php echo $engine;?></td></tr>
<?php	

	if ($options!=''){
?>
	<tr><td>Options:</td><td><?php echo htmlspecialchars($options);?></td></tr>
<?php		
	}
?>		
</table>
</div>
<?php
}
?>

<div class="sectionheader">Structure</div>

<table cellspacing="0" cellpadding="5">
<?php		
	
	$query="describe $tablename";
	if ($SQL_ENGINE=='SQLSRV') $query="select COLUMN_NAME as Field, DATA_TYPE as Type, IS_NULLABLE as 'Null' from information_schema.columns where table_name = '$tablename' order by ordinal_position";
	$rs=sql_prep($query,$db);
	$idx=0;
	while ($myrow=sql_fetch_assoc($rs)){
		$field=$myrow['Field'];
		$type=$myrow['Type'];
		$nullable=$myrow['Null'];
		$keytype=$myrow['Key'];
		$extra=$myrow['Extra'];
		if ($SQL_ENGINE=='ClickHouse'){
			$field=$myrow['name'];
			$type=$myrow['type'];
			$extra=$myrow['comment'];	
		}
	?>
	<tr class="gridrow <?php echo $idx%2==1?'even':'odd';?>">
	<td></td>
	<td><b><?php echo htmlspecialchars($field);?></b></td>
	<td><?php echo htmlspecialchars($type);?></td>
	<td><b><?php echo htmlspecialchars($extra);?></b></td>
	</tr>
	<?	
		$idx++;
	}//while
?>
</table>

<?php
if (in_array($SQL_ENGINE,array('MySQL','MySQLi','ClickHouse'))){
?>
<div style="padding-top:10px;padding-bottom:10px;">
	<a class="hovlink" onclick="showhide('tablecreater_<?php echo $dbname;?>_<?php echo $tablename;?>');">export table structure</a>
</div>
<?php

	$query="show create table $tablename";
	$rs=sql_query($query,$db);
	$myrow=sql_fetch_assoc($rs);
	$stmt=$myrow['Create Table'];
	if ($stmt==''&&$myrow['statement']!='') $stmt=$myrow['statement'];
?>
	<textarea style="display:none;" class="inplong" id="tablecreater_<?php echo $dbname;?>_<?php echo $tablename;?>"><?php echo htmlspecialchars($stmt);?></textarea>
<?php	
}	
?>	

</div><!-- section -->
<?	
}

