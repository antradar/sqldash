<html>
<head>
<link href="https://use.fontawesome.com/releases/v5.9.0/css/all.css" rel="stylesheet">
</head>
<body>
<div style="padding:20px 0;">
	<a href="http://fortawesome.github.io/Font-Awesome/icons/" target=_blank>Find icons &raquo;</a>
</div>


<?php
$icons=array(
	array('name'=>'database','adjust'=>-2),
	array('name'=>'table','adjust'=>-2),
	array('name'=>'scroll','adjust'=>-14),

	array('name'=>'user','adjust'=>-6),
	array('name'=>'cog','adjust'=>-6),
	array('name'=>'chart-bar','adjust'=>-4),
	array('name'=>'file-contract','adjust'=>0),
	array('name'=>'folder-open','adjust'=>-6,'style'=>'r','subs'=>array(
		array('name'=>'file-image','adjust'=>-24,'dx'=>14,'dy'=>-2,'style'=>'r')
	)),
	array('name'=>'folder-open','adjust'=>0),
	array('name'=>'folder-open','adjust'=>0,'style'=>'r','subs'=>array(
		array('name'=>'camera-retro','adjust'=>-36,'dx'=>16,'dy'=>20)
	)),
	array('name'=>'flask','adjust'=>0),
	array('name'=>'calendar','adjust'=>-2,'style'=>'r','subs'=>array(
		array('name'=>'glass-martini','adjust'=>-34,'dx'=>14,'dy'=>25)
	)),
	array('name'=>'tags','adjust'=>-8),
	array('name'=>'shopping-basket','adjust'=>-12),
	array('name'=>'gift','adjust'=>0),
	array('name'=>'calendar-alt','adjust'=>-2,'style'=>'s'),
	array('name'=>'utensils','adjust'=>0),
	array('name'=>'elementor','adjust'=>0,'style'=>'b'),
	array('name'=>'comment-alt','adjust'=>-4),
	array('name'=>'map-marker','adjust'=>0)
);
?>

<div style="margin-bottom:10px;">
SD Icons:
</div>

<div class="clear"></div>
<?php

foreach ($icons as $icon){
	$subs=$icon['subs'];
	$style=$icon['style'];
	if ($style=='') $style='s';
?>

<div style="position:relative;padding:10px;float:left;">
	<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="color:#72ADDE;font-size:<?php echo 32+ceil($icon['adjust']/2);?>px;margin-right:10px;position:relative;">
	<?php
	if (is_array($subs)){
		foreach ($subs as $sub){
			$sstyle=$sub['style'];
			if ($sstyle=='') $sstyle='s';
	?>
	<span class="fa<?php echo $sstyle;?> fa-<?php echo $sub['name'];?>" style="color:#72ADDE;font-size:<?php echo 32+ceil($sub['adjust']/2);?>px;position:absolute;top:<?php echo floor($sub['dy']/2);?>px;left:<?php echo floor($sub['dx']/2);?>px;"></span>
	<?php		
		}//foreach	
	}//subs
	?>
	</span>
	
	<?php
	if (is_array($subs)){
	?>
	<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="color:#72ADDE;font-size:<?php echo 32+ceil($icon['adjust']/2);?>px;margin-right:10px;position:relative;">
	<?php
		foreach ($subs as $sub){
			$sstyle=$sub['style'];
			if ($sstyle=='') $sstyle='s';
	?>
	+ <span class="fa<?php echo $sstyle;?> fa-<?php echo $sub['name'];?>" style="color:#72ADDE;font-size:<?php echo 32+ceil($sub['adjust']/2);?>px;"></span>
	<?php
		}
	}
	?>	
</div>

<div style="padding:10px;background:#3C3839;float:left;">
<div style="position:relative;">	
	<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="position:relative;color:#ffffff;font-size:<?php echo 24+floor($icon['adjust']*12/32);?>px;margin-right:10px;">
	<?php 
	if (is_array($subs)){
		foreach ($subs as $sub){
			$sstyle=$sub['style'];
			if ($sstyle=='') $sstyle='s';
	?>
	<span class="fa<?php echo $sstyle;?> fa-<?php echo $sub['name'];?>" style="color:#ffffff;font-size:<?php echo 24+ceil($sub['adjust']*12/32);?>px;position:absolute;top:<?php echo floor($sub['dy']*12/32);?>px;left:<?php echo floor($sub['dx']*12/32);?>px;"></span>
	<?php		
		}//foreach	
	}//subs
	?>
	</span>
	<?php
	if (is_array($subs)){
	?>
	<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="color:#ffffff;font-size:<?php echo 24+ceil($icon['adjust']*12/32);?>px;"></span>
	<?php
		foreach ($subs as $sub){
			$sstyle=$sub['style'];
			if ($sstyle=='') $sstyle='s';
	?>
	<span style="color:#ffffff;">+</span> <span class="fa<?php echo $sstyle;?> fa-<?php echo $sub['name'];?>" style="color:#ffffff;font-size:<?php echo 24+ceil($sub['adjust']*12/32);?>px;"></span>	
	<?php
		}
	}
	?>
</div>
</div>

<?php }?>


<div style="clear:both;margin-bottom:40px;"></div>

<div style="margin-bottom:10px;">
SD Tab Icons:
</div>

<div class="clear"></div>
<?php

foreach ($icons as $icon){
	$subs=$icon['subs'];
	$style=$icon['style'];
	if ($style=='') $style='s';
?>

<div style="position:relative;padding:10px;float:left;">
	<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="color:#666666;font-size:<?php echo 16+round($icon['adjust']/4);?>px;margin-right:10px;position:relative;"></span>
</div>

<div style="padding:10px;background:#CCCCCC;float:left;">
	<div style="position:relative;">	
		<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="position:relative;color:#666666;font-size:<?php echo 16+round($icon['adjust']/4);?>px;margin-right:10px;"></span>
	</div>
</div>


<?php }?>

<div style="clear:both;padding-top:20px;margin-bottom:10px;">
HD Tab Icons:
</div>

<div class="clear"></div>
<?php

foreach ($icons as $icon){
	$subs=$icon['subs'];
	$style=$icon['style'];
	if ($style=='') $style='s';
?>

<div style="position:relative;padding:10px;float:left;">
	<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="color:#666666;font-size:<?php echo 32+round($icon['adjust']/2);?>px;margin-right:10px;position:relative;"></span>
</div>

<div style="padding:10px;background:#CCCCCC;float:left;">
	<div style="position:relative;">	
		<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="position:relative;color:#666666;font-size:<?php echo 32+round($icon['adjust']/2);?>px;margin-right:10px;"></span>
	</div>
</div>


<?php }?>

<div style="clear:both;padding-top:20px;margin-bottom:10px;">
	HD Icons:
</div>

<?php foreach ($icons as $icon){
	$subs=$icon['subs'];
	$style=$icon['style'];
	if ($style=='') $style='s';
?>

<div style="position:relative;padding:10px;float:left;">
	<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="position:relative;color:#72ADDE;font-size:<?php echo 64+$icon['adjust'];?>px;">
	<?php
	if (is_array($subs)){
		foreach ($subs as $sub){
			$sstyle=$sub['style'];
			if ($sstyle=='') $sstyle='s';
	?>
	<span class="fa<?php echo $sstyle;?> fa-<?php echo $sub['name'];?>" style="color:#72ADDE;font-size:<?php echo 64+$sub['adjust'];?>px;position:absolute;top:<?php echo $sub['dy'];?>px;left:<?php echo $sub['dx'];?>px;"></span>
	<?php		
		}//foreach	
	}//subs
	?>
	</span>
	
	<?php
	if (is_array($subs)){
	?>
	<span class="fa fa-<?php echo $icon['name'];?>" style="position:relative;color:#72ADDE;font-size:<?php echo 64+$icon['adjust'];?>px;">
	<?php
		foreach ($subs as $sub){
			$sstyle=$sub['style'];
			if ($sstyle=='') $sstyle='s';
	?>
	<span style="font-size:22px;vertical-align:middle;">+</span> <span class="fa<?php echo $sstyle;?> fa-<?php echo $sub['name'];?>" style="color:#72ADDE;font-size:<?php echo 64+$sub['adjust'];?>px;"></span>
	<?php
		}
	}
	?>		
</div>

<div style="padding:10px;background:#3C3839;float:left;">
<div style="position:relative;">	
	<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="position:relative;color:#ffffff;font-size:<?php echo 48+$icon['adjust'];?>px;">
	<?php
	if (is_array($subs)){
		foreach ($subs as $sub){
			$sstyle=$sub['style'];
			if ($sstyle=='') $sstyle='s';
	?>
	<span class="fa<?php echo $sstyle;?> fa-<?php echo $sub['name'];?>" style="color:#ffffff;font-size:<?php echo 48+ceil($sub['adjust']*24/32);?>px;position:absolute;top:<?php echo floor($sub['dy']*24/32);?>px;left:<?php echo floor($sub['dx']*24/32);?>px;"></span>
	<?php		
		}//foreach	
	}//subs
	?>
	</span>
	<?php
	if (is_array($subs)){
	?>
	<span class="fa<?php echo $style;?> fa-<?php echo $icon['name'];?>" style="position:relative;color:#ffffff;font-size:<?php echo 48+$icon['adjust'];?>px;">
	<?php
		foreach ($subs as $sub){
			$sstyle=$sub['style'];
			if ($sstyle='') $sstyle='s';
	?>
	<span style="color:#ffffff;font-size:20px;vertical-align:middle;">+</span> 
	<span class="fa<?php echo $sstyle;?> fa-<?php echo $sub['name'];?>" style="color:#ffffff;font-size:<?php echo 48+ceil($sub['adjust']*24/32);?>px;"></span>
	<?php
		}
	}
	?>	
</div>
</div>


<?php }?>

<div style="clear:both;"></div>

</body>
</html>
