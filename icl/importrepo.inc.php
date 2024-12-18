<?php
include 'pfork.php';

function importrepo(){
	global $borg_repos;
	global $sqlmode;
	global $SQL_ENGINE;

	global $repodb_host;
	global $repodb_user;
	global $repodb_pass;
	
	if (!isset($repodb_host)){
?>
<div class="warnbox">
	$repodb_* settings are missing.
</div>
<?php		
		return;
	}

	$db=@sql_get_db($repodb_host,null,$repodb_user,$repodb_pass);
						
	$repokey=SGET('repokey');
	$file=SGET('file');
	$dbname=SGET('dbname');
	
	if ($repokey==''||!isset($borg_repos[$repokey])){
		echo "Invalid repokey";
		return;	
	}
	
	$repo=$borg_repos[$repokey]['repo'];
	$extpath=$borg_repos[$repokey]['extract_path']??null;
	if (!isset($extpath)) apperror('Extract path not set.');
	
	$archive=SGET('archive');
	
	$query="create database if not exists $dbname";
	sql_prep($query,$db);
	
?>
<div class="section">
	<div class="sectiontitle">Importing Snapshot <em><?php echo htmlspecialchars($archive);?></em></div>
	<div class="inputrow">Single file: <?php echo htmlspecialchars($file);?></div>
	<div class="inputrow">Target DB: <?php echo htmlspecialchars($dbname);?></div>
	
	<?php
	
	$repopath=escapeshellarg($repo.'::'.$archive);
	$repofile=escapeshellarg('/'.$file);

	$reposrc=escapeshellarg($extpath.'/'.$file);
		
		$cmd="cd $extpath && BORG_PASSCOMMAND='cat /var/www/.borg_passphrase' borg extract $repopath $repofile && mysql -h$repodb_host -u$repodb_user -p$repodb_pass $dbname < $reposrc &&rm -f $reposrc ";
		
		$pid=pfork($cmd,$extpath);
	?>
	<div class="infobox">
		The import command has been executed in the background:
		<br><br>
		
		<?php
		/* 
		echo htmlspecialchars($cmd);
		?>
		<br><br>
		<?php
		*/
		?>
		Process ID: <?php echo $pid;?>
	</div>
	<div class="warnbox">
		Do NOT reload this tab unless you want to repeat the import!
	</div>
</div>
<?php		
}
