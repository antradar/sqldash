<?php

function showdatepicker(){
	global $db;
	global $dict_mons;
	global $dict_wdays;

	$key=trim(GETSTR('key'));
	
	$mode=GETSTR('mode');

	$mini=GETSTR('mini')+0;
	
	$hstart=GETSTR('hstart');
	$hend=GETSTR('hend');
	
	$dmini='';
	if ($mini) $dmini=', 1';
		
	//get current month and year
	$m=date("n")+0;
	$y=date("Y");
	$d=date('j');


	if ($_GET['nodate']) {
		if ($_GET['y']) $y=$_GET['y'];
		if ($_GET['m']) $m=$_GET['m'];
		if ($_GET['d']) $d=$_GET['d'];
		
?>
<div id="timepicker">
	<?php showtimepicker($y,$m,$d,$hstart,$hend,60);?>
</div>
<?php	
		return;	
	}//nodate

	//detect user intent
	if ($key==($key+0)) {
		if ($key>0&&$key<=12) $m=$key;
	}

	$keys=explode(" ",str_replace("-"," ",$key));
	if (strlen($keys[0])==4) $y=$keys[0];
	if (strlen($keys[1])==4) $y=$keys[1];
	if (strlen($keys[0])<3&&$keys[0]>0&&$keys[0]<=12) $m=$keys[0];
	if (strlen($keys[1])<3&&$keys[1]>0&&$keys[1]<=12) $m=$keys[1];

	$nm=$m+1;
	$ny=$y;
	$py=$y;
	$pm=$m-1;

	if ($nm>12) {$ny++;$nm-=12;}
	if ($pm<1) {$py--;$pm+=12;}

	$fd=mktime(1,1,1,$m,1,$y);
	$ld=date('j',mktime(23,59,59,$nm,0,$ny));
	$w=date("w",$fd);

	$wdays=$dict_wdays;

	$start=$fd;
	$end=mktime(23,59,59,$nm,0,$ny);
	
	$today=date('Y-n-j');
	
	$dmdate=_tr('yearmonth',array('mon'=>$dict_mons[date('n',$fd)],'year'=>date('Y',$fd)));

?>
<div style="width:100%;text-align:center;padding-top:10px;" id="cale_daypicker">

<div style="width:100%;position:relative;margin-top:5px;text-align:center;"><?php echo $dmdate;?>
<span style="position:absolute;top:2px;left:12px;cursor:pointer;" onclick="<?php if ($mode=='datetime'){?>
pickdatetime(null,{start:'<?php echo $hstart;?>',end:'<?php echo $hend;?>',mini:<?php echo $mini;?>},'<?php echo "$py-$pm"?>');
<?php } else {?>
if (!document.hotspot) {pickdate(null,{mini:<?php echo $mini;?>},'<?php echo "$py-$pm"?>');return;} document.hotspot.value='<?php echo "$py-$pm"?>';pickdate(document.hotspot,{mini:<?php echo $mini;?>},null);
<?php }?>"><img class="img-calel" src="imgs/t.gif" width="5" height="12"></span>

<span style="position:absolute;top:2px;right:12px;cursor:pointer;" onclick="<?php if ($mode=='datetime'){?>
pickdatetime(null,{start:'<?php echo $hstart;?>',end:'<?php echo $hend;?>',mini:<?php echo $mini;?>},'<?php echo "$ny-$nm"?>');
<?php } else {?>
if (!document.hotspot) {pickdate(null,{mini:<?php echo $mini;?>},'<?php echo "$ny-$nm"?>');return;} document.hotspot.value='<?php echo "$ny-$nm"?>';pickdate(document.hotspot,{mini:<?php echo $mini;?>},null);<?php }?>"><img class="img-caler" src="imgs/t.gif" width="5" height="12"></span>
</div>

<div id="calepicker" style="font-size:12px;width:100%;height:200px;margin:0 auto;margin-top:5px;">
<?php for ($i=0;$i<7;$i++){?>
<div style="width:14%;float:left;">
<div style="height:20px;border:solid 1px #ffffff;margin-left:1px;"><?php echo $wdays[$i];?></div>
</div>
<?php }?>
<?php for ($i=0;$i<$w;$i++){?>
<div style="width:14%;float:left;">
<div style="height:25px;border:solid 1px #444444;margin:1px;"></div>
</div>
<?php }?>
<?php
for ($i=1;$i<=$ld;$i++){
?>
<div onclick="<?php if ($mode!='datetime'){?>if (document.hotspot) {document.hotspot.value='<?php echo "$y-$m-$i"?>'; if (document.hotspot.lookupview) document.hotspot.lookupview.style.display='none';if (gid(document.hotspot.id+'_lookup')) gid(document.hotspot.id+'_lookup').style.display='none';}else showday('<?php echo "$y-$m-$i"?>');<?php } else {?>gid('cale_daypicker').style.display='none';ajxpgn('timepicker',document.appsettings.codepage+'?cmd=showtimepicker&y=<?php echo $y;?>&m=<?php echo $m;?>&d=<?php echo $i;?>&start=<?php echo $hstart;?>&end=<?php echo $hend;?>&res=60',1);<?php }?>" style="cursor:pointer;width:14%;float:left;">
<div style="height:25px;border:solid 1px #444444;margin:1px;<?php if ($today=="$y-$m-$i") echo 'font-weight:bold;color:#ab0200';?>"><?php echo $i;?>
</div></div>
<?php
}
?>
</div>
</div>
<div style="clear:both;"></div>

<div id="timepicker" style="display:none;width:100%;position:relative;">
	
</div>
<?php
}

function showtimepicker($y=null,$m=null,$d=null,$start=null,$end=null,$res=null,$h24=1){
	
	if (!isset($y)){
		$y=GETVAL('y');
		$m=GETVAL('m');
		$d=GETVAL('d');
		
		if ($_GET['start']) $start=$_GET['start'];
		if ($_GET['end']) $end=$_GET['end'];
		if ($end==24) $end=26;

		$res=GETSTR('res');	
	}

	
	$base=mktime(0,0,0,$m,$d,$y);
	
	$rstart=$base+$start*3600;
	$rend=$base+$end*3600;
	
	if ($_GET['rstart']){
		$rstart=$_GET['rstart'];
		$rend=$_GET['rend'];	
	}

	if ($rend<$rstart) $rend=$rstart;

	$nextres=$res/4;
	if ($res==15) $nextres=1;
	
	$daykey=date('Y-n-j',$base);
	$ldaykey=date('Y-n-j',$base-3600);
	
	for ($i=$rstart-$res*60;$i<=$rend;$i+=$res*60){
	
		$val=$i;
		$t=date('g:ia',$val);	
		$hstart=$val+$nextres*60;
		$hend=$val+$res*60-$nextres*60;

		$picked=date('Y-n-j',$val).' '.$t;
		if ($_GET['nodate']) $picked=$t;
		$ds=date('I',$val);
		if ($ds) $picked.=' *';

		$dkey=date('Y-n-j',$val-3600);
		$mdkey=date('Y-n-j',$val);
		if ($dkey!=$daykey&&$dkey!=$ldaykey) continue;
	?>
		<div style="position:relative;height:30px;border-bottom:solid 1px #999999;">
			<?php if ($i>$rstart-$res*60){?>
			<a style="padding:10px 5px;display:block;margin-right:50px;" onclick="picklookup('<?php echo $picked;?>',<?php echo $val;?>);"><?php echo $t;?>
				<?php if ($ds){?><img src="imgs/t.gif" class="daylightsaving"><?php }?>
			</a>
			<?php }?>
			<?php if ($res>1&&$mdkey==$daykey){?>
			<a style="position:absolute;display:block;padding:1px 5px;font-size:10px;border-radius:5px;background-color:#666666;color:#ffffff;top:20px;right:10px;"
				onclick="this.style.display='none';ajxpgn('subtime_<?php echo $i;?>',document.appsettings.codepage+'?cmd=showtimepicker&nodate=<?php echo $_GET['nodate']+0;?>&y=<?php echo $y;?>&m=<?php echo $m;?>&d=<?php echo $d;?>&rstart=<?php echo $hstart;?>&rend=<?php echo $hend;?>&res=<?php echo $nextres;?>');">...</a>
			<?php }?>
		</div>
		<div id="subtime_<?php echo $i;?>" style="margin:0 10px;">
		
		</div>
	<?php
		
	} //for i
	
}
