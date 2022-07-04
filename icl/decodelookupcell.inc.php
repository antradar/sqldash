<?php

function decodelookupcell(){
	$fval=$_GET['fval'];
	$pfval=$_POST['pfval'];
	global $db;
	
	$valmode='inline';
	if (strlen($pfval)>strlen($fval)) {$valmode='block';$fval=$pfval;}
	
	$decoder=$_GET['decoder'];
		
	switch ($decoder){
	case 'datetime':
		$lines=explode("\n",$fval);
		foreach ($lines as $line){
			if (is_numeric($line)) $res.=date('Y-n-j H:i:s T',$line)."\r\n";
		}
	break;
	case 'phpobj':
		$res=print_r(unserialize($fval),1);
	break;
	case 'base64':
		$res=utf8_fix(base64_decode($fval));		
	break;
	case 'json':
		$res=print_r(json_decode($fval,1),1);
	break;
	case 'deflate': //technically this is an encoder
		$res=gzdeflate($fval);
		$ratio='n/a';
		if (strlen($fval)>0) $ratio=round(strlen($res)*100/strlen($fval),1).'%';
		echo "Compression ratio: $ratio\r\n";
		$tablename=$_GET['table'];
		$fkey=$_GET['fkey'];
		$pkey=noapos($_GET['pkey']);
		$pval=$_GET['pval'];

		
		$query="update $tablename set $fkey=? where $pkey=?";
		sql_prep($query,$db,array($res,$pval));
		
		echo "Compressed data is stored in DB. Reload to view the update.\r\n";
		
		return;
		
	break;
	default: echo "Error: unknown decoder";	
	}	
	
	if ($valmode=='inline'){
	?>
	<input class="inplong" style="font-size:12px;" value="<?php echo htmlspecialchars($res);?>">
	<?php			
	} else {
	?>
	<textarea class="inplong" style="line-height:1.6em;font-size:12px;"><?php echo htmlspecialchars($res);?></textarea>
	<?php		
	}
}
