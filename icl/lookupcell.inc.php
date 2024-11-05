<?php

if (!isset($_GET['sqlmode'])||$_GET['sqlmode']!='sqlite') include 'subconnect.php';

function lookupcell(){
	global $db;
	global $SQL_ENGINE;
	global $profile_root;
	
	$user=userinfo();

	if (!isset($_GET['sqlmode'])||$_GET['sqlmode']!='sqlite') $dbname=checkdbname();
		
	$tablename=$_GET['table'];
	$pkey=$_GET['pkey'];
	$pval=$_GET['pval'];
	$fkey=$_GET['fkey'];
	
	$dbpval=$pval;
	if (!is_numeric($pval)) $dbpval="'".noapos($pval)."'";

	
?>
<div class="section">
<?php
	if ($tablename==''){
	?>
	<div class="warnbox">unable to parse table name from the query</div>
	</div>
	<?php	
		return;		
	}

	if ($tablename==''){
	?>
	<div class="warnbox">unable to parse primary key from table "<?php echo htmlspecialchars($tablename);?>"</div>
	</div>
	<?php	
		return;		
	}
	
	if (!$pval){
	?>
	<div class="warnbox">the primary key "<?php echo htmlspecialchars($pkey);?>" must be in the result to enable editing</div>
	</div>
	<?php	
		return;		
	}
	
	$colinfo=array();
	$canedit=1;
	
	if (1==SQLDASH_AUTH_MODE&&!isset($user['groups']['update'])) $canedit=0;
	
	$query="describe $tablename";
	if ($SQL_ENGINE=='SQLSRV') $query="select * from information_schema.columns where table_name = '$tablename'";

	if ($SQL_ENGINE=='mongodb'){
		$rs=array();
	} else {
		$rs=sql_prep($query,$db);
	}

	while ($myrow=sql_fetch_assoc($rs)){
		if ($SQL_ENGINE=='SQLSRV'){
			$myrow['Field']=$myrow['COLUMN_NAME'];
			$myrow['Type']=$myrow['DATA_TYPE'];
		}
		if ($SQL_ENGINE=='ClickHouse'){
			$myrow['Field']=$myrow['name']; unset($myrow['name']);
			$myrow['Type']=$myrow['type']; unset($myrow['type']);			
		}
		if (strtolower($myrow['Field'])==strtolower($fkey)) $colinfo=$myrow;
	}//while
	
	if (count($colinfo)==0){
	?>
	<div class="warnbox">computed fields cannot be edited</div>
	<?php
		$canedit=0;
	//	return;	
	}
	
	if ($SQL_ENGINE=='mongodb'){
		if ($pval[0]=='!') $usepval=new MongoDB\BSON\ObjectId(ltrim($pval,'!')); else $usepval=$pval;
 		$cmd=new MongoDb\Driver\Command(array("find"=>$tablename,"filter"=>
				array("_id"=>$usepval) 
			));
		$rs=$db->executeCommand($dbname,$cmd);
	} else {
		$query="select $fkey from $tablename where $pkey=$dbpval";
		$rs=sql_prep($query,$db);
	}
	if (!$myrow=sql_fetch_assoc($rs)){
	?>
	<div class="warnbox">unable to find record. maybe it's already deleted?</div>
	</div>
	<?php
		return;			
	}
	
	$ofval=$myrow[$fkey];
	
	list($fval,$itr)=_utf8_fix($myrow[$fkey]);
	
	$viewmode=SGET('viewmode');
	
	if ($viewmode==''){
		if (isset($colinfo['Type'])&&in_array($colinfo['Type'],array('blob','mediumblob','longblob','geography'))) $viewmode='bin';	
	}
	
	$relmap=null;
	
	$relmapfn=$profile_root.$dbname.'.relmap.json';
	if (file_exists($relmapfn)){
		$relmap=json_decode(file_get_contents($relmapfn),1);	
	}
	
	
	?>
	<div style="line-height:1.5em;margin-bottom:20px;">
	<?php
	if (isset($relmap)&&isset($relmap[$tablename])&&isset($colinfo['Field'])&&isset($relmap[$tablename][$colinfo['Field']])){
		$rel=$relmap[$tablename][$colinfo['Field']];
		if (isset($rel['opts'])){
	?>
	Possible references:
	<div style="padding:5px;margin-left:10px;">
	<?php
		foreach ($rel['opts'] as $opt){
	?>
		<div class="listitem"><a class="hovlink" onclick="addquery('<?php echo $dbname;?>','<?php echo $tablename;?>',null,'',1,'<?php echo $opt['table'].'/'.$opt['pkey'].'/'.intval($ofval);?>');"">
		<?php echo $opt['table'];?>
		</a> 
		<?php if (isset($opt['notes'])){
		?>
		&nbsp; <em><?php echo htmlspecialchars($opt['notes']);?></em>
		<?php	
		}
		?>
		</div>
	<?php
		}//foreach opt
	?>
	</div>
	<?php		
		} else {
	?>
	<div class="listitem">
	Open record in <a class="hovlink" onclick="addquery('<?php echo $dbname;?>','<?php echo $tablename;?>',null,'',1,'<?php echo $rel['table'].'/'.$rel['pkey'].'/'.intval($ofval);?>');"><?php echo htmlspecialchars($rel['table']);?></a><br>
	</div>
	<?php
		}//single reference
	}
	?>
	
	Table: <?php echo htmlspecialchars($dbname.'.'.$tablename);?><br>
	Record: <?php echo htmlspecialchars($pkey);?>=<?php echo htmlspecialchars($pval);?><br>
	
	<?php 
	
	unset($colinfo['COLUMN_NAME'],$colinfo['DATA_TYPE'],$colinfo['TABLE_CATALOG'],$colinfo['TABLE_NAME'],$colinfo['ORDINAL_POSITION'],$colinfo['COLLATION_NAME'],$colinfo['TABLE_SCHEMA']);
	
	foreach ($colinfo as $ck=>$cv){
		if ($cv!=''){
			if ($SQL_ENGINE=='SQLSRV'){
				$ck=ucwords(strtolower(str_replace('_',' ',$ck)));	
			}
	?>
	<?php echo htmlspecialchars($ck);?>: <?php echo htmlspecialchars($cv);?><br>
	<?php	
		}	
	}?>
	<?php if ($viewmode=='bin'){
		if (is_array($ofval)&&$SQL_ENGINE=='mongodb') $ofval=$ofval['$oid'];
	?>
	Bytes: <?php echo strlen($ofval);?><br>
	Chars: <?php echo mb_strlen($ofval);?><br>
	<?php }?>
	</div>
	
	<div class="inputrow">
		<?php 
		
		

		
		switch ($viewmode){
			
		case 'bin':
		?>
		<div class="formlabel">Binary Hex View:
			&nbsp; <a style="font-weight:normal;" onclick="lookupentity(this,'cell&table=<?php echo $tablename;?>&pkey=<?php echo $pkey;?>&pval=<?php echo $pval;?>&fkey=<?php echo urlencode($fkey);?>&viewmode=txt','Cell Properties');" class="hovlink">view as text</a>
		</div>
		<div style="max-height:240px;overflow:auto;">
		<?php
			$len=strlen($ofval);
			for ($i=0;$i<$len;$i++){
				$hex=strtoupper(dechex(ord($ofval[$i])));
				$hex=str_pad($hex,2,'0',STR_PAD_LEFT);
			?>
			<div class="hexcell"><acronym title="<?php echo htmlspecialchars($ofval[$i]);?>"><?php echo $hex;?></acronym></div>
			<?php	
			}
		?>
			<div class="clear"></div>
		</div>
		<?php
		break;
			
		default:
		?>
		<div class="formlabel">Cell Value:
		<?php
			if (!is_numeric($ofval)){
		?>		 
			&nbsp; <a style="font-weight:normal;" onclick="lookupentity(this,'cell&table=<?php echo $tablename;?>&pkey=<?php echo $pkey;?>&pval=<?php echo $pval;?>&fkey=<?php echo urlencode($fkey);?>&viewmode=bin','Cell Properties');" class="hovlink">view as binary</a>
		<?php 
		}
		?>
		</div>
		<?php
		if (isset($colinfo['Type'])&&in_array($colinfo['Type'],array('text','mediumtext','longtext','blob','mediumblob','longblob','String'))){
		?>
		<textarea <?php if (!$canedit) echo 'readonly';?> <?php if ($itr!=0) echo 'style="background:#ffffcc;"';?> id="celllookuppfval" class="inplong"><?php echo htmlspecialchars($fval);?></textarea>
		<?php	
		} else {
		?>
		<input <?php if (!$canedit) echo 'readonly';?> <?php if ($itr!=0) echo 'style="background:#ffffcc;"';?> id="celllookupfval" class="inplong" value="<?php echo htmlspecialchars($fval);?>">
		<?php
		}
		?>		
		<div id="celllookupupdater" style="display:none;"></div>
	</div>
	
	<div class="inputrow">
		<?php if ($canedit){?>
		<button onclick="updatecell('<?php echo $dbname;?>','<?php echo $tablename;?>','<?php echo $pkey;?>','<?php echo $pval;?>','<?php echo $fkey;?>');">Update</button>
		&nbsp;
		<button onclick="updatecell('<?php echo $dbname;?>','<?php echo $tablename;?>','<?php echo $pkey;?>','<?php echo $pval;?>','<?php echo $fkey;?>',1);">Set Null</button>
		<?php
		}//canedit
		
		/*
		&nbsp; &nbsp;
		<button class="warn">Delete Row</button>
		*/
		?>
	</div>
	<?php
	}//switch type
	?>
	
	
	<div style="padding-top:20px;line-height:1.5em;">
		<nobr><a class="labelbutton" onclick="decodelookupcell('datetime');">date/time</a></nobr> &nbsp;
		<nobr><a class="labelbutton" onclick="decodelookupcell('phpobj');">php obj</a></nobr> &nbsp;
		<nobr><a class="labelbutton" onclick="decodelookupcell('base64');">base64</a></nobr> &nbsp;
		<nobr><a class="labelbutton" onclick="decodelookupcell('json');">json</a></nobr> &nbsp;
		<nobr><a class="labelbutton" onclick="gid('celllookupdeflater').style.display='block';">compress</a></nobr>
		<nobr><a class="labelbutton" onclick="bindecodelookupcell('inflate','<?php echo $dbname;?>','<?php echo $tablename;?>','<?php echo $pkey;?>','<?php echo $pval;?>','<?php echo $fkey;?>');" style="background:#3366aa;">inflate</a></nobr> &nbsp;
	</div>
	
	<div id="celllookupdeflater" style="padding:10px 0;display:none;">
		store results at:
		<div class="inputrow" style="padding-top:5px;">
			<input keeplookup class="inplong" id="celllookupdeflatedst" placeholder="Select Field"
			onfocus="lookupentity(this,'tablecol&table=<?php echo $tablename;?>&mode=stringonly','Text Fields',null,1);"
			>
			<span id="celllookupdeflatedst_val2"></span>
			<?php makelookup('celllookupdeflatedst');?>
		</div>
		<div class="inputrow">
			<button onclick="if (!gid('celllookupdeflatedst').value2) return; decodelookupcell('deflate&table=<?php echo $tablename;?>&pkey=<?php echo $pkey;?>&pval=<?php echo $pval;?>&fkey='+gid('celllookupdeflatedst').value2,1);">Deflate</button>
		</div>
	</div>
	
	<div id="celllookupdecoder" style="padding-top:10px;"></div>
	
	<div style="padding-top:20px;">
	<?php
		if (is_numeric($pval)) $dpval=$pval;
		else $dpval=urlencode($pval);
	?>
		<a class="hovlink" onclick="addquery('<?php echo $dbname;?>','<?php echo $tablename;?>',null,'',1,'<?php echo $tablename.'/'.$pkey.'/'.$dpval;?>');">show this record only</a>
	</div>
	
	<?php	
	
?>	
</div>
<?php		
}
