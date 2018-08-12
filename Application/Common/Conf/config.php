<?php

$offlineConfig=array(
	'DEFAULT_MODULE' => 'Home',
    'MODULE_ALLOW_LIST' => array('Home'),
	'LOG_LEVEL'=>'ERR',
	'LOG_RECORD'=> true,
    'LOG_EXCEPTION_RECORD' => true,
	'LOAD_EXT_CONFIG'=> 'country_code,db,router,aliyun,tags',  //router一定要放到最后
);

$onlineConfig=array(
    'DEFAULT_MODULE' => 'Home',
    'MODULE_ALLOW_LIST' => array('Home'),
    'LOG_LEVEL'=>'ERR',
    'LOG_RECORD'=> true,
    'LOG_EXCEPTION_RECORD' => true,
    'LOAD_EXT_CONFIG'=> 'country_code,db,router,aliyun,tags',  //router一定要放到最后
    'ERROR_MESSAGE'        => '系统维护中,请稍候再试!',
    'TMPL_EXCEPTION_FILE'  => THINK_PATH . 'Tpl/pop_exception.tpl',  //报异常模板文件
    'SHOW_ERROR_MSG' =>true
);

if(in_local_mode())
{
    return $offlineConfig;
}
else
{
    return $onlineConfig;
}
