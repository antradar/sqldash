<?php

function GETVAL($key){ $val=$_GET[$key]; if (!is_numeric($val)){header('apperror:invalid parameter '.$key);die('invalid parameter '.$key);} return $val;}
function QETVAL($key){ $val=$_POST[$key]; if (!is_numeric($val)){header('apperror:invalid parameter '.$key);die('invalid parameter '.$key);} return $val;}
function noapos($val){if (is_callable('sql_escape')) return sql_escape($val); return addslashes($val);}
function GETSTR($key){ $val=decode_unicode_url(isset($_GET[$key])?$_GET[$key]:null); return noapos($val); }
function QETSTR($key){ $val=decode_unicode_url(isset($_POST[$key])?$_POST[$key]:null); return noapos($val); }

function hspc($str){
	return htmlspecialchars($str,ENT_SUBSTITUTE);
}

function utf8_fix($str){
	list($res,$_)=_utf8_fix($str);
	return $res;	
}

function _utf8_fix($str){
		
	$tstr=utf8_encode($str);
	$oqc=0; for ($i=0;$i<strlen($str);$i++) if ($str[$i]=='?') $oqc++;
	$itr=0;
	while (preg_match('//u',$tstr)){
		$str=$tstr;
				
		$tstr=utf8_decode($str);
		if ($str==$tstr) break;
		
		$qc=0; for ($i=0;$i<strlen($tstr);$i++) if ($tstr[$i]=='?') $qc++;
		if ($qc>$oqc) break;
		$itr++;				
	}
		
	return array($str,$itr);
}

function decode_unicode_url($str){
	$str=utf8_encode($str);
	//$str=htmlentities($str); //French accent fix
	
	$res = '';
	
	$i = 0; $max=strlen($str)-6;
	
	while ($i<=$max){
		$c=$str[$i];
		if ($c=='%'&&$str[$i + 1]=='u'){
			$v=hexdec(substr($str,$i+2,4));
			$i+=6;
			
			if ($v<0x0080) $c=chr($v); //1 byte
			else if ($v<0x0800) $c=chr((($v&0x07c0)>>6)|0xc0).chr(($v&0x3f)|0x80); // 2 bytes: 110xxxxx 10xxxxxx
			else $c=chr((($v&0xf000)>>12)|0xe0).chr((($v&0x0fc0)>>6)|0x80).chr(($v&0x3f)|0x80); // 3 bytes: 1110xxxx 10xxxxxx 10xxxxxx
		} else $i++;
		
		$res.=$c;
	}//while
	
	return $res . substr($str, $i);
}

function tzconvert($stamp,$src,$dst){
	
	$tz=date_default_timezone_get();
	
		date_default_timezone_set($src);
		$y=date('Y',$stamp);
		$n=date('n',$stamp);
		$j=date('j',$stamp);
		$h=date('H',$stamp);
		$i=date('i',$stamp);
		$s=date('s',$stamp);
				
		date_default_timezone_set($dst);
		$nstamp=mktime($h,$i,$s,$n,$j,$y);
			
	date_default_timezone_set($tz);
	return $nstamp;	
}

function date2stamp($date,$hour=0,$min=0,$sec=0){
	$parts=explode('-',trim($date));
	if (count($parts)!=3) return null;
	return mktime($hour,$min,$sec,$parts[1],$parts[2],$parts[0]);	
}

function apperror($str,$msg=null){if (!isset($msg)) $msg=$str;header('apperror: '.$str);die('apperror - '.$msg);}


function makelookup($id,$fullscale=0){
?>
<div class="minilookup" id="<?php echo $id;?>_lookup"><a id="<?php echo $id;?>_lookup_closer" class="labelbutton closer" onclick="gid('<?php echo $id;?>_lookup').style.display='none';"><?php tr('lookup_closer')?></a>
<div id="<?php echo $id;?>_lookup_view" class="lookupview"<?php if ($fullscale) echo ' style="height:auto;overflow:normal;"';?>></div></div>
<?php 	
}

function cancelpickup($id){
?>
<a class="labelbutton" onclick="cancelpickup('<?php echo $id;?>');"><?php tr('pickup_edit');?></a>
<?php 	
}

function logaction($message,$rawobj=null,$syncobj=null){
	$user=userinfo();
	$userid=$user['userid']+0;
	$logname=$user['login'];
	$logname=str_replace("'",'',$logname);
	global $db;
	$wssid=isset($_GET['wssid_'])?($_GET['wssid_']+0):0;

	if (!isset($rawobj)) $rawobj=array();
	$message=noapos($message);

	$cobj=array();
	foreach ($rawobj as $k=>$v){
		if (is_array($v)) continue;
		$v=noapos($v);
		$v=str_replace('"','&quot;',$v);
		$cobj[$k]=$v;
	}
	
	$obj=json_encode($cobj);
	$obj=str_replace("\\'","'",$obj);

	$now=time();

	$query="insert into ".TABLENAME_ACTIONLOG."(userid,logname,logdate,logmessage,rawobj) values ($userid,'$logname','$now','$message','$obj')";
	
	if ($syncobj!=''){
		$sid=$wssid;
		$rectype=$syncobj['rectype'];
		$recid=$syncobj['recid']+0;
		$query="insert into ".TABLENAME_ACTIONLOG."(userid,logname,logdate,logmessage,rawobj,sid,rectype,recid) values ($userid,'$logname','$now','$message','$obj',$sid,'$rectype',$recid)";
	}
	sql_query($query,$db);
}

function timeformat($sec){
	$sec_num = $sec+0; // don't forget the second param
	$hours = floor($sec_num / 3600);
	$minutes = floor(($sec_num - ($hours * 3600)) / 60);
	$seconds = $sec_num - ($hours * 3600) - ($minutes * 60);
		
	if ($hours   < 10) $hours = "0".$hours;
	if ($minutes < 10) $minutes = "0".$minutes;
	if ($seconds < 10) $seconds = "0".$seconds;
	$time  = "$hours:$minutes:$seconds";
	return $time;	
}
