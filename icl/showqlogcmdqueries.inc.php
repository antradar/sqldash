<?php

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
    $log=file_get_contents($logfn);

    $queries=explode("==--==--==\r\n",$log);
    
?>

<div class="section">
    <div class="sectiontitle"><?php echo $dbname;?> / <?php echo $mycmd;?> / <?php echo $qkey;?></div>

<?php   
    $qobj=null;

    foreach ($queries as $query){
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


    if (!isset($qobj)) return;

    $params=$qobj['params'];

    $exq="explain format=json $q";

    $rs=@sql_prep($exq,$db,$params);
    $myrow=sql_fetch_assoc($rs);

    $res=json_decode($myrow['EXPLAIN'],1);

    ?>
    <textarea class="inplong" name="_"><?php echo htmlspecialchars($qobj['query']);?></textarea>
    <?php if (isset($qobj['params'])&&count($qobj['params'])>0){?>
        <div><b>Params:</b></div>
        <?php foreach ($qobj['params'] as $k=>$v){?>
            <div class="listitem"><?php echo $k;?> => <?php echo htmlspecialchars($v);?></div>  
        <?php } ?>
    <?php } ?>

    <?php
    echo '<pre>'; print_r($res); echo '</pre>';

?>
</div><!--section-->
<?php
}