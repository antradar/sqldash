<?php

function listrepoarchives($repokey=null){
	if (!isset($repokey)) $repokey=SGET('repokey');
	
	global $borg_repos;

	if ($repokey==''||!isset($borg_repos[$repokey])){
		echo "Invalid repokey";
		return;	
	}	
	
?>
<div class="section">
<?php
	$repo=$borg_repos[$repokey]['repo'];
	
	$cmd="BORG_PASSCOMMAND='cat /var/www/.borg_passphrase' borg list $repo 2>&1";
	
	$res=shell_exec($cmd);
	
	//echo htmlspecialchars($res); return;
	$vers=array();
	
	$lines=explode("\n",$res);
	
	$archives=array();

	foreach ($lines as $line){
		if (trim($line)=='') continue;

		$line=preg_replace("/\s+/","\t",$line);
		$parts=explode("\t",$line);
		$archive=$parts[0];
		$date=$parts[2];
		$time=$parts[3];
		$timestamp=strtotime($date.' '.$time);
		
		array_push($archives,array(
			'name'=>$archive,
			'date'=>$date,
			'time'=>$time,
			'timestamp'=>$timestamp
		));
		
	}//pass 1
	
	usort($archives,function($a,$b){
		$va=$a['timestamp'];
		$vb=$b['timestamp'];
		if ($va==$vb) return 0;
		return $va<$vb;
			
	});
	
	foreach ($archives as $arv){
		$archive=$arv['name'];
		$date=$arv['date'];
		$time=$arv['time'];
						
		$darchive=noapos(htmlspecialchars($archive));
	?>
	<div class="listitem">
		<a onclick="addtab('showrepoarchive_<?php echo $repokey;?>_<?php echo md5($archive);?>','<?php echo $darchive;?>','showrepoarchive&repokey=<?php echo urlencode($repokey);?>&archive=<?php echo urlencode($archive);?>');">
		<b><?php echo htmlspecialchars($archive);?></b>
		<br>
		<em class="diminished"><?php echo $date.' '.$time;?></em>
		</a>
	</div>
	<?php		
	}//foreach

?>
<script>
gid('tooltitle').innerHTML='<a><?php echo noapos(htmlspecialchars($repokey));?> Snapshots</a>';
</script>
</div>
<?php	
}