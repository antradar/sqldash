<?php

function sqldash_getexts(){
	global $sqldash_exts;
	
	$res=array('exts'=>array(),'hooks'=>array(),'routes'=>array());
	
	foreach ($sqldash_exts as $ext){
		$extfn='ext/'.$ext.'.config.php';
		if (!file_exists($extfn)){
			echo "Missing extension file ".$extfn."<br>";
			continue;	
		}
		include_once $extfn;
		$regfunc='ext_'.str_replace('/','_',$ext).'_register';
		
		$extinfo=$regfunc();
		
		//echo '<pre>'; print_r($extinfo); echo '</pre>';
		
		$res['exts'][$ext]=$extinfo;
		
		foreach ($extinfo['hooks'] as $hkey=>$func){
			if (!isset($res['hooks'][$hkey])) $res['hooks'][$hkey]=array();
			array_push($res['hooks'][$hkey],array('name'=>$extinfo['name'],'func'=>$func,'ext'=>$ext));	
		}//hooks
		
		foreach ($extinfo['routes'] as $route=>$subconnect) array_push($res['routes'],array('route'=>$route,'subconnect'=>$subconnect,'ext'=>$ext));
	}	
	
	return $res;
	
	
}
