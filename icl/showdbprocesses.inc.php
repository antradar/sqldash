<?php

$connid=isset($_COOKIE['connid'])?$_COOKIE['connid']:null;
if (isset($connid)) include 'subconnect.php';

function showdbprocesses(){
	global $db;
	$user=userinfo();

	$mode=SGET('mode');	
	$key=SGET('key');

	if ($mode!='embed'){
?>
<div class="section">
	<div class="sectiontitle">Active Processes &nbsp; <a class="labelbutton" onclick="_inline_lookupdbprocess(gid('dbprocesskey'),1);">reload</a></div>
<?php
	
	
	//echo '<pre>'; print_r($procs); echo '</pre>';
	
	?>
	<div>
		<input class="img-mg" id="dbprocesskey" onkeyup="_inline_lookupdbprocess(this);">
	</div>
	<div id="dbprocesses">
	<?php
	}//embed
	
	$procs=array();
	
	$query="show full processlist ";
	$rs=sql_prep($query,$db);
	while ($myrow=sql_fetch_assoc($rs)){
		$procid=$myrow['Id'];
		$cmd=$myrow['Command'];
		$time=$myrow['Time'];
		if ($cmd!='Execute'&&$cmd!='Query') continue;
		
		if ($time<1) continue;
		
		$hit=1;
		if (trim($myrow['Info'])=='show full processlist'||trim($myrow['Info'])=='show processlist') $hit=0;
		
		if ($key!=''){
			$hit=0;
			
			if (stripos($myrow['Info'],$key)!==false) $hit=1;
			if ($procid==$key) $hit=1;
			
		}
		
		if (!$hit) continue;
		
		$procs[$procid]=$myrow;
	}//myrow
	
	
	uasort($procs,function($a,$b){
		$va=$a['Time'];
		$vb=$b['Time'];
		if ($va==$vb) return 0;
		return $va<$vb;
	});
	
	if (count($procs)>1&&$key!=''&&isset($user['groups']['kill'])){
	?>
	<div style="padding:20px 0;">
		Terminate ALL <span class="largertext"><?php echo count($procs);?></span> processes matching <em>"<?php echo htmlspecialchars($key);?>"</em>:
		&nbsp;
		<button class="warn" onclick="killalldbprocesses();">Terminate Matched</button>
	</div>
	<?php	
	} else {
		if (count($procs)>1){
	?>
	<div style="padding:10px 0;">
	Found <span class="largertext"><?php echo count($procs);?></span> processes.
	</div>

	<?php	
		}
	}
	
	?>
	<table width="100%">
	<tr>
		<td width="10%"><b>ID</b></td>
		<td width="10%"><b>Time</b></td>
		<td width="15%"><b>Type</b></td>
		<td width="5%"><b>Sig.</b></td>
		<td>&nbsp;</td></tr>
	<?php foreach ($procs as $procid=>$proc){
		$hash=substr(md5($proc['Info']),0,6);
		
	?>
	<tr>
		<td><?php echo $procid;?> <input class="dbprocid" type="hidden" value="<?php echo $procid;?>"></td>
		<td><?php echo number_format($proc['Time']);?></td>
		<td><?php echo $proc['Command'];?></td>
		<td><img src="imgs/t.gif" style="width:10px;height:16px;background:#<?php echo $hash;?>;"></td>
		<td>
			<a class="button" onclick="showhide('qproc_<?php echo $procid;?>');">View</a>
			<?php if (isset($user['groups']['kill'])){?>
			&nbsp; &nbsp;
			<a class="button warn" onclick="killdbprocess(<?php echo $procid;?>);">Kill</a>
			<?php }?>
		</td>
	</tr>
	<tr>
		<td colspan="5">
		<div id="qproc_<?php echo $procid;?>" style="margin-left:20px;display:none;">
		<textarea spellcheck="false" class="inplong"><?php echo htmlspecialchars($proc['Info']);?></textarea>
		</div>
		</td>
	</tr>
	<?php
	}//foreach
	?>
	</table>

	<?php
	if (count($procs)==0){
	?>
	<div class="infobox">
		No significant processes found.
	</div>
	<?php	
	}
	?>
	
	
	<?php if ($mode!='embed'){?>
	</div><!-- dbprocesses -->
</div>
<?php
	}//embed		
}
