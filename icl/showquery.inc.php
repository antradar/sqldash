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
	
?>
<div class="section">
	<div class="sectiontitle"><?php echo $dbname;?> &raquo; Query &nbsp; <input class="inpshort" onfocus="this.select();" onchange="if (this.value=='') this.value='#<?php echo $queryidx;?>';settabtitle('query_<?php echo $queryidx;?>','<img src=&quot;imgs/t.gif&quot; class=&quot;ico-query&quot;>'+this.value);" value="#<?php echo $queryidx;?>"></div>
	
	<div style="margin-bottom:5px;"><em style="color:#666666;">use "#" on a single line to separate multiple queries; select part of the text for partial querying</em></div>
			
	<textarea spellcheck="false" class="inplong" id="query_<?php echo $queryidx;?>"><?php if ($tablename!=''){?>select * from <?php echo $dtablename;?><?php }?></textarea>
	<div class="inputrow">
		<button onclick="runquery(<?php echo $queryidx;?>,'<?php echo $dbname;?>','<?php echo $sqlmode;?>');">Execute</button>
		&nbsp; &nbsp;
		<button onclick="ajxjs(self.addquery,'queries.js');addquery('<?php echo $dbname;?>','<?php echo $tablename;?>',<?php echo $queryidx;?>);">Duplicate</button>
		&nbsp; &nbsp;
		<button onclick="runquery(<?php echo $queryidx;?>,'<?php echo $dbname;?>','<?php echo $sqlmode;?>',1);">Explain</button>
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