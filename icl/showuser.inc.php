<?php

function showuser($userid=null){
	if (!isset($userid)) $userid=GETVAL('userid');
	
	$user=userinfo();
	if (!$user['groups']['accounts']) die('Access denied');
	
	global $sdb;
	global $userroles;
	
	$jsroles=str_replace('"',"'",json_encode(array_keys($userroles)));	
	
	$query="select * from users where userid=$userid";
	$rs=$sdb->query($query);
	
	if (!$myrow=$rs->fetchArray(SQLITE3_ASSOC)) die('This user record has been removed');
	
	$login=$myrow['login'];
	$dispname=$myrow['dispname'];
	$active=$myrow['active'];
	$virtual=$myrow['virtualuser'];
	$passreset=$myrow['passreset'];
	$groupnames=$myrow['groupnames'];
	$groups=explode('|',$groupnames);
	
	header('newtitle: '.$login);
	
?>
<div class="section">
	<div class="sectiontitle"><?php echo $login;?></div>

	<div class="col">


	<div class="inputrow">
		<div class="formlabel"><?php tr('username');?>:</div>
		<input class="inpmed" id="login_<?php echo $userid;?>" value="<?php echo htmlspecialchars($login);?>"
			onblur="if (gid('dispname_new').value==''&&this.value!='') {var val=this.value.charAt(0).toUpperCase()+this.value.slice(1);gid('dispname_new').value=val;}"		
		>
	</div>
	<div class="inputrow">
		<div class="formlabel">Display Name:</div>
		<input class="inpmed" id="dispname_<?php echo $userid;?>" value="<?php echo htmlspecialchars($dispname);?>" onfocus="document.hotspot=this;this.select();">
	</div>		
	<div class="inputrow">
		<input type="checkbox" id="active_<?php echo $userid;?>" <?php if ($active) echo 'checked';?>> <label for="active_<?php echo $userid;?>"><?php tr('account_active');?></label>
		<span style="display:none;">
		&nbsp;&nbsp;
		<input type="checkbox" id="virtual_<?php echo $userid;?>" <?php if ($virtual) echo 'checked';?> onclick="if (this.checked) gid('userpasses_<?php echo $userid;?>').style.display='none'; else gid('userpasses_<?php echo $userid;?>').style.display='block';"> <label for="virtual_<?php echo $userid;?>"><?php tr('account_virtual');?></label>
		</span>
	</div>
	<div id="userpasses_<?php echo $userid;?>" style="<?php if ($virtual) echo 'display:none;';?>">
	<div class="inputrow">
		<div class="formlabel"><?php tr('new_password');?>:</div>
		<input class="inp" id="newpass_<?php echo $userid;?>" type="password">
	</div>
	<div class="inputrow">
		<div class="formlabel"><?php tr('repeat_password');?>:</div>
		<input class="inp" id="newpass2_<?php echo $userid;?>" type="password">
	</div>
	
	<div class="inputrow">
		<input type="checkbox" id="passreset_<?php echo $userid;?>" <?php if ($passreset) echo 'checked';?>> <label for="passreset_<?php echo $userid;?>"><?php tr('account_login_reset');?></label>
	</div>
	
	<div class="inputrow">
		<div class="formlabel"><?php tr('account_roles');?>:</div>
		<?php foreach ($userroles as $role=>$label){
		?>
		<div style="padding-left:10px;margin-bottom:3px;">
			<input type="checkbox" id="userrole_<?php echo $role;?>_<?php echo $userid;?>" <?php if (in_array($role,$groups)) echo 'checked';?>> 
			<label for="userrole_<?php echo $role;?>_<?php echo $userid;?>"><?php echo $label;?></label>
		</div>
		<?php	
		}?>
	</div>	
	</div><!-- userpasses -->
	
	<div class="inputrow">
		<button onclick="updateuser(<?php echo $userid;?>,<?php echo $jsroles;?>);"><?php tr('button_update');?></button>

		&nbsp; &nbsp;
		<button class="warn" onclick="deluser(<?php echo $userid;?>);"><?php tr('button_delete');?></button>


	</div>


	</div>
	<div class="col">

	</div>
	<div class="clear"></div>
</div>
<?php
}
