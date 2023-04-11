<?php
include 'fsql-sqlite.php';

function runquery(){
	global $db;
	global $SQL_ENGINE;
	
	global $codepage;
	global $sqlite_root;
	

	$dbname=GETSTR('dbname');
	$queryidx=GETVAL('queryidx');
	$shortview=GETVAL('shortview');
	$usemacros=GETVAL('usemacros');
	
	$sqlmode=SGET('sqlmode');
	
	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))) sql_select_db($db,$dbname);
		
	if ($sqlmode=='sqlite'){
		$dbname=basename($dbname);
		$fdb=fsql_get_db($sqlite_root.$dbname);
		$db=null;
	}
	
	
	$query=trim($_POST['query']);

	$query=str_replace("\r\n#\r\n","\n#\n",$query);
	$qlines=explode("\n#\n",$query);

	foreach($qlines as $qidx=>$query){	
	
	$query=trim($query,';');

	$now=time();
	if ($usemacros){
		$query=str_replace('##NOW##',$now,$query);
	}

	//check for select+limit
	
	$tokens=explode(' ',$query);
	$token0=$tokens[0];
	
	$tquery=preg_replace('/limit\s*(\d+),\s*\d+/','limit $1',$query);
	
	$tablename='';
	$pkey='';
	
	if (isset($db)&&$token0=='select'){
		//get table name
		preg_match('/\s*from (\S+)?/i',$query,$matches);
		$tablenames=explode(',',noapos($matches[1]));
		$tablename=trim($tablenames[0]);
		
		if ($tablename!=''){
			$dquery="describe $tablename";
			
			if ($SQL_ENGINE=='SQLSRV'){
				$dquery="select C.COLUMN_NAME FROM  
				INFORMATION_SCHEMA.TABLE_CONSTRAINTS T  
				JOIN INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE C  
				ON C.CONSTRAINT_NAME=T.CONSTRAINT_NAME  
				WHERE  
				C.TABLE_NAME='$tablename'  
				and T.CONSTRAINT_TYPE='PRIMARY KEY'";	
			}
			
			$rs=sql_prep($dquery,$db);
			while ($myrow=sql_fetch_assoc($rs)){
				if (isset($myrow['Key'])&&$myrow['Key']=='PRI') $pkey=$myrow['Field'];
				if ($SQL_ENGINE=='SQLSRV'&&$myrow['COLUMN_NAME']!='') $pkey=$myrow['COLUMN_NAME'];
				if ($pkey==''&&$SQL_ENGINE=='ClickHouse'){
					if ($myrow['type']=='UInt64'&&preg_match('/id$/',$myrow['name'])||$myrow['type']=='UUID'||$myrow['comment']=='identity') $pkey=$myrow['name'];
				}
			}
		}	
	}

	$perpage=30;
	$page=isset($_GET['page'])?intval($_GET['page']):0;
		
	
	if ($token0=='select'){
		if (preg_match('/\s*limit\s*(\d+)$/',$query,$matches)){//promote to a,b paging
			$query=preg_replace('/\s*limit\s*\d+$/',' limit 0,'.$matches[1],$query);
		}
		
		if (preg_match('/\s*limit\s*(\d+)\s*,\s*(\d+)$/',$query,$matches)){
			$rstart=$matches[1];
			$perpage=$matches[2];
			if (!isset($_GET['page'])) $page=ceil($rstart/$perpage);
			$start=$page*$perpage;
			
			$query=preg_replace('/\s*limit\s*\d+\s*,\s*\d+$/','',$query);
			
		}
	}
	
	if ($token0=='select'&&(!preg_match('/limit\s*/i',$query)||!preg_match('/fetch\s*next\s*\d+\s*rows\s*only/i',$query) )){

		if ($pkey!=''){
	?>
	<a class="labelbutton" onclick="if (!sconfirm('Are you sure you want to insert a blank record?')) return;ajxpgn('blankadder_<?php echo $queryidx;?>',document.appsettings.codepage+'?cmd=addblankrow&table=<?php echo $tablename;?>&pkey=<?php echo $pkey;?>',1);">insert a blank row</a>
	<div id="blankadder_<?php echo $queryidx;?>" style="margin-top:10px;padding:5px 10px;border:solid 1px #999999;display:none;">
		
	</div>
	<?php		
		}		

		$cquery="select count(*) as c from ($query) count_query";
		if (isset($db)) {
			$rs=sql_prep($cquery,$db);
			$myrow=sql_fetch_assoc($rs);
		}
		if (isset($fdb)) {
			$rs=fsql_query($cquery,$fdb);
			$myrow=fsql_fetch_assoc($rs);
		}

		$c=$myrow['c'];
		
	?>
	<div id="querydims_<?php echo $queryidx;?>" style="padding:10px 0;"></div>
	<div>
	Found records: <?php echo number_format($c);?>
	&nbsp; &nbsp;
	<a class="hovlink" onclick="exportcsv(<?php echo $queryidx;?>,'<?php echo $sqlmode;?>');">Export to CSV</a>
		<form style="display:none;" id="csvqueryform_<?php echo $queryidx;?>" action="<?php echo $codepage;?>?cmd=exportcsv&dbname=<?php echo $dbname;?>&tablename=<?php echo $tablename;?>&queryidx=<?php echo $queryidx;?>" method="POST" target=_blanks>
			<textarea id="csvquery_<?php echo $queryidx;?>" name="csvquery_<?php echo $queryidx;?>"></textarea>
		</form>
	</div>
	<?php	
		if ($page<0) $page=0;
		$maxpage=ceil($c/$perpage)-1;
		if ($maxpage<0) $maxpage=0;
		if ($page>$maxpage) $page=$maxpage;
				
		$start=$page*$perpage;
		if (in_array($SQL_ENGINE,array('MySQL','MySQLi','ClickHouse'))) $query.=" limit $start,$perpage ";
		if ($SQL_ENGINE=='SQLSRV') $query.=" order by @@identity offset $start rows fetch next $perpage rows only ";	
		
		if ($maxpage>0){
?>
<div class="listpager">
	<a class="hovlink" onclick="runquery(<?php echo $queryidx;?>,'<?php echo $dbname;?>&page=<?php echo $page-1;?>','<?php echo $sqlmode;?>');">&laquo; Prev</a>
	&nbsp; &nbsp;
	Page <a class="pageskipper" onclick="var pagenum=sprompt('Go to page:',1);if (pagenum==null||parseInt(pagenum,0)!=pagenum) return false;runquery(<?php echo $queryidx;?>,'<?php echo $dbname;?>&page='+(pagenum-1),'<?php echo $sqlmode;?>');"><?php echo $page+1;?></a> of <?php echo $maxpage+1;?>
	&nbsp;
	<a class="hovlink" onclick="runquery(<?php echo $queryidx;?>,'<?php echo $dbname;?>&page=<?php echo $page+1;?>','<?php echo $sqlmode;?>');">Next &raquo;</a>
	&nbsp; &nbsp;
	<em><?php echo $perpage;?> per page</em>
</div>
<?php			
		}
	}
	
	$ta=microtime(1);

	if (isset($db)) {
		$rs=sql_prep($query,$db);
		if (!isset($c)||$SQL_ENGINE!='SQLSRV') $c=sql_affected_rows($db,$rs);
	}
	
	if (isset($fdb)) {
		$rs=fsql_query($query,$fdb);
		if (!isset($c)) $c=fsql_affected_rows($fdb,$rs);
	}
	
	$idx=0;
	
	$colnames=array();
?>
<div class="stable" id="queryview_<?php echo $queryidx;?>">
<div class="grid">
<table>
<?php

	$fetchfunc='sql_fetch_assoc';
	if (isset($fdb)) $fetchfunc='fsql_fetch_assoc';
	
	while ($myrow=$fetchfunc($rs)){
		if ($idx==0&&$c>1){
	?>
	<tr class="gridheader">
	<?php
		foreach ($myrow as $k=>$v){
			array_push($colnames,$k);
	?>
		<td id="colbm_<?php echo $queryidx;?>_<?php echo $k;?>">
		
		<?php if($k!=$pkey||true){
		?>
		<b><a style="color:#000088;" onclick="lookupentity(this,'querydim&queryidx=<?php echo $queryidx;?>&table=<?php echo $tablename;?>&fkey=<?php echo $k;?>&pkey=<?php if ($k==$pkey) echo '1'; else echo '0';?>','Edit Dimensions');"><?php echo hspc($k);?></a></b>	
		<?php
		} else {
		?>
		<b><?php echo hspc($k);?></b>
		<?php
		}
		?>

		</td>
	<?php		
		}//foreach header col
	?>
	</tr>
	<?php		
		}
		
	?>
	<tr class="gridrow <?php echo $idx%2==1?'even':'odd';?>">
	<?php
		
		if ($c>1){
			foreach ($myrow as $k=>$v){
				$pval='';
				if (isset($pkey)&&isset($myrow[$pkey])) $pval=$myrow[$pkey];
				if ($shortview){
					if (is_string($v)&&mb_strlen($v)>60) $v=mb_substr($v,0,57).'...';	
				}
				$dv=hspc($v);
				if ($v==null&&!isset($v)) $dv='<span style="color:#EE00AA;">NULL</span>';
				if (isset($v)&&$v==='') $dv='<em style="color:#669966;">(empty)</em>';
				if (is_a($dv,'DateTime')){
					$dv=date_format($dv,'Y-n-j H:i:s e');	
				}
		?>
			<td valign="top"><acronym 
				style="cursor:pointer;" 
				class="cell_<?php echo $dbname;?>_<?php echo $tablename;?>_<?php echo $k;?>_<?php echo $pval;?>" 
				title="<?php echo hspc($k);?>" 
				onclick="lookupentity(this,'cell&table=<?php echo $tablename;?>&pkey=<?php echo $pkey;?>&pval=<?php echo $pval;?>&fkey=<?php echo $k;?>','Cell Properties');"
				id="cell_<?php echo $queryidx;?>_<?php echo $qidx;?>_<?php echo $idx;?>_<?php echo $k;?>"><?php echo $dv;?></acronym>
			</td>
		<?php
			}//foreach
		} else {//single mode
			foreach ($myrow as $k=>$v){
				$pval='';
				if (isset($pkey)&&isset($myrow[$pkey])) $pval=$myrow[$pkey];
				if ($shortview){
					if (is_string($v)&&mb_strlen($v)>60) $v=mb_substr($v,0,57).'...';	
				}
				$dv=hspc($v);
				if ($v==null&&!isset($v)) $dv='<span style="color:#000088;">NULL</span>';
				if (isset($v)&&$v==='') $dv='<em style="color:#666666;">(empty)</em>';
				if (is_a($dv,'DateTime')){
					$dv=date_format($dv,'Y-n-j H:i:s e');	
				}				
		?>
		<tr>
			<td valign="top"><?php echo hspc($k);?></td>
			<td valign="top"><span
				style="cursor:pointer"
				class="cell_<?php echo $dbname;?>_<?php echo $tablename;?>_<?php echo $k;?>_<?php echo $pval;?>" 
				title="<?php echo hspc($k);?>" 
				onclick="lookupentity(this,'cell&table=<?php echo $tablename;?>&pkey=<?php echo $pkey;?>&pval=<?php echo $pval;?>&fkey=<?php echo $k;?>','Cell Properties');"
				id="cell_<?php echo $queryidx;?>_<?php echo $qidx;?>_<?php echo $idx;?>_<?php echo $k;?>"			
			><?php echo $dv;?></span></td>
		</tr>
		<?php
			}//foreach
			
		}
	?>	
	</tr>
	<?php	
		
		$idx++;
	}
	
?>
</table>
</div>
</div>
<textarea style="display:none;" class="inplong" id="colnames_<?php echo $queryidx;?>"><?php echo json_encode($colnames);?></textarea>
<?php	
	
	$tb=microtime(1);
	
	echo "<br><br>Query time: ".round(($tb-$ta),3).' secs';
	
	if ($qidx<count($qlines)-1) echo "<hr>";
	
	}//foreach qline
	
}

