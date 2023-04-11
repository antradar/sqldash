<?php
include 'icl/dashquerydims_nav.inc.php';

function dashquerydims(){
	
	global $db;
	global $SQL_ENGINE;
	
	$queryidx=GETVAL('queryidx');
	
	$mode=GETSTR('mode');
	
	$dims=json_decode(SQET('dims'),1);
	
	$query=SQET('query'); $query=trim($query,';');
		
	$sqlmode=SQET('sqlmode');
	
	
	if ($mode!='embed'){		
	

		
	
?>
<div class="section navdash">
	<div class="sectiontitle">Query Explorer #<?php echo $queryidx;?></div>
	<input id="dashquerydims_<?php echo $queryidx;?>_chartrelay" type="hidden" onchange="nav_loadcharts('dashquerydims','dashquerydimkey','dashquerydims','<?php echo $queryidx;?>');">
	<div>
		<input id="dashquerydimkey_<?php echo $queryidx;?>" autocomplete="off" style="margin-bottom:0;display:none;" class="img-mg">
	</div>
	
	<div id="dashquerydims_<?php echo $queryidx;?>">	
<?php
	}//embed
	
	dashquerydims_shownavs('dashquerydims','dashquerydims');
?>

	
	<?php
	//echo '<pre>'; print_r($dims); echo '</pre>';
	
	$fields=array();
	foreach ($dims as $dimtype=>$dms){
		foreach ($dms as $dim) $fields[$dim]=$dimtype;	
	}

	//echo '<pre>'; print_r($fields); echo '</pre>';
	
	if (count($fields)==0) return;
	
	$params=array();
	$sqlfilters=dashquerydims_sqlfilters();
	
	$params=array_merge($params,$sqlfilters['params']);
	
	$strfields=implode(',',array_keys($fields));
	
	$cquery="select count(*) as c from ($query)t where 1=1 ".$sqlfilters['clauses'];
	$rs=sql_prep($cquery,$db,$params);
	$myrow=sql_fetch_assoc($rs);
	$count=intval($myrow['c']);	

	$perpage=30;
	
	$pager='';
	
	$maxpage=ceil($count/$perpage)-1;
	if ($maxpage<0) $maxpage=0;
	
	$page=intval(SGET('page'));
	
	if ($page<0) $page=0;
	if ($page>$maxpage) $page=$maxpage;
	
	$start=$page*$perpage;
	
	if ($maxpage>0){
		ob_start();
		
	?>
	<div style="padding:10px 0;">
		<a class="hovlink" onclick="nav_setfilter('dashquerydims_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','dashquerydims',gid('searchfilter_dashquerydims_<?php echo $queryidx;?>').value+'&page=<?php echo $page-1;?>','<?php echo $queryidx;?>');">&laquo; Prev</a>
		&nbsp; &nbsp;	
		Page 
		<a class="pageskipper" onclick="var pagenum=sprompt('Go to page:',1);if (pagenum==null||parseInt(pagenum,0)!=pagenum) return false;nav_setfilter('dashquerydims_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','dashquerydims',gid('searchfilter_dashquerydims_<?php echo $queryidx;?>').value+'&page='+(pagenum-1),'<?php echo $queryidx;?>');"><?php echo $page+1;?></a>
		of <?php echo $maxpage+1;?>
		&nbsp; &nbsp;
		<a class="hovlink" onclick="nav_setfilter('dashquerydims_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','dashquerydims',gid('searchfilter_dashquerydims_<?php echo $queryidx;?>').value+'&page=<?php echo $page+1;?>','<?php echo $queryidx;?>');">Next &raquo;</a>
		
		&nbsp; &nbsp;
		<em><?php echo $perpage;?> per page</em>
	</div>
	<?php	
		
		$pager=ob_get_clean();	
	}
	
	echo $pager;
	
	$query="select * from ($query)t where 1=1 ".$sqlfilters['clauses']." order by $strfields ";
			
	if ($SQL_ENGINE=='SQLSRV'){
		$query.=" offset $start rows fetch next $perpage rows only ";
	} else {
		$query.=" limit $start,$perpage";
	}
		
	$rs=sql_prep($query,$db,$params);
	
	?>
	<div class="stable">
	<div class="grid">
	<table class="subtable">
	<?php
	$idx=0;
		
	while ($myrow=sql_fetch_assoc($rs)){
		if ($idx==0){
	?>
	<tr class="gridheader">
		<?php foreach ($myrow as $k=>$v){
			if (!isset($fields[$k])) continue;	
		?>
		<td><b><?php echo $k;?></b></td>
		<?php }?>
	</tr>
	<?php		
		}//header
		
	?>
	<tr class="gridrow <?php if ($idx%2==0) echo 'odd'; else echo 'even';?>">
	<?php
		foreach ($myrow as $k=>$v){
			if (!isset($fields[$k])) continue;
			$dv=htmlspecialchars($v);
			if ($fields[$k]=='daterange') $dv=date('Y-n-j H:i:s',$v);
			//if ($fields[$k]=='range') $dv=number_format($v);
	?>
		<td valign="top"><?php echo $dv;?></td>
	<?php
		}//foreach row
	?>
	</tr>
	<?php

		$idx++;	
	}//while myrow

	?>
	</table>
	</div>
	</div>
	<?php
	
	echo $pager;
	
	if ($mode!='embed'){
	?>
</div>
	<?php
	}//embed

}