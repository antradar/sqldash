<?php
include 'fsql-sqlite.php';
if (!isset($_GET['sqlmode'])||$_GET['sqlmode']!='sqlite') include 'subconnect.php';

include 'pretty_array.php';
include 'icl/getbadtables.inc.php';

function runquery(){
	global $db;
	global $SQL_ENGINE;
	
	global $codepage;
	global $sqlite_root;
	global $profile_root;
	
	$dbname=GETSTR('dbname');
	$queryidx=GETVAL('queryidx');
	$shortview=GETVAL('shortview');
	$usemacros=GETVAL('usemacros');
	
	$predrop=intval(SQET('predrop'));
	
	$explain=GETVAL('explain');
		
	$sqlmode=SGET('sqlmode');
	
	$prepfunc='sql_prep';
	if ($SQL_ENGINE=='MySQL-noprep') $prepfunc='sql_query';
	
		
	if ($sqlmode!='sqlite'&&in_array($SQL_ENGINE,array('MySQL','MySQLi'))) sql_select_db($db,$dbname);
		
	if ($sqlmode=='sqlite'){
		$dbname=basename($dbname);
		$fdb=fsql_get_db($sqlite_root.$dbname);
		$db=null;
	}
	
	$relmap=null;
	
	$relmapfn=$profile_root.$dbname.'.relmap.json';
	if (file_exists($relmapfn)){
		$relmap=json_decode(file_get_contents($relmapfn),1);	
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
	
	if ($explain) $query="explain format=json ".$query;

	//check for select+limit
	
	$query=preg_replace('/^create\s*definer=\S+\s*trigger/i','CREATE TRIGGER',$query);
	$query=preg_replace('/^create\s*definer=\S+\s*function/i','CREATE FUNCTION',$query);
	
	$tokens=preg_split('/\r\n|\n|\s/',$query);
	$token0=trim(strtolower($tokens[0]));
	$token1=count($tokens)>1?strtolower($tokens[1]):'';
	$token2=count($tokens)>2?strtolower($tokens[2]):'';
	$tquery=preg_replace('/limit\s*(\d+),\s*\d+/','limit $1',$query);
	
	$tablename='';
	$pkey='';
	
	$user=userinfo();

	if ($SQL_ENGINE=='mongodb'){
		$qobj=json_decode($query,1);
		if (isset($qobj['count'])) $token0='select';
		if (isset($qobj['find'])) $token0='select';
		if (isset($qobj['aggregate'])) $token0='select';
		if (isset($qobj['update'])) $token0='update';
		if (isset($qobj['insert'])) $token0='insert';
		if (isset($qobj['delete'])) $token0='delete';
	}

	if (1==SQLDASH_AUTH_MODE||2==SQLDASH_AUTH_MODE){
		if ($token0!='select'&&$token0!='describe'&&$token0!='show'&&$token0!='explain'&&!in_array($token0,array_keys($user['groups']))) apperror('Access denied');
	}
	
	if ($token0=='select'){
		//get table name
		if (!preg_match('/\s*from (\[[\S\s]+?\])/',$query,$matches)){
			preg_match('/\s*from (\S+)?/i',$query,$matches);
		}
		$tablenames=explode(',',noapos($matches[1]));
		$tablename=trim($tablenames[0]);		
	}

	if (isset($db)&&$token0=='select'){
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
			
			if ($SQL_ENGINE=='MySQL-noprep') $dquery="select 1 ";
			
			$rs=$prepfunc($dquery,$db);
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
		
	if ($SQL_ENGINE=='mongodb'){
		$mobj=@json_decode(trim($query),1);
		if (!isset($mobj)){
			echo "Malformed MongoDB command. Check the JSON syntax.";
			return;
		}

		if (isset($mobj['find'])){
			$tablename=$mobj['find'];
			$pkey='_id';
		}

		if (isset($mobj['find'])&&!isset($mobj['limit'])){
			$cquery=array('count'=>$mobj['find']);
			if (isset($mobj['filter'])) $cquery['query']=$mobj['filter'];
			$cmd=new MongoDb\Driver\Command($cquery);
			$res=$db->executeCommand($dbname,$cmd);
			$c=$res->toArray()[0]->n;


			$mobj['limit']=$perpage;
			$mobj['skip']=$page*$perpage;
			$query=json_encode($mobj);
			$token0='select'; //injected
		}
	}
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
			if (0==SQLDASH_AUTH_MODE||isset($user['groups']['update'])){
	?>
	<a class="labelbutton" onclick="if (!sconfirm('Are you sure you want to insert a blank record?')) return;ajxpgn('blankadder_<?php echo $queryidx;?>',document.appsettings.codepage+'?cmd=addblankrow&table=<?php echo $tablename;?>&pkey=<?php echo $pkey;?>',1);">insert a blank row</a>
	<div id="blankadder_<?php echo $queryidx;?>" style="margin-top:10px;padding:5px 10px;border:solid 1px #999999;display:none;">
		
	</div>
	<?php		
			}
		}		

	if ($SQL_ENGINE!='mongodb'&&$SQL_ENGINE!='MySQL-noprep'){
		$cquery="select count(*) as c from ($query) count_query";
		$cta=microtime(1);
		if (isset($db)) {
			$rs=sql_prep($cquery,$db);
			$myrow=sql_fetch_assoc($rs);
		}
		if (isset($fdb)) {
			$rs=fsql_query($cquery,$fdb);
			$myrow=fsql_fetch_assoc($rs);
		}
		$ctb=microtime(1);

		$c=$myrow['c'];
	}
	
	if ($SQL_ENGINE=='MySQL-noprep'){
		$c=0;	
	}	
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
		if ($SQL_ENGINE=='sfdx'||$SQL_ENGINE=='MySQL-noprep') $query.=" limit $perpage offset $start ";	
		
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
		
		if (($token0=='create'||$token0=='drop')&&in_array($token1,array('trigger','function'))){

			if ($token0=='create'){
				$dquery="show create $token1 $token2";
				ob_start();
				$rs=sql_query($dquery,$db);
				ob_end_clean();
				if ($myrow=sql_fetch_assoc($rs)){
					if (!$predrop){
					header('errfunc: predrop');
					apperror("The $token1 $token2 already exists. Do you want to drop the old one first?");
					} else {//drop existing trigger/functrion
						$dquery="drop $token1 $token2";
						sql_query($dquery,$db);
					}
				}
			}
			
			$rs=sql_query($query,$db);	
		} else {
			if ($SQL_ENGINE=='MySQL-noprep'||$SQL_ENGINE=='MySQLi'){
				$rss=array();
				if ($db->multi_query($query)){
					do {
						if ($rs = $db->store_result()){
							array_push($rss,$rs);
						}
					} while ($db->next_result());
				} else {
					echo 'Error executing query: '.$db->error."<hr>";	
				}
			} else {
				$rs=$prepfunc($query,$db);
				$rss=array($rs);	
			}
			
		}
		if (!isset($c)||$SQL_ENGINE!='SQLSRV') $c=sql_affected_rows($db,$rs);
	}
	
	if (isset($fdb)) {
		$rs=fsql_query($query,$fdb);
		$rss=array($rs);
		if (!isset($c)) $c=fsql_affected_rows($fdb,$rs);
	}
	$idx=0;
	
	$colnames=array();
	
	$fetchfunc='sql_fetch_assoc';
	if (isset($fdb)) $fetchfunc='fsql_fetch_assoc';	
	
	if ($explain){
		foreach ($rss as $rs){
		$myrow=$fetchfunc($rs);
		$res=$myrow['EXPLAIN'];
		$eobj=json_decode($res,1);
		if (count($eobj)==1){
			foreach ($eobj as $k=>$v){
				$eobj=$v;
				break;	
			}	
		}
		pretty_array($eobj,'explain_'.$queryidx,0);
		$badtables=getbadtables($eobj);
		if (count($badtables)>0){
		?>
		<div class="warnbox">
			Some tables may not have proper keys:
			<?php foreach ($badtables as $badtable){
			?>
			<a class="hovlink" onclick="showtable('<?php echo $badtable;?>','<?php echo $dbname;?>');"><?php echo $badtable;?></a> &nbsp;
			<?php	
			}?>
		</div>
		<?php
		}
		
	?>
	<a class="hovlink" onclick="showhide('explainjson_<?php echo $queryidx;?>');">view raw</a>
	<div id="explainjson_<?php echo $queryidx;?>" style="display:none;padding:10px 0;">
	<textarea class="inplong" style="height:400px;"><?php echo htmlspecialchars($res);?></textarea>
	</div>
	<?php	
		//return;
		}
	}
	
	if($explain) return;
	
	foreach ($rss as $rs){
		
?>
<div class="stable" id="queryview_<?php echo $queryidx;?>">
<div class="grid">
<table>
<?php

	
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
		<b><a class="hovlink" onclick="lookupentity(this,'querydim&sqlmode=<?php echo $sqlmode;?>&queryidx=<?php echo $queryidx;?>&table=<?php echo $tablename;?>&fkey=<?php echo $k;?>&pkey=<?php if ($k==$pkey) echo '1'; else echo '0';?>','Edit Dimensions');"><?php echo hspc($k);?></a></b>	
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
		
		if ($c>1||count($rss)>1){
			foreach ($myrow as $k=>$v){
				$pval='';
				if (isset($pkey)&&isset($myrow[$pkey])) {
					$pval=$myrow[$pkey];
					if ($SQL_ENGINE=='mongodb'&&is_array($pval)) $pval='!'.$pval['$oid'];
				}
				if ($shortview){
					if (is_string($v)&&mb_strlen($v)>60) $v=mb_substr($v,0,57).'...';	
				}
				if ($SQL_ENGINE=='mongodb'){
					if ($k=='_id') {
						if (is_array($v)) $v=$v['$oid'];
					}
				}
				$dv=hspc($v);
				if ($v==null&&!isset($v)) $dv='<span style="color:#EE00AA;">NULL</span>';
				if (isset($v)&&$v==='') $dv='<em style="color:#669966;">(empty)</em>';
				if (is_a($dv,'DateTime')){
					$dv=date_format($dv,'Y-n-j H:i:s e');	
				}
				
				$reckv='';
				if (isset($relmap)&&isset($relmap[$tablename])&&isset($relmap[$tablename][$k])&&isset($relmap[$tablename][$k]['table'])&&is_numeric($v)){
					$reckv=$relmap[$tablename][$k]['table'].'/'.$relmap[$tablename][$k]['pkey'].'/'.$v;	
				}				
				
		?>
			<td valign="top"><acronym 
				style="
				<?php if (isset($relmap)&&isset($relmap[$tablename])&&isset($relmap[$tablename][$k])&&is_numeric($v)) echo 'display:inline-block;border:solid 1px #848cf7;padding:0 3px;';?>				
				cursor:pointer;" 
				class="cell_<?php echo $dbname;?>_<?php echo $tablename;?>_<?php echo $k;?>_<?php echo $pval;?>" 
				title="<?php echo hspc($k);?>" 
				onclick="resolvecell(this,'cell&sqlmode=<?php echo $sqlmode;?>','<?php echo $tablename;?>','<?php echo $pkey;?>','<?php echo $pval;?>','<?php echo $k;?>','Cell Properties','<?php echo $dbname;?>','<?php echo $reckv;?>');"
				id="cell_<?php echo $queryidx;?>_<?php echo $qidx;?>_<?php echo $idx;?>_<?php echo $k;?>"><?php if (is_string($dv)) echo $dv; else print_r($dv);?></acronym>
			</td>
		<?php
			}//foreach
		} else {//single mode
		
			if (isset($relmap)){
				$revmap=array();
				foreach ($relmap as $rev_table=>$rev){
					foreach ($rev as $rkey=>$r){
						if (isset($r['table'])&&$r['table']==$tablename){
							if (!isset($revmap[$rev_table])) $revmap[$rev_table]=array();
							array_push($revmap[$rev_table],array('rkey'=>$rkey,'rpkey'=>$r['pkey']));	
						}	
					}	
				}
				
				if (count($revmap)>0){
		
		?>
		<tr>
			<td>
			See Also: 
			</td>
			<td>
				<?php 
				//echo '<pre>'; print_r($revmap); echo '</pre>';
				foreach ($revmap as $rtable=>$rpkeys){
					foreach ($rpkeys as $rp){
						$rkey=$rp['rkey'];
						$rpkey=$rp['rpkey'];
						if (!isset($myrow[$rpkey])||!is_numeric($myrow[$rpkey])) continue;
				?>
				<nobr><a class="labelbutton" onclick="addquery('<?php echo $dbname;?>','<?php echo $rtable;?>',null,'',1,'<?php echo $rtable.'/'.$rkey.'/'.$myrow[$rpkey];?>');"><?php echo htmlspecialchars($rtable);?></a></nobr> &nbsp;
				<?php	
					}
				}
				?>
			</td>
		</tr>
		<?php
				}//revmap
			}
			
			foreach ($myrow as $k=>$v){
				$pval='';
				if (isset($pkey)&&isset($myrow[$pkey])) {
					$pval=$myrow[$pkey];
					if ($SQL_ENGINE=='mongodb'&&is_array($pval)) $pval='!'.$pval['$oid'];
				}

				if ($SQL_ENGINE=='mongodb'){
					if ($k=='_id') {
						if (is_array($v)) $v=$v['$oid'];
					}
				}

				if ($shortview){
					if (is_string($v)&&mb_strlen($v)>60) $v=mb_substr($v,0,57).'...';	
				}
				$dv=hspc($v);
				if ($v==null&&!isset($v)) $dv='<span style="color:#000088;">NULL</span>';
				if (isset($v)&&$v==='') $dv='<em style="color:#666666;">(empty)</em>';
				if (is_a($dv,'DateTime')){
					$dv=date_format($dv,'Y-n-j H:i:s e');	
				}
				$reckv='';
				if (isset($relmap)&&isset($relmap[$tablename])&&isset($relmap[$tablename][$k]['table'])&&isset($relmap[$tablename][$k])&&is_numeric($v)){
					$reckv=$relmap[$tablename][$k]['table'].'/'.$relmap[$tablename][$k]['pkey'].'/'.$v;	
				}				
		?>
		<tr>
			<td valign="top"><?php echo hspc($k);?></td>
			<td valign="top"><span
				style="
				<?php if (isset($relmap)&&isset($relmap[$tablename])&&isset($relmap[$tablename][$k])&&is_numeric($v)) echo 'display:inline-block;border:solid 1px #848cf7;padding:0 3px;';?>
				cursor:pointer"
				class="cell_<?php echo $dbname;?>_<?php echo $tablename;?>_<?php echo $k;?>_<?php echo $pval;?>" 
				title="<?php echo hspc($k);?>" 
				onclick="resolvecell(this,'cell&sqlmode=<?php echo $sqlmode;?>','<?php echo $tablename;?>','<?php echo $pkey;?>','<?php echo $pval;?>','<?php echo $k;?>','Cell Properties','<?php echo $dbname;?>','<?php echo $reckv;?>');"
				id="cell_<?php echo $queryidx;?>_<?php echo $qidx;?>_<?php echo $idx;?>_<?php echo $k;?>"			
			><?php if (is_string($dv)) echo $dv; else print_r($dv);?></span></td>
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
<hr>
<?php	
		if (isset($rs)&&isset($db)) $rs->free();

	}//foreach rss
	
	$tb=microtime(1);
	if (isset($ctb)) echo "<br><br>Count time: ".round(($ctb-$cta),3).' secs';
	echo "<br>Query time: ".round(($tb-$ta),3).' secs';
	
	if ($qidx<count($qlines)-1) echo "<hr>";
	
	}//foreach qline
	
}

