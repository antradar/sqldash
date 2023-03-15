<?php
include 'icl/showgyroscopeupdater.inc.php';
include 'icl/showguide.inc.php';

function showwelcome(){
	
	global $db;
	global $SQL_ENGINE;
	
	$dbname=isset($_COOKIE['dbname'])?$_COOKIE['dbname']:null;
	
?>
<div class="section">
	<div class="sectiontitle"><?php tr('hometab_welcome');?></div>
	
<div class="sectionheader">Database Status</div>
<div class="stable">
<div class="grid">
<table class="subtable" style="line-height:3em;">

	<?php
	if (isset($dbname)&&$dbname!=''){
?>
<tr>
	<td><b>Current Database:</b></td>
	<td>
		<b><?php echo htmlspecialchars($dbname);?></b> &nbsp; &nbsp;
		<button onclick="ajxjs(self.addquery,'queries.js');addquery('<?php echo $dbname;?>','');">Run Query</button>
	</td>
</tr>
<?php 
//if ($SQL_ENGINE=='sqlsrv'){
?>

<?php
//}	

	}	
	
	?>
<tr>
	<td>Engine:</td>
	<td><?php echo $SQL_ENGINE;?></td>
</tr>	
	<?php

	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))){
	$query="show variables";
	$rs=sql_prep($query,$db);
	
	$defvars=array('innodb_flush_log_at_trx_commit','character_set_database','character_set_server','character_set_client','character_set_filesystem','character_set_results','character_set_system','sql_mode');
	
	while ($myrow=sql_fetch_assoc($rs)){
		$var=$myrow['Variable_name'];
		if (!in_array($var,$defvars)) continue;
		
	?>
	<tr>
	<td><?php echo $var;?></td>
	<td><?php echo htmlspecialchars($myrow['Value']);?></td>
	</tr>
	<?php
		
	}//while

	if (isset($dbname)){
			
	$query="SELECT TABLE_SCHEMA, TABLE_NAME, CREATE_OPTIONS FROM INFORMATION_SCHEMA.TABLES WHERE table_schema like ? and CREATE_OPTIONS LIKE '%ENCRYPTION=\"Y\"%';";
	$rs=sql_prep($query,$db,$dbname);
	$c=sql_affected_rows($db,$rs);
	
	if ($c>0){
		
	?>
	<tr><td><b>Encrypted Tables:</b><td>
	<?php	
	
	while ($myrow=sql_fetch_assoc($rs)){
	?>	
	<a class="hovlink" onclick="showtable('<?php echo $myrow['TABLE_NAME'];?>','<?php echo $dbname;?>');"><?php echo htmlspecialchars($myrow['TABLE_NAME']);?></a> &nbsp; 
	<?php
	}//while
	
	?>
	</td></tr>
	<?php
	
	}//has encrypted tables	

	}
	
	?>
	<tr>
	<td colspan="2">
		<a class="hovlink" onclick="ajxpgn('home_tablesizes',document.appsettings.codepage+'?cmd=showtablesizes');">show table sizes</a>
		<div id="home_tablesizes">

		</div>
	</td>
	</tr>
	<?php
	
	}//dbname
?>
</table>
</div>
</div>

	<div style="padding-bottom:60px;"></div>

<?php


			

		//lazy way to generate a starter screen, but better than nothing
		
		//auto_welcome();	
		showgyroscopeupdater();
		
		if ($_SERVER['REMOTE_ADDR']=='127.0.0.1') showguide(); else echo '<div style="padding-bottom:100px;"></div>';
		
	?>			

	
</div><!-- section -->
<?php
}

function auto_welcome(){
	$user=userinfo();
	global $toolbaritems;
	?>
	<div class="section">

	<?php
	foreach ($toolbaritems as $modid=>$ti){
	if (isset($ti['type'])&&$ti['type']=='custom'){
	?>
	<?php echo $ti['desktop'];?>
	<?php	
		continue;
	}
	
	if (!isset($ti['icon'])||$ti['icon']=='') continue;
	
	if (isset($ti['groups'])){
		$canview=0;
		$gs=explode('|',$ti['groups']);
		foreach ($gs as $g) if (isset($user['groups'][$g])) $canview=1;
		if (!$canview) continue;	
	}
	
	$action="showview('".$modid."',null,1);";
	if (isset($ti['action'])&&$ti['action']!='') $action=$ti['action'];
	
?>	
	<div class="welcometile">
	<a onclick="<?php echo $action;?>"><img style="vertical-align:middle;margin-right:5px;" class="<?php echo $ti['icon'];?>-light" src="imgs/t.gif" width="32" height="32"> <span style="vertical-align:middle;"><?php echo $ti['title'];?></span></a>
	</div>
	
<?php }//foreach
?>

	
	<div class="clear"></div>
</div>
<?php
		
}


