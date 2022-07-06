<?php

function showgyroscopeupdater(){
	
	global $db;
	global $dict_dir;
	
?>
<div style="color:#444444;padding:10px 0;line-height:1.6em;">
	<div style="text-align:right;direction:<?php echo $dict_dir;?>;">
		<span style="font-size:12px;"><?php tr('powered_by_',array('power'=>'Antradar Gyroscope '.GYROSCOPE_VERSION.' '.VENDOR_INITIAL.VENDOR_VERSION));?> &nbsp; &nbsp;</span>
		<a class="labelbutton" onclick="updategyroscope();" style="white-space:nowrap;"><?php tr('check_updates');?></a>
	</div>
	
	<div id="gyroscope_updater" style="display:none;margin-top:10px;padding:10px;border:solid 1px #999999;font-size:13px;color:#000000;">
	</div>
	
</div>
<?php		
}