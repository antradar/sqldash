<?php

function dashquerydims_countfield($fieldname,$limit=null){
	global $db;
	
	$xquery=SQET('query'); $xquery=trim($xquery,';');
	$filters=dashquerydims_makefilters();

	//if a 1-N sum is used, use *count(distinct querytable.querykey)/count(querytable.querykey)	
	$qfieldname=$fieldname;

	// if ($fieldname=='expression_field') $qfieldname=" some_expression as mapped_field ";
	
	$query="select count(*) as c, $qfieldname from ($xquery) t ";
			
		
	$query.=" where 1=1 ";
	


	$params=array();
	$sqlfilters=dashquerydims_sqlfilters();
	$query.=$sqlfilters['clauses'];
	$params=array_merge($params,$sqlfilters['params']);
	
	$query.=" group by $fieldname ";
	
	if ($limit!=null) $query.=" order by c desc limit $limit ";
	else $query.=" order by $fieldname ";
			
	$rs=sql_prep($query,$db,$params);
	$counts=array();
	
	while ($myrow=sql_fetch_array($rs)){
		$c=intval($myrow['c']);
		$a=$c;
		if ($c<=0) continue;
		$fnparts=explode('.',$fieldname);
		$fn=$fnparts[count($fnparts)-1];
		$key=$myrow[$fn];
		if (trim($key)=='') continue;
		$counts[$key]=array('c'=>$c,'a'=>$a);	
	}
	return $counts;	
	
}

function dashquerydims_sqlfilters(){
	$filters='';
	$params=array();
	$key=GETSTR('key');
	$sqlmode=SQET('sqlmode');

	if ($sqlmode=='sqlite') die('SQLite not yet supported');	
	
	$dimtypes=json_decode(SQET('dims'),1);
	
	foreach ($dimtypes as $dimtype=>$dims){
		foreach ($dims as $dim){
			switch ($dimtype){
			case 'dim':
				if (isset($_GET[$dim])&&$_GET[$dim]!='') {
					$filters.=" and $dim=? ";
					array_push($params,SGET($dim));
				}			
			break;
			case 'range':
				if (isset($_GET[$dim.'_a'])&&$_GET[$dim.'_a']!='') {
					$filters.=" and $dim>=? ";
					array_push($params,$_GET[$dim.'_a']);
				}
				if (isset($_GET[$dim.'_b'])&&$_GET[$dim.'_b']!='') {
					$filters.=" and $dim<=? ";
					array_push($params,$_GET[$dim.'_b']);
				}			
			break;
			case 'daterange':
							
				if (isset($_GET[$dim.'_a'])&&$_GET[$dim.'_a']!='') {
					$filters.=" and $dim>=? ";
					array_push($params,date2stamp($_GET[$dim.'_a'],0,0,0));
				}
				if (isset($_GET[$dim.'_b'])&&$_GET[$dim.'_b']!='') {
					$filters.=" and $dim<=? ";
					array_push($params,date2stamp($_GET[$dim.'_b'],23,59,59));
				}
	
			break;
			}//switch	
		}
	}	

							
	
	return array('clauses'=>$filters,'params'=>$params);
}

function dashquerydims_makefilters(){
	$filters=array();
	$nfilters=array();
	
	$key=GETSTR('key');
	if ($key!='') {
		$filters['searchterm']=$key;
	}
	if (isset($_GET['visible'])) $filters['visible']=$_GET['visible'];


	$dimtypes=json_decode(SQET('dims'),1);
	
	foreach ($dimtypes as $dimtype=>$dims){
		foreach ($dims as $dim){
			switch ($dimtype){
			case 'dim':
				if (isset($_GET[$dim])&&$_GET[$dim]!='') $filters[$dim]=$_GET[$dim];
			
			break;
			case 'range': case 'daterange':
				if (isset($_GET[$dim.'_a'])&&$_GET[$dim.'_a']!='') $filters[$dim.'_a']=$_GET[$dim.'_a'];
				if (isset($_GET[$dim.'_b'])&&$_GET[$dim.'_b']!='') $filters[$dim.'_b']=$_GET[$dim.'_b'];
			break;
			}//switch	
		}
	}

		
	return array('filters'=>$filters,'nfilters'=>$nfilters);
}

function dashquerydims_strfilters($filters,$nfilters=null){
	
	$filter='';
	foreach ($filters as $key=>$val){
		if (is_array($val)) foreach ($val as $k=>$v) $filter.='&'.$key.'['.urlencode($k).']=1';
		else $filter.='&'.urlencode($key).'='.urlencode($val);

	}
	
	if (is_array($nfilters)){
		foreach ($nfilters as $key=>$vals) {
			foreach ($vals as $val) $filter.="&neg__${key}[]=$val";	
		}
	}
		
	return $filter;
}

function dashquerydims_shownav($container, $cmd, $title,$fieldname,$multi=null,$multior=false){
	
	global $pcharts;
	
	$queryidx=GETVAL('queryidx');

	$bfilters=dashquerydims_makefilters();
	$basefilters=$bfilters['filters'];
	$nbasefilters=$bfilters['nfilters'];

	
	if (isset($basefilters[$fieldname])&&!$multi){
		$myfilters=$basefilters;
		unset($myfilters[$fieldname]);
		$filter=dashquerydims_strfilters($myfilters,$nbasefilters);
?>
<div class="navgroupx ng_<?php echo $fieldname;?>">
	<div class="navtitle"><?php echo $title;?></div>
	
	<div class="navfilter">
		<a href=# onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','<?php echo $filter;?>','<?php echo $queryidx;?>');return false;">[x]</a> <?php echo dashquerydims_dispname($fieldname,$basefilters[$fieldname]);?>
	</div>
</div><!-- navgroupx -->
<?php		
		return;
	}

// end inline breadcrumb

	$counts=dashquerydims_countfield($fieldname);
	$dcounts=$counts;

	//add any parents of a nested dimension in the exemption list below:
	if (!$multi&&count($counts)<2&&!in_array($fieldname,array('exempt_1','exempt_2'))) return; //comment out to show singular filters
	
	$bfilters=dashquerydims_makefilters();
	$basefilters=$bfilters['filters'];
	$nbasefilters=$bfilters['nfilters'];
		
?>
<div class="navgroupx ng_<?php echo $fieldname;?>">
<div class="navtitle"><?php echo $title;?>
	<?php if ($multior){
		$mybasefilters=$basefilters;
		unset($mybasefilters[$fieldname]);
		$strbasefilter=dashquerydims_strfilters($mybasefilters,$nbasefilters);		
		
	?>
	<span style="margin-left:20px;visibility:hidden;"><button id="multior_<?php echo $fieldname;?>" onclick="nav_applymultior('<?php echo $container;?>','<?php echo $fieldname;?>','dashquerydimkey','<?php echo $cmd;?>','<?php echo $strbasefilter;?>');">apply filters</button></span>
	<?php }?>
</div>
<div class="navfilters" id="navfilters_<?php echo $fieldname;?>">
<?php	

		
	if ($multior){
		$selids=array();
		if (isset($_GET['multior_'.$fieldname])&&$_GET['multior_'.$fieldname]!=''){	
			$selids=explode('||',$_GET['multior_'.$fieldname]);
		}
	}
	
	$mymultiorfilters=$basefilters;
	if (isset($mymultiorfilters["multior_".$fieldname])) unset($mymultiorfilters["multior_".$fieldname]);
	$multiorfilters=dashquerydims_strfilters($mymultiorfilters,$nbasefilters);
			
	foreach ($counts as $key=>$count){
		$dispname=dashquerydims_dispname($fieldname,$key,array_keys($counts));
		$myfilters=$basefilters;
		if ($multi){
			if (!is_array($myfilters[$multi])) $myfilters[$multi]=array();
			$myfilters[$multi][$key]=1;
			if ($basefilters[$multi][$key]) unset($myfilters[$multi][$key]);
		} else $myfilters[$fieldname]=$key;
		$filters=dashquerydims_strfilters($myfilters,$nbasefilters);

		$dcounts[$key]['n']=$dispname.'';
		$dcounts[$key]['f']=$filters.'';

		//echo '<pre>';print_r($filters);echo '</pre>';
		/*
		todo: use your container ids for navigation, breadcrumb and record list
		*/
		
		?>
<div class="navfilter">
		<?php
		if ($multi){
	?>		
		<input class="multiand" <?php if ($basefilters[$multi][$key]) echo 'checked';?> type="checkbox" href=# onclick="nav_setfilter('<?php echo $container;?>','dashquerydimkey','<?php echo $cmd;?>','<?php echo $filters;?>');return false;"> 
	<?php		
		}
		
		if ($multior){
		?>
		<input <?php if (in_array($key,$selids)) echo 'checked';?> value="<?php echo $key;?>"  type="checkbox" onclick="nav_selectfilter(this,'<?php echo $container;?>','<?php echo $fieldname;?>','dashquerydimkey','<?php echo $cmd;?>','<?php echo $multiorfilters;?>');"> 
		<?php		
		}		

?>
	<a href=# onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','<?php echo $filters;?>','<?php echo $queryidx;?>');return false;"><?php echo htmlspecialchars($dispname);?></a> 
	<?php
	if (!$multi||!$basefilters[$multi][$key]||true){ //remove true to hide refinement count for selected multi fields
	?>
	<em>(<?php echo $count['c'];?>)</em>
	<?php }?>
	
		
</div>
<?php
	}
	
	if (!$multi||true){ //pie chart for multi-select fields skews the data but can nevertheless be useful
		if (!isset($pcharts)) $pcharts=array();
		if (!isset($pcharts[$fieldname])) $pcharts[$fieldname]=array(
			'title'=>$title,
			'type'=>'pie',
			'fieldname'=>$fieldname,
			'counts'=>array_values($dcounts)
		);
	}		
?>
</div><!-- navfilters -->
</div><!-- navgroupx -->
<?php		
}


function dashquerydims_dispname($fieldname,$key,$ids=null){
	global $db;

	//global $dimnames;
	
	if (!is_array($ids)) $ids=array($key);
	$strids=implode(',',$ids); //for creating name dictionaries

	
	switch ($fieldname){
		/*
		todo: add field value to field name mapping
		*/

		/*
		case 'dim':
			if (!isset($dimnames)){
				$query="select * from dims where dimid in ($strids)";
				$rs=sql_query($query,$db);
				while ($myrow=sql_fetch_assoc($rs)) $dimnames[$myrow['dimid']]=$myrow['dimname'];
			}
			$res=$dimnames[$key];
			if (count($ids)==1) $dimnames=null;
			return $res;
		break;
		*/

		default: return $key;
	}	
}


function dashquerydims_showrange($container,$cmd, $title,$fieldname){
	global $db;
	global $pcharts;
	$queryidx=GETVAL('queryidx');
	$sqlmode=SQET('sqlmode');

	if (stripos($fieldname,' ')!==false) $fieldname='['.$fieldname.']';
		
	if ($sqlmode=='sqlite'){
		echo "SQLite not yet supported";
		die();	
	}
		
	$bfilters=dashquerydims_makefilters();
	$basefilters=$bfilters['filters'];
	$nbasefilters=$bfilters['nfilters'];
	$filters=$basefilters;
		
	$dimmode=$filters[$fieldname.'__dimmode']??'';
	if ($dimmode=='') $dimmode='c';
	
// start inline breadcrumb		
	if ( (isset($filters[$fieldname.'_a'])&&is_numeric($filters[$fieldname.'_a'])) || (isset($filters[$fieldname.'_b'])&&is_numeric($filters[$fieldname.'_b'])) ){
		$myfilters=$filters;
		unset($myfilters[$fieldname.'_a']);
		unset($myfilters[$fieldname.'_b']);
		$filter=dashquerydims_strfilters($myfilters,$nbasefilters);		
?>
<div class="navgroupx ng_<?php echo $fieldname;?>">
	<div class="navtitle"><?php echo $title;?></div>
	<div class="navfilter">
<a class="filterclear" onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','<?php echo $filter;?>','<?php echo $queryidx;?>');return false;">[x]</a>
<?php echo $filters[$fieldname.'_a'].' - '.$filters[$fieldname.'_b'];?>
	</div>
</div> 
<?php	
		$filtered=1;
	}
// end inline breadcrumb

	$sqlfilters=dashquerydims_sqlfilters();	

	$params=array();
	
	$xquery=SQET('query'); $xquery=trim($xquery,';');
	
	$query="select min($fieldname) as a, max($fieldname) as b from ($xquery) t where 1=1 ".$sqlfilters['clauses'];
	$params=array_merge($params,$sqlfilters['params']);
	
	$rs=sql_prep($query,$db,$params);
	$myrow=sql_fetch_array($rs);
	
	$min=$myrow['a'];
	$max=$myrow['b'];

	if ($min==$max) return;
	
	$filter=dashquerydims_strfilters($filters,$nbasefilters);
	
	$bucketsize=($max-$min)/10;
					
	$params=array();
	
	$query="select count(distinct $fieldname) as c,min($bucketsize*floor($fieldname/$bucketsize)) as cmin, max($bucketsize*floor($fieldname/$bucketsize+1)) as cmax 
		from ($xquery) t where 1=1 ".$sqlfilters['clauses'];
	$query.=" group by floor($fieldname/$bucketsize) ";
		
	$params=array_merge($params,$sqlfilters['params']);
	
	$rs=sql_prep($query,$db,$params);
	$cs=array();
	
	while ($myrow=sql_fetch_array($rs)){
		if (!isset($myrow['cmin'])) continue;
		array_push($cs,array(
			'min'=>floatval($myrow['cmin']),
			'max'=>floatval($myrow['cmax']),
			'xlabel'=>$myrow['xlabel']??'',
			'count'=>intval($myrow['c']),
			'f'=>"$filter&${fieldname}_a=".$myrow['cmin'].'&'.$fieldname.'_b='.$myrow['cmax']
		));	
	}
	
	if (!isset($pcharts)) $pcharts=array();
	if (!isset($pcharts[$fieldname])) $pcharts[$fieldname]=array(
		'dimmode'=>$dimmode,
		'title'=>$title,
		'type'=>'column',
		'fieldname'=>$fieldname,
		'counts'=>array($cs)
	);	
				
	if (isset($filtered)&&$filtered) return;			
?>
<div class="navgroupx ng_<?php echo $fieldname;?>">
	<div class="navtitle"><?php echo $title;?></div>
		<input class="inpshort num" id="<?php echo $fieldname?>_a" name="<?php echo $fieldname;?>_a" value="<?php echo $min;?>" style="width:60px;"> - 
		<input class="inpshort num" id="<?php echo $fieldname?>_b" name="<?php echo $fieldname;?>_b" value="<?php echo $max;?>" style="width:60px;">
		<button onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','<?php echo $filter;?>&<?php echo $fieldname;?>_a='+gid('<?php echo $fieldname;?>_a').value+'&<?php echo $fieldname;?>_b='+gid('<?php echo $fieldname;?>_b').value,'<?php echo $queryidx;?>');return false;">Set</button>
</div>
<?php	
		
}

function dashquerydims_showdaterange($container,$cmd, $title,$fieldname,$subdims=null){ //subdims is a key-val associative array
	global $db;
	global $pcharts;
	
	$queryidx=GETVAL('queryidx');
	$xquery=SQET('query'); $xquery=trim($xquery,';');
	
	$bfilters=dashquerydims_makefilters();
	$basefilters=$bfilters['filters'];
	$nbasefilters=$bfilters['nfilters'];
	$filters=$basefilters;
	$filtered=0;
	
	$dimkey='';
		
	$dimmode='';
		
	switch ($dimmode){
		//case 'a': $tally='avg(...)'; break;
		//case 's': $tally='sum(...)'; break;
		default: $tally='count(*)';	
	}		

// start inline breadcrumb		
	if (isset($filters[$fieldname.'_a'])||isset($filters[$fieldname.'_b'])){
		$myfilters=$filters;
		unset($myfilters[$fieldname.'_a']);
		unset($myfilters[$fieldname.'_b']);
		$filter=dashquerydims_strfilters($myfilters,$nbasefilters);		
?>
<div class="navgroupx ng_<?php echo $fieldname;?>">
	<div class="navtitle"><?php echo $title;?></div>
	<div class="navfilter">
<a class="filterclear" onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','<?php echo $filter;?>','<?php echo $queryidx;?>');return false;">[x]</a>
<?php echo $filters[$fieldname.'_a'].' - '.$filters[$fieldname.'_b'];?>

	<?php if ($filters[$fieldname.'_a']==$filters[$fieldname.'_b']){
		$nextfilters=$myfilters;
		$nextfilters[$fieldname.'_a']=date('Y-n-j',date2stamp($filters[$fieldname.'_a'])+3600*(24+2)); //26 hours to counter daylight saving
		$nextfilters[$fieldname.'_b']=$nextfilters[$fieldname.'_a'];
		$nextfilter=dashquerydims_strfilters($nextfilters,$nbasefilters);
	
		$prevfilters=$myfilters;
		$prevfilters[$fieldname.'_a']=date('Y-n-j',date2stamp($filters[$fieldname.'_a'])-3600*24);
		$prevfilters[$fieldname.'_b']=$prevfilters[$fieldname.'_a'];
		$prevfilter=dashquerydims_strfilters($prevfilters,$nbasefilters);
			
		?>
			<div style="padding-top:5px;font-size:11px;text-align:center;">
				<a class="hovlink" onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','<?php echo $prevfilter;?>','<?php echo $queryidx;?>');return false;">&laquo; Prev Day</a>
				&nbsp; | &nbsp;
				<a class="hovlink" onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','<?php echo $nextfilter;?>','<?php echo $queryidx;?>');return false;">Next Day &raquo;</a>
			</div>
	<?php
	}
	$stampa=date2stamp($filters[$fieldname.'_a']);
	$stampb=date2stamp($filters[$fieldname.'_b']);
	$ndays=round(($stampb-$stampa)/3600/24);
	if ($ndays>=4&&$ndays<=8){
		$nextfilters=$myfilters;
		$nextfilters[$fieldname.'_a']=date('Y-n-j',$stampa+3600*24*7+3600*2);
		$nextfilters[$fieldname.'_b']=date('Y-n-j',$stampb+3600*24*7+3600*2);
		$nextfilter=dashquerydims_strfilters($nextfilters,$nbasefilters);
	
		$prevfilters=$myfilters;
		$prevfilters[$fieldname.'_a']=date('Y-n-j',$stampa-3600*24*7);
		$prevfilters[$fieldname.'_b']=date('Y-n-j',$stampb-3600*24*7);
		$prevfilter=dashquerydims_strfilters($prevfilters,$nbasefilters);
			
		?>
			<div style="padding-top:5px;font-size:11px;text-align:center;">
				<a class="hovlink" onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','<?php echo $prevfilter;?>','<?php echo $queryidx;?>');return false;">&laquo; Prev Week</a>
				&nbsp; | &nbsp;
				<a class="hovlink" onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','<?php echo $nextfilter;?>','<?php echo $queryidx;?>');return false;">Next Week &raquo;</a>
			</div>
		<?php
		
	}
	?>

	</div>
</div> 
<?php	
		$filtered=1;	
	}
// end inline breadcrumb

	$sqlfilters=dashquerydims_sqlfilters();	

	$params=array();
	
	$query="select min($fieldname) as a, max($fieldname) as b from ($xquery)t where 1=1 ".$sqlfilters['clauses'];
	$params=array_merge($params,$sqlfilters['params']);
	
	$rs=sql_prep($query,$db,$params);
	$myrow=sql_fetch_array($rs);
	
	$min=date('Y-n-j',$myrow['a']);
	$max=date('Y-n-j',$myrow['b']);

	if ($min==$max) $filtered=1;
	
	$params=array();
	
	$minyear=date('Y',$myrow['a']); $maxyear=date('Y',$myrow['b']);
	$minmon=date('n',$myrow['a']); $maxmon=date('n',$myrow['b']);
	$minday=date('j',$myrow['a']); $maxday=date('j',$myrow['b']);
	$minhour=date('H',$myrow['a']); $maxhour=date('H',$myrow['b']);
	
	$timebucket=" year(from_unixtime($fieldname)) ";
	
	if ($minyear==$maxyear){
		$timebucket=" concat(year(from_unixtime($fieldname)),'-',month(from_unixtime($fieldname))) ";
	}
	if ($minyear==$maxyear&&$minmon==$maxmon){
		$timebucket=" date(from_unixtime($fieldname)) ";
	}
	if ($minyear==$maxyear&&$minmon==$maxmon&&$minday==$maxday){
		$timebucket=" concat(date(from_unixtime(floor($fieldname/3600)*3600)),'-',hour(from_unixtime(floor($fieldname/3600)*3600))) ";
	}
	
	$query="select $tally as c,min($fieldname) as cmin,max($fieldname) as cmax ";
	if ($dimkey!='') $query.=", $dimkey ";	
	$query.=" from ($xquery) t where 1=1 ".$sqlfilters['clauses'];
	$query.=" group by $timebucket ";
	if ($dimkey!='') $query.=", $dimkey ";	
	$query.=" order by $fieldname ";

	$params=array_merge($params,$sqlfilters['params']);
	
	$rs=sql_prep($query,$db,$params);
	$cs=array();
	
	$filter=dashquerydims_strfilters($filters,$nbasefilters);
	$dtitle=$title;

	$xkeys=array();
	$dimkeys=array();
	
	while ($myrow=sql_fetch_array($rs)){
		if (!isset($myrow['cmin'])) continue;
		
		$xlabel=date('Y',$myrow['cmin']);
		if ($minyear==$maxyear) {$xlabel=date('M',$myrow['cmin']);$dtitle=$title.' - '.date('Y',$myrow['cmin']);}
		if ($minyear==$maxyear&&$minmon==$maxmon) {$xlabel=date('j',$myrow['cmin']);$dtitle=$title.' - '.date('M Y',$myrow['cmin']);}
		if ($minyear==$maxyear&&$minmon==$maxmon&&$minday==$maxday) {$xlabel=date('ga',$myrow['cmin']);$dtitle=$title.' - '.date('M j, Y',$myrow['cmin']);}
		
		$ckey=$xlabel.'@'.($myrow[$dimkey]??'');
		$xkeys[$xlabel]=$xlabel;
		$dimkeys[$myrow[$dimkey]??'']=$myrow[$dimkey]??'';
		if (!isset($cs[$ckey])) 		
		$cs[$ckey]=array(
			'min'=>$myrow['cmin'],
			'max'=>$myrow['cmax'],
			'xlabel'=>$xlabel,
			'count'=>0,
			'f'=>"$filter&${fieldname}_a=".date('Y-n-j',$myrow['cmin']).'&'.$fieldname.'_b='.date('Y-n-j',$myrow['cmax']),
			'k'=>$dimkey!=''?$myrow[$dimkey]:'',
			'kn'=>$dimkey!=''?dashquerydims_dispname($dimkey,$myrow[$dimkey]):''
		);
		
		$cs[$ckey]['count']+=round(floatval($myrow['c']),2);
	}
	
	if ($dimkey!=''){
		$ocs=$cs;
		$cs=array();
				
		foreach ($dimkeys as $dk){
			$series=array();
			foreach ($xkeys as $xkey){
				if (isset($ocs[$xkey.'@'.$dk])) array_push($series,$ocs[$xkey.'@'.$dk]);
				else array_push($series,array(
					'xlabel'=>$xkey,
					'count'=>0,
					'f'=>'',
					'k'=>'',
					'kn'=>dashquerydims_dispname($dimkey,$dk)
				));
			}
			
			array_push($cs,$series);
			
		}//foreach dimkey series
				
	} else $cs=array(array_values($cs)); //a series of one element
		
	
	if (!isset($pcharts)) $pcharts=array();
	if (!isset($pcharts[$fieldname])) $pcharts[$fieldname]=array(
		'dimmode'=>$dimmode,
		'title'=>$dtitle,
		'type'=>'column',
		'fieldname'=>$fieldname,
		'counts'=>$cs
	);		
	
	
	$dimfilters=$filters;unset($dimfilters['dimkey']); $dimkeyfilter=dashquerydims_strfilters($dimfilters,$nbasefilters);
	$dimfilters=$filters;unset($dimfilters['dimmode']); $dimmodefilter=dashquerydims_strfilters($dimfilters,$nbasefilters);
	
	$pcharts[$fieldname]['dimkeybase']=$dimkeyfilter; //base filters with dim key removed
	$pcharts[$fieldname]['dimmodebase']=$dimmodefilter; //base filters with dim mode removed
	$pcharts[$fieldname]['dimkey']=$dimkey;
	
	if (is_array($subdims)){
		$pcharts[$fieldname]['subdims']=$subdims;	
	}		
	
	
	if ($filtered) return;	
			
?>
<div class="navgroupx ng_<?php echo $fieldname;?>">
	<div class="navtitle"><?php echo $title;?></div>
		<input onfocus="pickdate(this);" onkeyup="_pickdate(this);" class="inp" id="<?php echo $fieldname?>_a" name="<?php echo $fieldname;?>_a" value="<?php echo $min;?>" style="width:80px;"> - 
		<input onfocus="pickdate(this);" onkeyup="_pickdate(this);" class="inp" id="<?php echo $fieldname?>_b" name="<?php echo $fieldname;?>_b" value="<?php echo $max;?>" style="width:80px;">
		<button onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','<?php echo $filter;?>&<?php echo $fieldname;?>_a='+gid('<?php echo $fieldname;?>_a').value+'&<?php echo $fieldname;?>_b='+gid('<?php echo $fieldname;?>_b').value,'<?php echo $queryidx;?>');return false;">Set</button>
</div>
<?php	
		
}


function dashquerydims_showchart($container,$cmd,$title,$fieldname){
	$queryidx=GETVAL('queryidx');
?>
<div id="dashquerydims_chartview_<?php echo $fieldname;?>_<?php echo $queryidx;?>" style="display:none;position:relative;" class="navchartview">
	<div class="navchartanchor" style="position:relative;">
		<div id="dashquerydims_chart_<?php echo $fieldname;?>_<?php echo $queryidx;?>" class="navchart"></div>		
	</div>
</div>
<?php	
}

function dashquerydims_showsearch($container,$cmd,$title,$fieldname){
	$bfilters=dashquerydims_makefilters();
	$basefilter=$bfilters['filters'];
	
	/*
	
	modify dashquerydims_shownavs to enable cascading search when the record set is trimmed "small enough"
	
	if (!isset($basefilter['this_dim'])&&(
		isset($basefilter['parent_dim_1'])
		||
		isset($basefilter['parent_dim_2'])
	)){
		dashquerydims_shownav($container, $cmd, 'This Dim Title','this_dim');	
	}
	
	
	*/
	
	if ($basefilter[$fieldname]!=''){
		dashquerydims_shownav($container,$cmd,$title,$fieldname);
		return;	
	}

	unset($basefilter[$fieldname]);
	
	$filters=dashquerydims_strfilters($basefilter,$nbasefilter);
	
?>
<div class="navgroupx ng_<?php echo $fieldname;?>">
	<div class="navtitle"><?php echo $title;?></div>
	<input class="inp" id="dashquerydims_<?php echo $fieldname?>" value="" style="width:120px;"> 
	<button onclick="nav_setfilter('<?php echo $container;?>','dashquerydimkey','<?php echo $cmd;?>','<?php echo $filters;?>&<?php echo $fieldname;?>='+encodeHTML(gid('dashquerydims_<?php echo $fieldname?>').value));return false;">Search</button>
</div>
<?php
}
	
function dashquerydims_shownavs($container, $cmd){
	
	$queryidx=GETVAL('queryidx');
	$dimtypes=json_decode(SQET('dims'),1);
	
				
	global $pcharts;

	$bfilters=dashquerydims_makefilters();
	$basefilter=$bfilters['filters'];
	$nbasefilter=$bfilters['nfilters'];
	$filters=dashquerydims_strfilters($basefilter,$nbasefilter);

	$vfilters=$basefilter;
	unset($vfilters['visible']);
	$vfilter=dashquerydims_strfilters($vfilters,$nbasefilter);

	if (count($basefilter)>0||count($nbasefilter)>0){
		$strfilters=dashquerydims_strfilters($basefilter,$nbasefilter);
	?>
		<button onclick="nav_setfilter('<?php echo $container;?>_<?php echo $queryidx;?>','dashquerydimkey_<?php echo $queryidx;?>','<?php echo $cmd;?>','','<?php echo $queryidx;?>');">Clear Filters</button>
	<?php		
	}	
?>
	<div class="clear"></div>
	<div style="padding:10px;display:nonea;">
		<input id="searchfilter_dashquerydims_<?php echo $queryidx;?>" type="hiddena" value="<?php echo $filters;?>" style="border:dashed 1px #dedede;width:90%;padding:10px;">
	</div>
	
<?php

	foreach ($dimtypes as $dimtype=>$dims){
		foreach ($dims as $dim){
			dashquerydims_showchart($container, $cmd, $dim, $dim);
		}	
	}




?>
<div class="clear" style="margin-bottom:20px;"></div>
<?php
		
	foreach ($dimtypes as $dimtype=>$dims){
		foreach ($dims as $dim){
			switch ($dimtype){
			case 'dim': dashquerydims_shownav($container, $cmd, $dim,$dim); break;	
			case 'range': dashquerydims_showrange($container, $cmd, $dim,$dim); break;	
			case 'daterange': dashquerydims_showdaterange($container, $cmd, $dim,$dim); break;	
			}	
		}	
	}
	
	if (!isset($pcharts)||!is_array($pcharts)) $pcharts=array();
	
	foreach ($pcharts as $pidx=>$chart){
		if ($chart['type']=='pie'){
			usort($pcharts[$pidx]['counts'],function($a,$b){
				if ($a['c']==$b['c']) return 0;
				if ($a['c']<$b['c']) return 1; else return -1;	
			});
		}
		if ($chart['type']=='column'){
			$pcharts[$pidx]['counts']=array_values($pcharts[$pidx]['counts']);
			
			/*

			foreach ($pcharts[$pidx]['counts'] as $cidx=>$item){
				
				usort($pcharts[$pidx]['counts'][$cidx],function($a,$b){
					if ($a['min']==$b['min']) return 0;
					if ($a['min']>$b['min']) return 1; else return -1;	
				});
				
			}
			
			*/
		}			
	}
		
?>	
	<div class="clear"></div>
	<textarea id="dashquerydims_chartdata_<?php echo $queryidx;?>" style="display:none;width:80%;height:300px;"><?php echo htmlspecialchars(json_encode(array_values($pcharts),JSON_PRETTY_PRINT)); ?></textarea>
	<div class="clear"></div>
<?php	
	
}



