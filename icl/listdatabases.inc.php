<?php
include 'subconnect.php';

function listdatabases(){
	global $db;
	global $connname;
	global $sqlmode;
	
	$key=GETSTR('key');

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