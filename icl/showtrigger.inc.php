<?php
include 'subconnect.php';

function showtrigger(){
	global $db;

	$dbname=GETSTR('dbname'); //checkdbname();
	$trigger=GETSTR('trigger');

	sql_select_db($db,$dbname);

			
	$query="show create trigger $trigger";
	$rs=sql_prep($query,$db);
	$myrow=sql_fetch_assoc($rs);
	$def=$myrow['SQL Original Statement'];
	
	$def=preg_replace('/^create\s*definer=\S+\s*trigger/i','CREATE TRIGGER',$def);
	

?>
<div class="section" style="height:100%;box-sizing:border-box;">
	<textarea class="inplong" style="height:100%;"><?php echo htmlspecialchars($def);?></textarea>
</div>
<?php					
}
