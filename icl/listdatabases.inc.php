<?php
include 'subconnect.php';

if ($sqlmode=='sfdx'||$sqlmode=='sfapi') include 'icl/listtables.inc.php';

function listdatabases(){
	global $db;
	global $connname;
	global $sqlmode;
	
	$key=GETSTR('key');

	if ($sqlmode=='sfdx'||$sqlmode=='sfapi'){
		listtables();
		return;
		
	?>
	<div style="text-align:center;padding-top:20px;">
		<button onclick="showview('sqldash.tables');">Show SObjects</button>
	</div>
	<script>
		gid('tooltitle').innerHTML='<a>Databases</a>';
	</script>
	<?php
		return;
	}

	global $SQL_ENGINE;
		
	$query="show databases ";
	if ($SQL_ENGINE=='SQLSRV') $query="select name from sys.databases";


	if ($key!='') {
		if ($SQL_ENGINE=='SQLSRV') $query.=" where name "; 
		$query.=" like '%${key}%' ";
	}
	$mode=GETSTR('mode');
		
	$rs=sql_query($query,$db);
	
	if ($mode!='embed'){
		if (isset($connname)){
	?>
	<div class="sectionheader" style="margin:0;">Connection: <?php echo htmlspecialchars($connname);?></div>
	<?php		
		}
?>
<div class="section">
<div class="listbar">
	<form class="listsearch" onsubmit="_inline_lookupdatabase(gid('databasekey'));return false;">
	<div class="listsearch_">
		<input id="databasekey" class="img-mg" onkeyup="_inline_lookupdatabase(this);" autocomplete="off">
	</div>
	<input type="image" src="imgs/mg.gif" class="searchsubmit" value=".">
	</form>
	<?php if (in_array($SQL_ENGINE,array('MySQLi','MySQL'))) {?>
	<div style="text-align:right;">
	<a class="hovlink" onclick="showdbprocesses();">processes &raquo;</a>
	</div>
	<?php }?>

</div>
<div id="databaselist">
<?php
	}//embed

	while ($myrow=sql_fetch_array($rs)){
		$dbname=$myrow[0];
	?>
	<div class="listitem"><a onclick="setdatabase('<?php echo $dbname;?>');"><?php echo $dbname;?></a></div>
	<?php	
			
	}//while	
	
	if ($mode!='embed'){
?>
</div><!-- databaselist -->
</div>
<script>
	ajxjs(self.setdatabase,'databases.js');
	gid('tooltitle').innerHTML='<a>Databases</a>';
</script>

<?php
	}
}