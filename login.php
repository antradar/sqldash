<?php

include 'lb.php';
include 'lang.php';

//if (isset($usehttps)&&$usehttps) include 'https.php'; 
include 'connect.php';
include 'auth.php';
include 'xss.php';


$csrfkey=sha1($salt.'csrf'.$_SERVER['REMOTE_ADDR'].date('Y-m-j-g'));
$csrfkey2=sha1($salt.'csrf'.$_SERVER['REMOTE_ADDR'].date('Y-m-j-g',time()-3600));

$error_message='';

$passreset=0;

if (isset($_POST['lang'])&&in_array($_POST['lang'],array_keys($langs))) {
	$lang=$_POST['lang'];include 'lang/dict.'.$lang.'.php';  
	setcookie('userlang',$_POST['lang'],time()+3600*24*30*6); //keep for 6 months
}

if ( (isset($_POST['password'])&&$_POST['password']) || (isset($_POST['login'])&&$_POST['login']) ){	
	
	xsscheck();

	$cfk=$_POST['cfk'];
	if ($cfk!=$csrfkey&&$cfk!=$csrfkey2){
	
		$error_message=_tr('csrf_expire');
	} else {
	
		$password=$_POST['password'];
		   
    	$dashpass=base64_encode(encstr($password,SQLDASHKEY));
 		
		$raw_login=$_POST['login'];
		$login=str_replace("'",'',$raw_login);
		
		$dbhost=$_POST['dbhost'];
		if (SQET('sqlmode')=='clickhouse'&&is_numeric(SQET('apiport'))) $dbhost.=':'.$_POST['apiport'];
		$dbname=$_POST['dbname'];
		if ($dbname=='') $dbname=null;
		
		$db=@sql_get_db($dbhost,$dbname,$raw_login,$password);

		if (isset($db)&&$db!==false&&(
			(is_array($db)&&isset($db['raw'])&&$db['raw'])
			||
			is_object($db)
			)){
		
			$auth=md5($salt.$salt.$raw_login);
			
			setcookie('auth',$auth);
			setcookie('login',$raw_login);
			setcookie('dashpass',$dashpass);
			setcookie('dbhost',$dbhost);
			$cdbname=$dbname.'';
			setcookie('dbname',$cdbname);
			setcookie('sqlmode',SQET('sqlmode'));
			
			if (isset($_POST['lang'])){
				if (!in_array($_POST['lang'],array_keys($langs))) $_POST['lang']=$deflang;
				setcookie('userlang',$_POST['lang'],time()+3600*24*30*6); //keep for 6 months
			}
			if (isset($_GET['from'])&&trim($_GET['from'])!='') {
			  $from=$_GET['from'];
			  $from=str_replace('://','',$from);
			  $from=str_replace("\r",'-',$from);
			  $from=str_replace("\n",'-',$from);
			  $from=str_replace(":",'-',$from);
			  header('Location: '.$from);
			} else header('Location:index.php');
			die();
					
		} else $error_message=_tr('invalid_password'); //passcheck
	
	}//csrf
	
} else {
	setcookie('userid',NULL,time()-3600);
	setcookie('login',NULL,time()-3600);
	setcookie('auth',NULL,time()-3600);
	setcookie('groupnames',NULL,time()-3600);
	setcookie('dashpass',NULL,time()-3600);
	setcookie('dbname',NULL,time()-3600);		
	setcookie('sqlmode',NULL,time()-3600);		
}

?>
<html>
<head>
	<title><?php tr('login');?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="refresh" content="1800" />
	<meta name = "viewport" content = "width=device-width, init-scale=1.0, user-scalable=no" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<style>
body{padding:0;margin:0;background:transparent url(imgs/bgtile.png) repeat;font-size:13px;font-family:arial,sans-serif;text-align:center;}
#loginbox__{width:320px;margin:0 auto;background-color:rgba(200,200,200,0.4);margin-top:100px;border-radius:4px;}
#loginbox_{padding:10px;}
#loginbox{background-color:#FFFFFF;text-align:left;}
.powered{color:#000000;text-align:right;font-size:12px;width:320px;margin:0 auto;padding-top:10px;}
#loginbutton{color:#ffffff;background:#29abe2;padding:8px 20px;border-radius:3px;border:none;cursor:pointer;box-shadow:0px 1px 2px #c9c9c9;-webkit-appearance:none;}
#loginbutton:active, #loginbuttonbutton:active{box-shadow:1px 1px 3px #999999;}

#cardlink, #passlink{display:none;text-align:center;padding-top:10px;}
#cardlink{display:none;}
#cardinfo{padding:5px;font-size:12px;padding-left:26px;background:#fcfcdd url(imgs/smartcard.png) no-repeat 5px 50%;margin-bottom:10px;display:none;}

.lfinp,.lfsel{border:solid 1px #999999;padding:5px;box-sizing:border-box;display:block;height:34px;line-height:32px;margin-bottom:5px;border-radius:3px;}
.lfinp{-webkit-appearance:none;}
.lfinp:active, .lfinp:focus, .lfsel:active, .lfsel:focus{outline:0;border:solid 2px #29abe2;}

@media screen and (max-width:400px){
	#loginbox__,.powered{width:90%;}
	#loginbox__{margin-top:50px;}
}

@media screen and (max-width:300px){
	#loginbutton{width:auto;padding-left:15px;padding-right:15px;}
}

@media screen and (max-width:260px){
	.powered{text-align:center;}
	.powered span{display:block;padding-top:3px;}
}
</style>
</head>
<body>
<div id="loginbox__"><div id="loginbox_">
<div id="loginbox">
	<form method="POST" style="padding:20px;margin:0;padding-top:10px;" onsubmit="return checkform();">
	<img src="imgs/logo.png" style="margin:10px 0;width:100%;">
	
	<?php 
	
	if (SQLDASHKEY=='') {
		$error_message='Incomplete Setup:<br><br>';
		if (!file_exists('sqldashkey.php')) $error_message.='sqldashkey.php must be created; SQLDASHKEY must be set to a unique value.';
		else $error_message.='SQLDASHKEY not set in sqldashkey.php';
	}
	
	
	if ($error_message!=''){
		
	?>
	<div style="color:#ab0200;font-weight:bold;padding-top:10px;line-height:1.4em;"><?php echo $error_message;?></div>
	<?php }?>
	
	<?php
	if (SQLDASHKEY!=''){
	?>

	<div style="padding-top:10px;padding-bottom:5px;">Engine:</div>
	<select style="width:100%" id="sqlmode" class="lfsel" type="text" name="sqlmode" onchange="if (this.value=='sqlsrv') gid('dbview').style.display='block'; else gid('dbview').style.display='none';if (this.value=='clickhouse') gid('apiportview').style.display='block'; else gid('apiportview').style.display='none';">
		<option value="mysqli">MySQLi</option>
		<option value="sqlsrv" <?php if (SQET('sqlmode')=='sqlsrv') echo 'selected';?>>SQLSrv</option>
		<option value="clickhouse" <?php if (SQET('sqlmode')=='clickhouse') echo 'selected';?>>ClickHouse/HTTP</option>
	</select>
		
	<div style="padding-top:10px;padding-bottom:5px;">Host:</div>
	<input style="width:100%;" id="dbhost" class="lfinp" type="text" name="dbhost" value="<?php echo isset($_POST['dbhost'])?$_POST['dbhost']:'localhost';?>">

	<div id="apiportview" style="display:none<?php if (SQET('sqlmode')=='clickhouse') echo 'a';?>;">
		<div style="padding-top:10px;padding-bottom:5px;">API Port:</div>
		<input style="width:100%;" id="apiport" class="lfinp" type="text" name="apiport" value="<?php echo isset($_POST['dbhost'])?$_POST['apiport']:'8123';?>">
	</div>
	
	<div id="dbview" style="display:none<?php if (SQET('sqlmode')=='sqlsrv') echo 'a';?>;">
		<div style="padding-top:10px;padding-bottom:5px;">Database:</div>
		<input style="width:100%;" id="dbname" class="lfinp" type="text" name="dbname" value="<?php echo isset($_POST['dbname'])?$_POST['dbname']:'';?>">
	</div>

	
	<div style="padding-top:10px;"><?php tr('username');?>: <?php if ($passreset){?><b><?php echo stripslashes($_POST['login']);?></b> &nbsp; <a href="<?php echo $_SERVER['PHP_SELF'];?>"><em><?php tr('switch_user');?></em></a><?php }?></div>
	<div style="padding-top:5px;padding-bottom:10px;">
	<input style="width:100%;<?php if ($passreset) echo 'display:none;';?>" id="login" class="lfinp" type="text" name="login" autocomplete="off" <?php if ($passreset) echo 'readonly';?> value="<?php if ($passreset) echo stripslashes($_POST['login']);?>"></div>

	<div id="passview">
		<div><?php tr('password');?>:</div>
		<div style="padding-top:5px;padding-bottom:15px;">
		<input style="width:100%;" class="lfinp" id="password" type="password" name="password"></div>
	

	<?php if ($passreset){?>
	<div><?php tr('new_password');?>:</div>
	<div style="padding-top:5px;padding-bottom:15px;">
	<input style="width:100%;" class="lfinp" id="password" type="password" name="newpassword"></div>
		
	<div><?php tr('repeat_password');?>:</div>
	<div style="padding-top:5px;padding-bottom:15px;">
	<input style="width:100%;" id="password" type="password" name="newpassword2"></div>
	<input type="hidden" name="passreset" value="1">
	<?php }?>

	<div style="width:100%;margin-bottom:10px;<?php if (count($langs)<2) echo 'display:none;';?>"><select style="width:100%;" name="lang" onchange="document.skipcheck=true;">
	<?php
	foreach ($langs as $langkey=>$label){
	?>
	<option value="<?php echo $langkey;?>" <?php if ($lang==$langkey) echo 'selected';?>><?php echo $label;?></option>
	<?php	
	}//foreach
	?>
	</select>
	</div>
	
	<div id="cardinfo"></div>
	
		<div  style="text-align:center;"><input id="loginbutton" type="submit" value="<?php echo $passreset?_tr('change_password'):_tr('signin');?>"></div>
		<div id="cardlink">
			<a href=# onclick="cardauth();return false;">Load ID Card</a>
		</div>
	</div><!-- passview -->
	<div id="cardview" style="display:none;">
		<div style="text-align:center;"><input id="loginbutton" type="submit" value="<?php tr('signin');?>" onclick="if (!cardauth()) return false;"></div>
		<div id="passlink">
			<a href=# onclick="passview();return false;">Sign in with password</a>
		</div>
	</div>
	<input name="cfk" value="<?php echo $csrfkey;?>" type="hidden">
	<div style="display:none;"><textarea name="certid" id="certid"></textarea></div>
	</form>
	&nbsp;
	<?php
	}
	?>
</div>
</div></div>	
	<?php
	$version=GYROSCOPE_VERSION;
	if (VENDOR_VERSION!='') $version.=VENDOR_VERSION;
	if (VENDOR_NAME) $version.=' '.VENDOR_NAME;
	$power='Antradar Gyroscope&trade; '.$version;
	?>
	<div class="powered"><?php tr('powered_by_',array('power'=>$power));?></div>
	
	<script src="nano.js"></script>
	<script>
		function checkform(){
			if (document.skipcheck) return true;
			if (gid('password').value=='') { //&&gid('certid').value==''
				gid('password').focus();
				if (gid('login').value=='') gid('login').focus();
				return false;
			}
			
			return true;
		}
		<?php if ($passreset){?>
		gid('password').focus();
		<?php }else{?>	
		gid('login').focus();
		<?php }?>
	</script>

<script src="smartcard.js"></script>
<script>
smartcard_init('reader',{
	'noplugin':function(){gid('cardlink').style.display='none';},
	'nohttps':function(){gid('cardlink').style.display='none';},
	'inited':function(){gid('cardlink').style.display='block';}	
});

function cardview(){
	gid('passview').style.display='none';
	gid('cardview').style.display='block';
}

function passview(){
	gid('cardview').style.display='none';
	gid('passview').style.display='block';
}

function cardauth(){
/*
	if (gid('login').value=='') {
		gid('login').focus();
		return;
	}
*/
	if (document.reader){
	  document.reader.getcert(function(cert){
	  if (cert){
		gid('certid').value=cert.certificateAsHex;
		gid('cardinfo').innerHTML=cert.CN;
		gid('cardinfo').style.display='block';
		return true;
	  }
	  });
	} else {//no reader
		alert('Smartcard reader not supported');
		return false;
	}
}

</script>

</body>
</html>
