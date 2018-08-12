<?php
	
	$returnConfig = array();
	
	$offlineConfig=array(
	
	   'DB_MONGO_DC'=>array(
				'DB_TYPE'               =>  'mongo',     // 数据库类型
				'DB_HOST'               =>  '172.16.100.1:30000', // 服务器地址
				'DB_NAME'               =>  '',          // 数据库名
				'DATA_DB_NAME'          =>  'datacenter',          // 数据库名
				'DB_USER'               =>  '',      // 用户名
				'DB_PWD'                =>  '',          // 密码
				'DB_PORT'               =>  '',        // 端口
				'DB_PREFIX'             =>  't_',    // 数据库表前缀
				'DB_DEBUG'              => true, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
				'DB_DEPLOY_TYPE'        => 0, // 设置分布式数据库支持
				'DB_RW_SEPARATE'        => 0, //读写分离
		),

		//默认库
		'DB_MYSQL_DEFAULT'=>array(
			'DB_TYPE'               =>  'mysql',     // 数据库类型
			'DB_HOST'               =>  '172.16.100.1', // 服务器地址
			'DB_NAME'               =>  'db_main',          // 数据库名
			'DB_USER'               =>  'root',      // 用户名
			'DB_PWD'                =>  'chwjbn',          // 密码
			'DB_PORT'               =>  '3306',        // 端口
			'DB_PREFIX'             =>  't_',    // 数据库表前缀
		),


        //默认redis
        'REDIS_DEFAULT'=>array(
            'REDIS_HOST'=>'172.16.100.1',
            'REDIS_PORT'=>6379,
            'REDIS_DB'=>0
        ),

        //缓存redis
        'REDIS_CACHE'=>array(
            'REDIS_HOST'=>'47.74.239.100',
            'REDIS_PORT'=>10086,
            'REDIS_DB'=>0,
            'REDIS_PASSWORD'=>'popcoin_180516_!@#',
        ),
	);
	
	$devlineConfig=array(
	
	   'DB_MONGO_DC'=>array(
				'DB_TYPE'               =>  'mongo',     // 数据库类型
				'DB_HOST'               =>  '172.16.100.1:30000', // 服务器地址
				'DB_NAME'               =>  '',          // 数据库名
				'DATA_DB_NAME'          =>  'datacenter',          // 数据库名
				'DB_USER'               =>  '',      // 用户名
				'DB_PWD'                =>  '',          // 密码
				'DB_PORT'               =>  '',        // 端口
				'DB_PREFIX'             =>  't_',    // 数据库表前缀
				'DB_DEBUG'              => true, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
				'DB_DEPLOY_TYPE'        => 0, // 设置分布式数据库支持
				'DB_RW_SEPARATE'        => 0, //读写分离
		),

        //默认库
        'DB_MYSQL_DEFAULT'=>array(
            'DB_TYPE'               =>  'mysql',     // 数据库类型
            'DB_HOST'               =>  '172.16.100.1', // 服务器地址
            'DB_NAME'               =>  'db_main',          // 数据库名
            'DB_USER'               =>  'root',      // 用户名
            'DB_PWD'                =>  'chwjbn',          // 密码
            'DB_PORT'               =>  '3306',        // 端口
            'DB_PREFIX'             =>  't_',    // 数据库表前缀
        ),

		//默认redis
		'REDIS_DEFAULT'=>array(
			'REDIS_HOST'=>'172.16.100.1',
			'REDIS_PORT'=>6379,
			'REDIS_DB'=>0
		),

        //缓存redis
        'REDIS_CACHE'=>array(
            'REDIS_HOST'=>'47.74.239.100',
            'REDIS_PORT'=>10086,
            'REDIS_DB'=>0,
            'REDIS_PASSWORD'=>'popcoin_180516_!@#',
        ),

	);
	
	$onlineConfig=array(
	
	   'DB_MONGO_DC'=>array(
				'DB_TYPE'               =>  'mongo',     // 数据库类型
				'DB_HOST'               =>  '172.16.100.1:30000', // 服务器地址
				'DB_NAME'               =>  '',          // 数据库名
				'DATA_DB_NAME'          =>  'datacenter',          // 数据库名
				'DB_USER'               =>  '',      // 用户名
				'DB_PWD'                =>  '',          // 密码
				'DB_PORT'               =>  '',        // 端口
				'DB_PREFIX'             =>  't_',    // 数据库表前缀
				'DB_DEBUG'              => true, // 数据库调试模式 开启后可以记录SQL日志 3.2.3新增
				'DB_DEPLOY_TYPE'        => 0, // 设置分布式数据库支持
				'DB_RW_SEPARATE'        => 0, //读写分离
		),

		//默认库
		'DB_MYSQL_DEFAULT'=>array(
			'DB_TYPE'               =>  'mysql',     // 数据库类型
			'DB_HOST'               =>  'db_master.in.popcoin.live', // 服务器地址
			'DB_NAME'               =>  'db_main',          // 数据库名
			'DB_USER'               =>  'root',      // 用户名
			'DB_PWD'                =>  'chwjbn',          // 密码
			'DB_PORT'               =>  '10086',        // 端口
			'DB_PREFIX'             =>  't_',    // 数据库表前缀
		),
	
	
		//默认redis
		'REDIS_DEFAULT'=>array(
			'REDIS_HOST'=>'172.21.90.27',
			'REDIS_PORT'=>10086,
			'REDIS_DB'=>0,
            'REDIS_PASSWORD'=>'popcoin_180516_!@#',
		),

        //缓存redis
        'REDIS_CACHE'=>array(
            'REDIS_HOST'=>'172.21.90.27',
            'REDIS_PORT'=>10086,
            'REDIS_DB'=>0,
            'REDIS_PASSWORD'=>'popcoin_180516_!@#',
        ),
	);

	
	if(in_local_mode())
	{
		$returnConfig=$offlineConfig;
        $returnConfig=$devlineConfig;
		$returnConfig['SHOW_PAGE_TRACE']=true;
	}
	elseif(in_dev())
	{
		$returnConfig=$devlineConfig;
	}
	else
	{
		$returnConfig=$onlineConfig;
	}
    //var_dump($returnConfig);
	return $returnConfig;