<?php

function lookupsfdx_org(){

    /*
    mkdir /var/www/.sfdx
    mkdir /var/www/.cache
    mkdir /var/www/.sf
    chmod 777 /var/www/.sfdx
    chmod 777 /var/www/.cache
    chmod 777 /var/www/.sf
    */

    $cmd="sfdx org:list --json 2>&1";
    $res=shell_exec($cmd);

    $obj=json_decode($res,1);

    if (!isset($obj)){
    ?>
    <div class="warnbox">
        sfdx is not set up correctly.<br>
    <?php  
        echo htmlspecialchars($res);
    ?>
    </div>
    <?php    
        return;
    }
    

    //echo '<pre>'; print_r($obj); echo '</pre>';

    $orgs=$obj['result']['nonScratchOrgs'];
    $sorgs=$obj['result']['scratchOrgs'];

    if (count($orgs)==0&&count($sorgs)==0){
    ?>
    <div class="warnbox">
        No organizations were authenticated.<br>
        <input class="inplong" value="su - www-data -s /bin/bash -c &quot;sfdx org:login:web -r https://[subdomain].my.salesforce.com -a [alias]&quot;">
    </div>
    <?php    
        return;
    }

    ?>
    <div class="section">
    <?php

    foreach ($orgs as $org){
        lookupsfdx_org_renderorg($org,0);
    }//foreach

    if (count($sorgs)>0){
    ?>
    <div class="sectionheaders">Scratch Orgs</div>
    <?php    
        foreach ($sorgs as $org){
            lookupsfdx_org_renderorg($org,1);
        }//foreach
    }
?>
</div><!-- section -->
<?php
}

function lookupsfdx_org_renderorg($org,$scratch=0){
    $orgid=$org['orgId'];
    $orguser=$org['username'];
    $orgurl=$org['instanceUrl'];
    $alias=$org['alias'];
?>
<div class="listitem">
    <?php 
    //echo '<pre>'; print_r($org); echo '</pre>';
    ?>
    <a onclick="picklookup('<?php echo $orguser;?>','<?php echo noapos($alias);?>');"><?php echo $orgid;?>
        <?php if ($alias!=''){
        ?>
        <span class="labelbutton"><?php echo htmlspecialchars($alias);?></span>
        <?php
        }
        ?><br>
        <?php echo htmlspecialchars($orguser);?>
    </a>
</div>
<?php    
}