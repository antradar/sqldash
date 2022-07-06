<?php

function showaccount(){
	global $user;

	global $db;
	
?>
<div class="section">

<div class="sectiontitle"><?php tr('account_settings');?></div>

<div class="sectionheader"><?php tr('password');?></div>
<table>

<tr><td><?php tr('current_password');?>:</td>
<td><input id="accountpass" type="password"></td>
</tr>

<tr><td><?php tr('new_password');?>:</td>
<td><input id="accountpass1" type="password"></td>
</tr>

<tr><td><?php tr('repeat_password');?>:</td>
<td><input id="accountpass2" type="password"></td>
</tr>

<tr><td></td>
<td>
<button onclick="setaccountpass();"><?php tr('change_password');?></button>
</td>
</tr>

</table>

</div>
<?php
	
}