<?php

function newconn(){
	global $conntypes;
?>
<div class="section">
	<div class="sectiontitle"><?php tr('list_conn_add_tab');?></div>
	
<div class="col">
	
	<div class="inputrow">
		<div class="formlabel"><?php tr('conn_label_connname');?>:</div>
		<input class="inp" id="connname_new">
	</div>
	<div class="inputrow">
		<div class="formlabel"><?php tr('conn_label_conntype');?>:</div>
		<select class="inp" id="conntype_new" onchange="setconntype(this);">
			<option value=""></option>
			<?php foreach ($conntypes as $k=>$v){?>
			<option value="<?php echo $k;?>"><?php echo $v;?></option>
			<?php }?>
		</select>
	</div>
	<div class="inputrow" id="newconn_host" style="display:none;">
		<div class="formlabel"><?php tr('conn_label_connhost');?>:</div>
		<input class="inp" id="connhost_new">
	</div>
	<div class="inputrow" id="newconn_dbname" style="display:none;">
		<div class="formlabel"><?php tr('conn_label_conndbname');?>:</div>
		<input class="inp" id="conndbname_new">
	</div>
	<div class="inputrow" id="newconn_apiport" style="display:none;">
		<div class="formlabel"><?php tr('conn_label_connapiport');?>:</div>
		<input class="inp" id="connapiport_new">
	</div>
	<div class="inputrow" id="newconn_connuser" style="display:none;">
		<div class="formlabel"><?php tr('conn_label_connuser');?>:</div>
		<input class="inp" id="connuser_new" onfocus="if (gid('conntype_new').value=='sfdx') lookupentity(this,'sfdx_org','Authenticated Orgs.');" 
		onchange="if (this.value2!=''&&gid('connname_new').value=='') gid('connname_new').value='SFDX:'+this.value2;"
		>
	</div>
	<div class="inputrow" id="newconn_connpass" style="display:none;">
		<div class="formlabel"><?php tr('conn_label_connpass');?>:</div>
		<input class="inp" id="connpass_new" type="password">
	</div>
	
	<div class="inputrow" id="newconn_appdomain" style="display:none;">
		<div class="formlabel">API Domain:</div>
		<input class="inplong" id="appdomain_new" onchange="makeconnbutton();">
	</div>
	<div class="inputrow" id="newconn_appredirurl" style="display:none;">
		<div class="formlabel">Redirect URL: &nbsp; <em>(not stored)</em></div>
		<input class="inpmed" id="appredirurl_new" onchange="makeconnbutton();">
	</div>	
	<div class="inputrow" id="newconn_appclientid" style="display:none;">
		<div class="formlabel">Client ID:</div>
		<input class="inpmed" id="appclientid_new" onchange="makeconnbutton();">
	</div>
	<div class="inputrow" id="newconn_appsecret" style="display:none;">
		<div class="formlabel">Secret:</div>
		<input class="inplong" id="appsecret_new">
	</div>
	<div class="inputrow" id="newconn_apptoken" style="display:none;">
		<div class="formlabel">Refresh Token:</div>
		<input class="inplong" id="apptoken_new">
	</div>
	
	
</div>
<div class="clear"></div>

	<div class="inputrow buttonbelt">
		<a class="button" href=# onclick="addconn('<?php emitgskey('addconn');?>');return false;"><?php tr('button_conn_add');?></a>
		&nbsp;
		<span id="newconnbuttons">
		
		</span>
	</div>
	
	<div id="newconnhelp">
	
	</div>

</div>
<?php

}
