<?php
include 'subconnect.php';

function showfunc(){
	global $db;

	$dbname=GETSTR('dbname'); //checkdbname();
	$func=GETSTR('func');

	sql_select_db($db,$dbname);

			
	$query="show create function $func";
	$rs=sql_prep($query,$db);
	$myrow=sql_fetch_assoc($rs);
	$def=$myrow['Create Function'];
	
?>
<div class="section" style="height:100%;box-sizing:border-box;">
	<textarea class="inplong" style="height:100%;"><?php echo htmlspecialchars($def);?></textarea>
</div>
<?php					
}
