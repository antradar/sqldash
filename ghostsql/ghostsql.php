<?php
//this file should not be run from an external location

set_time_limit(0);

if (file_exists('../sqldashkey.php')) include '../sqldashkey.php';

$datadir='/opt/data/'; //hardcoded, do not be flexible here, web mode: must be a non-root folder
$tempdir='/opt/datatmp/'; //folder for working copies, web mode: must be a general writable folder

$allowed_ips=array('127.0.0.1','::1');

function ghostsql_files(){
	global $datadir;
	$dh=opendir($datadir);
	$files=array();
	while ($file=readdir($dh)){
		$filetype=filetype($datadir.$file);
		if ($filetype!='file') continue;
		array_push($files,array(
		'name'=>$file,
		'size'=>filesize($datadir.$file),
		'date'=>filemtime($datadir.$file),
		));
	}
	return $files;
}

function ghostsql_tables($fn){
	global $datadir;
	global $tempdir;
	
	$fn=basename($fn);
	
	$rfn=$datadir.$fn;
	
	$cmd="grep -n 'create table `' -i -F ".$rfn;
	$tablestarts=explode("\n",shell_exec($cmd));
	
	$tables=array();
	
	foreach ($tablestarts as $idx=>$rec){
		if (!preg_match('/(\d+):create table (\S+)?/i',$rec,$matches)) continue;
		$start=$matches[1];
		$tablename=$matches[2];
		$tablename=str_replace('`','',$tablename);
		$tfn=$tablename.'_'.$fn;
		$tables[$idx]=array('name'=>$tablename,'datafn'=>$tfn,'start'=>$start);
		if ($idx>0) $tables[$idx-1]['end']=$start;
	}	
	
	
	$cmd="wc -l ".$rfn;
	$clineparts=explode(' ',shell_exec($cmd));
	
	$clines=$clineparts[0];
	
	$ntables=count($tables);
	if (!isset($tables[$ntables-1]['end'])) $tables[$ntables-1]['end']=$clines;
	$keyedtables=array();
	foreach ($tables as $table){
		if ($table['name']=='') continue;
		$keyedtables[$table['name']]=$table;	
	}
	return $keyedtables;
	
}

function ghostsql_makeslice($fn,$tablename,$start,$end){
	global $datadir;
	global $tempdir;
	
	$fn=basename($fn);
	
	$rfn=$datadir.$fn;
	$tfn=$tempdir.$tablename.'_'.$fn.'.tmp';
	$ofn=$tempdir.$tablename.'_'.$fn;
		
	$cmd="sed -n '".$start.",".($end-1)."p;".($end)."q' $rfn > $tfn";
	shell_exec($cmd);
	//todo: check if the file is successfully created
	
	$cmd="sed '/drop table /Id;/unlock table/Id;/lock table/Id' $tfn > $ofn";
	shell_exec($cmd);
	
	unlink($tfn);
	
	if (file_exists($ofn)){
		return array('fn'=>$tablename.'_'.$fn,'size'=>filesize($ofn));	
	} else return null; //error
}


if (defined('STDIN')){
	
	$switches=array();
	$actions=array();
	
	$rules=array(
		'i'=>array('var'=>'input_file','desc'=>'data dump file under '.$datadir),
		'ls'=>array('var'=>'','desc'=>'list tables and their locations'),
		't'=>array('var'=>'table','desc'=>'extract the structure and data of the given table into '.$tempdir),
		'f'=>array('var'=>'','desc'=>'force overwriting existing file'),
		'h'=>array('var'=>'','desc'=>'this help message'),
	);
			
	$validsyntax=1;
	
	if ($argc<4) $validsyntax=0;
	
	if ($validsyntax){
		$argidx=1;
		do{
			$s=$argv[$argidx];
			$nets=ltrim($s,'-');
			if (preg_match('/^-/',$s)&&isset($rules[$nets])){
				if ($rules[$nets]['var']!=''){
					if (!isset($argv[$argidx+1])){
						$validsyntax=0;
						break;	
					}
					$switches[$rules[$nets]['var']]=$argv[$argidx+1];
					$argidx++;
				}
				
				if (in_array($nets,array('ls','t','h'))) {
					$actions[$nets]=$nets;
				} else {
					if ($rules[$nets]['var']=='') $switches[$nets]=$nets;	
				} 
				
			} else {
				$validsyntax=0; //bad state machine
				break;	
			}
			$argidx++;
		} while ($argidx<$argc);	
	}
	
	echo "GhostSQL (Terminal Mode)\r\n";
	
	$actions=array_values($actions);

	if (count($actions)>1){
		echo "\r\nError: Specify only one action to perform.\r\n";
		$validsyntax=0;	
	}	
	
	if (!$validsyntax){

		echo "\r\nUsage: php ghostsql.php [options] -i [input_file]\r\n\r\n";
		foreach ($rules as $switch=>$rule){
			echo "  -".$switch.' ';
			if ($rule['var']!='') echo '['.$rule['var'].'] ';
			echo ' '.$rule['desc']."\r\n";
		}
		echo "\r\n\r\n";
		die();	
	}
	
	$fn=basename($switches['input_file']);
	$rfn=$datadir.$fn;
	if (!file_exists($rfn)) die("File not found: $rfn\r\n");
	
	$action=$actions[0];
	
	switch ($action){
		case 'ls':
			echo "\r\n";
			$tables=ghostsql_tables($fn);
			foreach ($tables as $rec){
				echo $rec['name'].' Line '.$rec['start'].' - '.$rec['end']."\r\n";
			}
			echo "\r\n";
		
		break;
		case 't':
			$table=$switches['table'];
			$tables=ghostsql_tables($fn);
			if (!isset($tables[$table])) die("Table [$table] not found in data dump\r\n");
			
			if (!isset($switches['f'])&&file_exists($tempdir.$tables[$table]['datafn'])) die("Target file already exists. Use -f to overwrite?\r\n");
			
			$res=ghostsql_makeslice($fn,$table,$tables[$table]['start'],$tables[$table]['end']);
			if (!isset($res)) die("Error extracting data. Check tempdir ($tempdir) permission?\r\n");
			
			echo "Data slice exported to $tempdir".$res['fn']."\r\n";
			echo "Size: ".$res['size']." bytes\r\n";
			
		break;
		default: die("Unsupported action: -$action\r\n");
	}
	
} else { // server mode

	$webaccess=1;
	
	if (!in_array($_SERVER['REMOTE_ADDR'],$allowed_ips)) $webaccess=0; //call from XHR, not CURL!
	
	$cmd=isset($_GET['cmd'])?$_GET['cmd']:'';
	
	$auth=isset($_GET['auth'])?$_GET['auth']:'';

	if (!defined('SQLDASHKEY')) $webaccess=0;
			
	$auth1=sha1(SQLDASHKEY.gmdate('Y-n-j-H'));
	$auth2=sha1(SQLDASHKEY.gmdate('Y-n-j-H'),time()-3600);
	
	if ($auth!=$auth1&&$auth!=$auth2) {
		echo "Access denied - try reloading the view?";
		die();	
	}
	
	if (!$webaccess){
		header('HTTP/1.0 403 Forbidden');
		die();	
	}

	$format='json';
	if (isset($_GET['format'])) $format=$_GET['format'];
	
	switch ($format){
		case 'gyroscope': break; //go with parent
		default: header('Content-Type: application/json');
	}
	

	
	switch ($cmd){
		case 'files':
			$files=ghostsql_files();
			if ($format=='gyroscope'){
				foreach ($files as $file){
				?>
				<div class="listitem">
					<a onclick="addtab('ghost_<?php echo $file['name'];?>','<img src=&quot;imgs/t.gif&quot; class=&quot;ico-ghost&quot;><?php echo $file['name'];?>','ghost_listtables&fn=<?php echo $file['name'];?>&auth=<?php echo $auth;?>',function(){ajxpgn('ghosttables_<?php echo $file['name'];?>','ghostsql/ghostsql.php?cmd=tables&format=gyroscope&fn=<?php echo $file['name'];?>&auth=<?php echo $auth;?>');});">
						<?php echo htmlspecialchars($file['name']);?>
						<br>
						<em style="color:#666666;"><?php echo number_format($file['size']/1024/1024);?> MB</em>
						<br>
						<em style="color:#666666;"><?php echo date('Y-n-j g:ia',$file['date']);?></em>
					</a>
				</div>
				<?php	
				}
			
				die();	
			}
			echo json_encode($files); die();
		break;
		case 'tables':
			if (!isset($_GET['fn'])) apierror("missing fn as filename");
			$fn=basename($_GET['fn']);
			$rfn=$datadir.$fn;
			if (!file_exists($rfn)) apierror("invalid file $rfn");
			$ta=microtime(1);			
			$rawtables=ghostsql_tables($fn);
			$tb=microtime(1);
			
			$tables=array();
			foreach ($rawtables as $tablename=>$_) array_push($tables,$tablename);
			if ($format=='gyroscope'){
			?>
			<div class="warnbox">index retrieved in <?php echo number_format(($tb-$ta)*1000);?> <em>ms</em></div>
			<?php
				foreach ($rawtables as $table){
				?>
				<div class="listitem">
					<b><?php echo htmlspecialchars($table['name']);?></b>
					<?php
					/*
					&nbsp; &nbsp;
					<a class="hovlink">view</a>
					*/
					?>
					&nbsp; &nbsp;
					<?php if (file_exists($tempdir.$table['datafn'])){?>
						<span class="labelbutton" style="color:#ffffff;">data ready</span>
						&nbsp; &nbsp;
						<span id="ghosttable_<?php echo $fn;?>_<?php echo $table['name'];?>"><a class="hovlink" onclick="ajxpgn('ghosttable_<?php echo $fn;?>_<?php echo $table['name'];?>','ghostsql/ghostsql.php?cmd=makeslice&format=gyroscope&fn=<?php echo $fn;?>&table=<?php echo $table['name'];?>&start=<?php echo $table['start'];?>&end=<?php echo $table['end'];?>&auth=<?php echo $auth;?>');">extract again</a></span>
					<?php } else {?>
						<span id="ghosttable_<?php echo $fn;?>_<?php echo $table['name'];?>"><a class="hovlink" onclick="ajxpgn('ghosttable_<?php echo $fn;?>_<?php echo $table['name'];?>','ghostsql/ghostsql.php?cmd=makeslice&format=gyroscope&fn=<?php echo $fn;?>&table=<?php echo $table['name'];?>&start=<?php echo $table['start'];?>&end=<?php echo $table['end'];?>&auth=<?php echo $auth;?>');">extract</a></span>
					<?php }?>
					
				</div>
				<?php	
				}		
				die();	
			}
			echo json_encode($tables); die();
		break;
		case 'makeslice':
			if (!isset($_GET['fn'])) apierror("missing fn as filename");
			$fn=basename($_GET['fn']);
			$rfn=$datadir.$fn;
			if (!file_exists($rfn)) apierror("invalid file $rfn");
		
			if (!isset($_GET['table'])) apierror("missing table");			
			$table=$_GET['table'];
		
			/*
			$tables=ghostsql_tables($fn);
			if (!isset($tables[$table])) apierror("Table [$table] not found in data dump\r\n");
			$start=$tables[$table]['start'];
			$end=$tables[$table]['end'];
			*/
			
			//read directly from positions to speed up
			$start=intval($_GET['start']);
			$end=intval($_GET['end']);
			
			$ta=microtime(1);			
			$res=ghostsql_makeslice($fn,$table,$start,$end);
			$tb=microtime(1);
			if (!isset($res)) apierror("Error extracting data. Check tempdir permission?\r\n");

			if ($format=='gyroscope'){
				echo $res['fn'].' &nbsp; &nbsp; '.number_format($res['size']).' <em>bytes</em> &nbsp; &nbsp; exported in '.number_format(($tb-$ta)*1000).' <em>ms</em>';
				die();	
			}
			echo json_encode($res); die();
			
		break;
	
		default: die('Unrecognized cmd ['.htmlspecialchars($cmd).']');	
	}
	
}

function apierror($msg){
	echo json_encode(array('error'=>$msg)); die();	
}

//print_r($argv); die();

//$fn='2022-11-13.sql';
//print_r(ghostsql_tables($fn));

//$res=ghostsql_makeslice($fn,'users',1738,1777);
//print_r($res);


