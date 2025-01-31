<?php
include 'icl/showgyroscopeupdater.inc.php';
include 'icl/showguide.inc.php';

$connid=isset($_COOKIE['connid'])?$_COOKIE['connid']:null;

if (isset($connid)) include 'subconnect.php';

function showwelcome(){
	
	global $db;
	global $SQL_ENGINE;
	
	global $connid;
	/*	
	$pipes=array();
	$fd=array();
	
	//$cmd="mysql -hlocalhost -uroot -pmnstudio relo < /var/dx/actionlog.0.sql"; //nohup? //00019

	$cmd="nohup /usr/bin/pdump 4 localhost root mnstudio loanstudio"; //nohup? //00019

	$p=proc_open($cmd,$fd,$pipes,'/var/dx');
	
	$status=proc_get_status($p);
	$pid=$status['pid'];
	
	echo "Task #$taskid  PID: $pid\r\n";
	*/
	
	if ((1==SQLDASH_AUTH_MODE||2==SQLDASH_AUTH_MODE)&&!isset($connid)){
?>
<div class="section">
	<div class="sectiontitle"><?php tr('hometab_welcome');?> / <?php echo SERVER_NAME;?></div>

	<?php welcome_show_auth_warning();?>
	
	<div class="infobox">
		Start by <a class="hovlink" onclick="showview('codegen.conns',null,1);">selecting a connection</a>.
	</div>
	
	
</div>
<?php
		return;		
	}
	
	$dbname=isset($_COOKIE['dbname'])?$_COOKIE['dbname']:null;
	
?>
<div class="section">
	<div class="sectiontitle"><?php tr('hometab_welcome');?> <span style="opacity:0.3;">/</span> <span style="display:inline-block;padding:4px 8px;border:dashed 1px #999999;border-radius:3px;"><?php echo SERVER_NAME;?></span></div>

	<?php welcome_show_auth_warning(); ?>
	
	
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
	$logfn='/dev/shm/'.$dbname.'.log';
	if (file_exists($logfn)){
?>
<tr>
	<td colspan="2">
		<div class="warnbox">
			A profile log is available for this database. &nbsp; 
			<a class="hovlink" onclick="addtab('plogview_<?php echo $dbname;?>','Log: <?php echo $dbname;?>','viewplog&dbname=<?php echo $dbname;?>');">Analyze</a>
		</div>
	</td>
</tr>
<?php
	}
?>

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
		
	$query="select ROUTINE_NAME from information_schema.routines where routine_schema=? order by ROUTINE_NAME";
	$rs=sql_prep($query,$db,$dbname);
	$c=sql_affected_rows($db,$rs);
	
	if ($c>0){
	?>
	<tr><td><b>Functions</b></td>
	<td>
		<?php while ($myrow=sql_fetch_assoc($rs)){
			$func=$myrow['ROUTINE_NAME'];
		?>
		<nobr><a class="hovlink" onclick="loadfs('Function / <?php echo $func;?>','showfunc&func=<?php echo $func;?>&dbname=<?php echo $dbname;?>');"><?php echo $func;?></a></nobr> &nbsp;
		<?php	
		}?>
	</td>
	</tr>
	<?php	
	}
	
		
			
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

function welcome_show_auth_warning(){
	if (2==SQLDASH_AUTH_MODE){?>
	<div class="warnbox">
		You are authenticated in Relaxed mode. 2FA is not enforced.<br>
		You can set SQLDASH_AUTH_MODE to "1" in config.php to enable Strict mode.
	</div>
	<?php 
	}
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


