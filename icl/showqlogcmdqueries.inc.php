<?php
if (!isset($_GET['sqlmode'])||$_GET['sqlmode']!='sqlite') include 'subconnect.php';
include 'pretty_array.php';

function showqlogcmdqueries(){
    $dbname=SGET('dbname');
    if ($dbname=='') return;

    $logfn='/dev/shm/'.$dbname.'.log';
    if (!file_exists($logfn)) return;

    $mycmd=SGET('cmdkey');
    if ($mycmd=='') return;

    $myqkey=SGET('qkey');

    global $db;

    sql_select_db($db,$dbname);
    $f=fopen($logfn,'r');    
?>

<div class="section">
    <div class="sectiontitle"><?php echo $dbname;?> / <?php echo $mycmd;?> / <?php echo $myqkey;?></div>

<?php   
    $qobj=null;

    while ($query=fgets($f)){

        if (trim($query)=='') continue;
        $obj=json_decode($query,1);
        $cmd=$obj['cmd'];
        $q=$obj['query'];
        $qkey=md5($q);
        if ($cmd!=$mycmd||$qkey!=$myqkey) {
            continue;
        } else {
            $qobj=$obj;
            break;
        }
    }//foreach

    fclose($f);

    if (!isset($qobj)) return;

    $params=$qobj['params'];

    $exq="explain format=json $q";

    $rs=@sql_prep($exq,$db,$params);
    $myrow=sql_fetch_assoc($rs);

    $res=json_decode($myrow['EXPLAIN'],1);

    $badtables=array();

    $looproot=&$res['query_block']['nested_loop'];
    if (!isset($looproot)) $looproot=&$res['query_block']['ordering_operation']['nested_loop'];
    if (isset($looproot)&&count($looproot)==0&&isset($res['query_block']['grouping_operation'])){
        $looproot=&$res['query_block']['grouping_operation']['nested_loop'];
    }

    foreach ($looproot as $nloop){
        if (!isset($nloop['table'])) continue;
        if (!isset($nloop['table']['possible_keys'])||count($nloop['table']['possible_keys'])==0) {
            if (!isset($nloop['table']['key'])) array_push($badtables,$nloop['table']['table_name']);
        }
    }

    ?>
    <textarea class="inplong" name="_"><?php echo htmlspecialchars($qobj['query']);?></textarea>
    <?php if (isset($qobj['params'])&&count($qobj['params'])>0){?>
        <div><b>Params:</b></div>
        <?php foreach ($qobj['params'] as $k=>$v){?>
            <div class="listitem"><?php echo $k;?> => <?php echo htmlspecialchars($v);?></div>  
        <?php } ?>
    <?php } ?>

    <?php
    //echo '<pre>'; print_r($res); echo '</pre>';

    if (count($badtables)>0){
    ?>
    <div class="warnbox">
        The following table<?php echo count($badtables)==1?' has':'s have';?> no possible keys for the above query:<br>
        <?php foreach ($badtables as $badtable) echo "<nobr><u><a onclick=\"showtable('$badtable','$dbname');\">".$badtable.'</a></u></nobr> &nbsp; ';?>
    </div>
    <?php    
    }

    pretty_array($res['query_block'],$dbname.'_'.$mycmd.'_'.$qkey);

?>
</div><!--section-->
<?php
}