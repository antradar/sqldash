<?php

function listtables(){
	global $db;
	global $SQL_ENGINE;
	
	$dbname=checkdbname();
	$mode=GETSTR('mode');
	
	if ($mode!='embed'){
	?>
	<div class="sectionheader" style="margin:0;">Database: <?php echo $dbname;?></div>
	<?php
	}

	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))){
	
		$query="show table status where Engine!='InnoDB' or Create_options!='' ";
	

		$rs=sql_prep($query,$db);
		$tabletypes=array();
		$tableopts=array();
		while ($myrow=sql_fetch_assoc($rs)){
			if ($myrow['Engine']!='InnoDB') $tabletypes[$myrow['Name']]=$myrow['Engine'];
			if ($myrow['Create_options']!='') $tableopts[$myrow['Name']]=$myrow['Create_options'];
		}//while
	}		
		
	$key=GETSTR('key');
	
	$query="show tables ";
	if ($key!='') $query.=" like '%${key}%' ";

	if ($SQL_ENGINE=='SQLSRV') {
		$query="select TABLE_NAME from [$dbname].INFORMATION_SCHEMA.TABLES where TABLE_TYPE='BASE TABLE'";
		if ($key!='') $query.=" and TABLE_NAME like '%${key}%' ";
		$query.=" order by TABLE_NAME ";
	}

	
	if ($mode!='embed'){
	
?>
<div class="section">
<div class="listbar">
	<form class="listsearch" onsubmit="_inline_lookuptable(gid('tablekey'));return false;">
	<div class="listsearch_">
		<input id="tablekey" class="img-mg" onkeyup="_inline_lookuptable(this);" autocomplete="off">
	</div>
	<input type="image" src="imgs/mg.gif" class="searchsubmit" value=".">
	</form>
</div>
<div id="tablelist">

<?php
	}//embed
	
	$rs=sql_prep($query,$db);
	
	while ($myrow=sql_fetch_array($rs)){
		$tablename=$myrow[0];
	?>
	<div class="listitem">
	<a onclick="showtable('<?php echo $tablename;?>','<?php echo $dbname;?>');"><?php echo $tablename;?>
		<?php if (isset($tabletypes[$tablename])&&$tabletypes[$tablename]!=''){?>
		<span class="labelbutton"><?php echo $tabletypes[$tablename];?></span>
		<?php }?>
		<?php if (isset($tableopts[$tablename])&&$tableopts[$tablename]!=''){
			$dopts=$tableopts[$tablename];
			if ($dopts=='partitioned') $dopts='Par';
			if ($dopts=='ENCRYPTION="Y"') $dopts='Enc';
		?>
		<span class="labelbutton"><?php echo $dopts;?></span>
		<?php }?>		
	</a></div>
	<?php	
			
	}//while	
	
	if ($mode!='embed'){
?>
</div>
</div>
<script>
	gid('tooltitle').innerHTML='<a>Tables</a>';
	ajxjs(self.showtable,'tables.js');
</script>
<?php
}
	
}