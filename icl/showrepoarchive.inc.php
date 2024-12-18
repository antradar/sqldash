<?php

function showrepoarchive(){
	global $borg_repos;
	$repokey=SGET('repokey');
	
	if ($repokey==''||!isset($borg_repos[$repokey])){
		echo "Invalid repokey";
		return;	
	}
	
	$repo=$borg_repos[$repokey]['repo'];
	
	$archive=SGET('archive');
	
?>
<div class="section">
	<div class="sectiontitle">Snapshot <?php echo htmlspecialchars($repokey);?>::<?php echo htmlspecialchars($archive);?> &nbsp; <button class="disabled">Import All</button></div>
	<?php
	
	$path=escapeshellarg("$repo::$archive");
	
	$cmd="BORG_PASSCOMMAND='cat /var/www/.borg_passphrase' borg list --json-lines $path 2>&1";
	
	$res=trim(shell_exec($cmd));
	
	
	$lines=explode("\n",$res);		
	$info=json_decode($lines[0],1); //todo: handle errors
	
	$base=$info['path'];
	$date=strtotime($info['mtime']);

	echo "Created: ".date('M j, Y H:i:s',$date);
	?>
	<div class="stable">
	<div class="grid">
	<table style="line-height:2em;">
	<?php
	for ($i=1;$i<count($lines);$i++){
		$obj=json_decode($lines[$i],1);
		$type=$obj['type'];
		$healthy=$obj['healthy'];
		$opath=$obj['path'];
		$path=preg_replace('/^'.preg_quote($base,'/').'/','',$opath);
		
		$parts=explode('/',$path);
		$nparts=count($parts)-2;
		$size=$obj['size']??0;
		$dsize=number_format($size);
		
		$mtime=strtotime($obj['mtime']);
		$dtime=date('Y-m-d H:i:s',$mtime);
	?>
	<tr class="gridrow<?php if ($i%2==1) echo ' even';?>">
		<td>
		<?php if ($type=='d'){?>
			<img src="imgs/t.gif" style="height:10px;width:<?php echo $nparts*15;?>px;"><b><?php echo $path;?></b>
		<?php } else {?>
				<img src="imgs/t.gif" style="height:10px;width:<?php echo $nparts*15;?>px;"><?php echo $path;?>
		<?php }?>
		</td>
		<td>
		<?php if ($type=='-'){?>
			<a class="hovlink" onclick="lookupentity(gid('statusc'),'repoimport&repokey=<?php echo urlencode($repokey);?>&archive=<?php echo urlencode($archive);?>&file=<?php echo urlencode($opath);?>','Sandbox DBs')">Import</a>
		<?php }?>
		</td>
		<td align="right">
		<?php echo $type=='d'?'':$dsize;?>
		</td>
		<td>
		<?php echo $type=='d'?'':$dtime;?>
		</td>
		<?php
		/*
		<td>
		<?php 
		echo '<pre>'; print_r($obj); echo '</pre>';
		?>
		</td>
		*/
		?>
	</tr>
	<?php	
	}//for
	?>
	</table>
	</div><!-- grid -->
	</div><!-- stable -->
</div>
<?php
		
}