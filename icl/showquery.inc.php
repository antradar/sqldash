<?php
if (!isset($_GET['sqlmode'])||$_GET['sqlmode']!='sqlite') include 'subconnect.php';

function showquery(){
	$queryidx=GETVAL('queryidx');
	global $db;
	global $SQL_ENGINE;

	$dbname=GETSTR('dbname');
	$tablename=GETSTR('tablename');
	$dtablename=$tablename;
	
	$sqlmode=SGET('sqlmode');
	
	header('newtitle: '.rawurlencode('<img src="imgs/t.gif" class="ico-query">'.$queryidx));
	
	if ($sqlmode=='sqlite') $dtablename="[$tablename]";
	
	if ($sqlmode!='sqlite'&&in_array($SQL_ENGINE,array('MySQL','MySQLi'))) sql_select_db($db,$dbname);
	
	$defquery='';
	if ($tablename!='') {
		$defquery="select * from $dtablename;";
		if ($SQL_ENGINE=='mongodb') $defquery='{"find":"'.$dtablename.'"}';
	}
	
	$reckv=GETSTR('reckv');
	if ($reckv!=''){
		$reckvparts=explode('/',$reckv);
		if (count($reckvparts)==3){
			$tablename=$reckvparts[0];
			$pkey=$reckvparts[1];
			$recid=$reckvparts[2];
			$drecid=$recid;
			if (!is_numeric($recid)) $drecid="'".$recid."'";
			$defquery="select * from $tablename where $pkey=$drecid";	
		}	
	}
	
?>
<div class="section">
	<div class="sectiontitle"><?php echo $dbname;?> &raquo; Query &nbsp; <input class="inpshort" onfocus="this.select();" onchange="if (this.value=='') this.value='#<?php echo $queryidx;?>';settabtitle('query_<?php echo $queryidx;?>','<img src=&quot;imgs/t.gif&quot; class=&quot;ico-query&quot;>'+this.value);" value="#<?php echo $queryidx;?>">
		<?php if (1==SQLDASH_AUTH_MODE||2==SQLDASH_AUTH_MODE){?>
		&nbsp; <button onclick="lookupentity(gid('statusc'),'squery&qidx=<?php echo $queryidx;?>','Saved Queries');">Load</button>
		<?php }?>
	</div>
	
	<div style="padding-bottom:10px;">
		<acronym title="hold Ctrl or CMD key to remove tabs">
		<a class="labelbutton" onclick="gid('query_<?php echo $queryidx;?>').value=query_remove_spaces(gid('query_<?php echo $queryidx;?>').value,!(document.keyboard['key_17']||document.keyboard['key_91']||document.keyboard['key_224']));">remove spaces</a>
		</acronym>
	</div>
	<div style="margin-bottom:5px;"><em style="color:#666666;">use "#" on a single line to separate multiple queries; select part of the text for partial querying</em></div>
			
	<textarea spellcheck="false" class="inplong" id="query_<?php echo $queryidx;?>"><?php if ($tablename!=''){?><?php echo htmlspecialchars($defquery);?><?php }?></textarea>
	<div class="inputrow">
		<button onclick="runquery(<?php echo $queryidx;?>,'<?php echo $dbname;?>','<?php echo $sqlmode;?>');">Execute</button>
		<?php if ($SQL_ENGINE=='MySQLi'||$SQL_ENGINE=='ClickHouse'){?>
		&nbsp; &nbsp;
		<button onclick="runquery(<?php echo $queryidx;?>,'<?php echo $dbname;?>','<?php echo $sqlmode;?>',1);">Explain</button>
		<?php }?>
		<img src="imgs/t.gif" style="height:1px;width:50px;">
		<button onclick="ajxjs(self.addquery,'queries.js');addquery('<?php echo $dbname;?>','<?php echo $tablename;?>',<?php echo $queryidx;?>);">Duplicate</button>
		
		<?php if (1==SQLDASH_AUTH_MODE||2==SQLDASH_AUTH_MODE){?>
		&nbsp; &nbsp;
		<button onclick="savequery(<?php echo $queryidx;?>);">Save</button>
		<?php }?>
	</div>
	<div class="inputrow">
		<input id="shortview_<?php echo $queryidx;?>" type="checkbox" checked>
		<label for="shortview_<?php echo $queryidx;?>">concise view</label>
		&nbsp; &nbsp;
		<input id="usemacros_<?php echo $queryidx;?>" type="checkbox">
		<label for="usemacros_<?php echo $queryidx;?>">use macros</label>
	</div>
	
	<div id="queryresult_<?php echo $queryidx;?>"></div>
	
</div>
<div style="position:absolute;top:45px;left:0;">
	<div id="colnav_<?php echo $queryidx;?>" style="float:left;padding:2px 4px;background:#8f8cf7;border-radius:0 4px 4px 0;color:#ffffff;"><a onclick="if (gid('colnames_<?php echo $queryidx;?>')) lookupentity(this,'colnav&queryidx=<?php echo $queryidx;?>','Columns','colnames='+encodeHTML(gid('colnames_<?php echo $queryidx;?>').value));">&#9636;</a></div>
</div>
<?php	
}
