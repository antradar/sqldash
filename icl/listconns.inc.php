<?php

function listconns(){
	global $sdb;
	global $conntypes;
		
	$user=userinfo();
	$userid=intval($user['userid']);
	
	$mode=SGET('mode');
	$key=SGET('key',0); //do not trim
	$dbkey=addslashes($key);
	
	$page=isset($_GET['page'])?intval($_GET['page']):0;
	
	header('listviewtitle:'.tabtitle(_tr('icon_conns')));
		
	if ($mode!='embed'){

?>
<div class="section">
<div class="listbar">
	<form class="listsearch" onsubmit="_inline_lookupconn(gid('connkey'));return false;" style="position:relative;">
		<div class="listsearch_">
			<input id="connkey" class="img-mg" onfocus="document.hotspot=this;" onkeyup="_inline_lookupconn(this);" autocomplete="off">
			<img src="imgs/inpback.gif" class="inpback" onclick="inpbackspace('connkey');_inline_lookupconn(gid('connkey'));">
		</div>
		<input type="image" src="imgs/mg.gif" class="searchsubmit" value=".">
	</form>

	<?php
	if (isset($user['groups']['connedit'])){
	?>
	<div style="padding-top:10px;">
	<a class="recadder" onclick="addtab('conn_new','<?php tr('list_conn_add_tab');?>','newconn');"> <img src="imgs/t.gif" class="img-addrec"><?php tr('list_conn_add');?></a>
	</div>
	<?php
	}
	?>
</div>

<div id="connlist">
<?php		
	}

	$params=array();
	$query="select * from conns where 1 "; // userid=$userid ";	 //share connections
	
	$sxsearch='';

	
	if ($key!='') {
		$query.=" and (connname like '%$dbkey%' or connhost like '%$dbkey%') ";

	}
	
	$cquery="select count(*) as c from ($query)t";
	$rs=$sdb->query($cquery);
	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	$count=$myrow['c']; //sql_affected_rows($db,$rs);
	$perpage=20;
	$maxpage=ceil($count/$perpage)-1;
	if ($maxpage<0) $maxpage=0;
	if ($page<0) $page=0;
	if ($page>$maxpage) $page=$maxpage;
	$start=$perpage*$page;

	$pager='';
	
	if ($maxpage>0){
	ob_start();
?>
<div class="listpager">
<a href=# class="hovlink" onclick="ajxpgn('connlist',document.appsettings.codepage+'?cmd=slv_codegen__conns&key='+encodeHTML(gid('connkey').value)+'&page=<?php echo $page-1;?>&mode=embed');return false;"><img src="imgs/t.gif" class="img-pageleft">Prev</a>
&nbsp;
<a class="pageskipper" onclick="var pagenum=sprompt('Go to page:',<?php echo $page+1;?>);if (pagenum==null||parseInt(pagenum,0)!=pagenum) return false;ajxpgn('connlist',document.appsettings.codepage+'?cmd=slv_codegen__conns&key='+encodeHTML(gid('connkey').value)+'&page='+(pagenum-1)+'&mode=embed');return false;"><?php echo $page+1;?></a>
 of <?php echo $maxpage+1;?>
&nbsp;
<a href=# class="hovlink" onclick="ajxpgn('connlist',document.appsettings.codepage+'?cmd=slv_codegen__conns&key='+encodeHTML(gid('connkey').value)+'&page=<?php echo $page+1;?>&mode=embed');return false;">Next<img src="imgs/t.gif" class="img-pageright"></a>
</div>
<?php		
	$pager=ob_get_clean();
	}
	
	echo $pager;
	
	$lastconntype='';
	
	$query.=" order by conntype,connname limit $start,$perpage";	
	
	$rs=$sdb->query($query);
	
	$defconnid=isset($_COOKIE['connid'])?$_COOKIE['connid']:null;
	
	while ($myrow=$rs->fetchArray(SQLITE3_ASSOC)){
		$connid=$myrow['connid'];
		$connname=$myrow['connname'];
		$conntype=$myrow['conntype'];
		
		if ($lastconntype!=$conntype){
		?>
		<div class="sectionheader"><?php echo $conntypes[$conntype];?></div>
		<?php	
			$lastconntype=$conntype;
		}
		
		$conntitle="$connname"; //change this if needed
		
		$dbconntitle=noapos(htmlspecialchars(htmlspecialchars($conntitle)));
?>
<div class="listitem">
<a style="<?php if ($connid==$defconnid) echo 'font-weight:bold;';?>">
	<span onclick="setactiveconn(<?php echo $connid;?>);"><?php echo htmlspecialchars($conntitle);?></span>
	<?php if (isset($user['groups']['connedit'])){?>
	 &nbsp;
	<span class="labelbutton" onclick="showconn(<?php echo $connid;?>,'<?php echo $dbconntitle;?>');">edit</span>
	<?php
	}
	?>
</a>
</div>
	
<?php		
	}//while
	
	echo $pager;
	
	if ($mode!='embed'){
?>
</div>
</div>
<script>
gid('tooltitle').innerHTML='<a><?php tr('icon_conns');?></a>';
ajxjs(self.showconn,'conns.js');
</script>
<?php	

	}//embed mode

}

