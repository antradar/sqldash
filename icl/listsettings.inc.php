<?php

function listsettings(){
	global $sdb;
	$user=userinfo();
	

	header('listviewtitle:'.tabtitle(_tr('icon_settings')));
?>
<div class="section">
	<div class="listitem"><a onclick="ajxjs(self.setaccountpass,'accounts.js');reloadtab('account','<?php tr('account_settings');?>','showaccount');addtab('account','<?php tr('account_settings');?>','showaccount');return false;"><?php tr('account_settings');?></a></div>
	
	<?php	
	if (isset($user['groups']['accounts'])){
	?>
	<div class="listitem"><a onclick="ajxjs(self.showuser,'users.js');ajxjs(self.setaccountpass,'accounts.js');showview('core.users',1,null,null,null,null,true);"><?php tr('icon_accounts');?></a></div>	
	<?php
	}
	
	
	?>
	
	
	<?php
		
	
		
?>	
</div>
<?php
/*
?>
<script>
gid('tooltitle').innerHTML='<a><?php tr('icon_settings');?></a>';
</script>
<?php
*/

}