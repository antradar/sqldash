<?php

if (count($borg_repos)==1){
	include 'icl/listrepoarchives.inc.php';
}

	
function listrepos(){
	global $borg_repos;

	if (count($borg_repos)==1){
		foreach ($borg_repos as $repokey=>$repo){
			listrepoarchives($repokey);
			break;
		}//foreach
		
		return;
	}
?>
<div class="section">
<?php
	foreach ($borg_repos as $repokey=>$repo){
	?>
	<div class="listitem"><a onclick="showview('sqldash.repoarchives',0,1,'repokey=<?php echo urlencode($repokey);?>');"><?php echo htmlspecialchars($repokey);?></a></div>
	<?php	
	}//foreach
	?>
</div>
<script>
gid('tooltitle').innerHTML='<a>Snapshot Sources</a>';
</script>
<?php		
}
