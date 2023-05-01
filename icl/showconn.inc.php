<?php

function showconn($connid=null){
	if (!isset($connid)) $connid=GETVAL('connid');
	
	global $sdb;
	
	$user=userinfo();
	
	$query="select * from conns where connid=$connid";
	$rs=$sdb->query($query);

	
	if (!$myrow=$rs->fetchArray(SQLITE3_ASSOC)) die(_tr('record_removed'));
	
	$connname=$myrow['connname'];
	$conntype=$myrow['conntype'];
	$connhost=$myrow['connhost'];
	$conndbname=$myrow['conndbname'];
	$connapiport=$myrow['connapiport'];
	$connuser=$myrow['connuser'];
	$connpass=$myrow['connpass'];
	
	if ($connpass!='') $connpass=decstr($connpass,SQLDASH_DB_TOKEN);

	header('newtitle:'.tabtitle(htmlspecialchars($connname)));
	//makechangebar('conn_'.$connid,"updateconn('$connid','".makegskey('updateconn_'.$connid)."');");
	//makesavebar('conn_'.$connid);
?>
<div class="section">
	<div class="sectiontitle"><?php echo htmlspecialchars($connname);?></div>

	<div class="col">


	<div class="inputrow">
		<div class="formlabel"><?php tr('conn_label_connname');?>:</div>
		<input class="inpmed" id="connname_<?php echo $connid;?>" value="<?php echo htmlspecialchars($connname);?>" oninput="this.onchange();" onchange="marktabchanged('conn_<?php echo $connid;?>');">
	</div>
	<div class="inputrow">
		<div class="formlabel"><?php tr('conn_label_conntype');?>:</div>
		<input class="inpmed" id="conntype_<?php echo $connid;?>" value="<?php echo htmlspecialchars($conntype);?>" oninput="this.onchange();" onchange="marktabchanged('conn_<?php echo $connid;?>');">
	</div>
	<div class="inputrow">
		<div class="formlabel"><?php tr('conn_label_connhost');?>:</div>
		<input class="inpmed" id="connhost_<?php echo $connid;?>" value="<?php echo htmlspecialchars($connhost);?>" oninput="this.onchange();" onchange="marktabchanged('conn_<?php echo $connid;?>');">
	</div>
	<div class="inputrow">
		<div class="formlabel"><?php tr('conn_label_conndbname');?>:</div>
		<input class="inpmed" id="conndbname_<?php echo $connid;?>" value="<?php echo htmlspecialchars($conndbname);?>" oninput="this.onchange();" onchange="marktabchanged('conn_<?php echo $connid;?>');">
	</div>
	<div class="inputrow">
		<div class="formlabel"><?php tr('conn_label_connapiport');?>:</div>
		<input class="inpmed" id="connapiport_<?php echo $connid;?>" value="<?php echo htmlspecialchars($connapiport);?>" oninput="this.onchange();" onchange="marktabchanged('conn_<?php echo $connid;?>');">
	</div>
	<div class="inputrow">
		<div class="formlabel"><?php tr('conn_label_connuser');?>:</div>
		<input class="inpmed" id="connuser_<?php echo $connid;?>" value="<?php echo htmlspecialchars($connuser);?>" oninput="this.onchange();" onchange="marktabchanged('conn_<?php echo $connid;?>');">
	</div>
	<div class="inputrow">
		<div class="formlabel"><?php tr('conn_label_connpass');?>:</div>
		<input class="inpmed" type="password" id="connpass_<?php echo $connid;?>" value="<?php echo htmlspecialchars($connpass);?>" oninput="this.onchange();" onchange="marktabchanged('conn_<?php echo $connid;?>');">
	</div>

	
	<div class="inputrow buttonbelt">
		<button onclick="updateconn('<?php echo $connid;?>','<?php emitgskey('updateconn_'.$connid);?>');"><?php tr('button_update');?></button>

		&nbsp; &nbsp;
		<button class="warn" onclick="delconn('<?php echo $connid;?>','<?php emitgskey('delconn_'.$connid);?>');"><?php tr('button_delete');?></button>


	</div>


	</div>
	<div class="col">

	</div>
	<div class="clear"></div>
</div>
<?php
}
