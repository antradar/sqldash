<?php

function newuser(){
	$user=userinfo();
	if (!$user['groups']['accounts']) die('Access denied');
	
	global $userroles;	
?>
<div class="section">
	<div class="sectiontitle"><?php tr('list_user_add_tab');?></div>
	
	
	<div class="inputrow">
		<div class="formlabel"><?php tr('username');?>:</div>
		<input class="inp" id="login_new">
	</div>
	<div class="inputrow">
		<input type="checkbox" id="active_new" checked> <label for="active_new"><?php tr('account_active');?></label>
		&nbsp; &nbsp;
		<input type="checkbox" id="virtual_new" onclick="if (this.checked) gid('userpasses_new').style.display='none'; else gid('userpasses_new').style.display='block';"> <label for="virtual_new"><?php tr('account_virtual');?></label>
	</div>
	<div id="userpasses_new">
		<div class="inputrow">
			<div class="formlabel"><?php tr('new_password');?>:</div>
			<input class="inp" id="newpass_new" type="password">
		</div>
		<div class="inputrow">
			<div class="formlabel"><?php tr('repeat_password');?>:</div>
			<input class="inp" id="newpass2_new" type="password">
		</div>	
		<div class="inputrow">
			<input type="checkbox" id="passreset_new"> <label for="passreset_new"><?php tr('account_login_reset');?></label>
		</div>
		
		<div class="inputrow">
			<div class="formlabel"><?php tr('account_roles');?>:</div>
			<?php foreach ($userroles as $role=>$label){
			?>
			<div style="padding-left:10px;margin-bottom:3px;">
				<input type="checkbox" id="userrole_<?php echo $role;?>_new"> <label for="userrole_<?php echo $role;?>_new"><?php echo $label;?></label>
			</div>
			<?php	
			}?>
		</div>		
	</div>
		
		<div class="inputrow">
			<button onclick="adduser();"><?php tr('button_user_add');?></button>
		</div>

</div>
<?php

}
