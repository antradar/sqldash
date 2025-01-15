<?php
include 'subconnect.php';

function showtable(){
	global $db;
	global $SQL_ENGINE;

	$tablename=GETSTR('tablename');
	$triggers=array();
		
	$dbname=GETSTR('dbname'); //checkdbname();
	
	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))) {
		sql_select_db($db,$dbname);

		$query="select TRIGGER_NAME, EVENT_MANIPULATION from information_schema.TRIGGERS
				where 
				TRIGGER_SCHEMA = ? AND EVENT_OBJECT_TABLE = ? ";
				
		$rs=sql_prep($query,$db,array($dbname, $tablename));
		
		while ($myrow=sql_fetch_assoc($rs)){
			array_push($triggers,array(
				'name'=>$myrow['TRIGGER_NAME'],
				'event'=>$myrow['EVENT_MANIPULATION']
			));
		}
				
	}
	
		
?>
<div class="section">
	<div class="sectiontitle"><?php echo $dbname;?> &raquo; <?php echo $tablename;?></div>
	
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
		<?php	
			return;
		}
	
?>
<div class="stable">
<table class="subtable">
	<tr><td>Engine:</td><td><?php echo $engine;?></td></tr>
<?php	
	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))){

			$query="select table_comment from information_schema.tables where table_name=? and table_schema=?";
			$rs=sql_prep($query,$db,array($tablename,$dbname));
			$myrow=sql_fetch_assoc($rs);
			$table_comment=$myrow['table_comment'];
	?>
	<tr>
	<td valign="top">Comment:</td>
	<td>
		<textarea class="inp" id="tablecomment_<?php echo $dbname;?>_<?php echo $tablename;?>"><?php echo htmlspecialchars($table_comment);?></textarea>
		<br>
		<button onclick="update_table_comment('<?php echo $tablename;?>','<?php echo $dbname;?>');">Update</button>
	</td>
	</tr>
	<?php
	}


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

if (count($triggers)>0){
?>
<div class="sectionheader">Triggers</div>
<table cellspacing="0" cellpadding="5">
<?php foreach ($triggers as $trigger){?>
<tr>
	<td><b><a class="hovlink" onclick="loadfs('Trigger / <?php echo $trigger['name'];?>','showtrigger&dbname=<?php echo $dbname;?>&trigger=<?php echo $trigger['name'];?>');"><?php echo $trigger['name'];?></a></b></td>
	<td><?php echo $trigger['event'];?></td>
</tr>
<?php }?>
</table>
<?php	
}//triggers

?>

<div class="sectionheader">Structure</div>

<table cellspacing="0" cellpadding="5">
<?php		
	
			$query="select column_name, column_comment from information_schema.columns
			where table_name = ? and table_schema = ? and column_comment!=''
			";
			
			$rs=sql_prep($query,$db,array($tablename,$dbname));
			$colcomments=array();
			while ($myrow=sql_fetch_assoc($rs)){
				$colcomments[$myrow['column_name']]=$myrow['column_comment'];	
			}
			
	$query="describe $tablename";
	

				
	
	if ($SQL_ENGINE=='SQLSRV') $query="select COLUMN_NAME as Field, DATA_TYPE as Type, IS_NULLABLE as 'Null' from information_schema.columns where table_name = '$tablename' order by ordinal_position";
		
	if ($query!=''){
	$rs=sql_query($query,$db);
	$idx=0;
	while ($myrow=sql_fetch_assoc($rs)){
		$field=isset($myrow['Field'])?$myrow['Field']:'';
		$type=isset($myrow['Type'])?$myrow['Type']:'';
		$nullable=isset($myrow['Null'])?$myrow['Null']:'';
		$keytype=isset($myrow['Key'])?$myrow['Key']:'';
		$extra=isset($myrow['Extra'])?$myrow['Extra']:'';
		$colcomment=$colcomments[$field]??'';
		if ($SQL_ENGINE=='ClickHouse'){
			$field=$myrow['name'];
			$type=$myrow['type'];
			$extra=$myrow['comment'];	
		}
	?>
	<tr class="gridrow <?php echo $idx%2==1?'even':'odd';?>">
	<td></td>
	<td><b><?php echo htmlspecialchars($field);?></b></td>
	<td>
	<?php if ($keytype!=''){?>
		<span class="labelbutton"><?php echo htmlspecialchars(strtolower($keytype));?></span>
		<?php
		}
		?>
	</td>
	<td><?php echo htmlspecialchars($type);?></td>
	<td><b><?php echo htmlspecialchars($extra);?></b></td>
	<td><input class="inp" value="<?php echo htmlspecialchars($colcomment);?>" onchange="update_col_comment(this,'<?php echo $dbname;?>','<?php echo $tablename;?>','<?php echo $field;?>');"></td>
	</tr>
	<?php	
		$idx++;
	}//while
	
	}//no-empty describe querys
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
	$stmt=isset($myrow['Create Table'])?$myrow['Create Table']:'';
	if ($stmt==''&&$myrow['statement']!='') $stmt=$myrow['statement'];
?>
	<textarea style="display:none;" class="inplong" id="tablecreater_<?php echo $dbname;?>_<?php echo $tablename;?>"><?php echo htmlspecialchars($stmt);?></textarea>
<?php	
}	
?>	

</div><!-- section -->
<?php	
}

