<?php

function listsettings(){
	global $sdb;
	$user=userinfo();

	//header('listviewtitle:'.tabtitle(_tr('icon_settings')));
	//$exts=sqldash_getexts();
	global $allexts;
	$exts=$allexts;
		
?>
<div class="section">
	<div class="listitem"><a onclick="ajxjs(self.setaccountpass,'accounts.js');reloadtab('account','<?php tr('account_settings');?>','showaccount');addtab('account','<?php tr('account_settings');?>','showaccount');return false;"><?php tr('account_settings');?></a></div>
	
	<?php	
	if (isset($user['groups']['accounts'])){
	?>
	<div class="listitem"><a onclick="ajxjs(self.showuser,'users.js');ajxjs(self.setaccountpass,'accounts.js');showview('core.users',1,null,null,null,null,true);"><?php tr('icon_accounts');?></a></div>	
	<?php
	}
		
	if ($user['groups']['gsconfig']){
		if (isset($exts['hooks']['gsconfig'])){
			foreach ($exts['hooks']['gsconfig'] as $hfunc){
				$func=$hfunc['func'];
				$label=$hfunc['name'];
				$ext=$hfunc['ext'];
	?>
	<div class="listitem"><a onclick="ajxjs(self.showextsettings,'extsettings.js');ajxjs(self.ext_<?php echo str_replace('/','_',$ext);?>,'ext/<?php echo $ext;?>.js');showextsettings('<?php echo $ext;?>','<?php echo $label;?>');"><em><?php echo $label;?></em></a></div>	
	<?php			
			}
		
		}
	}
	
	?>
	
	
	<?php
		
	
		
?>	
</div>
<script>
gid('tooltitle').innerHTML='<a><?php tr('icon_settings');?></a>';
</script>
<?php

}