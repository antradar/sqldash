<?php

function ghost_listtables(){
	$fn=basename($_GET['fn']);
	
?>
<div class="section">
	<div class="sectiontitle"><?php echo htmlspecialchars($fn);?> / tables</div>
	
	<div id="ghosttables_<?php echo $fn;?>">...</div>
	
</div>
<?php
		
}