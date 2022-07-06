<?php

function showuser($userid=null){
	if (!isset($userid)) $userid=GETVAL('userid');
	
	$user=userinfo();
	if (!$user['groups']['accounts']) die('Access denied');
	
	global $db;
	global $userroles;
	
	$query="select * from users where userid=$userid";
	$rs=sql_query($query,$db);
	
	if (!$myrow=sql_fetch_array($rs)) die('This user record has been removed');
	
	$login=$myrow['login'];
	$active=$myrow['active'];
	$virtual=$myrow['virtualuser'];
	$passreset=$myrow['passreset'];
	$groupnames=$myrow['groupnames'];
	$groups=explode('|',$groupnames);
	$needcert=$myrow['needcert'];
	$certname=$myrow['certname'];
	if ($certname=='') $certname='<em>not set</em>';
	
	header('newtitle: '.$login);
	
?>
<div class="section">
	<div class="sectiontitle"><?php echo $login;?></div>

	<div class="col">


	<div class="inputrow">
		<div class="formlabel"><?php tr('username');?>:</div>
		<input class="inpmed" id="login_<?php echo $userid;?>" value="<?php echo htmlspecialchars($login);?>">
	</div>
	<div class="inputrow">
		<input type="checkbox" id="active_<?php echo $userid;?>" <?php if ($active) echo 'checked';?>> <label for="active_<?php echo $userid;?>"><?php tr('account_active');?></label>
		&nbsp;&nbsp;
		<input type="checkbox" id="virtual_<?php echo $userid;?>" <?php if ($virtual) echo 'checked';?> onclick="if (this.checked) gid('userpasses_<?php echo $userid;?>').style.display='none'; else gid('userpasses_<?php echo $userid;?>').style.display='block';"> <label for="virtual_<?php echo $userid;?>"><?php tr('account_virtual');?></label>
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

	<div class="inputrow" id="cardsettings_<?php echo $userid;?>">
		<div class="formlabel">ID Card: &nbsp; 
			<span style="font-weight:normal;" id="cardstatus_<?php echo $userid;?>"><?php echo $certname;?></span> <a class="labelbutton" onclick="loadsmartcard(<?php echo $userid;?>);">load card</a>
			<span style="display:none;"><textarea id="cert_<?php echo $userid;?>" value=""></textarea></span>
		</div>
		<input type="checkbox" id="needcert_<?php echo $userid;?>" <?php if ($needcert) echo 'checked';?>> card must be present at sign-in

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
		<button onclick="updateuser(<?php echo $userid;?>);"><?php tr('button_update');?></button>

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
