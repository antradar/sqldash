<?php

function listusers(){
	
	global $sdb; 
	
	$mode=GETSTR('mode');
	$key=GETSTR('key');
	
	$page=isset($_GET['page'])?$_GET['page']+0:0;
	
	$user=userinfo();
	$myuserid=$user['userid']+0;
	
	if (!isset($user['groups']['accounts'])) die('access denied');
	
	if ($mode!='embed'){

?>
<div class="section">
<div class="listbar">
<input id="userkey" class="img-mg" onkeyup="_inline_lookupuser(this);">
	<div style="padding-top:10px;margin-bottom:10px;">
	<a class="recadder" onclick="addtab('user_new','<?php tr('list_user_add_tab');?>','newuser');"> <img src="imgs/t.gif" class="img-addrec" width="18" height="18"><?php tr('list_user_add');?></a>
	</div>
</div>

<div id="userlist">
<?php		
	}

	$query="select * from users where 1 ";
	if ($key!='') $query.=" and (login like '$key%') ";
	
	$cquery="select count(*) as c from ($query)t";
	$rs=$sdb->query($cquery);
	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	$count=$myrow['c'];	
	
	$perpage=20;
	$maxpage=ceil($count/$perpage)-1;
	if ($maxpage<0) $maxpage=0;
	if ($page<0) $page=0;
	if ($page>$maxpage) $page=$maxpage;
	$start=$perpage*$page;

	if ($maxpage>0){
?>
<div style="font-size:12px;padding:10px 0;">
<?php echo $page+1;?> of <?php echo $maxpage+1;?>
&nbsp;
<a href=# onclick="ajxpgn('userlist',document.appsettings.codepage+'?cmd=slv_core__users&page=<?php echo $page-1;?>&mode=embed');return false;">&laquo; Prev</a>
|
<a href=# onclick="ajxpgn('userlist',document.appsettings.codepage+'?cmd=slv_core__users&page=<?php echo $page+1;?>&mode=embed');return false;">Next &raquo;</a>
</div>
<?php		
	}
	
	$query.=" order by userid=$myuserid desc, virtualuser, login limit $start,$perpage";	
	
	$rs=$sdb->query($query);
	
	while ($myrow=$rs->fetchArray(SQLITE3_ASSOC)){
		$userid=$myrow['userid'];
		$login=$myrow['login'];
		$virtual=$myrow['virtualuser'];
		
		$usertitle="$login"; //change this if needed
		
		$dbusertitle=noapos(htmlspecialchars($usertitle));
		$groupnames=$myrow['groupnames'];
		$hash=substr(md5($groupnames),0,6);
		if ($virtual) $hash='ffffff';
		
?>
<div class="listitem" style="border-left:solid 3px #<?php echo $hash;?>;padding-left:5px;"><a onclick="showuser(<?php echo $userid;?>,'<?php echo $dbusertitle;?>');"><?php echo $usertitle;?></a></div>
<?php		
	}//while
	
	if ($mode!='embed'){
?>
</div>
</div>

<script>
gid('tooltitle').innerHTML='<a><?php tr('list_users');?></a>';
ajxjs(self.showuser,'users_js.php');
</script>
<?php	
	}//embed mode

}

