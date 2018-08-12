<?php
$onLineConfig=array(
	'PAGE_STATIC_ON'=>true,
	'PAGE_STATIC_RULE'=>array(
	    array('path'=>'Home/Index/index','time'=>'m'),
        array('path'=>'Home/Coin/view','time'=>'m'),
        array('path'=>'Home/News/view','time'=>'h'),
	 ),
);


$offLineConfig=array(
	'PAGE_STATIC_ON'=>false,
	'PAGE_STATIC_RULE'=>array(
	    array('path'=>'Home/Index/index','time'=>'h'),
        array('path'=>'Home/Coin/view','time'=>'h'),
        array('path'=>'Home/News/view','time'=>'h'),
	 ),
);

if(in_local_mode())
{
	return $offLineConfig;
}
else
{
	return $onLineConfig;
}