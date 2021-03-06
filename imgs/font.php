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
	array('name'=>'scroll','adjust'=>-8),

	array('name'=>'user','adjust'=>0),
	array('name'=>'stethoscope','adjust'=>0),
	array('name'=>'user-md','adjust'=>0),
	array('name'=>'ambulance','adjust'=>-2),
	array('name'=>'flask','adjust'=>0),
	array('name'=>'car','adjust'=>-4),
	array('name'=>'tags','adjust'=>-4),
	array('name'=>'map-marker','adjust'=>0)
);

foreach ($icons as $icon){
?>

<div style="position:relative;padding:10px;float:left;">
	<span class="fa fa-<?php echo $icon['name'];?>" style="color:#72ADDE;font-size:<?php echo 32+$icon['adjust'];?>px;margin-right:10px;"></span>	
</div>

<div style="padding:10px;background:#3C3839;float:left;">
<div style="position:relative;">	
	<span class="fa fa-<?php echo $icon['name'];?>" style="color:#ffffff;font-size:<?php echo 24+$icon['adjust'];?>px;margin-right:10px;"></span>
</div>
</div>

<?php }?>

<div style="clear:both;margin-bottom:40px;"></div>

<?php foreach ($icons as $icon){?>

<div style="position:relative;padding:10px;float:left;">
	<span class="fa fa-<?php echo $icon['name'];?>" style="color:#72ADDE;font-size:<?php echo 64+$icon['adjust']*2;?>px;"></span>
</div>

<div style="padding:10px;background:#3C3839;float:left;">
<div style="position:relative;">	
	<span class="fa fa-<?php echo $icon['name'];?>" style="color:#ffffff;font-size:<?php echo 48+$icon['adjust']*2;?>px;"></span>
</div>
</div>


<?php }?>

<div style="clear:both;"></div>

</body>
</html>
