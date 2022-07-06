<?php
include 'lb.php';
if ($usehttps) include 'https.php';

include 'settings.php';
include 'retina.php';

include 'evict.php';
evict_check();

login();
$user=userinfo();
?>
<html>
<head>
	<title>Antradar Gyroscope&trade; Mobile</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	<link href='iphone/gyrodemo.css' type='text/css' rel='stylesheet'>
	<link href='toolbar.css' type='text/css' rel='stylesheet'>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<style>
body{font-family:helvetica;}
.menuitem{padding-left:10px;height:30px;float:left;margin-right:3px;}
.menuitem a, .menuitem a:hover, .menuitem a:visited, .menuitem a:link{
	display:block;
	padding-top:3px;
	color:#000000;
	text-decoration:none;
}

</style>

</head>
<body onload="setTimeout(scrollTo, 0, 0, 1);">

<div id="toolbg" style="position:fixed;width:100%;z-index:1000;top:0;background:#333333;opacity:0.9"></div>
<div id="toolicons" style="position:fixed;width:100%;z-index:2000;top:0;">

	<div id="toollist" style="overflow:auto;width:100%;"><div style="width:<?php echo 50*(count($toolbaritems)+2);?>px;">
		
	<div class="menuitem"><a id="speechstart" href=# onclick="speech_startstop(1);return false;" style="display:none;"><img style="" class="img-speechrecog" src="imgs/t.gif" border="0" width="32" height="32"></a></div>

	<?php foreach ($toolbaritems as $modid=>$ti){
		if ($ti['type']=='break') continue;
		if ($ti['noiphone']) continue;	
		if ($ti['type']=='custom'){
		?>
		<?php echo $ti['iphone'];?>
		<?php	
			continue;
		}
		
		$action="showview('".$modid."',null,1);";
		if ($ti['action']!='') $action=$ti['action'];
		if (!isset($ti['icon'])||$ti['icon']=='') continue;
		
		if (isset($ti['groups'])){
			$canview=0;
			$gs=explode('|',$ti['groups']);
			foreach ($gs as $g) if (isset($user['groups'][$g])) $canview=1;
			if (!$canview) continue;	
		}
		
	?>
	<div class="menuitem"><a href=# onclick="<?php echo $action;?>return false;"><img class="<?php echo $ti['icon'];?>" src="imgs/t.gif" border="0" width="32" height="32"></a></div>
	<?php }?>

	</div></div>
		
	<a href="login.php?from=<?php echo $_SERVER['PHP_SELF'];?>" style="position:absolute;top:10px;right:10px;"><img border="0" width="16" height="16" src="imgs/t.gif" class="admin-logout"></a>
</div><!-- toolicons -->
<div id="pusher" style="width:100%;height:50px;"></div>

<div style="display:none;"><img src="imgs/t.gif"><img src="imgs/hourglass.gif"></div>
<div id="leftview" style="float:left;margin-left:10px;width:210px;margin-right:10px;">
	<div id="tooltitle" style="width:150px;position:fixed;top:50px;z-index:1000;height:25px;"></div>
	<div id="tooltitleshadow" style="width:150px;height:25px;"></div>
	<div id="lvviews">
	<?php foreach ($toolbaritems as $modid=>$ti){?>
		<div id="lv<?php echo $modid;?>" style="background-color:#ffffff;display:none;"></div>
	<?php }?>	
	</div>
	<div id="lkv" style="height:100%;">
		<div id="lkvtitle"><a id="lkvt"></a><img id="lkvx" src="imgs/t.gif" onclick="hidelookup();" width="30" height="24"></div>
		<div id="lkvc"></div>
	</div>
	
</div>
<div id="content" style="float:left;width:320px;">

	<div id="backlist" style="display:none;position:fixed;width:100%;z-index:1000;"><a id="backlistbutton"><img onclick="navback();" src="iphone/bb_<?php echo $lang;?>.png"></a></div>
	<div id="backlistshadow" style="display:none;width:100%;"></div>

	<div id="tabtitles" style="width:325px;position:fixed;z-index:1000;"></div>
	<div id="tabtitleshadow" style="height:25px;width:100px;display:none;"></div>

	<div id="tabviews" style=""></div>
	<div id="statusinfo" style="display:none;"><div id="statusc"></div></div>
</div>
<div id="rotate_indicator" style="display:none;position:fixed;width:100px;height:100px;top:220px;left:110px;z-index:3000;"></div>
<div id="fsmask"></div>
<div id="fstitlebar">
	<div id="fstitle"></div>
	<a id="fsclose" onclick="closefs();"><img width="10" height="10" class="img-closeall" src="imgs/t.gif"></a>
</div>
<div id="fsview"></div>


<script>
document.appsettings={codepage:'<?php echo $codepage;?>',fastlane:'<?php echo $fastlane;?>', views:<?php echo json_encode(array_keys($toolbaritems));?>};
</script>
<script src="lang/dict.<?php echo $lang;?>.js"></script>
<script src="nano.js"></script>
<script>
hdpromote('toolbar_hd.css');
</script>
<script src="iphone/tabs.js"></script>
<script src="iphone/viewport.js"></script>
<script src="validators.js"></script>
<script src="autocomplete.js"></script>

<script>

function showdeck(){
	switch(document.viewmode){
		
		case 1: 
			gid('leftview').style.display='block'; 
			gid('tabtitles').style.display='block';
			
			gid('content').style.display='none';

		break;
		case 2:
			gid('leftview').style.display='none'; 
			gid('tabtitles').style.display='none';
			
			gid('content').style.display='block';
			
		break;
	}
		
}


function rotate(){
	
<?php
	$ori_portrait_backward=180;
	$ori_portrait_forward=0;
	$ori_landscape_backward=-90;
	$ori_landscape_forward=90;

	$agent=$_SERVER['HTTP_USER_AGENT'];

	if (preg_match('/playbook/i',$agent)||preg_match('/android/i',$agent)) $ori_invert=1;
	if (preg_match('/mobile/i',$agent)) $ori_invert=0; //do not invert any phones
	
	if ($ori_invert){
		$ori_portrait_backward=-90;
		$ori_portrait_forward=90;
		$ori_landscape_backward=180;
		$ori_landscape_forward=0;
	}
?>	
	var ori=90;
	if (window.matchMedia){
		if (window.matchMedia('(orientation: landscape)').matches) ori=<?php echo $ori_landscape_forward;?>;
		else if (window.matchMedia('(orientation: portrait)').matches) ori=<?php echo $ori_portrait_forward;?>;
	}
	
	if (window.operamini) ori=0;	

	setTimeout(scrollTo, 0, 0, 1);
	
	if (!document.appsettings.cw) document.appsettings.cw=320;
	if (document.appsettings.cw<document.body.clientWidth) document.appsettings.cw=document.body.clientWidth;
		
	var cw=document.appsettings.cw;
	var vw=document.body.clientWidth;
	
	switch(ori){
	case <?php echo $ori_portrait_backward;?>: case <?php echo $ori_portrait_forward;?>: 
		
		//gid('panel2').style.display='block';
		showdeck();
		gid('leftview').style.width=vw+'px';
		gid('leftview').style.marginLeft=0;
		gid('backlist').style.display='block';
		gid('backlistshadow').style.display='block';
		//gid('leftview').style.fontSize='25px';
		gid('tooltitle').style.width=vw+'px';
		//gid('tooltitle').style.top='40px';
		gid('pusher').style.height='50px';
		gid('toollist').style.width=document.documentElement.clientWidth-50+'px';//'280px';
		gid('tabtitleshadow').style.display='none';
		gid('content').style.width=vw+'px';
		
		if (document.lastori==null||document.lastori!=ori) ajxcss(self.cssloader,'iphone/portrait.css');
		document.viewheight=vw+30;
		scaleall(document.body);
		document.iphone_portrait=1;
		
		hidelookup();

		
	break;
	case <?php echo $ori_landscape_forward;?>: case <?php echo $ori_landscape_backward;?>: 
		//gid('panel2').style.display='none';
		gid('leftview').style.display='block';
		gid('leftview').style.width='210px';
		gid('leftview').style.marginLeft='10px';
		//gid('leftview').style.fontSize='14px';
		gid('tabtitles').style.display='block';
		gid('content').style.display='block';
		gid('backlist').style.display='none';
		gid('backlistshadow').style.display='none';
		
		gid('tooltitle').style.width=vw+'px';
		//gid('tooltitle').style.top='50px';
		gid('pusher').style.height='50px';
		gid('toollist').style.width=cw-50+'px';
		gid('tabtitleshadow').style.display='block';
		gid('content').style.width=cw-230+'px';
		gid('tabtitles').style.width=cw-230+'px';
		if (document.lastori==null||document.lastori!=ori) ajxcss(self.cssloader,'iphone/landscape.css');
		document.viewheight=210;

		scaleall(document.body);
		document.iphone_portrait=null;
		gid('rotate_indicator').style.display='none';
		
	break;
	}
	
	document.lastori=ori;

}

function portrait_ignore(ttl){
	if (!ttl) ttl=2000;

	document.portraitlock=1;

	setTimeout(function(){document.portraitlock=null;},ttl);
}

addtab('welcome','<?php tr('tab_welcome');?>','wk',null,null,{noclose:true});

function onrotate(){
	if (document.resizetimer) clearTimeout(document.resizetimer);
	document.resizetimer=setTimeout(function(){
		rotate();
		setTimeout(rotate,500);
	},100);
}

setInterval(authpump,60000); //check if needs to re-login; comment this out to disable authentication

addtab('welcome','Welcome','wk',null,null,{noclose:true});

if (typeof(window.onorientationchange)!='object') window.onresize=onrotate;
else window.onorientationchange=onrotate;

onrotate();

scaleall(document.body);

</script>
<script src="wss.js"></script>
<script>
<?php include 'ws_js.php';?>
</script>

<script src="speech.js"></script>
</body>
</html>
