<?php

function runquery(){
	global $db;
	global $SQL_ENGINE;

	$dbname=GETSTR('dbname');
	$queryidx=GETVAL('queryidx');
	$shortview=GETVAL('shortview');
	$usemacros=GETVAL('usemacros');
	
	if (in_array($SQL_ENGINE,array('MySQL','MySQLi'))) sql_select_db($db,$dbname);
	
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
	
	if ($token0=='select'){
		//get table name
		preg_match('/ from (\S+)?/i',$query,$matches);
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

		
	if ($token0=='select'&&!preg_match('/limit\s*/i',$query)){
		$cquery="select count(*) as c from ($query) count_query";
		$rs=sql_prep($cquery,$db);
		$myrow=sql_fetch_assoc($rs);
		$c=$myrow['c'];
		
	?>
	<div>
	Found records: <?php echo number_format($c);?>
	</div>
	<?php	
		
		$perpage=30;
		$page=isset($_GET['page'])?intval($_GET['page']):0;
		$maxpage=ceil($c/$perpage)-1;
		if ($maxpage<0) $maxpage=0;
		if ($page>$maxpage) $page=$maxpage;
		
		$start=$page*$perpage;
		if (in_array($SQL_ENGINE,array('MySQL','MySQLi','ClickHouse'))) $query.=" limit $start,$perpage ";
		if ($SQL_ENGINE=='SQLSRV') $query.=" order by @@identity offset $start rows fetch next $perpage rows only ";	
		
		if ($maxpage>0){
?>
<div class="listpager">
	<a class="hovlink" onclick="runquery(<?php echo $queryidx;?>,'<?php echo $dbname;?>&page=<?php echo $page-1;?>');">&laquo; Prev</a>
	&nbsp; &nbsp;
	Page <a class="pageskipper" onclick="var pagenum=sprompt('Go to page:',1);if (pagenum==null||parseInt(pagenum,0)!=pagenum) return false;runquery(<?php echo $queryidx;?>,'<?php echo $dbname;?>&page='+(pagenum-1),null,null,{persist:true});"><?php echo $page+1;?></a> of <?php echo $maxpage+1;?>
	&nbsp;
	<a class="hovlink" onclick="runquery(<?php echo $queryidx;?>,'<?php echo $dbname;?>&page=<?php echo $page+1;?>');">Next &raquo;</a>
	&nbsp; &nbsp;
	<em><?php echo $perpage;?> per page</em>
</div>
<?php			
		}
	}
	
	$ta=microtime(1);

		
	$rs=sql_prep($query,$db);
	if (!isset($c)) $c=sql_affected_rows($db,$rs);
	
	
	$idx=0;
	
	$colnames=array();
?>
<div class="stable" id="queryview_<?php echo $queryidx;?>">
<div class="grid">
<table>
<?php	
	
	while ($myrow=sql_fetch_assoc($rs)){
		if ($idx==0&&$c>1){
	?>
	<tr class="gridheader">
	<?php
		foreach ($myrow as $k=>$v){
			array_push($colnames,$k);
	?>
		<td id="colbm_<?php echo $queryidx;?>_<?php echo $k;?>"><b><?php echo hspc($k);?></b></td>
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
				if (isset($pkey)) $pval=$myrow[$pkey];
				if ($shortview){
					if (mb_strlen($v)>60) $v=mb_substr($v,0,57).'...';	
				}
				$dv=hspc($v);
				if ($v==null&&!isset($v)) $dv='<span style="color:#EE00AA;">NULL</span>';
				if (isset($v)&&$v==='') $dv='<em style="color:#669966;">(empty)</em>';
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
				if (isset($pkey)) $pval=$myrow[$pkey];
				if ($shortview){
					if (mb_strlen($v)>60) $v=mb_substr($v,0,57).'...';	
				}
				$dv=hspc($v);
				if ($v==null&&!isset($v)) $dv='<span style="color:#000088;">NULL</span>';
				if (isset($v)&&$v==='') $dv='<em style="color:#666666;">(empty)</em>';
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

