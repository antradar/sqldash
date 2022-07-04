<?php

function listreports(){
	$user=userinfo();
?>
<div class="section">
	<div class="listitem"><a onclick="reloadtab('actionlog','Activity Log','rptactionlog');addtab('actionlog','Activity Log','rptactionlog');">Activity Log</a></div>
	<?
	if (isset($user['groups']['dbadmin'])){
	?>
	<div class="listitem"><a onclick="addtab('rptsqlcomp','SQL Compare','rptsqlcomp');">SQL Compare</a></div>	
	<?
	}
	?>
</div>
<script>
gid('tooltitle').innerHTML='<a><?tr('icon_reports');?></a>';
</script>
<?
}
