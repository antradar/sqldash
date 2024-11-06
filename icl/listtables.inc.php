<?php
include 'subconnect.php';

function listtables(){
	global $db;
	global $SQL_ENGINE;
	global $connname;
			
	$dbname=checkdbname();
	$mode=GETSTR('mode');
		
	$sqlmode=SGET('sqlmode');	
		
	if ($mode!='embed'){
		$ddbname=$dbname;
		if ($SQL_ENGINE=='sfdx'||$SQL_ENGINE=='sfapi') $ddbname='';
	?>
	<div class="sectionheader" style="margin:0;">Database: <?php if (isset($connname)) echo htmlspecialchars($connname); if ($SQL_ENGINE!='sfdx'&&$SQL_ENGINE!='sfapi') echo '//';?><?php echo $ddbname;?></div>
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

	if ($SQL_ENGINE=='sfdx'||$SQL_ENGINE=='sfapi'){
		$perpage=20;
		$page=isset($_GET['page'])?intval($_GET['page']):0;

		$cquery="select count() from EntityDefinition ";
		$query="show tables ";
		if ($key!='') {
			$query.=" where QualifiedApiName like '%$key%' ";
			$cquery.=" where QualifiedApiName like '%$key%' ";
		}

		$query.=" order by QualifiedApiName ";
		
		$rs=sql_prep($cquery,$db);
		$count=$rs['totalSize'];
		$maxpage=ceil($count/$perpage)-1;
		if ($maxpage<0) $maxpage=0;
		if ($page<0) $page=0;
		if ($page>$maxpage) $page=$maxpage;
		$start=$page*$perpage;
		

		$query.=" limit $perpage offset $start ";
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

	if (isset($maxpage)&&$maxpage>0){
	?>
	<div>
	<a class="hovlink" onclick="ajxpgn('tablelist',document.appsettings.codepage+'?cmd=slv_sqldash__tables&mode=embed&key='+encodeHTML(gid('tablekey').value)+'&page=<?php echo $page-1;?>');">&laquo; Prev</a>
	&nbsp;
	Page <?php echo $page+1;?> of <?php echo $maxpage+1;?>
	&nbsp;
	<a class="hovlink" onclick="ajxpgn('tablelist',document.appsettings.codepage+'?cmd=slv_sqldash__tables&mode=embed&key='+encodeHTML(gid('tablekey').value)+'&page=<?php echo $page+1;?>');">Next &raquo;</a>
	</div>
	<?php	
	}
	
	$rs=sql_prep($query,$db);
	
	while ($myrow=sql_fetch_array($rs)){
		$tablename=$myrow[0];
	?>
	<div class="listitem">
	<a onclick="showtable('<?php echo $tablename;?>','<?php echo $dbname;?>');">
		<?php echo $tablename;?>
		&nbsp; <span class="labelbutton" onclick="ajxjs(self.addquery,'queries.js');addquery('<?php echo $dbname;?>','<?php echo $tablename;?>',null,'<?php echo $sqlmode;?>',1);return false;">..</span>
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