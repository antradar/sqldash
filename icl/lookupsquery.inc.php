<?php

function lookupsquery(){
	global $sdb;
	global $SQL_ENGINE;
	
	$qidx=GETVAL('qidx');
	$user=userinfo();
	$userid=$user['userid'];
	
	$refid=intval(SGET('squeryid'));
	
	$key=GETSTR('key');
	
	$mode=SGET('mode');
	
	if ($mode!='embed'){
		
?>
<div class="section">
<div class="listbar">
	<form class="listsearch" onsubmit="_inline_lookupquery(gid('lkv_querykey'),<?php echo $qidx;?>);return false;">
	<div class="listsearch_">
		<input id="lkv_querykey" class="img-mg" onkeyup="_inline_lookupquery(this,<?php echo $qidx;?>);" autocomplete="off">
	</div>
	<input type="image" src="imgs/mg.gif" class="searchsubmit" value=".">
	</form>
</div>

<div id="lkv_querylist">
<?php
}//embed

	$query="select squeryid,squeryname,squeryconntype from squeries where userid=$userid ";
	if ($key!=''){
		$query.=" and squeryname like '%$key%' ";	
	}
	$query.=" order by squeryid desc";
	$rs=$sdb->query($query);
	

	while ($myrow=$rs->fetchArray(SQLITE3_ASSOC)){
		$squeryid=$myrow['squeryid'];
		$squeryname=$myrow['squeryname'];
	?>
	<div class="listitem" style="position:relative;<?php if ($squeryid==$refid) echo 'border:solid 2px #ffab00;';?>">
		<a onclick="loadquery(<?php echo $squeryid;?>,<?php echo $qidx;?>);"><?php echo htmlspecialchars($squeryname);?></a>
		<a onclick="delquery(<?php echo $squeryid;?>,<?php echo $qidx;?>);" style="position:absolute;top:0;right:5px;"><img src="imgs/t.gif" class="img-del"></a>
	</div>
	<?php	
	}//while
		
	if ($mode!='embed'){
?>
</div>
</div>
<?php
	}//embed	
}