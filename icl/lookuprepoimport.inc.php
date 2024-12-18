<?php

function lookuprepoimport(){
	global $borg_repos;
	$repokey=SGET('repokey');
	
	if ($repokey==''||!isset($borg_repos[$repokey])){
		echo "Invalid repokey";
		return;	
	}
	
	$repo=$borg_repos[$repokey]['repo'];
	
	$archive=SGET('archive');
	
	$file=SGET('file');

	$path=escapeshellarg("$repo::$archive");
	
	$cmd="BORG_PASSCOMMAND='cat /var/www/.borg_passphrase' borg list --json-lines $path 2>&1";
	
	$res=trim(shell_exec($cmd));
	
	
	$lines=explode("\n",$res);		
	$info=json_decode($lines[0],1); //todo: handle errors
	
	$base=$info['path'];
	$date=strtotime($info['mtime']);
	
	global $borg_sandboxes;
	
	$dfile=preg_replace('/^'.preg_quote($base,'/').'/','',$file);
	
	$ddfile=noapos(htmlspecialchars($dfile));
	
?>
<div class="section">
	<div style="margin-bottom:10px;">Import <?php echo htmlspecialchars($archive);?></div>
	
	<div style="margin-bottom:10px;">
	<b><?php echo htmlspecialchars($dfile);?></b> into
	</div>
<?php
	foreach ($borg_sandboxes as $dbname){
		$importkey=md5("$repo $archive $file $dbname");
		
	?>
	<div class="listitem">
		<a onclick="addtab('repoimport_<?php echo $importkey;?>','Import <?php echo $ddfile;?>','importrepo&repokey=<?php echo urlencode($repokey);?>&archive=<?php echo urlencode($archive);?>&file=<?php echo urlencode($file);?>&dbname=<?php echo $dbname;?>');" class="hovlink"><?php echo htmlspecialchars($dbname);?></a>
	</div>
	<?php	
	}//foreach
?>	
</div>
<?php		
}
