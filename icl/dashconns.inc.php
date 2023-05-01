<?php
include 'icl/listconns.inc.php';

function dashconns(){
	global $uiconfig;
	header('tabctx: dash');
	header("newloadfunc: ajxjs(self.showconn,'conns.js');");
	if ($uiconfig['toolbar_position']=='top') header('newtitle: '._tr('icon_conns'));
	

?>
<div class="section">
	<div class="sectiontitle"><?php tr('icon_conns');?></div>
	
	
		<?php listconns(); ?>
	
		<div class="clear"></div>
</div>

<?php		
}
