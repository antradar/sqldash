<?php

function ghost_listfiles(){
	$auth=sha1(SQLDASHKEY.gmdate('Y-n-j-H'));
?>
<div class="section">
	<div id="ghostfiles">
	...
	</div>
</div>
<script>
	gid('tooltitle').innerHTML='<a>Data Dumps</a>';
	ajxpgn('ghostfiles','ghostsql/ghostsql.php?cmd=files&format=gyroscope&auth=<?php echo $auth;?>');
</script>
<?php	
}
