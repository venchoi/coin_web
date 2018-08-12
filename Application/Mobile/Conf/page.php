<?php
$onLineConfig=array(
	'PAGE_STATIC_ON'=>true,
	'PAGE_STATIC_RULE'=>array(
	    array('path'=>'Mobile/Index/index','time'=>'m'),
        array('path'=>'Mobile/Coin/view','time'=>'h'),
        array('path'=>'Mobile/News/view','time'=>'h'),
	 ),
);


$offLineConfig=array(
	'PAGE_STATIC_ON'=>false,
	'PAGE_STATIC_RULE'=>array(
	    array('path'=>'Mobile/Index/index','time'=>'h'),
        array('path'=>'Mobile/Coin/view','time'=>'h'),
        array('path'=>'Mobile/News/view','time'=>'h'),
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