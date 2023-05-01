<?php

include 'icl/showgaqr.inc.php';
include 'icl/listyubikeys.inc.php';

function showaccount(){
		
	global $sdb;
	global $missing2fa;
	$user=userinfo();
	$userid=intval($user['userid']);
	
	$query="select * from users where userid=$userid";
	$rs=$sdb->query($query);
	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	$usega=$myrow['usega'];
	$gakey=$myrow['gakey'];
	$darkmode=isset($myrow['darkmode'])?intval($myrow['darkmode']):0;

	
	//ob_start();
	
	$user=userinfo();

	global $db;
		
	$quicklist=isset($myrow['quicklist'])?intval($myrow['quicklist']):0;
	$darkmode=isset($myrow['darkmode'])?intval($myrow['darkmode']):0;
	
	
	$useyubi=$myrow['useyubi'];
	
	
	$login=$myrow['login'];
	
	
	if ($gakey=='') $usega=0;
	
	

	//makechangebar('account',"setaccountpass($darkmode);",''); //disabled for now
	//makesavebar('account');	
?>
<div class="section">

<div class="sectiontitle">
	<a ondblclick="toggletabdock();"><?php tr('account_settings');?></a>
</div>

<?php
	if (isset($missing2fa)&&$missing2fa){
?>
<div class="warnbox">
	Either Authenticator or a Hardware/Biometric Key must be added.
</div>
<?php		
	}
?>

<div class="inputrow">Your Login: <?php echo htmlspecialchars($login);?></div>


<div class="col">
	<div class="sectionheader"><?php tr('password');?></div>
	
	<div class="inputrow">
		<div class="formlabel"><?php tr('current_password');?>:</div>
		<input class="inp" id="accountpass" type="password" oninput="this.onchange();" onchange="marktabchanged('account');">
	</div>
	
	<div class="inputrow">
		<div class="formlabel"><?php tr('new_password');?>: &nbsp; &nbsp; <span style="font-weight:normal;color:#ab0200;" id="accountpasswarn"></span></div>
		<input class="inp" autocomplete="new-password" id="accountpass1" type="password" onkeyup="_checkpass(this,'accountpasswarn');" onchange="checkpass(this,'accountpasswarn');">
	</div>
	
	<div class="inputrow">
		<div class="formlabel"><?php tr('repeat_password');?>:</div>
		<input class="inp" id="accountpass2" type="password">
	</div>
		
	<div class="inputrow">
		<input type="checkbox" id="myaccount_usega" <?php if ($usega) echo 'checked';?> onclick="marktabchanged('account'); if (this.checked) {gid('myaccount_gaview').style.display='block';gid('myaccount_gatestpin').focus();} else gid('myaccount_gaview').style.display='none';">
		<label for="myaccount_usega"> use Google Authenticator</label>
	</div>
	<div id="myaccount_gaview" style="padding-left:30px;display:none<?php if ($usega) echo 'a';?>;">
		<div>
		<?php showgaqr($userid);?>
		</div>
		<div class="inputrow">
			Test PIN: <input class="inpshort" id="myaccount_gatestpin"> <button onclick="testgapin();">Test</button>
		</div>
	</div>

	<div class="inputrow">
		<input type="checkbox" id="myaccount_useyubi" <?php if ($useyubi) echo 'checked';?> onclick="marktabchanged('account'); if (this.checked) {gid('myaccount_yubikeys').style.display='block';} else gid('myaccount_yubikeys').style.display='none';">
		<label for="myaccount_useyubi">enable hardware security keys and screen lock</label>
	</div>
	
	<div id="myaccount_yubikeys" style="padding-left:30px;padding-bottom:10px;display:none<?php if ($useyubi) echo 'a';?>;">
		<?php listyubikeys();?>
	</div>
	
	
	<div class="inputrow buttonbelt">
		<button onclick="setaccountpass(<?php echo $darkmode;?>);"><?php tr('button_update');?></button>
	</div>
	
</div>
<div class="col">
	<div class="sectionheader">Interface Preferences</div>

	
	<div class="inputrow" style="line-height:1.5em;">
		<div class="formlabel">Dark Mode:</div>

		<input type="radio" name="myaccount_darkmode" id="myaccount_darkmode_0" onclick="sv('myaccount_darkmode',0);marktabchanged('account');" <?php if ($darkmode==0) echo 'checked';?>>
		<label for="myaccount_darkmode_0">same as the web browser</label> <br>

		<input type="radio" name="myaccount_darkmode" id="myaccount_darkmode_1" onclick="sv('myaccount_darkmode',1);marktabchanged('account');" <?php if ($darkmode==1) echo 'checked';?>>
		<label for="myaccount_darkmode_1">use dark theme</label> <br>

		<input type="radio" name="myaccount_darkmode" id="myaccount_darkmode_2" onclick="sv('myaccount_darkmode',2);marktabchanged('account');" <?php if ($darkmode==2) echo 'checked';?>>
		<label for="myaccount_darkmode_2">use light theme</label> <br>
				
		<input id="myaccount_darkmode" value="<?php echo $darkmode;?>" type="hidden">		
	</div>
	
</div>
<div class="clear"></div>



</div><!-- section -->

<?php

	
}